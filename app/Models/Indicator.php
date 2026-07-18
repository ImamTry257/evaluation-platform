<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indicator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subComponentId',
        'name',
        'description',
        'orderNumber',
    ];

    protected function casts(): array
    {
        return [
            'orderNumber' => 'integer',
        ];
    }

    public function subComponent(): BelongsTo
    {
        return $this->belongsTo(SubComponent::class, 'subComponentId');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'indicatorId');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'indicatorId');
    }

    public function evaluationResultDetails(): HasMany
    {
        return $this->hasMany(EvaluationResultDetail::class, 'indicatorId');
    }
}
