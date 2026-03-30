<x-prsystem::guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    @if (Route::has('password.confirm'))
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-prsystem::input-label for="password" :value="__('Password')" />

            <x-prsystem::text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-prsystem::input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-prsystem::primary-button>
                {{ __('Confirm') }}
            </x-prsystem::primary-button>
        </div>
    </form>
    @else
    <div class="rounded-md border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800">
        Konfirmasi password tambahan tidak diaktifkan pada sistem ini.
    </div>
    @endif
</x-prsystem::guest-layout>
