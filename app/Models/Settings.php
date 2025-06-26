<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{

    protected $guarded = [];
    protected $primaryKey = 'key';
    public $timestamps = false;
    protected $table = 'settings';

    private static function getDefaultKeyValues()
    {
        return [
            "style.children-port-class" => "bg-[#ff6f61]",
            "style.device-background-color-class" => "bg-gray-800",

            "style.self-port-class" => "bg-red-400",//class="{value}"
            "style.main-class" => "bg-gray-900",//class="{value}"
            "style.alt-class" => "bg-gray-800",//class="{value}"
            "style.input-class" => "bg-gray-700",//class="{value}"

            "style.main-button-class" => "bg-blue-600",
            "style.alt-button-class" => "",
            "style.danger-button-class" => "bg-red-600",

            "visiblity.mac" => "name", 
            
            "arp.args" => "--interface=eth0 --localnet", 
            "arp.frquency-cron" => "*/5 * * * *",
        ];
    }

    private static function getDefaultKeyDescriptions()
    {
        return [
            "style.children-port-class" => "Sets the default background color for child port elements. Used with 'background-color' CSS property.",
            "style.device-background-color-class" => "Sets the default background color for device elements. Used with 'background-color' CSS property.",

            "style.self-port-class" => "Defines the CSS class for self-port elements. Used with the 'class' attribute (e.g., Tailwind CSS classes).",
            "style.main-class" => "Defines the main CSS class for primary UI elements. Used with the 'class' attribute.",
            "style.alt-class" => "Defines an alternative CSS class for secondary UI elements. Used with the 'class' attribute.",
            "style.input-class" => "Defines the CSS class for input fields. Used with the 'class' attribute.",

            "style.main-button-class" => "Defines the CSS class for primary action buttons.",
            "style.alt-button-class" => "Defines the CSS class for alternative action buttons.",
            "style.danger-button-class" => "Defines the CSS class for destructive or dangerous action buttons.",

            "visiblity.mac" => "Controls what is displayed for MAC addresses (e.g., 'name' for name, 'mac' for MAC address).",
            
            "arp.args" => "Command-line arguments to be passed to the ARP command (e.g., network interface, local network scan).", 
            "arp.frquency-cron" => "Cron expression defining the frequency at which the ARP scan should run (e.g., '*/5 * * * *' for every 5 minutes).",
        ];
    }

    public static function getDescription($key)
    {
        $db = self::where("key", $key)->first();
        if ($db) { return $db->description; }
        return self::getDefaultKeyDescriptions()[$key];
    }

    public static function use($key)
    {
        $db = self::where("key", $key)->first();
        if ($db) { return $db->value; }
        return self::getDefaultKeyValues()[$key];
    }

    public static function reset()
    {
        $defaults = self::getDefaultKeyValues();
        self::query()->delete();
        foreach($defaults as $key => $value) {
            self::create(
                [
                    "key"=> $key,
                    "value"=> $value,
                    "description" => self::getDescription($key),
                ]
            );
                
        }
    }
}
