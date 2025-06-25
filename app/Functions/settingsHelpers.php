<?php
    function getSetting(string $key) : string
    {
        return App\Models\Settings::use($key);
    }

    function displayMac(App\Models\Device | string $device) : string 
    {
        if(!is_object($device)){ $device = App\Models\Device::find($device); }
        if(!$device){ return ""; }
        $displayMacSetting = "visiblity.mac";
        $value = getSetting($displayMacSetting);

        return match (strtolower($value)) {
            "name" => $device->name,
            "mac" => $device->mac,
            default => null
        };
    }