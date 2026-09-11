<?php

namespace App\Support;

use App\Models\SellerKycVerification;
use RuntimeException;

class KycIdentityFingerprint
{
    protected string $key;

    public function __construct(?string $key = null)
    {
        $this->key = trim(
            (string) (
                $key
                ?? config('midpoint.kyc.fingerprint_key')
            )
        );

        if ($this->key === '') {
            throw new RuntimeException(
                'MIDPOINT_KYC_FINGERPRINT_KEY is not configured.'
            );
        }
    }

    public function make(string $bvn): string
    {
        $normalized = preg_replace(
            '/\D+/',
            '',
            $bvn
        );

        if (strlen($normalized) !== 11) {
            throw new RuntimeException(
                'A valid 11-digit BVN is required.'
            );
        }

        return hash_hmac(
            'sha256',
            $normalized,
            $this->key
        );
    }

    public function submittedNameMatches(
        SellerKycVerification $verifiedKyc,
        string $firstName,
        string $middleName,
        string $lastName
    ): bool {
        $submittedLegalName = trim(
            implode(
                ' ',
                array_filter([
                    $firstName,
                    $middleName,
                    $lastName,
                ])
            )
        );

        $storedLegalName = trim(
            (string) $verifiedKyc->legal_name
        );

        if (
            $storedLegalName === ''
            ||
            !hash_equals(
                $this->normalizeName($storedLegalName),
                $this->normalizeName($submittedLegalName)
            )
        ) {
            return false;
        }

        $verifiedFirstName = trim(
            (string) $verifiedKyc->identity_first_name
        );

        $verifiedLastName = trim(
            (string) $verifiedKyc->identity_last_name
        );

        if (
            $verifiedFirstName === ''
            ||
            $verifiedLastName === ''
        ) {
            $parts = preg_split(
                '/\s+/',
                $storedLegalName,
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            if (count($parts) < 2) {
                return false;
            }

            $verifiedFirstName = $parts[0];
            $verifiedLastName = $parts[count($parts) - 1];
        }

        return
            hash_equals(
                $this->normalizeName($verifiedFirstName),
                $this->normalizeName($firstName)
            )
            &&
            hash_equals(
                $this->normalizeName($verifiedLastName),
                $this->normalizeName($lastName)
            );
    }

    protected function normalizeName(string $value): string
    {
        $ascii = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $value
        );

        if ($ascii !== false) {
            $value = $ascii;
        }

        return strtolower(
            preg_replace(
                '/[^a-z0-9]+/i',
                '',
                $value
            )
        );
    }
}