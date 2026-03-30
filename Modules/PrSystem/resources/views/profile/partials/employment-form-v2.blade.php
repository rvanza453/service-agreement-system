<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Kepegawaian (Edit)') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Informasi detail mengenai posisi dan penempatan kerja Anda.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update-employment') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Site -->
        <div>
            <x-prsystem::input-label for="site_id" :value="__('Lokasi / Site')" />
            <select id="site_id" name="site_id" class="mt-1 block w-full bg-white border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Pilih Site --</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ $user->site_id == $site->id ? 'selected' : '' }}>
                        {{ $site->name }} ({{ $site->code }})
                    </option>
                @endforeach
            </select>
            <x-prsystem::input-error class="mt-2" :messages="$errors->get('site_id')" />
        </div>

        <!-- Department -->
        <div>
            <x-prsystem::input-label for="department_id" :value="__('Unit')" />
             <select id="department_id" name="department_id" class="mt-1 block w-full bg-white border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-- Pilih Unit --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $user->department_id == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }} ({{ $dept->coa }})
                    </option>
                @endforeach
            </select>
            <x-prsystem::input-error class="mt-2" :messages="$errors->get('department_id')" />
        </div>

        <!-- Position -->
        <div>
            <x-prsystem::input-label for="position" :value="__('Jabatan / Posisi')" />
            <input id="position" name="position" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{ old('position', $user->position) }}">
            <x-prsystem::input-error class="mt-2" :messages="$errors->get('position')" />
        </div>

        <!-- Role (Read Only) -->
        <div>
            <x-prsystem::input-label for="role" :value="__('Role Akses (Tidak dapat diubah)')" />
            <div class="mt-1 block w-full p-2.5 bg-gray-100 border border-gray-300 text-gray-500 text-sm rounded-lg">
                @foreach($user->getRoleNames() as $role)
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">{{ $role }}</span>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Simpan Perubahan') }}
            </button>

            @if (session('status') === 'employment-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 font-medium"
                >{{ __('Data berhasil disimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
