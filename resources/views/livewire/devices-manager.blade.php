<div><div>
    <style>
        .node:hover{
            background-color: rgba(238, 216, 54, 0)!important;
        }
    </style>
    <div class="" wire:ignore>
        <div id="chart-container"></div>
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
        $transformDevice = function ($device, $hasParent = false) use (&$transformDevice) {
            // Determine relationship string: 'hasParent' 'hasSiblings' 'hasChildren'
            // For simplicity, we'll assume a node always has siblings unless it's the only child of its parent.
            // However, without the full context of how siblings are determined, we'll make a general assumption.
            // For this basic transformation, we'll assume '1' for siblings and parent if it's not the root.
            $hasChildren = $device->children->isNotEmpty() ? '1' : '0';
            $relationship = ($hasParent ? '1' : '0') . '1' . $hasChildren; // Assuming '1' for siblings for now

            // The new library expects 'nodeTitle' and 'nodeContent'.
            // We'll use device name for title and IP for content, or a combination.
            $nodeTitle = $device->name ?: 'Unknown Device';
            $nodeContent = $device->last_ip ?: 'No IP';

            // The 'id' property is used for the node's unique ID.
            $nodeId = $device->mac;

            $node = [
                'id' => $nodeId,
                'nodeTitle' => $nodeTitle,
                'nodeContent' => $nodeContent,
                'relationship' => $relationship,
                'className' => getSetting('style.device-background-color-class') . " mx-[10px!important]  text-white", // Or use a custom class
                'collapsed' => false, // Default to expanded, adjust as needed
                'self_portHTML' => $device->self_port ? '<div class="h-full ' . getSetting('style.self-port-class') . ' items-center p-1">' . $device->self_port . '</div>' : '',
                'otherPro' => [ // This is where you can store additional data
                    'mac' => $device->mac,
                    // You might add the background colors here if you intend to style via nodeTemplate JS
                    'nodeBGColor' => getSetting('style.device-background-color-class'),
                ]
            ];

            // If the device has children, we need to create port nodes first.
            if ($device->children->isNotEmpty()) {
                $ports = $device->children->pluck('parent_port')->unique()->values();
                
                $node['children'] = $ports->map(function ($port) use ($device, $transformDevice) {
                    // Create a port node
                    $portNodeId = displayMac($device) . '-' . $port;
                    $portNodeTitle = 'Port: ' . $port;

                    // Port nodes are children of devices, so they have a parent.
                    $portHasChildren = $device->children->where('parent_port', $port)->isNotEmpty() ? '1' : '0';
                    $portRelationship = '11' . $portHasChildren; // Has parent, has siblings (among ports), has children

                    $portNode = [
                        'id' => $portNodeId,
                        'nodeTitle' => $portNodeTitle,
                        'className' => getSetting('style.children-port-class') . " mx-[10px!important] text-white",
                        'relationship' => $portRelationship,
                        'collapsed' => false, // Default to expanded
                        'children' => [],
                    ];

                    // Filter children for this specific port and recursively transform them.
                    $childrenForPort = $device->children->where('parent_port', $port);
                    if ($childrenForPort->isNotEmpty()) {
                        // Pass true for $hasParent to indicate these are children nodes
                        $portNode['children'] = $childrenForPort->map(fn($child) => $transformDevice($child, true))->values();
                    }
                    return $portNode;
                })->values()->all(); // Convert collection to plain array
            } else {
                // If no children, ensure 'children' property is an empty array for consistency
                $node['children'] = [];
            }

            return $node;
        };

        // 1. Get top-level devices and eager load ALL descendants.
        $devices = App\Models\Device::whereNull('parent_mac')
                                    ->with('childrenRecursive')
                                    ->get();

        // 2. Map over the top-level devices to start the transformation process.
        // Top-level devices do not have a parent, so $hasParent is false.
        $treeData = $devices->map(fn($device) => $transformDevice($device, false))->values()->all(); // Convert collection to plain array

        // 3. Assemble the final data structure for the OrgChart library.
        // The root node 'INTERNET' doesn't have a parent, so its relationship is '011'
        // (no parent, has siblings if other root nodes were present, has children if $treeData is not empty).
        $internetHasChildren = !empty($treeData) ? '1' : '0';
        $fullData = [
            'id' => 'INTERNET',
            'nodeTitle' => 'THE INTERNET',
            'className' => getSetting('style.device-background-color-class') . " mx-[10px!important] text-white",
            'relationship' => '01' . $internetHasChildren, // Assuming it could have siblings if there were multiple 'internet' roots
            'collapsed' => false,
            'children' => $treeData,
        ];
    @endphp

    <script>
        function customNodeTemplate(data) {
            var fixStyleStyle = `style="
                transform:rotate(-90deg) translate(-10px, -20px) rotateY(180deg);
                transform-origin: bottom center;
                width: 130px;
                height:50px;
                font-size: 12px;
                overflow: hidden;
            "`;
            return `<div class="flex gap-2" ${fixStyleStyle}>
            
                <div>${data.self_portHTML}</div>
                <div>${data.nodeTitle}</div>
            
            </div>`;
        }

        const data = @json($fullData);
        $(function() {
            var oc = $('#chart-container').orgchart({
                'data' : data,
                'pan' : true, 
                'zoom' : true,
                'nodeTitle' : 'nodeTitle',
                'nodeContent' : 'nodeContent', 
                'nodeId' : 'id',
                'direction': 'l2r',
                'toggleSiblingsResp': false,
                'nodeTemplate': customNodeTemplate
            });
        });
    </script>

</div></div>
