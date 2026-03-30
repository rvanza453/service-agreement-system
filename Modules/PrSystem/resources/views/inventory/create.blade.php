<x-prsystem::app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">{{ isset($warehouse) ? 'Edit Gudang' : 'Buat Gudang Baru' }}</h2>
            <a href="{{ route('inventory.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                &larr; Kembali
            </a>
        </div>

        <form action="{{ isset($warehouse) ? route('inventory.update', $warehouse) : route('inventory.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-6">
            @csrf
            @if(isset($warehouse))
                @method('PUT')
            @endif

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Gudang *</label>
                <input type="text" name="name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" 
                       value="{{ old('name', $warehouse->name ?? '') }}" 
                       placeholder="Contoh: Gudang Unit 1, Gudang Utama Site A" required>
            </div>

            <!-- Site -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi (Site) *</label>
                <select name="site_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                    <option value="">-- Pilih Site --</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" {{ (old('site_id', $warehouse->site_id ?? '') == $site->id) ? 'selected' : '' }}>
                            {{ $site->name }} ({{ $site->location }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Gudang ini akan dikaitkan dengan site/lokasi tersebut.</p>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white font-bold rounded-lg hover:bg-primary-700 transition shadow-md">
                    {{ isset($warehouse) ? 'Simpan Perubahan' : 'Buat Gudang' }}
                </button>
            </div>
        </form>
    </div>
</x-prsystem::app-layout>
