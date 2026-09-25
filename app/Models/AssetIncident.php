<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'affected_staff_id',
        'assigned_to_user_id',
        'incident_type_id',
        'incident_status_id',
        'incident_date',
        'description',
        'action_taken',
        'warranty_claim_ref',
        'warranty_claim_date',
        'warranty_excess',
        'associated_cost',
        'resolved_date',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'warranty_claim_date' => 'date',
            'resolved_date' => 'date',
            'warranty_excess' => 'decimal:2',
            'associated_cost' => 'decimal:2',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function affectedStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'affected_staff_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function incidentType(): BelongsTo
    {
        return $this->belongsTo(IncidentType::class);
    }

    public function incidentStatus(): BelongsTo
    {
        return $this->belongsTo(IncidentStatus::class);
    }
}