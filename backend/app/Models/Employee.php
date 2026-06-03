<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Fiche salariale liée à un User employé (salaire, plafond d'avance, actif/inactif). */
class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'monthly_salary',
        'advance_limit_pct',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'monthly_salary' => 'decimal:2',
            'advance_limit_pct' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function advanceRequests(): HasMany
    {
        return $this->hasMany(AdvanceRequest::class);
    }
}
