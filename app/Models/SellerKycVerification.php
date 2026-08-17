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

        'document_front_path',
        'document_back_path',
        'selfie_path',

        'status',

        'verification_method',
        'provider',
        'provider_environment',
        'provider_status',

        'identity_first_name',
        'identity_middle_name',
        'identity_last_name',
        'identity_date_of_birth',

        'liveness_passed',
        'liveness_probability',

        'face_match',
        'face_confidence',

        'name_match',
        'dob_match',
        'bank_name_match',

        'seller_withdrawal_account_id',

        'failure_code',
        'failure_message',

        'provider_response',

        'verification_attempts',
        'last_verification_attempt_at',

        'rejection_reason',

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
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'seller_id'
        );
    }


    public function reviewer()
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }


    public function withdrawalAccount()
    {
        return $this->belongsTo(
            SellerWithdrawalAccount::class,
            'seller_withdrawal_account_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Encrypt Identity Number
    |--------------------------------------------------------------------------
    */

    public function setIdNumberAttribute(
        $value
    ): void {

        $number =
            preg_replace(
                '/\s+/',
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
    | Official Verified Full Name
    |--------------------------------------------------------------------------
    */

    public function getVerifiedFullNameAttribute(): string
    {
        return trim(
            implode(
                ' ',
                array_filter([
                    $this->identity_first_name,
                    $this->identity_middle_name,
                    $this->identity_last_name,
                ])
            )
        );
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