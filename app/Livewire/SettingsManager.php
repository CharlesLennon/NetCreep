<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Settings as Settings;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class SettingsManager extends Component
{

    public $settings, $sections;

    public function mount()
    {
        $this->settings = Settings::select(["key as name","value","description"])->get()->toArray();
        $keys = collect($this->settings)->pluck('name');
        $sections = [];
        foreach($keys as $key){
            $sections[] = explode(".",$key)[0];
        }
        $this->sections = array_unique($sections);
    }

    public function updateSettings()
    {
        foreach ($this->settings as $setting) {
            Settings::where("key", $setting["name"])->update(["value"=> $setting["value"]]);
        } 
        return redirect(request()->header('Referer'));   
    }

    public function resetSettings()
    {
        Settings::reset();
        return redirect(request()->header('Referer'));
    }


}
