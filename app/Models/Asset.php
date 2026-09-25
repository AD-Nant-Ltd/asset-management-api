<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_subtype_id',
        'asset_status_id',
        'asset_condition_id',
        'delivery_date',
        'asset_tag',
        'serial_num',
        'purchase_date',
        'retired_date',
        'warranty_expiry',
        'disposal_date',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'purchase_date' => 'date',
            'retired_date' => 'date',
            'warranty_expiry' => 'date',
            'disposal_date' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    public function assetSubtype(): BelongsTo
    {
        return $this->belongsTo(AssetSubtype::class);
    }

    public function assetStatus(): BelongsTo
    {
        return $this->belongsTo(AssetStatus::class);
    }

    public function assetCondition(): BelongsTo
    {
        return $this->belongsTo(AssetCondition::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(AssetIncident::class);
    }
}