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
        'response_session_id',
        'overall_score',
        'overall_percentage',
        'overall_category',
        'conclusion',
    ];

    protected function casts(): array
    {
        return [
            'overall_score' => 'decimal:2',
            'overall_percentage' => 'decimal:2',
        ];
    }

    public function responseSession(): BelongsTo
    {
        return $this->belongsTo(ResponseSession::class, 'response_session_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(EvaluationResultDetail::class, 'evaluation_result_id');
    }
}
