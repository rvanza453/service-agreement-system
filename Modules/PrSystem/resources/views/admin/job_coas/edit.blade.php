<x-prsystem::app-layout>
    <div class="max-w-xl mx-auto space-y-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Job COA</h2>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('job-coas.update', $jobCoa) }}" method="POST">
                @csrf
                @method('PUT')
                

                <div class="mb-4">
                    <x-prsystem::input-label for="code" value="Kode Job COA (Misal: 600-01)" />
                    <x-prsystem::text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $jobCoa->code)" required />
                    <x-prsystem::input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-prsystem::input-label for="name" value="Deskripsi Job / Nama" />
                    <x-prsystem::text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $jobCoa->name)" required />
                    <x-prsystem::input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="flex justify-end pt-4 border-t">
                    <a href="{{ route('job-coas.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition mr-2">Batal</a>
                    <x-prsystem::primary-button>
                        {{ __('Simpan Perubahan') }}
                    </x-prsystem::primary-button>
                </div>
            </form>
        </div>
    </div>

</x-prsystem::app-layout>
