<x-prsystem::app-layout>
    <div class="max-w-7xl mx-auto py-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Buat Pengajuan PR Baru</h2>
            <p class="mt-1 text-sm text-gray-500">Lengkapi formulir di bawah untuk mengajukan Purchase Request</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <form action="{{ route('pr.store') }}" method="POST" enctype="multipart/form-data">
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Terjadi kesalahan pada form Anda:</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Budget Warning Container --}}
                <div id="budget-warning" class="hidden bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Peringatan Budget</h3>
                            <div id="budget-warning-list" class="mt-2 text-sm text-yellow-700"></div>
                        </div>
                    </div>
                </div>
                @csrf
                
                {{-- Informasi Dasar --}}
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Informasi Dasar</h3>
                </div>
                
                <div class="p-6 space-y-4">
                    {{-- Department Select --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-prsystem::input-label for="department_id" value="Unit" class="required" />
                            <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="">-- Pilih Unit --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->coa }}) - {{ $dept->site->name }}</option>
                                @endforeach
                            </select>
                            <x-prsystem::input-error :messages="$errors->get('department_id')" class="mt-2" />
                        </div>

                        <div id="station-container">
                             <x-prsystem::input-label for="sub_department_id" value="Stasiun / Afdeling" class="required" />
                             <select id="sub_department_id" name="sub_department_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                                 <option value="">-- Pilih Stasiun / Afdeling --</option>
                             </select>
                             <x-prsystem::input-error :messages="$errors->get('sub_department_id')" class="mt-2" />
                         </div>
                     </div>

                     {{-- Date --}}
                     <div>
                         <x-prsystem::input-label for="request_date" value="Tanggal Pengajuan" />
                         <x-prsystem::text-input id="request_date" class="block mt-1 w-full bg-gray-50 text-gray-600 cursor-not-allowed" type="date" name="request_date" :value="date('Y-m-d')" readonly />
                         <x-prsystem::input-error :messages="$errors->get('request_date')" class="mt-2" />
                     </div>

                     {{-- Global Job Selection (Visible only for JOB_COA) --}}
                     <div id="global-job-container" class="hidden">
                         <x-prsystem::input-label for="global_job_id" :value="__('Pilih Job (Pekerjaan)')" class="required" />
                         <select id="global_job_id" name="global_job_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500" onchange="onGlobalJobChange()">
                             <option value="">-- Pilih Job --</option>
                         </select>
                     </div>
                 </div>
                     
                     <div class="mt-4 px-6 pb-6 space-y-4">
                         {{-- File Attachment --}}
                         <div>
                            <x-prsystem::input-label for="attachment" value="Lampiran Pendukung (Foto/Dokumen)" />
                            <input type="file" name="attachment" id="attachment" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <p class="text-xs text-gray-500 mt-1">Maksimal 10MB. Format: PDF, JPG, PNG, DOCX.</p>
                            <x-prsystem::input-error :messages="$errors->get('attachment')" class="mt-2" />
                         </div>
                     </div>

                 {{-- Items Section --}}
                 <div class="bg-gray-50 px-6 py-4 border-y border-gray-200">
                     <div class="flex justify-between items-center">
                         <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Daftar Item Barang</h3>
                         <button type="button" onclick="addItem()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 shadow-sm transition">
                             <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                             Tambah Item
                         </button>
                     </div>
                 </div>
                 
                 <div class="p-6">
                     {{-- Info Banner --}}
                     <div class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-4">
                         <div class="flex">
                             <div class="flex-shrink-0">
                                 <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                     <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                 </svg>
                             </div>
                             <div class="ml-3">
                                 <p class="text-sm text-blue-700">
                                     <strong>INFO:</strong> Opsi "INPUT BARANG BARU" saat ini dinonaktifkan. Mohon cari nama barang dengan seksama di dalam daftar yang tersedia.
                                     Apabila tidak menemukan barang yang dicari, silahkan hubungi tim IT untuk penambahan data barang.
                                 </p>
                             </div>
                         </div>
                     </div>

                     {{-- Table Container --}}
                     <div class="border border-gray-200 rounded-lg overflow-hidden">
                         {{-- Table Header --}}
                         <div class="grid grid-cols-12 gap-3 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200 py-3 px-4">
                             <div class="col-span-3">Nama Barang</div>
                             <div class="col-span-2">Spesifikasi</div>
                             <div class="col-span-2">Keterangan</div>
                             <div class="col-span-1">Qty</div>
                             <div class="col-span-1">Satuan</div>
                             <div class="col-span-2">Est. Harga</div>
                             <div class="col-span-1 text-center">Aksi</div>
                         </div>
                         
                         {{-- Table Body --}}
                         <div id="items-container" class="bg-white divide-y divide-gray-100">
                             {{-- Item rows will appear here --}}
                         </div>

                         {{-- Empty State --}}
                         <div id="empty-items-placeholder" class="text-center py-10 bg-gray-50">
                             <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                             </svg>
                             <p class="mt-2 text-sm text-gray-500">Belum ada item barang.</p>
                             <p class="text-xs text-gray-400">Klik tombol "Tambah Item" untuk memulai.</p>
                         </div>
                     </div>
                 </div>

                 <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                     <p class="text-sm text-gray-500">
                         <span class="text-red-500">*</span> Wajib diisi
                     </p>
                     <div class="flex gap-3">
                         <a href="{{ route('pr.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition">
                             Batal
                         </a>
                         <x-prsystem::primary-button class="inline-flex items-center">
                             <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                             </svg>
                             {{ __('Simpan & Ajukan PR') }}
                         </x-prsystem::primary-button>
                     </div>
                 </div>
             </form>
         </div>
     </div>

     {{-- Data for JS --}}
     <script>
         // Default fallback jika tidak ada site (misal admin super user belum pilih site)
         let currentProductList = []; 
         const categories = @json($categories);
         const departmentsData = @json($departments);
         const allProductsData = @json($products);
         
         const BUDGET_TYPE_STATION = 'station';
         const BUDGET_TYPE_JOB_COA = 'job_coa';
 
         let globalItemIndex = 0;
         let currentBudgetType = BUDGET_TYPE_STATION;
         let availableJobs = [];
         let budgetData = {};
         let jobSelectInstance = null; // Store TomSelect instance for Global Job
         
         const departmentData = @json($departments->keyBy('id'));
         
         const deptSelect = document.getElementById('department_id');
         const subDeptSelect = document.getElementById('sub_department_id');
         const warningContainer = document.getElementById('budget-warning');
         const listContainer = document.getElementById('budget-warning-list');
 
         deptSelect.addEventListener('change', updateSubDepartments);
         subDeptSelect.addEventListener('change', onSubDepartmentChange);
         
 
         if (deptSelect.value) updateSubDepartments();
 
         function updateSubDepartments() {
             const deptId = deptSelect.value;
             
             // Reset
             subDeptSelect.innerHTML = '<option value="">-- Pilih Stasiun / Afdeling --</option>';
             availableJobs = [];
             currentBudgetType = BUDGET_TYPE_STATION;
             budgetData = {};
             document.getElementById('budget-warning').classList.add('hidden');
             
             // Reset Products saat ganti unit
             currentProductList = [];
 
             if (deptId) {
                 const selectedDept = departmentsData.find(d => d.id == deptId);
                 if (selectedDept) {
                     currentBudgetType = selectedDept.budget_type || BUDGET_TYPE_STATION;
                     
                     // 1. Fetch Products berdasarkan Site ID dari Department
                     if (selectedDept.site && selectedDept.site.id) {
                         currentProductList = allProductsData.filter(p => {
                             return p.sites && p.sites.some(s => s.id == selectedDept.site.id);
                         });
                     }

                     // 2. Populate Station (Sub Department)
                     if (currentBudgetType === BUDGET_TYPE_STATION) {
                         if (selectedDept.sub_departments && selectedDept.sub_departments.length > 0) {
                             selectedDept.sub_departments.forEach(sub => {
                                 subDeptSelect.insertAdjacentHTML('beforeend', `<option value="${sub.id}">${sub.name}</option>`);
                             });
                             subDeptSelect.removeAttribute('disabled');
                         } else {
                             subDeptSelect.setAttribute('disabled', 'disabled');
                         }
                     }
 
                     // 3. Direct Job Fetching for JOB_COA
                     if (currentBudgetType === BUDGET_TYPE_JOB_COA) {
                          fetch(`/api/department/${deptId}/jobs`)
                             .then(r => r.json())
                             .then(jobs => {
                                 availableJobs = jobs;
                                 refreshJobDropdowns();
                             })
                             .catch(err => console.error('Error fetching jobs:', err));
                     }
                 }
             }
             
             updateUIForBudgetType();
         }
         
         function onSubDepartmentChange() {
              // Can load budget per station if needed
         }
 
         function updateUIForBudgetType() {
             // Manage Global Job Select Visibility
             const jobContainer = document.getElementById('global-job-container');
             const globalJobSelect = document.getElementById('global_job_id');
             const stationContainer = document.getElementById('station-container');
             const stationSelect = document.getElementById('sub_department_id');
             
             if (currentBudgetType === BUDGET_TYPE_JOB_COA) {
                 jobContainer.classList.remove('hidden');
                 globalJobSelect.setAttribute('required', 'required');
                 
                 stationContainer.classList.add('hidden');
                 stationSelect.removeAttribute('required');
                 stationSelect.value = '';
             } else {
                 jobContainer.classList.add('hidden');
                 globalJobSelect.removeAttribute('required');
                 globalJobSelect.value = '';
                 
                 stationContainer.classList.remove('hidden');
                 stationSelect.setAttribute('required', 'required');
             }
         }
        
        function refreshJobDropdowns() {
            // Update Global Job Select
            const globalSelect = document.getElementById('global_job_id');
            const currentVal = globalSelect.value; // Store old value if any
            
            // Destroy previous TomSelect instance if exists
            if (jobSelectInstance) {
                jobSelectInstance.destroy();
                jobSelectInstance = null;
            }

            let html = '<option value="">-- Pilih Job --</option>';
            availableJobs.forEach(job => {
                const label = job.code ? `${job.code} - ${job.name}` : job.name;
                html += `<option value="${job.id}">${label}</option>`;
            });
            globalSelect.innerHTML = html;
            
            // Restore if valid
            if (availableJobs.find(j => j.id == currentVal)) {
                globalSelect.value = currentVal;
            } else {
                globalSelect.value = '';
            }

            // Initialize TomSelect agar searchable
            if (availableJobs.length > 0) {
                jobSelectInstance = new TomSelect('#global_job_id', {
                    create: false,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: "Cari Job / Pekerjaan...",
                    dropdownParent: 'body',
                    onChange: function(value) {
                        onGlobalJobChange();
                    }
                });
            }
        }
        
        function onGlobalJobChange() {
            // When global job changes, update logic
            const globalSelect = document.getElementById('global_job_id');
            const jobVal = globalSelect.value;
            
            document.querySelectorAll('input[name$="[job_id]"]').forEach(input => {
                input.value = jobVal;
            });
            
            checkBudget();
        }
 
         function addItem() {
             const container = document.getElementById('items-container');
             const emptyPlaceholder = document.getElementById('empty-items-placeholder');
             
             if(emptyPlaceholder) emptyPlaceholder.classList.add('hidden');
 
             const currentIndex = globalItemIndex; 
             const globalJobVal = document.getElementById('global_job_id')?.value || '';
 
             // Build Options from Fetched Products
             let productOptions = '<option value="">-- Cari Barang --</option>';
             const selectedDeptId = document.getElementById('department_id').value;
             const selectedDeptObj = departmentsData.find(d => d.id == selectedDeptId);
             const warehouseId = selectedDeptObj ? selectedDeptObj.warehouse_id : null;

             currentProductList.forEach(p => {
                 let stockInfo = '';
                 if (warehouseId && p.stocks) {
                     const stockObj = p.stocks.find(s => s.warehouse_id == warehouseId);
                     const stockQty = stockObj ? parseFloat(stockObj.quantity) : 0;
                     stockInfo = ` [Stok: ${stockQty}]`;
                 }
                 productOptions += `<option value="${p.id}" data-name="${p.name}" data-unit="${p.unit}" data-price="${p.price_estimation || 0}">${p.code} - ${p.name}${stockInfo}</option>`;
             });
 
             const rowId = `row-${currentIndex}`;
 
             const row = `
                 <div class="grid grid-cols-12 gap-3 item-row p-4 border-b border-gray-100 items-start hover:bg-gray-50 transition duration-150 group" id="${rowId}">
                     
                     <input type="hidden" name="items[${currentIndex}][job_id]" value="${globalJobVal}">
 
                     {{-- 1. Nama Barang --}}
                     <div class="col-span-3">
                         <select id="product-select-${currentIndex}" name="items[${currentIndex}][product_id]" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                             ${productOptions}
                         </select>
                         
                         {{-- Manual Input Group --}}
                         <div id="manual-name-container-${currentIndex}" class="mt-2 hidden space-y-2 p-2 bg-yellow-50 rounded-md border border-yellow-100">
                             <input type="text" id="item-name-${currentIndex}" name="items[${currentIndex}][item_name]" placeholder="Nama Barang..." class="block w-full border-gray-300 rounded-md text-sm focus:border-primary-500 focus:ring-primary-500">
                             <select name="items[${currentIndex}][manual_category]" class="block w-full border-gray-300 rounded-md text-sm focus:border-primary-500 focus:ring-primary-500">
                                 <option value="">-- Kategori --</option>
                                 @foreach($categories as $cat)
                                     <option value="{{ $cat }}">{{ $cat }}</option>
                                 @endforeach
                             </select>
                             <input type="url" name="items[${currentIndex}][url_link]" placeholder="Link Produk (WAJIB)" class="block w-full border-gray-300 rounded-md text-sm focus:border-primary-500 focus:ring-primary-500">
                         </div>
                     </div>
                     
                     {{-- 2. Spesifikasi (Col Span 2) --}}
                     <div class="col-span-2">
                         <textarea name="items[${currentIndex}][specification]" rows="1" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm min-h-[38px] resize-none overflow-hidden leading-snug" placeholder="Spec..." oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                     </div>
 
                     {{-- 3. Keterangan (Col Span 2) --}}
                     <div class="col-span-2">
                         <select id="remarks-select-${currentIndex}" name="items[${currentIndex}][remarks]" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Ket...">
                             <option value="">-- Pilih / Ketik --</option>
                             <option value="Kehabisan Stock">Kehabisan Stock</option>
                             <option value="Kekurangan Stock">Kekurangan Stock</option>
                             <option value="Ngestock">Ngestock</option>
                             <option value="Stock Menipis">Stock Menipis</option>
                         </select>
                     </div>
 
                     {{-- 3. Qty (Col Span 1) --}}
                     <div class="col-span-1">
                         <input type="number" name="items[${currentIndex}][quantity]" value="1" min="1" onchange="calculateSubtotal(${currentIndex})" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-center text-sm h-[38px]" required>
                     </div>
                     
                     {{-- 4. Satuan (Col Span 1) --}}
                     <div class="col-span-1">
                         <input type="text" name="items[${currentIndex}][unit]" placeholder="Unit" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm bg-gray-50 text-center text-gray-500 h-[38px]" readonly required>
                     </div>
                     
                     {{-- 5. Harga (Col Span 2) --}}
                     <div class="col-span-2">
                         <div class="relative rounded-md shadow-sm">
                             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2">
                                 <span class="text-gray-500 sm:text-xs">Rp</span>
                             </div>
                             <input type="number" name="items[${currentIndex}][price_estimation]" value="0" min="0" onchange="calculateSubtotal(${currentIndex})" class="block w-full rounded-md border-gray-300 pl-7 focus:border-primary-500 focus:ring-primary-500 text-right text-sm h-[38px]" required>
                         </div>
                         <div class="text-[10px] text-gray-500 mt-1 text-right">Total: <span id="subtotal-${currentIndex}" class="font-bold text-gray-700">0</span></div>
                     </div>
                     
                     {{-- 6. Aksi (Col Span 1) --}}
                     <div class="col-span-1 flex items-start justify-center pt-1">
                         <button type="button" onclick="removeItem(this)" class="text-gray-400 hover:text-red-600 transition-colors p-1.5 rounded-full hover:bg-red-50" title="Hapus Baris">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                         </button>
                     </div>
                 </div>
             `;
             
             container.insertAdjacentHTML('beforeend', row);
             
             new TomSelect(`#product-select-${currentIndex}`, {
                 create: false,
                 sortField: { field: "text", direction: "asc" },
                 placeholder: "Cari Barang...",
                 dropdownParent: 'body',
                 onChange: function(value) {
                     onProductChange(this.input, currentIndex);
                 }
             });
 
             // Initialize TomSelect for Remarks
             new TomSelect(`#remarks-select-${currentIndex}`, {
                 create: true,
                 sortField: { field: "text", direction: "asc" },
                 placeholder: "Ket...",
                 dropdownParent: 'body'
             });
 
             globalItemIndex++;
         }
 
         function onProductChange(select, index) {
             const selectedVal = select.value;
             const containerName = document.getElementById(`manual-name-container-${index}`);
             const inputName = containerName.querySelector('input');
             const inputCategory = containerName.querySelector('select');
             const inputUnit = document.querySelector(`input[name="items[${index}][unit]"]`);
             const inputPrice = document.querySelector(`input[name="items[${index}][price_estimation]"]`);
             
             if (selectedVal === 'manual') {
                 containerName.classList.remove('hidden');
                 inputName.required = true;
                 
                 if(inputCategory) inputCategory.required = (currentBudgetType === BUDGET_TYPE_STATION);
 
                 inputUnit.value = '';
                 inputUnit.removeAttribute('readonly');
                 inputUnit.classList.remove('bg-gray-50', 'text-gray-500');
                 
                 inputPrice.value = '0';
                 inputPrice.removeAttribute('readonly');
                 inputPrice.classList.remove('bg-gray-50', 'text-gray-500');
                 inputName.value = '';
 
             } else if (selectedVal) {
                 containerName.classList.add('hidden');
                 inputName.required = false;
                 if(inputCategory) inputCategory.required = false;
 
                 const p = currentProductList.find(x => x.id == selectedVal);
                 if (p) {
                     inputName.value = p.name;
                     inputUnit.value = p.unit;
                     inputUnit.setAttribute('readonly', true);
                     inputUnit.classList.add('bg-gray-50', 'text-gray-500');
                     inputPrice.value = p.price_estimation || 0;
                     inputPrice.setAttribute('readonly', true);
                     inputPrice.classList.add('bg-gray-50', 'text-gray-500');
                     calculateSubtotal(index);
                 }
             } else {
                 containerName.classList.add('hidden');
                 inputName.required = false;
                 if(inputCategory) inputCategory.required = false;
                  inputUnit.value = '';
                  inputPrice.value = '0';
             }
             checkBudget(); 
         }
         
         function removeItem(btn) {
             btn.closest('.item-row').remove();
             checkBudget();
             
             const container = document.getElementById('items-container');
             const placeholder = document.getElementById('empty-items-placeholder');
             if(container && container.children.length === 0 && placeholder) {
                 placeholder.classList.remove('hidden');
             }
         }
 
         function calculateSubtotal(index) {
             const qty = document.querySelector(`input[name="items[${index}][quantity]"]`).value;
             const price = document.querySelector(`input[name="items[${index}][price_estimation]"]`).value;
             const subtotal = qty * price;
             document.getElementById(`subtotal-${index}`).innerText = new Intl.NumberFormat('id-ID').format(subtotal);
             checkBudget();
         }
 
         function checkBudget() {
             const warningContainer = document.getElementById('budget-warning');
             const listContainer = document.getElementById('budget-warning-list');
             let warnings = [];
             let currentRequest = {}; 
             
             const rows = document.querySelectorAll('.item-row');
             rows.forEach(row => {
                  const selectProduct = row.querySelector('select[name^="items"][name$="[product_id]"]');
                  const manualCatSelect = row.querySelector('select[name^="items"][name$="[manual_category]"]');
                  const qtyInput = row.querySelector('input[name^="items"][name$="[quantity]"]');
                  const priceInput = row.querySelector('input[name^="items"][name$="[price_estimation]"]');
                  const jobInput = row.querySelector('input[name$="[job_id]"]');
                  
                  if(qtyInput && priceInput) {
                      const qty = parseFloat(qtyInput.value) || 0;
                      const price = parseFloat(priceInput.value) || 0;
                      const total = qty * price;
                      
                      let key = null;
 
                      if (currentBudgetType === BUDGET_TYPE_JOB_COA) {
                          const jobId = jobInput ? jobInput.value : null;
                          if (jobId) {
                              const job = availableJobs.find(j => j.id == jobId);
                              if (job) key = `${job.code} - ${job.name}`;
                          }
                      } else {
                          if (selectProduct && selectProduct.value === 'manual') {
                              if (manualCatSelect) key = manualCatSelect.value;
                          } else if (selectProduct && selectProduct.value) {
                              const p = currentProductList.find(x => x.id == selectProduct.value);
                              if (p && p.category) key = p.category;
                          }
                          if (!key) key = 'Uncategorized';
                      }
 
                      if (key) {
                         if (!currentRequest[key]) currentRequest[key] = 0;
                         currentRequest[key] += total;
                      }
                  }
             });
 
             for (const [key, amount] of Object.entries(currentRequest)) {
                 if (budgetData[key]) {
                     const remaining = budgetData[key].remaining;
                     if (amount > remaining) {
                         const fmtAmount = new Intl.NumberFormat('id-ID').format(amount);
                         const fmtRemaining = new Intl.NumberFormat('id-ID').format(remaining);
                         warnings.push(`<strong>${key}</strong>: Estimasi (${fmtAmount}) melebihi sisa budget (${fmtRemaining}).`);
                     }
                 }
             }
 
             if (warnings.length > 0) {
                 listContainer.innerHTML = warnings.join('<br>');
                 warningContainer.classList.remove('hidden');
             } else {
                 warningContainer.classList.add('hidden');
             }
         }
         
         try {
             if (deptSelect.options.length === 2 && deptSelect.options[1].value) {
                 deptSelect.selectedIndex = 1;
                 updateSubDepartments();
             } else if (deptSelect.value) {
                 updateSubDepartments();
             }
         } catch (e) {
             console.error('Error in PR Create Init:', e);
         }

         // Prevent form submission on Enter key press
         document.addEventListener('keydown', function(event) {
             if (event.key === 'Enter') {
                 // Allow Enter key on textareas and buttons
                 if (event.target.tagName.toLowerCase() !== 'textarea' && 
                     event.target.tagName.toLowerCase() !== 'button' &&
                     event.target.type !== 'submit') {
                     event.preventDefault();
                     return false;
                 }
             }
         });
 
     </script>
 </x-prsystem::app-layout>
