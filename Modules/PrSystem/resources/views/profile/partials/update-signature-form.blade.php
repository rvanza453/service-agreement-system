<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Tanda Tangan Digital') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Upload tanda tangan digital Anda untuk digunakan pada dokumen PR yang telah disetujui.') }}
        </p>
    </header>

    @if (session('status') === 'signature-uploaded')
        <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ __('Tanda tangan berhasil diupload!') }}
        </div>
    @endif

    @if (session('status') === 'signature-deleted')
        <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ __('Tanda tangan berhasil dihapus!') }}
        </div>
    @endif

    <!-- Current Signature Preview -->
    @if($user->signature_path)
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan Saat Ini:</label>
            <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 inline-block">
                <img src="{{ asset('storage/' . $user->signature_path) }}" 
                     alt="Signature" 
                     class="max-h-24 max-w-xs">
            </div>
            
            <form method="POST" action="{{ route('profile.signature.delete') }}" class="mt-3">
                @csrf
                @method('DELETE')
                <x-prsystem::danger-button type="submit" onclick="return confirm('Hapus tanda tangan?')">
                    {{ __('Hapus Tanda Tangan') }}
                </x-prsystem::danger-button>
            </form>
        </div>
    @endif

    <!-- Upload Form -->
    <form method="POST" action="{{ route('profile.signature.upload') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-prsystem::input-label for="signature" :value="__('Upload Tanda Tangan Baru')" />
            <input id="signature" 
                   name="signature" 
                   type="file" 
                   accept="image/png,image/jpg,image/jpeg"
                   class="mt-1 block w-full text-sm text-gray-500
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-md file:border-0
                          file:text-sm file:font-semibold
                          file:bg-primary-50 file:text-primary-700
                          hover:file:bg-primary-100" />
            <p class="mt-1 text-xs text-gray-500">
                Format: PNG, JPG, JPEG. Maksimal 2MB. Rekomendasi ukuran: 300x100px dengan background transparan.
            </p>
            <x-prsystem::input-error class="mt-2" :messages="$errors->get('signature')" />
        </div>

        <div class="flex items-center gap-4">
            <x-prsystem::primary-button>{{ __('Upload Tanda Tangan') }}</x-prsystem::primary-button>
        </div>
    </form>
</section>
