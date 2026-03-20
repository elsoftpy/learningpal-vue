<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-200 min-h-screen font-sans antialiased">

    {{-- TopNav mirror (matches resources/js/components/layout/navigation/TopNav.vue) --}}
    <nav class="w-full bg-blue-200 shadow-lg border-blue-200">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center">
                <img src="{{ Vite::asset('resources/js/images/brandLogo.png') }}"
                     class="h-14 me-3"
                     alt="{{ config('app.name') }}">
                <span class="self-center text-xl font-semibold whitespace-nowrap text-slate-900">
                    {{ config('app.name') }}
                </span>
            </div>
        </div>
    </nav>

    {{-- Content (matches AppLayout main + PageContainer) --}}
    <div class="w-full mt-2 px-2">
        <div class="flex w-full bg-slate-50 shadow-md rounded-md">
            <div class="w-full p-2">
                <div class="flex flex-col space-y-2">

                    {{-- PageContainer header bar mirror --}}
                    <div class="flex w-full items-center bg-blue-500 rounded-md p-4">
                        <span class="text-2xl font-semibold text-slate-50">
                            @yield('page-title')
                        </span>
                    </div>

                    {{-- Body slot --}}
                    <div class="flex flex-col items-center justify-center p-4">
                        @yield('body')
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>
</html>
