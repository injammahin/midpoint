<?php

namespace App\Console\Commands;

use App\Services\PaystackService;
use Illuminate\Console\Command;
use Throwable;

class InspectPaystackKyc extends Command
{
    protected $signature = 'midpoint:inspect-paystack-kyc';

    protected $description = 'Read-only inspection of Paystack KYC response shapes; no identity values are printed';

    public function handle(): int
    {
        $this->info('Read-only check. This command does not approve KYC or change customer records.');

        try {
            // Enter the email/code privately, rather than in shell history.
            $lookup = trim((string) $this->secret('Seller email or Paystack customer code', false));

            if ($lookup === '') {
                $this->error('A seller email or customer code is required.');

                return self::FAILURE;
            }

            // Resolve inside the try block so configuration errors are also caught.
            $customer = app(PaystackService::class)->fetchCustomer($lookup);
        } catch (Throwable $exception) {
            // Never print/report the raw exception: it may include private data.
            $message = 'Customer lookup failed. Check the Paystack configuration and connection.';

            if (preg_match('/\[HTTP ([0-9]{3})\]/', $exception->getMessage(), $matches) === 1) {
                $message .= ' HTTP status: ' . $matches[1] . '.';
            }

            $this->error($message);

            return self::FAILURE;
        }

        if ($customer === null) {
            $this->warn('No customer was found on this Paystack integration.');

            return self::SUCCESS;
        }

        $key = (string) config('services.paystack.secret_key', '');
        $mode = str_starts_with($key, 'sk_live_') ? 'live'
            : (str_starts_with($key, 'sk_test_') ? 'test' : 'unknown');

        $identified = $customer['identified'] ?? null;
        $identifications = $customer['identifications'] ?? null;

        $rows = [
            ['API key mode', $mode],
            ['Customer found', 'yes'],
            ['identified', $identified === true ? 'true' : ($identified === false ? 'false' : 'missing/non-boolean')],
            ['identifications container', gettype($identifications)],
            ['metadata present (not inspected or trusted)', empty($customer['metadata']) ? 'no' : 'yes'],
        ];

        // Report field shapes only. Never print names, BVNs, account numbers,
        // authorization details, response bodies, or metadata values.
        $rows = array_merge($rows, $this->describeFields('customer', $customer));

        if (is_array($identifications) && $identifications !== []) {
            $records = array_is_list($identifications) ? $identifications : [$identifications];

            foreach (array_slice($records, 0, 10) as $index => $record) {
                if (is_array($record)) {
                    $rows = array_merge($rows, $this->describeFields('identification ' . ($index + 1), $record));
                }
            }
        }

        $this->table(['Check', 'Result (no private values)'], $rows);
        $this->warn('Field presence is diagnostic only; it is not proof that a submitted BVN or bank account is verified.');
        $this->info('No KYC, bank account, or Paystack customer was changed.');

        return self::SUCCESS;
    }

    private function describeFields(string $label, array $record): array
    {
        $rows = [];

        foreach (['bvn' => 11, 'bvn_number' => 11, 'value' => 11, 'account_number' => 10] as $field => $digits) {
            $value = $record[$field] ?? null;

            if ($value === null || $value === '') {
                $shape = 'missing/empty';
            } elseif (!is_string($value) && !is_int($value)) {
                $shape = 'non-scalar';
            } elseif (preg_match('/\A[0-9]{' . $digits . '}\z/', (string) $value) === 1) {
                $shape = 'full-length numeric value present (not independently verified)';
            } else {
                $shape = 'masked or another format';
            }

            $rows[] = [$label . ': ' . $field, $shape];
        }

        return $rows;
    }
}