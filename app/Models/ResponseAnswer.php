<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'responseSessionId',
        'questionId',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function responseSession(): BelongsTo
    {
        return $this->belongsTo(ResponseSession::class, 'responseSessionId');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'questionId');
    }
}
