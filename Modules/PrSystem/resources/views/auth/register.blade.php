<x-prsystem::guest-layout>
    @if (Route::has('register'))
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-prsystem::input-label for="name" :value="__('Name')" />
            <x-prsystem::text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-prsystem::input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-prsystem::input-label for="email" :value="__('Email')" />
            <x-prsystem::text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-prsystem::input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-prsystem::input-label for="password" :value="__('Password')" />

            <x-prsystem::text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-prsystem::input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-prsystem::input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-prsystem::text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-prsystem::input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-prsystem::primary-button class="ms-4">
                {{ __('Register') }}
            </x-prsystem::primary-button>
        </div>
    </form>
    @else
    <div class="rounded-md border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800">
        Registrasi akun dinonaktifkan pada sistem ini.
    </div>
    @endif
</x-prsystem::guest-layout>
