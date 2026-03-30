<x-prsystem::app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                {{ __('Edit Product') }}
            </h2>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6 max-w-xl">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-prsystem::input-label for="code" :value="__('Product Code')" />
                            <x-prsystem::text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $product->code)" required autofocus />
                            <x-prsystem::input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>

                        <div>
                            <x-prsystem::input-label for="name" :value="__('Product Name')" />
                            <x-prsystem::text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $product->name)" required />
                            <x-prsystem::input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-prsystem::input-label for="category" :value="__('Category')" />
                            <select id="category" name="category" class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">
                                <option value="">-- Select Category --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category', $product->category) == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                            <x-prsystem::input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-prsystem::input-label for="unit" :value="__('Unit (e.g. PCS, KG)')" />
                                <x-prsystem::text-input id="unit" class="block mt-1 w-full" type="text" name="unit" :value="old('unit', $product->unit)" required />
                                <x-prsystem::input-error :messages="$errors->get('unit')" class="mt-2" />
                            </div>

                            <div>
                                <x-prsystem::input-label for="price_estimation" :value="__('Estimasi Harga (Rp)')" />
                                <x-prsystem::text-input id="price_estimation" class="block mt-1 w-full" type="number" name="price_estimation" :value="old('price_estimation', $product->price_estimation)" min="0" step="0.01" />
                                <x-prsystem::input-error :messages="$errors->get('price_estimation')" class="mt-2" />
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">Tersedia di Site:</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($sites as $site)
                                    <label class="inline-flex items-center space-x-2">
                                        <input type="checkbox" 
                                               name="sites[]" 
                                               value="{{ $site->id }}"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                               {{-- Logika untuk Edit: Jika produk punya relasi ke site ini, centang --}}
                                               @if(isset($product) && $product->sites->contains($site->id)) checked @endif
                                        >
                                        <span class="text-sm text-gray-700">{{ $site->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('sites')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-prsystem::input-label for="min_stock" :value="__('Min Stock')" />
                            <x-prsystem::text-input id="min_stock" class="block mt-1 w-full" type="number" name="min_stock" :value="old('min_stock', $product->min_stock)" min="0" />
                            <x-prsystem::input-error :messages="$errors->get('min_stock')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-prsystem::primary-button>{{ __('Update Product') }}</x-prsystem::primary-button>
                            <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
