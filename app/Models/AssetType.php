<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetType extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_type',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function assetSubtypes(): HasMany
    {
        return $this->hasMany(AssetSubtype::class);
    }
}