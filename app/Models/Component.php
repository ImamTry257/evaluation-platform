<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Component extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'questionnaire_id',
        'name',
        'description',
        'order_number',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'order_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class, 'questionnaire_id');
    }

    public function subComponents(): HasMany
    {
        return $this->hasMany(SubComponent::class, 'component_id');
    }
}
