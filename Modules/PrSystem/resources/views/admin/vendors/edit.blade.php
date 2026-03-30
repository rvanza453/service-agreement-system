<x-prsystem::app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                {{ __('Edit Supplier') }}
            </h2>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('vendors.update', $vendor) }}" class="space-y-6 max-w-2xl">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div>
                            <x-prsystem::input-label for="name" :value="__('Nama Supplier')" />
                            <x-prsystem::text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $vendor->name)" required autofocus />
                            <x-prsystem::input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Status -->
                        <div>
                            <x-prsystem::input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">
                                @foreach(['Belum Transaksi', 'Pernah Transaksi', 'Di Ajukan'] as $st)
                                    <option value="{{ $st }}" {{ old('status', $vendor->status) == $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                            <x-prsystem::input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <!-- Kategori & Lokasi -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-prsystem::input-label for="category" :value="__('Kategori Supplier')" />
                                <x-prsystem::text-input id="category" class="block mt-1 w-full" type="text" name="category" :value="old('category', $vendor->category ?? '')" />
                                <x-prsystem::input-error :messages="$errors->get('category')" class="mt-2" />
                            </div>
                            <div>
                                <x-prsystem::input-label for="location" :value="__('Lokasi')" />
                                <x-prsystem::text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location', $vendor->location ?? '')" />
                                <x-prsystem::input-error :messages="$errors->get('location')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <x-prsystem::input-label for="description" :value="__('Keterangan')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">{{ old('description', $vendor->description) }}</textarea>
                            <x-prsystem::input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Alamat -->
                        <div>
                            <x-prsystem::input-label for="address" :value="__('Alamat Lengkap')" />
                            <textarea id="address" name="address" rows="3" class="block mt-1 w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">{{ old('address', $vendor->address) }}</textarea>
                            <x-prsystem::input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <!-- PIC & Kontak -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-prsystem::input-label for="pic_name" :value="__('Nama PIC')" />
                                <x-prsystem::text-input id="pic_name" class="block mt-1 w-full" type="text" name="pic_name" :value="old('pic_name', $vendor->pic_name)" />
                            </div>
                            <div>
                                <x-prsystem::input-label for="phone" :value="__('No Telpon / HP')" />
                                <x-prsystem::text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $vendor->phone)" />
                            </div>
                            <div>
                                <x-prsystem::input-label for="email" :value="__('Email')" />
                                <x-prsystem::text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $vendor->email)" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-prsystem::primary-button>{{ __('Update Supplier') }}</x-prsystem::primary-button>
                            <a href="{{ route('vendors.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
