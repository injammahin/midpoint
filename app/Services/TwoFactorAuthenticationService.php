<?php

namespace App\Services;

use App\Models\User;

class TwoFactorAuthenticationService
{
    /*
    |--------------------------------------------------------------------------
    | TOTP Settings
    |--------------------------------------------------------------------------
    */

    private const DIGITS =
        6;


    private const PERIOD =
        30;


    private const BASE32_ALPHABET =
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';


    /*
    |--------------------------------------------------------------------------
    | Generate Secret
    |--------------------------------------------------------------------------
    */

    public function generateSecret(
        int $length = 32
    ): string {

        $secret =
            '';


        $alphabet =
            self::BASE32_ALPHABET;


        $max =
            strlen(
                $alphabet
            )
            -
            1;


        for (
            $i = 0;
            $i < $length;
            $i++
        ) {

            $secret .=
                $alphabet[
                    random_int(
                        0,
                        $max
                    )
                ];
        }


        return $secret;
    }


    /*
    |--------------------------------------------------------------------------
    | Authenticator URI
    |--------------------------------------------------------------------------
    */

    public function otpAuthUri(
        User $user,
        string $secret
    ): string {

        $issuer =
            'MidPoint';


        $account =
            $user->email;


        $label =
            rawurlencode(
                $issuer
                .
                ':'
                .
                $account
            );


        return
            'otpauth://totp/'
            .
            $label
            .
            '?secret='
            .
            rawurlencode(
                $secret
            )
            .
            '&issuer='
            .
            rawurlencode(
                $issuer
            )
            .
            '&algorithm=SHA1'
            .
            '&digits='
            .
            self::DIGITS
            .
            '&period='
            .
            self::PERIOD;
    }


    /*
    |--------------------------------------------------------------------------
    | Verify TOTP
    |--------------------------------------------------------------------------
    */

    public function verifyCode(
        string $secret,
        string $code,
        int $window = 1
    ): bool {

        $code =
            preg_replace(
                '/\D/',
                '',
                $code
            );


        if (
            strlen(
                $code
            )
            !==
            self::DIGITS
        ) {

            return false;
        }


        $counter =
            (int)
            floor(
                time()
                /
                self::PERIOD
            );


        for (
            $offset = -$window;
            $offset <= $window;
            $offset++
        ) {

            $candidate =
                $this->generateCode(
                    $secret,
                    $counter
                    +
                    $offset
                );


            if (
                hash_equals(
                    $candidate,
                    $code
                )
            ) {

                return true;
            }
        }


        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate TOTP
    |--------------------------------------------------------------------------
    */

    private function generateCode(
        string $secret,
        int $counter
    ): string {

        $key =
            $this->base32Decode(
                $secret
            );


        /*
        |--------------------------------------------------------------------------
        | 64-bit counter, big-endian
        |--------------------------------------------------------------------------
        */

        $high =
            ($counter >> 32)
            &
            0xFFFFFFFF;


        $low =
            $counter
            &
            0xFFFFFFFF;


        $binaryCounter =
            pack(
                'N2',
                $high,
                $low
            );


        $hash =
            hash_hmac(
                'sha1',
                $binaryCounter,
                $key,
                true
            );


        $offset =
            ord(
                $hash[
                    strlen(
                        $hash
                    )
                    -
                    1
                ]
            )
            &
            0x0F;


        $binary =

            (
                (
                    ord(
                        $hash[$offset]
                    )
                    &
                    0x7F
                )
                <<
                24
            )

            |

            (
                (
                    ord(
                        $hash[
                            $offset + 1
                        ]
                    )
                    &
                    0xFF
                )
                <<
                16
            )

            |

            (
                (
                    ord(
                        $hash[
                            $offset + 2
                        ]
                    )
                    &
                    0xFF
                )
                <<
                8
            )

            |

            (
                ord(
                    $hash[
                        $offset + 3
                    ]
                )
                &
                0xFF
            );


        $otp =
            $binary
            %
            (10 ** self::DIGITS);


        return str_pad(
            (string)
            $otp,
            self::DIGITS,
            '0',
            STR_PAD_LEFT
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Decode Base32
    |--------------------------------------------------------------------------
    */

    private function base32Decode(
        string $secret
    ): string {

        $secret =
            strtoupper(
                preg_replace(
                    '/[^A-Z2-7]/',
                    '',
                    $secret
                )
            );


        $buffer =
            0;


        $bitsLeft =
            0;


        $output =
            '';


        for (
            $i = 0;
            $i < strlen($secret);
            $i++
        ) {

            $value =
                strpos(
                    self::BASE32_ALPHABET,
                    $secret[$i]
                );


            if (
                $value === false
            ) {

                continue;
            }


            $buffer =
                ($buffer << 5)
                |
                $value;


            $bitsLeft +=
                5;


            if (
                $bitsLeft >= 8
            ) {

                $bitsLeft -=
                    8;


                $output .=
                    chr(
                        (
                            $buffer
                            >>
                            $bitsLeft
                        )
                        &
                        0xFF
                    );
            }
        }


        return $output;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Recovery Codes
    |--------------------------------------------------------------------------
    */

    public function generateRecoveryCodes(
        int $count = 8
    ): array {

        $codes =
            [];


        for (
            $i = 0;
            $i < $count;
            $i++
        ) {

            $codes[] =
                $this->recoveryPart()
                .
                '-'
                .
                $this->recoveryPart();
        }


        return $codes;
    }


    private function recoveryPart(): string
    {
        $characters =
            'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';


        $code =
            '';


        for (
            $i = 0;
            $i < 4;
            $i++
        ) {

            $code .=
                $characters[
                    random_int(
                        0,
                        strlen(
                            $characters
                        )
                        -
                        1
                    )
                ];
        }


        return $code;
    }


    /*
    |--------------------------------------------------------------------------
    | Recovery Hash
    |--------------------------------------------------------------------------
    */

    public function hashRecoveryCode(
        string $code
    ): string {

        return hash(
            'sha256',
            $this->normalizeRecoveryCode(
                $code
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Hash Recovery Codes
    |--------------------------------------------------------------------------
    */

    public function hashRecoveryCodes(
        array $codes
    ): array {

        return array_map(
            fn ($code) =>
                $this->hashRecoveryCode(
                    $code
                ),
            $codes
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Consume Recovery Code
    |--------------------------------------------------------------------------
    */

    public function consumeRecoveryCode(
        User $user,
        string $code
    ): bool {

        $stored =
            json_decode(
                $user
                    ->two_factor_recovery_codes
                ?:
                '[]',
                true
            );


        if (
            !is_array(
                $stored
            )
        ) {

            return false;
        }


        $hash =
            $this->hashRecoveryCode(
                $code
            );


        foreach (
            $stored
            as
            $index =>
            $storedHash
        ) {

            if (
                hash_equals(
                    (string)
                    $storedHash,
                    $hash
                )
            ) {

                unset(
                    $stored[
                        $index
                    ]
                );


                $user->forceFill([

                    'two_factor_recovery_codes' =>
                        json_encode(
                            array_values(
                                $stored
                            )
                        ),

                ])->saveQuietly();


                return true;
            }
        }


        return false;
    }


    private function normalizeRecoveryCode(
        string $code
    ): string {

        return strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                $code
            )
        );
    }
}