<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResponseSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'questionnaireId',
        'status',
        'startedAt',
        'submittedAt',
        'remainingSeconds',
    ];

    protected function casts(): array
    {
        return [
            'startedAt' => 'datetime',
            'submittedAt' => 'datetime',
            'remainingSeconds' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ResponseAnswer::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(EvaluationResult::class);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'inProgress');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }
}
