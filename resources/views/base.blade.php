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
            @livewireScripts
        </div>
    </body>
</html>
