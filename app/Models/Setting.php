<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    // Helper methods
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }

    public static function getInt($key, $default = 0)
    {
        return (int) static::get($key, $default);
    }

    public static function getBoolean($key, $default = false)
    {
        $value = static::get($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
