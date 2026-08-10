<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SellerApplication extends Model
{
    const STATUS_SUBMITTED =
        'submitted';

    const STATUS_REVISION_REQUIRED =
        'revision_required';

    const STATUS_SUPERSEDED =
        'superseded';

    const STATUS_PAYMENT_PENDING =
        'payment_pending';

    const STATUS_ACTIVE =
        'active';


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


    /*
    |--------------------------------------------------------------------------
    | Generate Reference
    |--------------------------------------------------------------------------
    */

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
                    Str::random(6)
                );

        } while (
            static::where(
                'reference',
                $reference
            )->exists()
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


    public function invoice()
    {
        return $this->hasOne(
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
    | Labels
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute()
    {
        return match(
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