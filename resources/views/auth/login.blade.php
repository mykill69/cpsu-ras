<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#13291D">
    <title>Sign in · CPSU RAS</title>
    <link rel="shortcut icon" href="{{ asset('images/cpsu-logo.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Faint ledger rules — a nod to the paper registers this system replaces */
        .ledger {
            background-image:
                linear-gradient(to right, rgba(215, 177, 62, .10) 1px, transparent 1px),
                repeating-linear-gradient(to bottom, transparent 0 31px, rgba(255, 255, 255, .045) 31px 32px);
            background-size: 100% 100%, 100% 32px;
            background-position: 72px 0, 0 0;
            background-repeat: no-repeat, repeat;
        }
    </style>
</head>
<body class="h-full">
    <div class="flex min-h-full">

        <!-- Brand panel -->
        <aside class="ledger relative hidden w-[46%] max-w-[640px] flex-col justify-between bg-primary-900 px-14 py-12 text-primary-100 lg:flex">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/cpsu-logo.png') }}" alt="" class="h-10 w-10" width="40" height="40">
                <p class="text-sm leading-tight">
                    <span class="block font-medium text-white">Central Philippines State University</span>
                    <span class="block text-primary-300">Negros Occidental · Est. 1946</span>
                </p>
            </div>

            <div class="max-w-md">
                <p class="eyebrow !text-gold-400">Records Archive System</p>
                <h1 class="mt-4 font-serif text-[40px] font-semibold leading-[1.1] text-white">
                    Every record,<br>filed where you can find it.
                </h1>
                <p class="mt-5 text-[15px] leading-relaxed text-primary-200">
                    Upload, organise and retrieve university documents by department,
                    with every action written to an audit trail.
                </p>
            </div>

            <dl class="grid grid-cols-3 gap-6 border-t border-white/10 pt-6 text-sm">
                <div>
                    <dt class="text-primary-300">Storage</dt>
                    <dd class="mt-1 font-medium text-white">Encrypted at rest</dd>
                </div>
                <div>
                    <dt class="text-primary-300">Access</dt>
                    <dd class="mt-1 font-medium text-white">Role-based</dd>
                </div>
                <div>
                    <dt class="text-primary-300">History</dt>
                    <dd class="mt-1 font-medium text-white">Full audit log</dd>
                </div>
            </dl>
        </aside>

        <!-- Form -->
        <main class="flex flex-1 flex-col px-5 py-10 sm:px-10">
            <div class="m-auto w-full max-w-[380px]">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('images/cpsu-logo.png') }}" alt="" class="h-11 w-11" width="44" height="44">
                    <p class="leading-tight">
                        <span class="block font-serif text-lg font-semibold text-primary-900">CPSU Records</span>
                        <span class="block text-xs text-gray-500">Archive System</span>
                    </p>
                </div>

                <h2 class="font-serif text-[28px] font-semibold text-primary-900">Sign in</h2>
                <p class="mt-1.5 text-sm text-gray-500">Use your university-issued account.</p>

                @if (session('status'))
                    <div class="mt-6 rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-800" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5" x-data="{ submitting: false }"
                    @submit="submitting = true">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-800">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            autocomplete="username" placeholder="name@cpsu.edu.ph"
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                            class="mt-1.5 block w-full rounded-lg border-gray-300 bg-white px-3.5 py-2.5 text-[15px] text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary-600 focus:ring-primary-600 @error('email') border-red-400 @enderror">
                        @error('email')
                            <p id="email-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ show: false }">
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium text-gray-800">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm text-primary-700 hover:underline">Forgot?</a>
                            @endif
                        </div>
                        <div class="relative mt-1.5">
                            <input id="password" :type="show ? 'text' : 'password'" type="password" name="password" required
                                autocomplete="current-password"
                                @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                                class="block w-full rounded-lg border-gray-300 bg-white py-2.5 pl-3.5 pr-16 text-[15px] text-gray-900 shadow-sm focus:border-primary-600 focus:ring-primary-600 @error('password') border-red-400 @enderror">
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-1 right-1 rounded-md px-2.5 text-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900"
                                :aria-pressed="show.toString()" x-text="show ? 'Hide' : 'Show'">Show</button>
                        </div>
                        @error('password')
                            <p id="password-error" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <label for="remember_me" class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-gray-600">
                        <input id="remember_me" type="checkbox" name="remember"
                            class="h-4 w-4 rounded border-gray-300 text-primary-700 focus:ring-primary-600">
                        Keep me signed in on this device
                    </label>

                    <button type="submit" class="btn-primary w-full py-2.5 text-[15px]" :disabled="submitting">
                        <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3" />
                            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        <span x-text="submitting ? 'Signing in…' : 'Sign in'">Sign in</span>
                    </button>
                </form>

                <p class="mt-10 text-xs text-gray-400">
                    Trouble signing in? Contact the Records Office.<br>
                    &copy; {{ date('Y') }} Central Philippines State University
                </p>
            </div>
        </main>
    </div>
</body>
</html>
