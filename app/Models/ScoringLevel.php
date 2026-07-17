<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoringLevel extends Model
{
    protected $table = 'scoring_level';
    protected $fillable = ['title', 'value', 'is_active', 'description'];
}
