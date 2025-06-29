<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

function getInterface() {
    // Get the default network interface
    $myInterface = exec('ip route | grep default | awk \'{print $5}\'');
    return $myInterface ?: 'eth0'; // Fallback to eth0 if not found
}

// tmp funcs
function processArpScanOutput($output) {
    $myInterface = getInterface();
    $lines = explode("\n", $output);
    foreach ($lines as $line) {
        $mac = arpScanGetMacAddress($line);
        if ($mac) {
            $ip = arpScanGetIPAddress($line);
            $name = arpScanGetName($line);
            $device = App\Models\Device::updateOrCreate(
                [
                    'mac' => $mac,
                ],
                [
                    'last_ip' => $ip,
                    'last_seen' => now(),
                ]
            );

            if ($device->first_found == null) { $device->first_found = now(); }
            // Update the device's name if it is not set or unknown
            if (!$device->name || $device->name === 'Unknown') {
                $device->name = $name ?: 'Unknown';
            }
            $device->save();
            Log::info("[$mac][$ip] Found MAC address, Name: $name");   
        }
    }
    // what is my IP and MAC address
    $myMac = exec("cat /sys/class/net/$myInterface/address");
    $myIp = exec('hostname -I | awk \'{print $1}\''); // Get the first IP address
    Log::info("[$myMac][$myIp] My MAC address");
    // Update or create my device
    $d = App\Models\Device::updateOrCreate(
        [
            'mac' => $myMac,
        ],
        [
            'last_ip' => $myIp,
            'last_seen' => now(),
            'name' => env('APP_NAME', 'NetCreep'),
        ]
    );

    if ($d->first_found == null) { $d->first_found = now(); $d->save(); }

    return "ARP scan completed. Found " . count($lines) . " devices.";
}

function arpScanGetMacAddress($line) {
    // Example line: "192.168.1.82    66:c7:33:41:01:bc       (Unknown: locally administered)"
    $parts = preg_split('/\s+/', $line);
    if (count($parts) >= 2) {
        $mac = $parts[1];
        // Validate MAC address format
        if (preg_match('/^([0-9a-f]{2}:){5}[0-9a-f]{2}$/i', $mac)) {
            return $mac;
        }
    }
    return null; // Return null if no valid MAC address found
}

function arpScanGetIPAddress($line) {
    // Example line: "192.168.1.82    66:c7:33:41:01:bc       (Unknown: locally administered)"
    $parts = preg_split('/\s+/', $line);
    if (count($parts) >= 1) {
        $ip = $parts[0];
        // Validate IP address format
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return null; // Return null if no valid IP address found
}

function arpScanGetName($line) {
    // Example line: "192.168.1.82    66:c7:33:41:01:bc       (Unknown: locally administered)"
    $parts = preg_split('/\s+/', $line);
    if (count($parts) >= 3) {
        // The name is usually in parentheses at the end of the line
        $name = trim($parts[2], '()');
        return $name;
    }
    return null; // Return null if no valid name found
}

Artisan::command('reset-settings', function () {
   App\Models\Settings::reset();
})->purpose('Set settings to defaults (also used for 1st set up)');



// run an arp-scan every 5 minutes
Artisan::command('arp-scan', function () {
    $this->comment('Running arp-scan...');
    $output = shell_exec("arp-scan " . getSetting('arp.args'));
    $result = processArpScanOutput($output);
    $this->comment($result);
})->purpose('Run arp-scan ' . getSetting('arp.args') .'');


$frequencyCron = getSetting('arp.frquency-cron');

Schedule::command('arp-scan')
    ->cron($frequencyCron)
    ->withoutOverlapping()
    ->onFailure(function () {
        Log::error('Arp-scan command failed to execute.');
    })
    ->onSuccess(function () {
        Log::info('Arp-scan command executed successfully.');
    });
