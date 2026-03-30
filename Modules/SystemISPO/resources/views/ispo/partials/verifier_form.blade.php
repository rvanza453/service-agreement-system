@php
    $status = $entry ? $entry->status : '';
    $notes = $entry ? $entry->notes : '';
    $hasFile = $entry && $entry->attachment_path;
@endphp

<div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm" id="verifier-card-{{ $verifier->id }}">
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
                <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2 py-0.5 rounded dark:bg-indigo-900 dark:text-indigo-300 mr-2">Verifier</span>
                {{ $verifier->name }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ $verifier->description }}
            </p>
        </div>

        <div class="w-full md:w-1/2 bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-600">
            <form class="verifier-form space-y-3" id="form-{{ $verifier->id }}" enctype="multipart/form-data">
                <input type="hidden" name="item_id" value="{{ $verifier->id }}">
                
                <!-- Status & File Row -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">- Select -</option>
                            <option value="Tersedia" {{ $status == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                            <option value="Tidak Tersedia" {{ $status == 'Tidak Tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                            <option value="Not Applicable" {{ $status == 'Not Applicable' ? 'selected' : '' }}>Not Applicable</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Document File</label>
                        <input type="file" name="attachment" class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900 dark:file:text-indigo-300">
                        @if($hasFile)
                            <a href="#" class="text-xs text-blue-500 hover:underline mt-1 block">View Existing File</a>
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan (Notes)</label>
                    <textarea name="notes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Add notes here...">{{ $notes }}</textarea>
                </div>

                <!-- Action -->
                <div class="flex justify-end items-center">
                    <span class="text-xs text-green-600 mr-2 hidden" id="msg-{{ $verifier->id }}">Saved!</span>
                    <button type="button" onclick="saveVerifier({{ $verifier->id }}, {{ $document->id }})" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-1.5 px-3 rounded">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Simple inline script since we are not using a bundler for logic
    if (typeof saveVerifier !== 'function') {
        window.saveVerifier = function(verifierId, docId) {
            const form = document.getElementById('form-' + verifierId);
            const formData = new FormData(form);
            const msgSpan = document.getElementById('msg-' + verifierId);
            const btn = form.querySelector('button');

            // Disable button
            btn.disabled = true;
            btn.innerText = 'Saving...';

            fetch(`/ispo/${docId}/entry`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    msgSpan.classList.remove('hidden');
                    setTimeout(() => msgSpan.classList.add('hidden'), 2000);
                } else {
                    alert('Error saving data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = 'Save Changes';
            });
        }
    }
</script>
