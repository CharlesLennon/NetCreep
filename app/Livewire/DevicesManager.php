<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On; 

class DevicesManager extends Component
{


    public $device_id = null;
    //can be edited
    public $device_name, $device_mac, $device_type, $device_vendor;
    public $device_parent_mac, $device_parent_port, $device_self_port;

    public $device_ip, $device_last_seen, $device_first_found;

    public $port, $newPort, $port_parent_mac; // Port information for mass update


    // deviceSelected 
    #[On('deviceSelected')]
    public function deviceSelected($deviceId)
    {
        $this->resetDeviceFields();

        // if it starts with port-
        if (str_starts_with($deviceId, 'port-')) {
           
            $explodded = explode('-', $deviceId);
            $parentMac = $explodded[1];
            $portValue = $explodded[2];
            $this->massUpdatePort($parentMac, $portValue);
            return;
        }
        $this->device_id = $deviceId;
        $device = \App\Models\Device::find($deviceId);
        if ($device) {
            $this->device_name = $device->name;
            $this->device_mac = $device->mac;
            $this->device_type = $device->type;
            $this->device_vendor = $device->vendor;
            $this->device_parent_mac = $device->parent_mac;
            $this->device_parent_port = $device->parent_port;
            $this->device_self_port = $device->self_port;

            $this->device_ip = $device->last_ip;
            //how long ago diff for humands
            $this->device_last_seen = $device->last_seen ? $device->last_seen->diffForHumans() : null;
            $this->device_first_found = $device->first_found ? $device->first_found->diffForHumans() : null;
        } else {
            $this->resetDeviceFields();
        }
    }

    public function massUpdatePort($parentMac, $port = null)
    {
        $this->deselectDevice();
        $this->port = $port;
        $this->newPort = $port;
        $this->port_parent_mac = $parentMac;
    }

    public function savePort()
    {
        $devices = \App\Models\Device::
            where('parent_mac', $this->port_parent_mac)
            ->when($port = $this->port, fn($d) => $d->where('parent_port', $port), fn($d) => $d->whereNull('parent_port'))
        ->get();

        foreach ($devices as $device) {
            $device->parent_port = $this->newPort;
            $device->save();
        }
        $this->resetDeviceFields();
    }

    public function deletePort()
    {
        $devices = \App\Models\Device::
            where('parent_mac', $this->port_parent_mac)
            ->where('parent_port', $this->port)
        ->get();

        foreach ($devices as $device) {
            $device->parent_port = null;
            $device->save();
        }
        $this->resetDeviceFields();
    }

    public function deselectPort()
    {
        $this->resetDeviceFields();
    }

    public function deselectDevice()
    {
        $this->device_id = null;
        $this->resetDeviceFields();
    }

    public function resetDeviceFields()
    {
        $this->port = null;
        $this->newPort = null;
        $this->port_parent_mac = null;
        $this->device_id = null;
        $this->device_name = '';
        $this->device_mac = '';
        $this->device_type = '';
        $this->device_vendor = '';
        $this->device_parent_mac = '';
        $this->device_parent_port = '';
        $this->device_self_port = '';
    }

    public function saveDevice()
    {
        $device = \App\Models\Device::find($this->device_id);
        if (!$device) {
            $device = new \App\Models\Device();
        }

        $device->name = $this->device_name;
        $device->mac = $this->device_mac;
        $device->type = $this->device_type;
        $device->vendor = $this->device_vendor;
        $device->parent_mac = $this->device_parent_mac;
        $device->parent_port = $this->device_parent_port;
        $device->self_port = $this->device_self_port;

        // Save the device
        $device->save();

        // Reset fields after saving
        $this->resetDeviceFields();
        $this->deviceSelected($device->id);
        
    }

    public function deleteDevice()
    {
        $device = \App\Models\Device::find($this->device_id);
        if ($device) {
            $device->delete();
            $this->deselectDevice();
        }
    }

    public function render()
    {
        return view('livewire.devices-manager');
    }
}
