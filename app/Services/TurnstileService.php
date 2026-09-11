<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TurnstileService
{
    /**
     * Return the public key used by the browser widget.
     */
    public function siteKey(): string
    {
        return trim(
            (string) config('services.turnstile.site_key', '')
        );
    }

    /**
     * Confirm that both required Turnstile keys are configured.
     */
    public function isConfigured(): bool
    {
        return $this->siteKey() !== ''
            && $this->secretKey() !== '';
    }

    /**
     * Validate a browser-generated token with Cloudflare Siteverify.
     *
     * The verification intentionally fails closed when keys are missing,
     * Cloudflare is unavailable, or any expected response value differs.
     */
    public function verify(?string $token, ?string $ipAddress): bool
    {
        $token = trim((string) $token);

        if (
            !$this->isConfigured()
            || $token === ''
            || strlen($token) > 2048
        ) {
            Log::warning('Turnstile validation could not start.', [
                'configured' => $this->isConfigured(),
                'token_present' => $token !== '',
                'ip_address' => $ipAddress,
            ]);

            return false;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(
                    max(
                        3,
                        (int) config(
                            'services.turnstile.timeout_seconds',
                            10
                        )
                    )
                )
                ->post(
                    (string) config(
                        'services.turnstile.verify_url',
                        'https://challenges.cloudflare.com/turnstile/v0/siteverify'
                    ),
                    [
                        'secret' => $this->secretKey(),
                        'response' => $token,
                        'remoteip' => $ipAddress,
                        'idempotency_key' => (string) Str::uuid(),
                    ]
                );
        } catch (Throwable $exception) {
            Log::warning('Turnstile Siteverify request failed.', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'ip_address' => $ipAddress,
            ]);

            return false;
        }

        if (!$response->successful()) {
            Log::warning('Turnstile Siteverify returned an HTTP error.', [
                'http_status' => $response->status(),
                'ip_address' => $ipAddress,
            ]);

            return false;
        }

        $result = $response->json();

        if (!is_array($result) || ($result['success'] ?? false) !== true) {
            Log::notice('Turnstile rejected a contact submission.', [
                'error_codes' => is_array($result)
                    ? ($result['error-codes'] ?? [])
                    : ['invalid-response'],
                'ip_address' => $ipAddress,
            ]);

            return false;
        }

        $expectedAction = trim(
            (string) config('services.turnstile.action', 'contact_form')
        );

        if (
            $expectedAction !== ''
            && (string) ($result['action'] ?? '') !== $expectedAction
        ) {
            Log::notice('Turnstile action mismatch.', [
                'expected_action' => $expectedAction,
                'received_action' => $result['action'] ?? null,
                'ip_address' => $ipAddress,
            ]);

            return false;
        }

        $expectedHostname = strtolower(
            trim(
                (string) config(
                    'services.turnstile.expected_hostname',
                    ''
                )
            )
        );

        if (
            $expectedHostname !== ''
            && strtolower((string) ($result['hostname'] ?? ''))
                !== $expectedHostname
        ) {
            Log::notice('Turnstile hostname mismatch.', [
                'expected_hostname' => $expectedHostname,
                'received_hostname' => $result['hostname'] ?? null,
                'ip_address' => $ipAddress,
            ]);

            return false;
        }

        return true;
    }

    private function secretKey(): string
    {
        return trim(
            (string) config('services.turnstile.secret_key', '')
        );
    }
}
