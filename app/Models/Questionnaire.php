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
        'evaluation_period_id',
        'title',
        'description',
        'duration_minutes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
        ];
    }

    public function evaluationPeriod(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class, 'evaluation_period_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class, 'questionnaire_id');
    }

    public function responseSessions(): HasMany
    {
        return $this->hasMany(ResponseSession::class, 'questionnaire_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
