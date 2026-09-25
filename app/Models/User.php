<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'staff_id',
        'role_id',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function assignmentsCreated(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'assigned_by_id');
    }

    public function returnsProcessed(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'returned_by_id');
    }

    public function incidentsAssigned(): HasMany
    {
        return $this->hasMany(AssetIncident::class, 'assigned_to_user_id');
    }
}