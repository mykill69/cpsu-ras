<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'CPSU RAS') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-delayed {
            animation: float 6s ease-in-out 2s infinite;
        }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900">
    <div class="min-h-full flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:flex-1 lg:flex-col lg:justify-center lg:px-12 lg:relative overflow-hidden">
            <!-- Decorative Background -->
            <div class="absolute inset-0">
                <div class="absolute top-0 left-0 w-96 h-96 bg-gold-500/10 rounded-full blur-3xl floating"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-primary-400/10 rounded-full blur-3xl floating-delayed"></div>
                <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-gold-400/10 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative z-10">
                <!-- Logo -->
                <div class="mb-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gold-500/20 rounded-2xl backdrop-blur-sm border border-gold-500/30 mb-6">
                        <svg class="w-10 h-10 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-3">CPSU Records Archive</h1>
                    <p class="text-lg text-primary-200/80 leading-relaxed max-w-md">
                        Secure document management system for Central Philippines State University. 
                        Powered by innovation and sustainability.
                    </p>
                </div>

                <!-- Features -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-4 text-primary-100">
                        <div class="w-8 h-8 bg-gold-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <span class="text-sm">Enterprise-grade security & encryption</span>
                    </div>
                    <div class="flex items-center space-x-4 text-primary-100">
                        <div class="w-8 h-8 bg-gold-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <span class="text-sm">Drag & drop file uploads</span>
                    </div>
                    <div class="flex items-center space-x-4 text-primary-100">
                        <div class="w-8 h-8 bg-gold-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-sm">Complete audit trail & activity logs</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <!-- Mobile Logo -->
                <div class="lg:hidden mb-8 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-gold-500/20 rounded-xl border border-gold-500/30 mb-4">
                        <svg class="w-8 h-8 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">CPSU RAS</h2>
                </div>

                <div class="bg-white/10 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-white">Welcome back</h2>
                        <p class="mt-2 text-sm text-primary-200">Sign in to access your records archive</p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="mb-4 p-4 bg-gold-500/10 border border-gold-500/30 rounded-xl text-sm text-gold-300">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-primary-100">Email</label>
                            <div class="mt-2 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                                <input id="email" 
                                       type="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autofocus 
                                       autocomplete="username"
                                       placeholder="admin@cpsu.edu.ph"
                                       class="block w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-primary-300/50 focus:ring-2 focus:ring-gold-500 focus:border-transparent transition-all duration-200">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-primary-100">Password</label>
                            <div class="mt-2 relative" x-data="{ show: false }">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input id="password" 
                                       :type="show ? 'text' : 'password'"
                                       name="password" 
                                       required 
                                       autocomplete="current-password"
                                       placeholder="••••••••"
                                       class="block w-full pl-10 pr-12 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-primary-300/50 focus:ring-2 focus:ring-gold-500 focus:border-transparent transition-all duration-200">
                                <button type="button" 
                                        @click="show = !show"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg x-show="!show" class="h-5 w-5 text-primary-400 hover:text-gold-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="show" class="h-5 w-5 text-primary-400 hover:text-gold-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M15 12a3 3 0 01-3 3m0 0a3 3 0 01-3-3m3 3v4m0-4a9.97 9.97 0 009.543-7 9.97 9.97 0 00-1.563-3.029M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember_me" 
                                       type="checkbox" 
                                       name="remember"
                                       class="h-4 w-4 rounded bg-white/10 border-white/20 text-gold-500 focus:ring-gold-500">
                                <label for="remember_me" class="ml-2 block text-sm text-primary-200">
                                    Remember me
                                </label>
                            </div>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" 
                                   class="text-sm text-gold-400 hover:text-gold-300 transition-colors">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full py-3 px-4 bg-gold-500 hover:bg-gold-600 text-primary-900 font-semibold rounded-xl transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-gold-500 focus:ring-offset-2 focus:ring-offset-primary-900">
                            Sign in
                        </button>
                    </form>
                </div>

                <!-- Footer -->
                <p class="mt-8 text-center text-sm text-primary-300">
                    &copy; {{ date('Y') }} Central Philippines State University
                </p>
            </div>
        </div>
    </div>
</body>
</html>