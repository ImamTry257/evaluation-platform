<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    use HasFactory;

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
        return $this->belongsTo(EvaluationPeriod::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }

    public function responseSessions(): HasMany
    {
        return $this->hasMany(ResponseSession::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
