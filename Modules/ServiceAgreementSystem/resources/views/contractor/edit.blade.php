<x-serviceagreementsystem::layouts.master :title="'Edit Kontraktor'">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Edit Kontraktor: {{ $contractor->name }}</div>
        </div>
        <div class="card-body">
            <form action="{{ route('sas.contractors.update', $contractor) }}" method="POST">
                @csrf @method('PUT')
                @include('serviceagreementsystem::contractor._form', ['contractor' => $contractor])

                <div class="d-flex gap-2" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Perbarui
                    </button>
                    <a href="{{ route('sas.contractors.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-serviceagreementsystem::layouts.master>
