<div><div>
    <style>
        .bg-red-400 {
            background-color: oklch(70.4% 0.191 22.216);
        }
        .node:hover{
            background-color: var(--original-bg) !important;
            cursor: pointer !important;
        }
        .orgchart .node.focused {
            background-color: var(--original-bg) !important;
        }
    </style>
    <div class="" wire:ignore>
        <div id="chart-container"></div>
    </div>
    
    @if($device_id)
        <div class="fixed bottom-0 left-0 right-0 {{getSetting('style.alt-class')}} p-4 text-white max-w-90 h-full overflow-y-auto">
            <button wire:click="deselectDevice" class="absolute top-4 right-4 text-red-500 hover:text-red-700 cursor-pointer">X</button>
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
            <button wire:click="deselectPort" class="absolute top-4 right-4 text-red-500 hover:text-red-700 cursor-pointer">X</button>
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
            $hasChildren = $device->children->isNotEmpty() ? '1' : '0';
            $relationship = ($hasParent ? '1' : '0') . '1' . $hasChildren; 
            $nodeTitle = $device->name ?: 'Unknown Device';
            $nodeContent = $device->last_ip ?: 'No IP';

            $nodeId = $device->mac;
            $node = [
                'id' => $nodeId,
                'nodeTitle' => $nodeTitle,
                'nodeContent' => $nodeContent,
                'relationship' => $relationship,
                'className' => getSetting('style.device-background-color-class') . " mx-[10px!important]  text-white",
                'collapsed' => false, 
                'isPort' => false,
                'self_portHTML' => $device->self_port ? '<div class="h-full ' . getSetting('style.self-port-class') . ' items-center p-1">' . $device->self_port . '</div>' : '',
            ];

            if ($device->children->isNotEmpty()) {
                $ports = $device->children->pluck('parent_port')->unique()->values();
                
                $node['children'] = $ports->map(function ($port) use ($device, $transformDevice) {
                    $portNodeId = "port-" . $device->mac . "-" . $port;
                    $portNodeTitle = $port;

                    $portHasChildren = $device->children->where('parent_port', $port)->isNotEmpty() ? '1' : '0';
                    $portRelationship = '11' . $portHasChildren; 

                    $portNode = [
                        'id' => $portNodeId,
                        'nodeTitle' => $portNodeTitle,
                        'className' => getSetting('style.children-port-class') . " mx-[10px!important] text-white portNode",
                        'relationship' => $portRelationship,
                        'collapsed' => false, 
                        'children' => [],
                        'isPort' => true,
                        'self_portHTML' => '',
                    ];

                    $childrenForPort = $device->children->where('parent_port', $port);
                    if ($childrenForPort->isNotEmpty()) {
                        $portNode['children'] = $childrenForPort->map(fn($child) => $transformDevice($child, true))->values();
                    }
                    return $portNode;
                })->values()->all(); 
            } else {
                $node['children'] = [];
            }

            return $node;
        };

        $devices = App\Models\Device::whereNull('parent_mac')
                                    ->with('childrenRecursive')
                                    ->get();

        $treeData = $devices->map(fn($device) => $transformDevice($device, false))->values()->all(); 

        $internetHasChildren = !empty($treeData) ? '1' : '0';
        $fullData = [
            'id' => 'INTERNET',
            'nodeTitle' => 'THE INTERNET',
            'className' => getSetting('style.device-background-color-class') . " mx-[10px!important] text-white",
            'relationship' => '01' . $internetHasChildren, 
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
            var subTitle = data.isPort ? "Port:" : "";
            var contentStyle = data.isPort ? `style="transform: translate(4%,-90%);"` : `style="width: 100%;height: 100%;"`;
            return `<div class="flex gap-2 noIsibs" ${fixStyleStyle} wire:click="deviceSelected('${data.id}')">
                ${data.self_portHTML ? `<div class="flex self-center">${data.self_portHTML}</div>` : ''}
                
                <div class="flex-col items-center self-center text-xs" ${contentStyle}>
                    ${subTitle ? `${subTitle}` : ''} ${data.nodeTitle}
                    ${data.nodeContent ? `<div>(${data.nodeContent})</div>` : ''}
                </div>
            </div>
            `;
            
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

            $('#chart-container').on('click', '.toggle-children-btn', function(e) {
                console.log("oi");
                e.stopPropagation(); // Prevent orgchart's default node click behavior
                var $btn = $(this);
                var nodeDiv = $btn.parent();
                console.log(nodeDiv);
                if (nodeDiv.length) {
                    var $childrenUls = nodeDiv.siblings('ul.nodes');
                    if ($childrenUls.length) {
                        $childrenUls.toggleClass('hidden');
                        if ($childrenUls.first().hasClass('hidden')) {
                            $btn.text('+');
                        } else {
                            $btn.text('-');
                        }
                    }
                }
            });

            $('.noIsibs').each(function() {
                const siblingsToRemove = $(this).siblings('i');
                if (siblingsToRemove.length > 0) {
                    siblingsToRemove.remove();
                }
            });

            $('.node').each(function() {
                var $this = $(this);
                var originalBgColor = $this.css('background-color');
                $this.css('--original-bg', originalBgColor);
            });
            
            $('.portNode').each(function() {
                var $this = $(this);
                var portStyle = {
                    "height": "85px",
                    "width": "20px"
                };
                $this.css(portStyle);
            });
            $('.portContent').each(function() {
                var $this = $(this);
                var portStyle = {
                    "height": "69px",
                    "width": "22px"
                };
                $this.css(portStyle);
            });
            

            $('div.jump-up').each(function() {
                var $jumpUpDiv = $(this); 
                var $parentDiv = $jumpUpDiv.parent(); 

                if ($parentDiv.length) { 
                    $jumpUpDiv.insertAfter($parentDiv);
                } else {
                    console.warn(`Element without parent found:`, $jumpUpDiv[0]);
                }
            });
        });

    </script>

</div></div>
