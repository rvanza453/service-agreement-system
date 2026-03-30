<x-qccomplaintsystem::layouts.master :title="'Edit Temuan QC'">
    @php
        $existingSource = old('source_type', $finding->source_type);
        $isCustomSource = !in_array($existingSource, $sourceOptions, true);
        $selectedSourceType = $isCustomSource ? 'other' : $existingSource;
        $customSourceValue = old('source_type_custom', $isCustomSource ? $existingSource : '');
        $selectedPicIds = array_map('intval', old('pic_user_ids', $finding->pic_user_ids ?? ($finding->pic_user_id ? [$finding->pic_user_id] : [])));
    @endphp

    <div class="card">
        <div class="card-header">Perbarui Temuan {{ $finding->finding_number }}</div>
        <div class="card-body">
            <form action="{{ route('qc.findings.update', $finding) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid">
                    <div class="field">
                        <label>Pelapor (User Login Saat Dibuat)</label>
                        <input type="text" class="input" value="{{ $finding->reporter?->name ?? $finding->reporter_name ?? $authUser?->name ?? '-' }}" readonly>
                    </div>
                    <div class="field">
                        <label>PIC Tanggung Jawab (bisa lebih dari satu)</label>
                        <input type="text" id="pic_search" class="input" placeholder="Cari nama PIC..." style="margin-bottom:6px;">
                        <select name="pic_user_ids[]" id="pic_user_ids" class="select" multiple size="6">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(in_array((int) $user->id, $selectedPicIds, true))>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted">Tekan Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu PIC.</div>
                    </div>
                </div>

                <div class="field">
                    <label>Judul Temuan</label>
                    <input type="text" name="title" class="input" value="{{ old('title', $finding->title) }}" required>
                </div>

                <div class="grid">
                    <div class="field">
                        <label>Tanggal Temuan</label>
                        <input type="date" name="finding_date" class="input" value="{{ old('finding_date', optional($finding->finding_date)->toDateString()) }}" required>
                    </div>
                    <div class="field"></div>
                </div>

                <div class="field">
                    <label>Deskripsi Temuan</label>
                    <textarea name="description" rows="4" class="input">{{ old('description', $finding->description) }}</textarea>
                </div>

                <div class="grid">
                    <div class="field">
                        <label>Sumber Temuan</label>
                        <select name="source_type" id="source_type" class="select" required>
                            @foreach($sourceOptions as $source)
                                <option value="{{ $source }}" @selected($selectedSourceType === $source)>
                                    @if($source === 'self')
                                        Temuan Sendiri
                                    @elseif($source === 'worker_direct')
                                        Pekerja Lain (Direct)
                                    @else
                                        {{ $source }}
                                    @endif
                                </option>
                            @endforeach
                            <option value="other" @selected($selectedSourceType === 'other')>Lainnya (Input Manual)</option>
                        </select>
                        <input
                            type="text"
                            name="source_type_custom"
                            id="source_type_custom"
                            class="input"
                            value="{{ $customSourceValue }}"
                            placeholder="Tulis sumber temuan lainnya"
                            style="margin-top:8px; display:none;"
                        >
                    </div>
                    <div class="field">
                        <label>Urgensi</label>
                        <select name="urgency" class="select" required>
                            @foreach($urgencyOptions as $urgency)
                                <option value="{{ $urgency }}" @selected(old('urgency', $finding->urgency) === $urgency)>{{ \Modules\QcComplaintSystem\Models\QcFinding::urgencyLabel($urgency) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid">
                    <div class="field">
                        <label>Kategori</label>
                        <select name="kategori" id="kategori" class="select" required>
                            <option value="">Pilih Kategori</option>
                            @foreach(\Modules\QcComplaintSystem\Models\QcFinding::categoryHierarchy() as $key => $cat)
                                <option value="{{ $key }}" @selected(old('kategori', $finding->kategori) === $key)>{{ $cat['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field" id="sub_kategori_wrap" style="display:none;">
                        <label>Sub Kategori</label>
                        <select name="sub_kategori" id="sub_kategori" class="select">
                            <option value="">Pilih Sub Kategori</option>
                        </select>
                    </div>
                </div>

                <div class="grid">
                    <div class="field">
                        <label>Department</label>
                        <select name="department_id" id="department_id" class="select" required>
                            <option value="">Pilih Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) old('department_id', $finding->department_id) === (string) $department->id)>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Afdeling</label>
                        <input
                            type="text"
                            name="sub_department_name"
                            class="input"
                            value="{{ old('sub_department_name', optional($finding->subDepartment)->name) }}"
                            placeholder="Contoh: Afdeling 1"
                            required
                        >
                    </div>
                </div>

                <div class="grid">
                    <div class="field">
                        <label>Blok</label>
                        <input
                            type="text"
                            name="block_name"
                            class="input"
                            value="{{ old('block_name', optional($finding->block)->name) }}"
                            placeholder="Contoh: Blok A12"
                            required
                        >
                    </div>
                    <div class="field">
                        <label>Detail Lokasi Tambahan</label>
                        <input type="text" name="location" class="input" value="{{ old('location', $finding->location) }}">
                    </div>
                </div>

                <div class="grid">
                    <div class="field">
                        <label>Ganti Lampiran / Bukti Temuan</label>
                        <div class="file-picker-wrap">
                            <button type="button" class="btn" id="btn-add-files"><i class="fas fa-plus"></i> Pilih Lampiran</button>
                            <input type="file" id="file-picker-input" accept="image/*,application/pdf,.kml,.kmz,.zip" style="display:none;" multiple>
                            <input type="file" id="file-final-input" name="finding_attachments[]" multiple style="display:none;">
                        </div>
                        <div id="file-list" style="display:none; margin-top:8px; display:grid; gap:6px;"></div>
                        <div class="text-muted" style="margin-top:6px;">Kosongkan jika tidak ingin mengganti lampiran saat ini. Bisa pilih lebih dari satu file (bisa klik tombol berkali-kali).<br>Format: Gambar, PDF, KML, ZIP. Maks 20MB.</div>
                    </div>
                    <div class="field">
                        <label>Lampiran Saat Ini</label>
                        @if(!empty($finding->finding_attachments) && is_array($finding->finding_attachments))
                            <div class="flex flex-col gap-2" style="display:flex; flex-direction:column; gap:8px;">
                                @foreach($finding->finding_attachments as $attachment)
                                    <a class="btn" href="{{ asset('storage/' . $attachment) }}" target="_blank" style="width:fit-content;"><i class="fas fa-paperclip"></i> Lihat Lampiran {{ $loop->iteration }}</a>
                                @endforeach
                            </div>
                        @elseif($finding->finding_photo_path)
                            <a class="btn" href="{{ asset('storage/' . $finding->finding_photo_path) }}" target="_blank" style="width:fit-content;"><i class="fas fa-image"></i> Lihat Foto (Lama)</a>
                        @else
                            <div class="text-muted">Belum ada lampiran bukti temuan.</div>
                        @endif
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Simpan Perubahan</button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sourceTypeSelect = document.getElementById('source_type');
                const sourceTypeCustomInput = document.getElementById('source_type_custom');
                const picSearchInput = document.getElementById('pic_search');
                const picSelect = document.getElementById('pic_user_ids');

                function initPicSearchableSelect() {
                    if (!picSearchInput || !picSelect) return;

                    const allOptions = Array.from(picSelect.options).map((option) => ({
                        value: option.value,
                        text: option.text,
                        selected: option.selected,
                    }));

                    function syncSelectedStateFromDOM() {
                        const selectedValues = new Set(Array.from(picSelect.selectedOptions).map((opt) => opt.value));
                        allOptions.forEach((item) => {
                            if (selectedValues.has(item.value)) {
                                item.selected = true;
                            } else if (Array.from(picSelect.options).some((opt) => opt.value === item.value)) {
                                item.selected = false;
                            }
                        });
                    }

                    function renderFilteredOptions() {
                        syncSelectedStateFromDOM();

                        const query = picSearchInput.value.trim().toLowerCase();
                        const filtered = allOptions.filter((item) => {
                            if (item.selected) return true;
                            return item.text.toLowerCase().includes(query);
                        });

                        picSelect.innerHTML = '';
                        filtered.forEach((item) => {
                            const option = document.createElement('option');
                            option.value = item.value;
                            option.textContent = item.text;
                            option.selected = item.selected;
                            picSelect.appendChild(option);
                        });
                    }

                    picSearchInput.addEventListener('input', renderFilteredOptions);
                    picSelect.addEventListener('change', syncSelectedStateFromDOM);

                    renderFilteredOptions();
                }

                /* ── Kategori & Sub Kategori Logic ── */
                const categoryHierarchy = @json(\Modules\QcComplaintSystem\Models\QcFinding::categoryHierarchy());
                const kategoriSelect = document.getElementById('kategori');
                const subKategoriWrap = document.getElementById('sub_kategori_wrap');
                const subKategoriSelect = document.getElementById('sub_kategori');
                const oldSubKategori = '{{ old('sub_kategori', $finding->sub_kategori) }}';

                function updateSubKategori() {
                    if (!kategoriSelect || !subKategoriWrap || !subKategoriSelect) return;

                    const catKey = kategoriSelect.value;
                    const catData = categoryHierarchy[catKey];

                    subKategoriSelect.innerHTML = '<option value="">Pilih Sub Kategori</option>';

                    if (catData && catData.subs && Object.keys(catData.subs).length > 0) {
                        subKategoriWrap.style.display = 'flex';
                        subKategoriSelect.required = true;
                        
                        for (const [subKey, subData] of Object.entries(catData.subs)) {
                            const option = document.createElement('option');
                            option.value = subKey;
                            option.textContent = subData.label;
                            if (oldSubKategori === subKey) {
                                option.selected = true;
                            }
                            subKategoriSelect.appendChild(option);
                        }
                    } else {
                        subKategoriWrap.style.display = 'none';
                        subKategoriSelect.required = false;
                    }
                }

                if (kategoriSelect) {
                    kategoriSelect.addEventListener('change', updateSubKategori);
                    updateSubKategori();
                }

                /* ── Multi-file picker untuk edit ── */
                const btnAdd      = document.getElementById('btn-add-files');
                const pickerInput = document.getElementById('file-picker-input');
                const finalInput  = document.getElementById('file-final-input');
                const fileList    = document.getElementById('file-list');

                if (btnAdd && pickerInput && finalInput && fileList) {
                    const MAX_FILES = 10;
                    const MAX_BYTES = 20 * 1024 * 1024;
                    let dt = new DataTransfer();

                    function formatSize(bytes) {
                        if (bytes < 1024) return bytes + ' B';
                        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
                        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
                    }

                    function renderList() {
                        fileList.innerHTML = '';
                        if (dt.files.length === 0) {
                            fileList.style.display = 'none';
                            return;
                        }
                        fileList.style.display = 'grid';
                        Array.from(dt.files).forEach((file, idx) => {
                            const row = document.createElement('div');
                            row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:7px 10px;border:1px solid #d7e4dd;border-radius:8px;background:#f8fcfa;font-size:13px;';
                            row.innerHTML = `<i class="fas fa-paperclip" style="color:#0f766e;"></i>
                                <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${file.name}</span>
                                <span style="color:#64748b;font-size:11px;white-space:nowrap;">${formatSize(file.size)}</span>
                                <button type="button" data-idx="${idx}" style="background:none;border:none;cursor:pointer;color:#b91c1c;padding:0 4px;font-size:14px;" title="Hapus">&times;</button>`;
                            row.querySelector('button').addEventListener('click', function () {
                                const newDt = new DataTransfer();
                                Array.from(dt.files).forEach((f, i) => { if (i !== idx) newDt.items.add(f); });
                                dt = newDt;
                                syncFinal();
                                renderList();
                            });
                            fileList.appendChild(row);
                        });
                        const countMsg = document.createElement('div');
                        countMsg.style.cssText = 'font-size:12px;color:#475569;';
                        countMsg.textContent = dt.files.length + ' file dipilih (opsi pengganti)';
                        fileList.appendChild(countMsg);
                    }

                    function syncFinal() {
                        const newDt = new DataTransfer();
                        Array.from(dt.files).forEach(f => newDt.items.add(f));
                        finalInput.files = newDt.files;
                    }

                    btnAdd.addEventListener('click', () => pickerInput.click());

                    pickerInput.addEventListener('change', function () {
                        let rejected = [];
                        Array.from(this.files).forEach(file => {
                            if (dt.files.length >= MAX_FILES) {
                                rejected.push(file.name + ' (batas 10 file)');
                                return;
                            }
                            if (file.size > MAX_BYTES) {
                                rejected.push(file.name + ' (lebih dari 20 MB)');
                                return;
                            }
                            dt.items.add(file);
                        });
                        this.value = '';
                        syncFinal();
                        renderList();
                        if (rejected.length) {
                            alert('File berikut tidak ditambahkan:\n' + rejected.join('\n'));
                        }
                    });
                }

                function toggleCustomSourceInput() {
                    const isOther = sourceTypeSelect && sourceTypeSelect.value === 'other';
                    if (!sourceTypeCustomInput) {
                        return;
                    }

                    sourceTypeCustomInput.style.display = isOther ? 'block' : 'none';
                    sourceTypeCustomInput.required = isOther;

                    if (!isOther) {
                        sourceTypeCustomInput.value = '';
                    }
                }

                if (sourceTypeSelect) {
                    sourceTypeSelect.addEventListener('change', toggleCustomSourceInput);
                    toggleCustomSourceInput();
                }

                initPicSearchableSelect();
            });
        </script>
    @endpush
</x-qccomplaintsystem::layouts.master>
