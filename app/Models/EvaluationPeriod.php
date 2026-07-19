<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'startDate',
        'endDate',
        'isActive',
    ];

    protected function casts(): array
    {
        return [
            'startDate' => 'datetime',
            'endDate' => 'datetime',
            'isActive' => 'boolean',
        ];
    }

    public function questionnaires(): HasMany
    {
        return $this->hasMany(Questionnaire::class, 'evaluation_period_id');
    }

    public function scopeActive($query)
    {
        return $query->where('isActive', true);
    }
}
