<?php

namespace App\Models;

class Setting extends Model
{
    protected $fillable = ['name', 'value', 'type', 'description'];
    protected $primaryKey = 'name';
    public $incrementing = false;

    public function scopeSystem($query)
    {
        return $query->where('system', 1);
    }

    public function scopeUserDefined($query)
    {
        return $query->where('system', 0);
    }

    /**
     * Get settings
     * @param type $name Name of setting
     * @param type $cast Type to cast to. number, boolean and array supported
     * @return type Value
     */
    public static function get($name, $default = null, $cast = null)
    {
        $setting = self::where('name', $name)->first();
        if (is_object($setting)) {
            $type = $cast ?: $setting->type;
            switch ($type) {
                case 'number':
                    return is_numeric($setting->value) ? floatval($setting->value) : $setting->value;
                case 'boolean':
                    return boolval($setting->value);
                case 'array':
                    return json_decode($setting->value, true);
                default:
                    return $setting->value;
            }
        } else {
            return $default;
        }
    }

    public static function set($name, $value, $type = null, $description = null, $system = 1)
    {
        if (is_array($value)) {
            $value = json_encode($value);
            $type = 'array';
        }

        $setting = self::where('name', $name)->first();
        if (!is_object($setting)) {
            $setting = new Setting;
            $setting->name = $name;
            $setting->description = $description ?: str_replace('_', ' ', $name);
            //            $setting->system = $system;
        }

        $setting->value = $value;
        if (isset($type)) {
            $setting->type = $type;
        }
        $setting->save();
    }
}
