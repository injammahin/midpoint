<?php

namespace App\Mail;

use App\Models\SecureTransaction;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionStatusUpdateMail extends Mailable
{
    use Queueable;
    use SerializesModels;


    public SecureTransaction $transaction;

    public string $heading;

    public string $statusMessage;

    public string $actionText;

    public string $actionUrl;

    public ?string $badgeText;


    public function __construct(
        SecureTransaction $transaction,
        string $heading,
        string $statusMessage,
        string $actionText,
        string $actionUrl,
        ?string $badgeText = null
    ) {

        $this->transaction =
            $transaction;


        $this->heading =
            $heading;


        $this->statusMessage =
            $statusMessage;


        $this->actionText =
            $actionText;


        $this->actionUrl =
            $actionUrl;


        $this->badgeText =
            $badgeText;


        $this->transaction
            ->loadMissing([
                'seller',
                'buyer',
            ]);
    }


    public function build()
    {
        return $this

            ->subject(
                $this->heading
                .
                ' - '
                .
                $this->transaction
                    ->reference
            )

            ->view(
                'emails.transactions.status-update'
            );
    }
}