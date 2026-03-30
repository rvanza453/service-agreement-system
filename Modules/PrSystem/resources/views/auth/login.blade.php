<x-prsystem::guest-layout>
    <!-- Session Status -->
    <x-prsystem::auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-10">
        <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">
            Purchasing System
        </h2>
        <p class="text-gray-500 mt-2 text-sm">Please sign in to your account</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="relative group">
            <x-prsystem::input-label for="email" :value="__('Email')" class="sr-only" />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
            </div>
            <x-prsystem::text-input id="email" class="block w-full pl-10 pr-3 py-3 border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-xl transition duration-200 bg-gray-50 hover:bg-white" 
                        type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Email address" />
            <x-prsystem::input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="relative group">
            <x-prsystem::input-label for="password" :value="__('Password')" class="sr-only" />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
            </div>
            <x-prsystem::text-input id="password" class="block w-full pl-10 pr-3 py-3 border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-xl transition duration-200 bg-gray-50 hover:bg-white"
                            type="password"
                            name="password"
                            required autocomplete="current-password" 
                            placeholder="Password" />

            <x-prsystem::input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 transition duration-150 ease-in-out" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition duration-150 ease-in-out" href="https://wa.me/+6281234422621">
                    {{ __('Forgot password? Text Me') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <button type="submit" 
                style="background-color: #4f46e5;"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-indigo-600 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition hover:scale-[1.02] duration-200">
                {{ __('LOG IN') }}
            </button>
        </div>
    </form>
</x-prsystem::guest-layout>
