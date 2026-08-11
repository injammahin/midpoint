<?php

namespace App\Services;

use App\Models\SecureTransaction;
use App\Models\TransactionEmailDelivery;

use Illuminate\Mail\Mailable;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use Throwable;

class TransactionEmailDeliveryService
{
    public function send(
        SecureTransaction $transaction,
        string $eventKey,
        string $audience,
        string $email,
        string $subject,
        Mailable $mailable
    ): bool {
        $email =
            strtolower(
                trim($email)
            );

        if ($email === '') {
            return false;
        }

        $delivery =
            TransactionEmailDelivery::firstOrCreate(
                [
                    'secure_transaction_id' =>
                        $transaction->id,

                    'event_key' =>
                        $eventKey,

                    'audience' =>
                        $audience,

                    'email' =>
                        $email,
                ],
                [
                    'subject' =>
                        $subject,
                ]
            );

        if ($delivery->sent_at) {
            return true;
        }

        $lockKey =
            'transaction-email:'
            .
            $transaction->id
            .
            ':'
            .
            sha1(
                $eventKey
                .
                '|'
                .
                $audience
                .
                '|'
                .
                $email
            );

        try {
            return Cache::lock(
                $lockKey,
                30
            )->block(
                5,
                function () use (
                    $delivery,
                    $email,
                    $mailable,
                    $subject
                ) {
                    $delivery->refresh();

                    if ($delivery->sent_at) {
                        return true;
                    }

                    $delivery->increment(
                        'attempts'
                    );

                    $delivery->update([
                        'last_attempt_at' =>
                            now(),

                        'subject' =>
                            $subject,

                        'failed_at' =>
                            null,

                        'last_error' =>
                            null,
                    ]);

                    try {
                        Mail::to(
                            $email
                        )->send(
                            $mailable
                        );

                        $delivery->update([
                            'sent_at' =>
                                now(),

                            'failed_at' =>
                                null,

                            'last_error' =>
                                null,
                        ]);

                        return true;

                    } catch (Throwable $exception) {
                        $delivery->update([
                            'failed_at' =>
                                now(),

                            'last_error' =>
                                mb_substr(
                                    $exception->getMessage(),
                                    0,
                                    5000
                                ),
                        ]);

                        Log::error(
                            'Transaction email delivery failed.',
                            [
                                'delivery_id' =>
                                    $delivery->id,

                                'transaction_id' =>
                                    $delivery
                                        ->secure_transaction_id,

                                'event_key' =>
                                    $delivery->event_key,

                                'audience' =>
                                    $delivery->audience,

                                'email' =>
                                    $email,

                                'error' =>
                                    $exception
                                        ->getMessage(),
                            ]
                        );

                        return false;
                    }
                }
            );

        } catch (Throwable $exception) {
            Log::error(
                'Transaction email lock failed.',
                [
                    'transaction_id' =>
                        $transaction->id,

                    'event_key' =>
                        $eventKey,

                    'error' =>
                        $exception->getMessage(),
                ]
            );

            return false;
        }
    }
}