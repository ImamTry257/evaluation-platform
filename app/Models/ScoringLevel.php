<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoringLevel extends Model
{
    protected $table = 'scoring_level';
    protected $fillable = ['title', 'value', 'is_active', 'description'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Convert model to array with camelCase keys.
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Rename is_active to isActive for API consistency
        if (isset($data['is_active'])) {
            $data['isActive'] = $data['is_active'];
            unset($data['is_active']);
        }
        
        return $data;
    }
}
