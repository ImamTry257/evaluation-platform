<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'responseSessionId',
        'overallScore',
        'overallPercentage',
        'overallCategory',
        'conclusion',
    ];

    protected function casts(): array
    {
        return [
            'overallScore' => 'decimal:2',
            'overallPercentage' => 'decimal:2',
        ];
    }

    public function responseSession(): BelongsTo
    {
        return $this->belongsTo(ResponseSession::class, 'responseSessionId');
    }

    public function details(): HasMany
    {
        return $this->hasMany(EvaluationResultDetail::class, 'evaluationResultId');
    }
}
