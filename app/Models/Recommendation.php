<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicatorId',
        'minScore',
        'maxScore',
        'category',
        'recommendationText',
    ];

    protected function casts(): array
    {
        return [
            'minScore' => 'decimal:2',
            'maxScore' => 'decimal:2',
        ];
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }
}
