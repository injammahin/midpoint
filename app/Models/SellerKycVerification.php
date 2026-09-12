<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SellerKycVerification extends Model
{
    public const STATUS_PENDING =
        'pending';

    public const STATUS_PROCESSING =
        'processing';

    public const STATUS_APPROVED =
        'approved';

    public const STATUS_REJECTED =
        'rejected';

    public const STATUS_PROVIDER_ERROR =
        'provider_error';


    protected $fillable = [

        'seller_id',

        'legal_name',

        'date_of_birth',

        'country_code',

        'id_type',

        'id_number',

        'identity_fingerprint',

        'reused_from_kyc_id',

        'identity_reused_at',

        'document_front_path',

        'document_back_path',

        'selfie_path',

        'status',

        'verification_method',

        'provider',

        'provider_environment',

        'provider_status',

        /*
        |--------------------------------------------------------------------------
        | Paystack
        |--------------------------------------------------------------------------
        */

        'paystack_customer_code',

        'paystack_customer_id',

        'paystack_identification_status',

        'paystack_identification_requested_at',

        'paystack_identification_completed_at',

        /*
        |--------------------------------------------------------------------------
        | Verified Identity
        |--------------------------------------------------------------------------
        */

        'identity_first_name',

        'identity_middle_name',

        'identity_last_name',

        'identity_date_of_birth',

        /*
        |--------------------------------------------------------------------------
        | Legacy Automated Checks
        |--------------------------------------------------------------------------
        */

        'liveness_passed',

        'liveness_probability',

        'face_match',

        'face_confidence',

        'name_match',

        'dob_match',

        'bank_name_match',

        /*
        |--------------------------------------------------------------------------
        | Bank Used For KYC
        |--------------------------------------------------------------------------
        */

        'seller_withdrawal_account_id',

        /*
        |--------------------------------------------------------------------------
        | Failure
        |--------------------------------------------------------------------------
        */

        'failure_code',

        'failure_message',

        'provider_response',

        'verification_attempts',

        'last_verification_attempt_at',

        'rejection_reason',

        /*
        |--------------------------------------------------------------------------
        | Review / Audit
        |--------------------------------------------------------------------------
        */

        'reviewed_by',

        'submitted_at',

        'reviewed_at',

        'approved_at',

        'rejected_at',

        'auto_verified_at',

    ];


    protected $hidden = [

        'id_number_encrypted',

    ];


    protected $casts = [

        'date_of_birth' =>
            'date',


        'identity_date_of_birth' =>
            'date',


        'liveness_passed' =>
            'boolean',


        'liveness_probability' =>
            'float',


        'face_match' =>
            'boolean',


        'face_confidence' =>
            'float',


        'name_match' =>
            'boolean',


        'dob_match' =>
            'boolean',


        'bank_name_match' =>
            'boolean',


        'provider_response' =>
            'array',


        'verification_attempts' =>
            'integer',


        'submitted_at' =>
            'datetime',


        'reviewed_at' =>
            'datetime',


        'approved_at' =>
            'datetime',


        'rejected_at' =>
            'datetime',


        'auto_verified_at' =>
            'datetime',


        'last_verification_attempt_at' =>
            'datetime',


        'paystack_identification_requested_at' =>
            'datetime',


        'paystack_identification_completed_at' =>
            'datetime',


        'identity_reused_at' =>
            'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Seller
    |--------------------------------------------------------------------------
    */

    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'seller_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reviewer
    |--------------------------------------------------------------------------
    */

    public function reviewer()
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Bank Used During Identity Verification
    |--------------------------------------------------------------------------
    */

    public function withdrawalAccount()
    {
        return $this->belongsTo(
            SellerWithdrawalAccount::class,
            'seller_withdrawal_account_id'
        );
    }


    public function reusedFromVerification()
    {
        return $this->belongsTo(
            self::class,
            'reused_from_kyc_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Encrypt BVN
    |--------------------------------------------------------------------------
    */

    public function setIdNumberAttribute(
        $value
    ): void {

        $number =
            preg_replace(
                '/\D+/',
                '',
                trim(
                    (string)
                    $value
                )
            );


        $this->attributes[
            'id_number_encrypted'
        ] =
            Crypt::encryptString(
                $number
            );


        $this->attributes[
            'id_number_last4'
        ] =
            substr(
                $number,
                -4
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Decrypt BVN
    |--------------------------------------------------------------------------
    */

    public function getIdNumberAttribute(): ?string
    {
        if (
            empty(
                $this->attributes[
                    'id_number_encrypted'
                ]
            )
        ) {

            return null;
        }


        return Crypt::decryptString(
            $this->attributes[
                'id_number_encrypted'
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Verified Full Name
    |--------------------------------------------------------------------------
    */

    public function getVerifiedFullNameAttribute(): string
    {
        $verified =
            trim(
                implode(
                    ' ',
                    array_filter([

                        $this
                            ->identity_first_name,

                        $this
                            ->identity_middle_name,

                        $this
                            ->identity_last_name,

                    ])
                )
            );


        /*
         * If fetching the final Paystack customer failed,
         * webhook success itself is still valid.
         *
         * Fall back to seller-submitted legal name.
         */

        return $verified !== ''
            ? $verified
            : (string)
                $this
                    ->legal_name;
    }


    /*
    |--------------------------------------------------------------------------
    | Bank-Bound Approval
    |--------------------------------------------------------------------------
    |
    | A KYC approval is usable only for the exact verified withdrawal account
    | that Paystack checked. A global `approved` status is never sufficient.
    |
    */

    public function isApprovedForWithdrawalAccount(
        ?SellerWithdrawalAccount $account
    ): bool {

        return
            $this->status
                ===
                self::STATUS_APPROVED
            && $this->provider === 'paystack'
            && $this->paystack_identification_status === 'success'
            && $this->paystack_identification_completed_at !== null
            && $account !== null
            && $account->is_verified
            && (int) $account->seller_id
                ===
                (int) $this->seller_id
            && (int) $this->seller_withdrawal_account_id
                ===
                (int) $account->id
            && $this->bank_name_match
                ===
                true
            && data_get(
                $this->provider_response,
                'verification_source'
            ) === 'signed_webhook'
            && data_get(
                $this->provider_response,
                'exact_bvn_confirmed'
            ) === true;
    }


    /*
    |--------------------------------------------------------------------------
    | Status Label
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match (
            $this->status
        ) {

            self::STATUS_APPROVED =>
                'Identity verified',

            self::STATUS_REJECTED =>
                'Verification failed',

            self::STATUS_PROCESSING =>
                'Verifying',

            self::STATUS_PROVIDER_ERROR =>
                'Verification unavailable',

            default =>
                'Not verified',

        };
    }
}
