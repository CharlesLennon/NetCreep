<div class="w-full">
    <div class="m-5">
        @foreach($sections as $section)
            <h2 class="text-white">{{ $section }}<h2>
            <div class="m-5">
                @foreach($settings as $arrayKey => $setting)
                    @if(\Illuminate\Support\Str::startsWith($setting['name'],$section))
                        <label class="mb-1 text-white">{{$setting['name']}}</label>
                        <p class="mb-1 text-gray-400 text-sm">{{$setting['description']}}</p>
                        <input type="text" wire:model="settings.{{$arrayKey}}.value" class="w-full p-2 {{getSetting('style.input-class')}} text-white rounded" />
                    @endif
                @endforeach
            </div>
        @endforeach
        <button wire:click="updateSettings" class="mt-2 {{getSetting('style.main-button-class')}} hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Update
        </button>
        <button wire:click="resetSettings" class="mt-2 {{getSetting('style.danger-button-class')}} hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
            Reset
        </button>
</div>
</div>
