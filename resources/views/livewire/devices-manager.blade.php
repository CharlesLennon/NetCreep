<div><div>

    <div class="flex justify-center items-center h-screen" wire:ignore>
        <div id="svg-tree" class="w-full h-full"></div>
    </div>
    
    @if($device_id)
        <div class="fixed bottom-0 left-0 right-0 {{getSetting('style.alt-class')}} p-4 text-white max-w-90 h-full overflow-y-auto">
            <button wire:click="deselectDevice" class="absolute top-4 right-4 text-red-500 hover:text-red-700">X</button>
            <h2 class="text-lg font-bold">Selected Device: {{ displayMac($device_id) }}</h2>
            <h1 class="text-md font-semibold">Device Details</h1>
            <p><strong>MAC:</strong> {{ displayMac($device_mac) }}</p>
            <p><strong>IP:</strong> {{ $device_ip }}</p>
            <p><strong>First Found:</strong> {{ $device_first_found }}</p>
            <p><strong>Last Seen:</strong> {{ $device_last_seen }}</p>
            <div class="mt-2">
                <label class="block mb-1">Name:</label>
                <input type="text" wire:model="device_name" class="w-full p-2 {{getSetting('style.input-class')}} text-white rounded" />
            
                <label class="block mt-2 mb-1">Parent MAC:</label>
                <select wire:model="device_parent_mac" class="w-full p-2 {{getSetting('style.input-class')}} text-white rounded">
                    <option value="">None</option>
                    @php
                        $parentMacOptions = App\Models\Device::select(['mac','name'])->get()->pluck('mac','name')->toArray();
                    @endphp
                    @foreach($parentMacOptions as $key => $value)
                        <option value="{{ $value }}">{{ displayMac($value) }} ({{ $key }})</option>
                    @endforeach
                </select>
                <label class="block mt-2 mb-1">Parent Port:</label>
                <input type="text" wire:model="device_parent_port" class="w-full p-2 {{getSetting('style.input-class')}} text-white rounded" />
                <label class="block mt-2 mb-1">Self Port:</label>
                <input type="text" wire:model="device_self_port" class="w-full p-2 {{getSetting('style.input-class')}} text-white rounded" />
                <div class="flex justify-between mt-4">
                    <button wire:click="saveDevice" class="mt-2 {{getSetting('style.main-button-class')}} hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Device
                    </button>
                    <button wire:click="deleteDevice" class="mt-2 {{getSetting('style.danger-button-class')}} hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Delete Device
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($port_parent_mac)
        <div class="fixed bottom-0 left-0 right-0 {{getSetting('style.alt-class')}} p-4 text-white max-w-90 h-full overflow-y-auto">
            <button wire:click="deselectPort" class="absolute top-4 right-4 text-red-500 hover:text-red-700">X</button>
            <h2 class="text-lg font-bold">Selected Port: {{ $port }} on {{displayMac($this->port_parent_mac)}}</h2>
            <div class="mt-2">
                <label class="block mb-1">New Port Value:</label>
                <input type="text" wire:model="newPort" class="w-full p-2 {{getSetting('style.input-class')}} text-white rounded" />
                <div class="flex justify-between mt-4">
                    <button wire:click="savePort" class="mt-2 {{getSetting('style.main-button-class')}} hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Port
                    </button>
                    <button wire:click="deletePort" class="mt-2 {{getSetting('style.danger-button-class')}} hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Delete Port
                    </button>
                </div>
            </div>
        </div>


    @endif

    @php
        // We define a recursive function (a "closure") to transform each device.
        // The `use (&$transform)` part allows the function to call itself.
        $transformDevice = function ($device) use (&$transformDevice) {
            $selfPortBackgroundColor = getSetting('style.self-port-class');
            $node = [
                'id' => $device->mac,
                'data' => [
                    'name' => ($device->name ?: 'unknown') ." ". $device->last_ip ,
                    'id' => $device->mac,
                    'self_portHTML' => $device->self_port ? '<div class="flex h-full ' . $selfPortBackgroundColor . ' items-center p-1">' . $device->self_port . '</div>' : '',
                ],
                'options' => [
                    'nodeBGColor' =>  getSetting('style.device-background-color'),
                    'nodeBGColorHover' => getSetting('style.device-background-color-hover')
                ]
            ];

            // If the device has children, we recursively transform them.
            if ($device->children->isNotEmpty()) {
                // We use ->map() to apply this same function to each child.
                //it should be device -> children_parent_port -> children
                $ports = $device->children->pluck('parent_port')->unique()->values();
                //each port should be a node
                $node['children'] = $ports->map(function ($port) use ($device, $transformDevice) {
                    // Create a port node
                    $portNode = [
                        'id' => displayMac($device) . '-' . $port,
                        'data' => [
                            'name' => 'Port: ' . $port,
                            'id' => "port-" . $device->mac . '-' . $port,
                        ],
                        'options' => [
                            'nodeBGColor' => getSetting('style.children-port-background-color'),
                            'nodeBGColorHover' => getSetting('style.children-port-background-color-hover')
                        ],
                        'children' => []
                    ];
                    // Filter children for this port
                    $childrenForPort = $device->children->where('parent_port', $port);
                    // If there are children for this port, transform them
                    if ($childrenForPort->isNotEmpty()) {
                        $portNode['children'] = $childrenForPort->map($transformDevice)->values();
                    }
                    return $portNode;
                })->values();
            } else {
                // If there are no children, we just set an empty array.
                // This is important to ensure the structure is consistent.
                // Otherwise, ApexTree might not render the node correctly.


                $node['children'] = $device->children->map($transformDevice)->values();
            }

            return $node;
        };

        // 1. Get top-level devices and eager load ALL descendants using our new relationship.
        $devices = App\Models\Device::whereNull('parent_mac')
                                    ->with('childrenRecursive')
                                    ->get();

        // 2. Map over the top-level devices to start the transformation process.
        $treeData = $devices->map($transformDevice)->values();

        // 3. Assemble the final data structure for the ApexTree library.
        $fullData = [
            'id' => 'INTERNET',
            'data' => [
                'name' => 'THE INTERNET',
                'id' => 'INTERNET'
            ],
            'options' => ['nodeBGColor' => 'var(--color-red-900)', 'nodeBGColorHover' => 'var(--color-red-900)'],
            'children' => $treeData,
        ];
    @endphp

    <script>
        const data = @json($fullData);

        const options = {
            contentKey: 'data',
            width: "100vw",
            height: "100vh",
            nodeWidth: 250,
            nodeHeight: 50,
            fontColor: '#fff',
            borderColor: '#333',
            childrenSpacing: 50,
            siblingSpacing: 20,
            direction: 'left',
            enableExpandCollapse: true,
            nodeTemplate: (content) =>
            `
            <div style='display: flex;flex-direction: row;justify-content: flex-start;align-items: center;height: 100%;' wire:click="deviceSelected('${content.id}')">
                ${content.self_portHTML || ''}
                <div class='px-4' style="font-weight: bold; font-family: Arial; font-size: 14px">${content.name}</div>
            </div>`,
            canvasStyle: 'border: 1px solid black;background: var(--color-gray-900)',
            enableToolbar: false,
        };
        
        const tree = new ApexTree(document.getElementById('svg-tree'), options);
        tree.render(data);
    </script>

</div></div>
