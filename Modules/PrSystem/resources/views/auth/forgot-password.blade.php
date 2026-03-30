<x-prsystem::guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-prsystem::auth-session-status class="mb-4" :status="session('status')" />

    @if (Route::has('password.email'))
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-prsystem::input-label for="email" :value="__('Email')" />
            <x-prsystem::text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-prsystem::input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-prsystem::primary-button>
                {{ __('Email Password Reset Link') }}
            </x-prsystem::primary-button>
        </div>
    </form>
    @else
    <div class="rounded-md border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800">
        Fitur reset password melalui email belum tersedia pada sistem ini.
    </div>
    @endif
</x-prsystem::guest-layout>
