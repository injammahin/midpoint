<?php

namespace App\Services;

use App\Exceptions\DojahKycException;
use App\Models\SellerKycVerification;
use App\Models\SellerWithdrawalAccount;
use App\Models\User;
use App\Support\IdentityNameMatcher;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class AutomatedSellerKycService
{
    public function __construct(
        protected DojahKycService $dojah
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Automatically Verify Seller
    |--------------------------------------------------------------------------
    */

    public function verify(
        User $seller,
        array $data,
        UploadedFile $selfie
    ): SellerKycVerification {

        /*
        |--------------------------------------------------------------------------
        | Active Verified Bank Is Required First
        |--------------------------------------------------------------------------
        */

        $activeBank =
            SellerWithdrawalAccount::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->where(
                    'is_verified',
                    true
                )

                ->where(
                    'is_active',
                    true
                )

                ->first();


        if (
            !$activeBank
        ) {

            throw ValidationException::withMessages([
                'id_number' =>
                    'Add and activate a verified withdrawal bank account before completing KYC.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Reverification Of Approved KYC
        |--------------------------------------------------------------------------
        */

        $kyc =
            SellerKycVerification::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->first();


        if (
            $kyc
            &&
            $kyc->status
            ===
            SellerKycVerification::STATUS_APPROVED
        ) {

            return $kyc;
        }


        /*
        |--------------------------------------------------------------------------
        | Process Selfie In Memory
        |--------------------------------------------------------------------------
        |
        | We are NOT moving the selfie into public or permanent storage.
        |
        */

        $selfieContents =
            file_get_contents(
                $selfie->getRealPath()
            );


        if (
            $selfieContents === false
        ) {

            throw ValidationException::withMessages([
                'selfie' =>
                    'The selfie could not be read. Please upload it again.',
            ]);
        }


        $selfieBase64 =
            base64_encode(
                $selfieContents
            );


        /*
        |--------------------------------------------------------------------------
        | Create / Update Processing Record
        |--------------------------------------------------------------------------
        */

        $kyc =
            DB::transaction(
                function () use (
                    $seller,
                    $kyc,
                    $activeBank,
                    $data
                ) {

                    $record =
                        $kyc
                        ??
                        new SellerKycVerification();


                    /*
                     * Delete old legacy stored KYC files if present.
                     */
                    foreach (
                        [
                            $record->document_front_path,
                            $record->document_back_path,
                            $record->selfie_path,
                        ]
                        as
                        $oldPath
                    ) {

                        if (
                            $oldPath
                        ) {

                            Storage::disk(
                                'local'
                            )
                                ->delete(
                                    $oldPath
                                );
                        }
                    }


                    $record->fill([
                        'seller_id' =>
                            $seller->id,

                        'legal_name' =>
                            trim(
                                $data[
                                    'legal_name'
                                ]
                            ),

                        'date_of_birth' =>
                            $data[
                                'date_of_birth'
                            ],

                        'country_code' =>
                            'NG',

                        'id_type' =>
                            $data[
                                'id_type'
                            ],

                        'id_number' =>
                            $data[
                                'id_number'
                            ],

                        /*
                         * Legacy columns remain present,
                         * but automated KYC doesn't retain these files.
                         */
                        'document_front_path' =>
                            '',

                        'document_back_path' =>
                            null,

                        'selfie_path' =>
                            '',

                        'status' =>
                            SellerKycVerification::STATUS_PROCESSING,

                        'verification_method' =>
                            'automated',

                        'provider' =>
                            'dojah',

                        'provider_environment' =>
                            $this
                                ->providerEnvironment(),

                        'provider_status' =>
                            'processing',

                        'seller_withdrawal_account_id' =>
                            $activeBank->id,

                        'failure_code' =>
                            null,

                        'failure_message' =>
                            null,

                        'rejection_reason' =>
                            null,

                        'provider_response' =>
                            null,

                        'liveness_passed' =>
                            null,

                        'liveness_probability' =>
                            null,

                        'face_match' =>
                            null,

                        'face_confidence' =>
                            null,

                        'name_match' =>
                            null,

                        'dob_match' =>
                            null,

                        'bank_name_match' =>
                            null,

                        'submitted_at' =>
                            now(),

                        'last_verification_attempt_at' =>
                            now(),

                        'verification_attempts' =>
                            (
                                (int)
                                $record->verification_attempts
                            )
                            +
                            1,

                        'approved_at' =>
                            null,

                        'rejected_at' =>
                            null,

                        'auto_verified_at' =>
                            null,

                        'reviewed_by' =>
                            null,

                        'reviewed_at' =>
                            null,
                    ]);


                    $record->save();


                    return $record;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Provider Calls
        |--------------------------------------------------------------------------
        */

        try {

            if (
                $this
                    ->shouldFakeLocally()
            ) {

                $results =
                    $this
                        ->fakeVerification(
                            $data
                        );

            } else {

                /*
                |--------------------------------------------------------------------------
                | 1. Liveness
                |--------------------------------------------------------------------------
                */

                $livenessResponse =
                    $this
                        ->dojah
                        ->checkLiveness(
                            $selfieBase64
                        );


                /*
                |--------------------------------------------------------------------------
                | 2. Government Identity + Selfie Match
                |--------------------------------------------------------------------------
                */

                $identityResponse =
                    $this
                        ->dojah
                        ->verifyIdentityWithSelfie(
                            $data[
                                'id_type'
                            ],
                            $data[
                                'id_number'
                            ],
                            $selfieBase64
                        );


                $results =
                    $this
                        ->parseProviderResults(
                            $data[
                                'id_type'
                            ],
                            $livenessResponse,
                            $identityResponse
                        );
            }


        } catch (
            DojahKycException $exception
        ) {

            return $this
                ->handleProviderFailure(
                    $kyc,
                    $exception
                );

        } catch (
            Throwable $exception
        ) {

            Log::error(
                'Automated seller KYC failed unexpectedly.',
                [
                    'seller_id' =>
                        $seller->id,

                    'error' =>
                        $exception
                            ->getMessage(),
                ]
            );


            return $this
                ->markProviderError(
                    $kyc,
                    'unexpected_error',
                    'Identity verification is temporarily unavailable. Please try again.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Extract Identity
        |--------------------------------------------------------------------------
        */

        $officialFullName =
            trim(
                implode(
                    ' ',
                    array_filter([
                        $results[
                            'first_name'
                        ],

                        $results[
                            'middle_name'
                        ],

                        $results[
                            'last_name'
                        ],
                    ])
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Automated Matching
        |--------------------------------------------------------------------------
        */

        $nameMatch =
            IdentityNameMatcher::matches(
                $data[
                    'legal_name'
                ],
                $officialFullName
            );


        $dobMatch =
            $this
                ->datesMatch(
                    $data[
                        'date_of_birth'
                    ],
                    $results[
                        'date_of_birth'
                    ]
                );


        $bankNameMatch =
            IdentityNameMatcher::matches(
                $officialFullName,
                $activeBank
                    ->account_name
            );


        $faceThreshold =
            (float)
            config(
                'services.dojah.face_confidence_min',
                90
            );


        $livenessThreshold =
            (float)
            config(
                'services.dojah.liveness_probability_min',
                70
            );


        $livenessPassed =
            (
                $results[
                    'liveness_passed'
                ]
                ===
                true
            )

            &&

            (
                $results[
                    'liveness_probability'
                ]
                >=
                $livenessThreshold
            );


        $facePassed =
            (
                $results[
                    'face_match'
                ]
                ===
                true
            )

            &&

            (
                $results[
                    'face_confidence'
                ]
                >=
                $faceThreshold
            );


        /*
        |--------------------------------------------------------------------------
        | Final Automated Decision
        |--------------------------------------------------------------------------
        */

        $approved =
            $livenessPassed

            &&

            $facePassed

            &&

            $nameMatch

            &&

            $dobMatch

            &&

            $bankNameMatch;


        /*
        |--------------------------------------------------------------------------
        | Work Out Failure
        |--------------------------------------------------------------------------
        */

        $failure =
            $this
                ->failureReason(
                    $livenessPassed,
                    $facePassed,
                    $nameMatch,
                    $dobMatch,
                    $bankNameMatch
                );


        /*
        |--------------------------------------------------------------------------
        | Save Final Result
        |--------------------------------------------------------------------------
        */

        $kyc->forceFill([
            'status' =>
                $approved

                    ? SellerKycVerification::STATUS_APPROVED

                    : SellerKycVerification::STATUS_REJECTED,


            'provider_status' =>
                $approved
                    ? 'verified'
                    : 'verification_failed',


            'identity_first_name' =>
                $results[
                    'first_name'
                ],


            'identity_middle_name' =>
                $results[
                    'middle_name'
                ],


            'identity_last_name' =>
                $results[
                    'last_name'
                ],


            'identity_date_of_birth' =>
                $results[
                    'date_of_birth'
                ],


            'liveness_passed' =>
                $livenessPassed,


            'liveness_probability' =>
                $results[
                    'liveness_probability'
                ],


            'face_match' =>
                $facePassed,


            'face_confidence' =>
                $results[
                    'face_confidence'
                ],


            'name_match' =>
                $nameMatch,


            'dob_match' =>
                $dobMatch,


            'bank_name_match' =>
                $bankNameMatch,


            'failure_code' =>
                $approved
                    ? null
                    : $failure[
                        'code'
                    ],


            'failure_message' =>
                $approved
                    ? null
                    : $failure[
                        'message'
                    ],


            'rejection_reason' =>
                $approved
                    ? null
                    : $failure[
                        'message'
                    ],


            /*
             * Sanitized only.
             *
             * No selfie.
             * No government base64 image.
             * No raw NIN/BVN.
             */
            'provider_response' => [
                'environment' =>
                    $this
                        ->providerEnvironment(),

                'identity_name' =>
                    $officialFullName,

                'identity_dob' =>
                    $results[
                        'date_of_birth'
                    ],

                'liveness_passed' =>
                    $livenessPassed,

                'liveness_probability' =>
                    $results[
                        'liveness_probability'
                    ],

                'face_match' =>
                    $facePassed,

                'face_confidence' =>
                    $results[
                        'face_confidence'
                    ],

                'legal_name_score' =>
                    IdentityNameMatcher::score(
                        $data[
                            'legal_name'
                        ],
                        $officialFullName
                    ),

                'bank_name_score' =>
                    IdentityNameMatcher::score(
                        $officialFullName,
                        $activeBank
                            ->account_name
                    ),
            ],


            'approved_at' =>
                $approved
                    ? now()
                    : null,


            'auto_verified_at' =>
                $approved
                    ? now()
                    : null,


            'rejected_at' =>
                $approved
                    ? null
                    : now(),


            'reviewed_by' =>
                null,


            'reviewed_at' =>
                null,
        ])->save();


        return $kyc
            ->fresh();
    }


    /*
    |--------------------------------------------------------------------------
    | Parse Dojah Result
    |--------------------------------------------------------------------------
    */

    protected function parseProviderResults(
        string $type,
        array $livenessResponse,
        array $identityResponse
    ): array {

        $livenessEntity =
            data_get(
                $livenessResponse,
                'entity',
                []
            );


        $identity =
            data_get(
                $identityResponse,
                'entity',
                []
            );


        if (
            $type === 'bvn'
        ) {

            $firstName =
                data_get(
                    $identity,
                    'first_name'
                );


            $middleName =
                data_get(
                    $identity,
                    'middle_name'
                );


            $lastName =
                data_get(
                    $identity,
                    'last_name'
                );


            $dob =
                data_get(
                    $identity,
                    'date_of_birth'
                );

        } else {

            /*
             * Dojah's NIN response currently uses
             * firstname / surname / birthdate.
             *
             * We accept alternate spellings defensively.
             */

            $firstName =
                data_get(
                    $identity,
                    'firstname'
                )
                ??
                data_get(
                    $identity,
                    'first_name'
                );


            $middleName =
                data_get(
                    $identity,
                    'middlename'
                )
                ??
                data_get(
                    $identity,
                    'middle_name'
                );


            $lastName =
                data_get(
                    $identity,
                    'surname'
                )
                ??
                data_get(
                    $identity,
                    'last_name'
                );


            $dob =
                data_get(
                    $identity,
                    'birthdate'
                )
                ??
                data_get(
                    $identity,
                    'date_of_birth'
                );
        }


        if (
            !$firstName
            ||
            !$lastName
            ||
            !$dob
        ) {

            throw new DojahKycException(
                'The government identity response was incomplete.',
                null,
                true
            );
        }


        $face =
            data_get(
                $identity,
                'selfie_verification',
                []
            );


        return [
            'first_name' =>
                trim(
                    (string)
                    $firstName
                ),

            'middle_name' =>
                trim(
                    (string)
                    $middleName
                ),

            'last_name' =>
                trim(
                    (string)
                    $lastName
                ),

            'date_of_birth' =>
                $this
                    ->parseDate(
                        $dob
                    ),

            'face_match' =>
                $this
                    ->toBoolean(
                        data_get(
                            $face,
                            'match'
                        )
                    ),

            'face_confidence' =>
                (float)
                (
                    data_get(
                        $face,
                        'confidence_value'
                    )
                    ??
                    0
                ),

            'liveness_passed' =>
                $this
                    ->toBoolean(
                        data_get(
                            $livenessEntity,
                            'liveness.liveness_check'
                        )
                    ),

            'liveness_probability' =>
                (float)
                (
                    data_get(
                        $livenessEntity,
                        'liveness.liveness_probability'
                    )
                    ??
                    0
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Local Fake KYC
    |--------------------------------------------------------------------------
    */

    protected function fakeVerification(
        array $data
    ): array {

        $name =
            preg_replace(
                '/\s+/',
                ' ',
                trim(
                    $data[
                        'legal_name'
                    ]
                )
            );


        $parts =
            preg_split(
                '/\s+/',
                $name
            )
            ?:
            [];


        $first =
            array_shift(
                $parts
            )
            ??
            'MIDPOINT';


        $last =
            count(
                $parts
            )
            ?
            array_pop(
                $parts
            )
            :
            'SELLER';


        $middle =
            implode(
                ' ',
                $parts
            );


        return [
            'first_name' =>
                $first,

            'middle_name' =>
                $middle,

            'last_name' =>
                $last,

            'date_of_birth' =>
                Carbon::parse(
                    $data[
                        'date_of_birth'
                    ]
                )
                    ->format(
                        'Y-m-d'
                    ),

            'face_match' =>
                true,

            'face_confidence' =>
                99.99,

            'liveness_passed' =>
                true,

            'liveness_probability' =>
                99.99,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Provider Error
    |--------------------------------------------------------------------------
    */

    protected function handleProviderFailure(
        SellerKycVerification $kyc,
        DojahKycException $exception
    ): SellerKycVerification {

        /*
         * 400 / 404 usually means identifier/input could not be verified.
         */
        if (
            in_array(
                $exception
                    ->statusCode(),
                [
                    400,
                    404,
                ],
                true
            )
        ) {

            $kyc->forceFill([
                'status' =>
                    SellerKycVerification::STATUS_REJECTED,

                'provider_status' =>
                    'verification_failed',

                'failure_code' =>
                    'identity_not_verified',

                'failure_message' =>
                    'We could not verify this NIN/BVN. Check the identity number and upload a clear selfie.',

                'rejection_reason' =>
                    'We could not verify this NIN/BVN. Check the identity number and upload a clear selfie.',

                'rejected_at' =>
                    now(),
            ])->save();


            return $kyc
                ->fresh();
        }


        return $this
            ->markProviderError(
                $kyc,
                'provider_unavailable',
                'The identity verification service is temporarily unavailable. Please try again.'
            );
    }


    protected function markProviderError(
        SellerKycVerification $kyc,
        string $code,
        string $message
    ): SellerKycVerification {

        $kyc->forceFill([
            'status' =>
                SellerKycVerification::STATUS_PROVIDER_ERROR,

            'provider_status' =>
                'error',

            'failure_code' =>
                $code,

            'failure_message' =>
                $message,

            'rejection_reason' =>
                null,

            'approved_at' =>
                null,

            'rejected_at' =>
                null,

            'auto_verified_at' =>
                null,
        ])->save();


        return $kyc
            ->fresh();
    }


    /*
    |--------------------------------------------------------------------------
    | Failure Message
    |--------------------------------------------------------------------------
    */

    protected function failureReason(
        bool $liveness,
        bool $face,
        bool $name,
        bool $dob,
        bool $bank
    ): array {

        if (
            !$liveness
        ) {

            return [
                'code' =>
                    'liveness_failed',

                'message' =>
                    'We could not confirm that the selfie was captured from a live person. Please take a new clear selfie and try again.',
            ];
        }


        if (
            !$face
        ) {

            return [
                'code' =>
                    'face_mismatch',

                'message' =>
                    'Your selfie did not sufficiently match the photo associated with your NIN/BVN.',
            ];
        }


        if (
            !$name
        ) {

            return [
                'code' =>
                    'legal_name_mismatch',

                'message' =>
                    'The legal name you entered does not match the government identity record.',
            ];
        }


        if (
            !$dob
        ) {

            return [
                'code' =>
                    'dob_mismatch',

                'message' =>
                    'The date of birth you entered does not match the government identity record.',
            ];
        }


        if (
            !$bank
        ) {

            return [
                'code' =>
                    'bank_name_mismatch',

                'message' =>
                    'Your verified withdrawal bank account name does not match your government identity.',
            ];
        }


        return [
            'code' =>
                'verification_failed',

            'message' =>
                'Identity verification was not successful.',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Dates
    |--------------------------------------------------------------------------
    */

    protected function datesMatch(
        $submitted,
        $provider
    ): bool {

        $submitted =
            $this
                ->parseDate(
                    $submitted
                );


        $provider =
            $this
                ->parseDate(
                    $provider
                );


        return
            $submitted
            &&
            $provider
            &&
            $submitted
            ===
            $provider;
    }


    protected function parseDate(
        $value
    ): ?string {

        if (
            !$value
        ) {

            return null;
        }


        $value =
            trim(
                (string)
                $value
            );


        $formats = [
            'Y-m-d',
            'd-m-Y',
            'd/m/Y',
            'd-M-Y',
            'd M Y',
            'm/d/Y',
        ];


        foreach (
            $formats
            as
            $format
        ) {

            try {

                $date =
                    Carbon::createFromFormat(
                        $format,
                        $value
                    );


                if (
                    $date
                ) {

                    return $date
                        ->format(
                            'Y-m-d'
                        );
                }

            } catch (
                Throwable $exception
            ) {
                //
            }
        }


        try {

            return Carbon::parse(
                $value
            )
                ->format(
                    'Y-m-d'
                );

        } catch (
            Throwable $exception
        ) {

            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Boolean Conversion
    |--------------------------------------------------------------------------
    */

    protected function toBoolean(
        $value
    ): bool {

        if (
            is_bool(
                $value
            )
        ) {

            return $value;
        }


        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    */

    protected function shouldFakeLocally(): bool
    {
        return
            app()
                ->environment(
                    'local'
                )

            &&

            (bool)
            config(
                'services.dojah.fake_kyc',
                false
            );
    }


    protected function providerEnvironment(): string
    {
        if (
            $this
                ->shouldFakeLocally()
        ) {

            return
                'local_fake';
        }


        $url =
            strtolower(
                (string)
                config(
                    'services.dojah.base_url'
                )
            );


        return
            str_contains(
                $url,
                'sandbox'
            )

                ? 'sandbox'

                : 'live';
    }
}