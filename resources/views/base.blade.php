<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{-- style --}}
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        {{-- diagram --}}
        <script src="https://cdn.jsdelivr.net/npm/apextree"></script>

        @livewireStyles
        <title>{{ config('app.name') }}</title>
        @yield('head')
    </head>
    <body class="antialiased">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            <div style="font-size:12px; line-height:normal;" class="fixed top-0 bg-red-900 px-2 text-white z-50 self-center">
                @php
                    use Symfony\Component\Process\Process;

                    function css_yellow($text) {
                        return '<span style="color: #f59e0b;">' . $text . '</span>';
                    }

                    $process = new Process(['which', 'arp-scan']);
                    $process->run();
                   
                    $result = $process->getOutput();
                    if(!empty($result)) {
                        $arpScanVersion = trim($result);
                    } else {
                        $arpScanVersion = 'Not installed';
                    }

                    $gitHeadPath = base_path('.git/HEAD');
                    $gitStr = file($gitHeadPath);
                    $firstLine = $gitStr[0];
                    $gitBranchArray = explode("/", $firstLine, 3);
                    $branchname = array_key_exists(2, $gitBranchArray) ? $gitBranchArray[2] : 'unknown';
                @endphp
                
                <p style="font-size:12px; line-height:normal;" class="text-white ">
                    Branch: {!!css_yellow($branchname)!!} | 
                    Database: {!!css_yellow(env('DB_DATABASE'))!!} | 
                    App Name: {!!css_yellow(env('APP_NAME'))!!} | 
                    Laravel: {!! css_yellow(app()->version()) !!} | 
                    PHP  {!! css_yellow(PHP_VERSION) !!} | 
                    arp-scan: {!! css_yellow($arpScanVersion) !!}
                </p>

            </div>
            @yield('body')
            <div class="fixed top-0 right-0 p-2 z-50">
                <a href="{{ route('devices') }}" class="inline-block bg-gray-800 text-white rounded-full p-1 hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 rounded-full fill-white" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M0 224L0 416c0 17.7 14.3 32 32 32l64 0 0-112c0-8.8 7.2-16 16-16s16 7.2 16 16l0 112 64 0 0-112c0-8.8 7.2-16 16-16s16 7.2 16 16l0 112 64 0 0-112c0-8.8 7.2-16 16-16s16 7.2 16 16l0 112 64 0 0-112c0-8.8 7.2-16 16-16s16 7.2 16 16l0 112 64 0c17.7 0 32-14.3 32-32l0-192c0-17.7-14.3-32-32-32l-32 0 0-32c0-17.7-14.3-32-32-32l-32 0 0-32c0-17.7-14.3-32-32-32L160 64c-17.7 0-32 14.3-32 32l0 32-32 0c-17.7 0-32 14.3-32 32l0 32-32 0c-17.7 0-32 14.3-32 32z"/></svg>
                </a>
                <a href="" class="inline-block bg-gray-800 text-white rounded-full p-1 hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 rounded-full fill-white" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M487.4 315.7l-42.6-24.6c4.3-23.2 4.3-47 0-70.2l42.6-24.6c4.9-2.8 7.1-8.6 5.5-14-11.1-35.6-30-67.8-54.7-94.6-3.8-4.1-10-5.1-14.8-2.3L380.8 110c-17.9-15.4-38.5-27.3-60.8-35.1V25.8c0-5.6-3.9-10.5-9.4-11.7-36.7-8.2-74.3-7.8-109.2 0-5.5 1.2-9.4 6.1-9.4 11.7V75c-22.2 7.9-42.8 19.8-60.8 35.1L88.7 85.5c-4.9-2.8-11-1.9-14.8 2.3-24.7 26.7-43.6 58.9-54.7 94.6-1.7 5.4 .6 11.2 5.5 14L67.3 221c-4.3 23.2-4.3 47 0 70.2l-42.6 24.6c-4.9 2.8-7.1 8.6-5.5 14 11.1 35.6 30 67.8 54.7 94.6 3.8 4.1 10 5.1 14.8 2.3l42.6-24.6c17.9 15.4 38.5 27.3 60.8 35.1v49.2c0 5.6 3.9 10.5 9.4 11.7 36.7 8.2 74.3 7.8 109.2 0 5.5-1.2 9.4-6.1 9.4-11.7v-49.2c22.2-7.9 42.8-19.8 60.8-35.1l42.6 24.6c4.9 2.8 11 1.9 14.8-2.3 24.7-26.7 43.6-58.9 54.7-94.6 1.5-5.5-.7-11.3-5.6-14.1zM256 336c-44.1 0-80-35.9-80-80s35.9-80 80-80 80 35.9 80 80-35.9 80-80 80z"/></svg>
                </a>
                @if(asset('images/logo.png'))
                    <a href="" class="inline-block bg-gray-800 text-white rounded-full p-1 hover:bg-gray-700">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class=" -10 w-10 object-cover rounded-full">
                    </a>
                @endif
            </div>
            @livewireScripts
        </div>
    </body>
</html>
