<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'componentId',
        'name',
        'description',
        'orderNumber',
    ];

    protected function casts(): array
    {
        return [
            'orderNumber' => 'integer',
        ];
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'componentId');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class, 'subComponentId');
    }
}
