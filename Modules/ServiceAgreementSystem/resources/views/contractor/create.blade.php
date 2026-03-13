<x-serviceagreementsystem::layouts.master :title="'Tambah Kontraktor'">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Form Tambah Kontraktor</div>
        </div>
        <div class="card-body">
            <form action="{{ route('sas.contractors.store') }}" method="POST">
                @csrf
                @include('serviceagreementsystem::contractor._form')

                <div class="d-flex gap-2" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('sas.contractors.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-serviceagreementsystem::layouts.master>
