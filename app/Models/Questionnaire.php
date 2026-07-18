<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'evaluationPeriodId',
        'title',
        'description',
        'durationMinutes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'durationMinutes' => 'integer',
        ];
    }

    public function evaluationPeriod(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class, 'evaluationPeriodId');
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class, 'questionnaireId');
    }

    public function responseSessions(): HasMany
    {
        return $this->hasMany(ResponseSession::class, 'questionnaireId');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
