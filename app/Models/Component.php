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
        'questionnaireId',
        'name',
        'description',
        'orderNumber',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'orderNumber' => 'integer',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class, 'questionnaireId');
    }

    public function subComponents(): HasMany
    {
        return $this->hasMany(SubComponent::class, 'componentId');
    }
}
