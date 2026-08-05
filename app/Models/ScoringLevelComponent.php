<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScoringLevelComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'scoring_level_component';

    protected $fillable = [
        'questionnaire_id',
        'component_id',
        'score_title',
        'start_from',
        'end_at',
        'is_active',
        'action_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'start_from' => 'decimal:2',
            'end_at' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class, 'questionnaire_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

    /**
     * Convert model to array with camelCase keys for API consistency.
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        if (isset($data['is_active'])) {
            $data['isActive'] = $data['is_active'];
            unset($data['is_active']);
        }

        return $data;
    }
}
