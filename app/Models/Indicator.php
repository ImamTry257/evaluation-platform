<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicator extends Model
{
    use HasFactory;

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
        return $this->belongsTo(SubComponent::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    public function evaluationResultDetails(): HasMany
    {
        return $this->hasMany(EvaluationResultDetail::class);
    }
}
