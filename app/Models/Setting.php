<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'instansi_id'];

    public static function get(string $key, $default = null)
    {
        $query = static::where('key', $key);

        if ($tenantId = \Filament\Facades\Filament::getTenant()?->id) {
            $query->where('instansi_id', $tenantId);
        }

        return $query->first()?->value ?? $default;
    }

    public static function set(string $key, $value)
    {
        $attributes = ['key' => $key];

        if ($tenantId = \Filament\Facades\Filament::getTenant()?->id) {
            $attributes['instansi_id'] = $tenantId;
        }

        return static::updateOrCreate($attributes, ['value' => $value]);
    }
}
