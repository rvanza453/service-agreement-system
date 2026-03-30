<x-prsystem::guest-layout>
    @if (Route::has('password.store'))
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-prsystem::input-label for="email" :value="__('Email')" />
            <x-prsystem::text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-prsystem::input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-prsystem::input-label for="password" :value="__('Password')" />
            <x-prsystem::text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
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
            <x-prsystem::primary-button>
                {{ __('Reset Password') }}
            </x-prsystem::primary-button>
        </div>
    </form>
    @else
    <div class="rounded-md border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800">
        Endpoint reset password tidak tersedia pada sistem ini.
    </div>
    @endif
</x-prsystem::guest-layout>
