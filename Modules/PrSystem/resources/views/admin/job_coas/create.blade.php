<x-prsystem::app-layout>
    <div class="max-w-xl mx-auto space-y-6">
        <h2 class="text-2xl font-bold text-gray-800">Tambah Job COA Baru</h2>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('job-coas.store') }}" method="POST">
                @csrf
                
                <div class="p-6 text-gray-900 space-y-6">
                    
                     <!-- Code -->
                    <div>
                        <x-prsystem::input-label for="code" :value="__('Kode Job COA')" />
                        <x-prsystem::text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code')" placeholder="Contoh: 600-01" required autofocus />
                        <x-prsystem::input-error class="mt-2" :messages="$errors->get('code')" />
                        <p class="text-xs text-gray-500 mt-1">Kode unik untuk Job COA ini.</p>
                    </div>

                    <!-- Name -->
                    <div>
                        <x-prsystem::input-label for="name" :value="__('Nama / Deskripsi Job')" />
                        <x-prsystem::text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" placeholder="Contoh: Biaya Panen" required />
                        <x-prsystem::input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t">
                    <a href="{{ route('job-coas.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition mr-2">Batal</a>
                    <x-prsystem::primary-button>
                        {{ __('Simpan Job COA') }}
                    </x-prsystem::primary-button>
                </div>
            </form>
        </div>
    </div>

</x-prsystem::app-layout>
