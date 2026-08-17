<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SellerApplication extends Model
{
    public const STATUS_SUBMITTED =
        'submitted';

    public const STATUS_REVISION_REQUIRED =
        'revision_required';

    public const STATUS_SUPERSEDED =
        'superseded';

    public const STATUS_PAYMENT_PENDING =
        'payment_pending';

    public const STATUS_ACTIVE =
        'active';

    public const STATUS_EXPIRED =
        'expired';


    protected $fillable = [

        'reference',

        'user_id',

        'seller_package_id',

        'package_name',

        'package_price',

        'billing_period',

        'product_limit',

        'business_name',

        'category',

        'location',

        'phone',

        'cac_or_bvn',

        'store_link',

        'description',

        'status',

        'revision_note',

        'reviewed_by',

        'submitted_at',

        'reviewed_at',

        'approved_at',

        'activated_at',
    ];


    protected $casts = [

        'package_price' =>
            'decimal:2',

        'product_limit' =>
            'integer',

        'submitted_at' =>
            'datetime',

        'reviewed_at' =>
            'datetime',

        'approved_at' =>
            'datetime',

        'activated_at' =>
            'datetime',
    ];


    public static function generateReference(): string
    {
        do {

            $reference =
                'MP-SA-'
                .
                now()->format(
                    'Ymd'
                )
                .
                '-'
                .
                Str::upper(
                    Str::random(
                        6
                    )
                );

        } while (
            static::query()

                ->where(
                    'reference',
                    $reference
                )

                ->exists()
        );


        return $reference;
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function package()
    {
        return $this->belongsTo(
            SellerPackage::class,
            'seller_package_id'
        );
    }


    public function documents()
    {
        return $this->hasMany(
            SellerApplicationDocument::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Original Application Invoice
    |--------------------------------------------------------------------------
    */

    public function invoice()
    {
        return $this->hasOne(
            SellerInvoice::class
        )
            ->where(
                'purchase_type',
                SellerInvoice::TYPE_INITIAL
            );
    }


    /*
    |--------------------------------------------------------------------------
    | All Invoices
    |--------------------------------------------------------------------------
    |
    | Initial + Renewal + Upgrade + Downgrade
    |
    */

    public function invoices()
    {
        return $this->hasMany(
            SellerInvoice::class
        );
    }


    public function reviewer()
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Status Label
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute()
    {
        return match (
            $this->status
        ) {

            self::STATUS_SUBMITTED =>
                'Under review',

            self::STATUS_REVISION_REQUIRED =>
                'Revision required',

            self::STATUS_PAYMENT_PENDING =>
                'Payment required',

            self::STATUS_ACTIVE =>
                'Active seller',

            self::STATUS_EXPIRED =>
                'Package expired',

            self::STATUS_SUPERSEDED =>
                'Replaced by new application',

            default =>
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $this->status
                    )
                ),
        };
    }
}