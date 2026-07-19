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
        'component_id',
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

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class, 'sub_component_id');
    }
}
