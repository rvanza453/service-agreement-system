<x-prsystem::app-layout>
    <div class="max-w-xl mx-auto space-y-6">
        <h2 class="text-2xl font-bold text-gray-800">Tambah Unit Baru</h2>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('departments.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <x-prsystem::input-label for="site_id" value="Site / Lokasi" />
                    <select id="site_id" name="site_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                        <option value="">Pilih Site</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->code }})</option>
                        @endforeach
                    </select>
                    <x-prsystem::input-error :messages="$errors->get('site_id')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-prsystem::input-label for="name" value="Nama Unit (contoh: KDE, PKS)" />
                    <x-prsystem::text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                    <x-prsystem::input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-prsystem::input-label for="coa" value="COA (Unik per Site)" />
                    <x-prsystem::text-input id="coa" class="block mt-1 w-full" type="text" name="coa" :value="old('coa')" required />
                    <x-prsystem::input-error :messages="$errors->get('coa')" class="mt-2" />
                </div>

                <div class="flex justify-end pt-4 border-t">
                    <x-prsystem::primary-button>
                        {{ __('Simpan Unit') }}
                    </x-prsystem::primary-button>
                </div>
            </form>
        </div>
    </div>
</x-prsystem::app-layout>
