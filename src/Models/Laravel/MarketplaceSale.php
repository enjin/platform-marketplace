<?php

namespace Enjin\Platform\Marketplace\Models\Laravel;

use Enjin\Platform\Marketplace\Database\Factories\MarketplaceSaleFactory;
use Enjin\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class MarketplaceSale extends BaseModel
{
    use HasFactory;
    use Traits\EagerLoadSelectFields;
    use HasEagerLimit;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    public $guarded = [];

    /**
     * The fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'marketplace_listing_id',
        'listing_id',
        'wallet_id',
        'price',
        'amount',
    ];

    /**
     * The hidden fields.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * The bidder wallet's relationship.
     */
    public function bidder(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    /**
     * The listing's relationship.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(MarketplaceListing::class, 'marketplace_listing_id');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return MarketplaceSaleFactory::new();
    }
}
