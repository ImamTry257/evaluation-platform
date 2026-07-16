<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationResultDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluationResultId',
        'indicatorId',
        'score',
        'percentage',
        'category',
        'recommendation',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'percentage' => 'decimal:2',
        ];
    }

    public function evaluationResult(): BelongsTo
    {
        return $this->belongsTo(EvaluationResult::class, 'evaluationResultId');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class, 'indicatorId');
    }
}
