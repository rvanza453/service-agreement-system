<!-- TEST: Simple visible button -->
<button class="fixed bottom-6 right-6 bg-red-600 text-white p-4 rounded-full shadow-2xl z-[9999]" style="z-index: 99999 !important;">
    TEST
</button>

<div x-data="poCart()" x-init="init()" class="relative z-[9999]">
    <!-- Floating Action Button -->
    <button @click="toggle()" 
            class="fixed bottom-20 right-6 md:bottom-10 md:right-10 bg-primary-600 text-white p-4 rounded-full shadow-2xl hover:bg-primary-700 transition hover:scale-105 active:scale-95 flex items-center justify-center group z-[9999]"
            title="Keranjang PO">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span x-text="count" 
              x-show="count > 0"
              class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full border-2 border-white shadow-sm min-w-[24px] text-center">
        </span>
    </button>

    <!-- Modal Backdrop -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm z-[10000]"
         @click="isOpen = false"
         style="display: none;"></div>

    <!-- Modal Content -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="fixed bottom-24 right-4 md:right-10 w-full max-w-md bg-white rounded-xl shadow-2xl overflow-hidden max-h-[80vh] flex flex-col z-[10001]"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-6 py-4 bg-primary-600 text-white flex justify-between items-center shrink-0">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Keranjang PO
            </h3>
            <button @click="isOpen = false" class="text-white hover:text-gray-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-0 bg-gray-50">
            <template x-if="count === 0">
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <p>Keranjang masih kosong.</p>
                </div>
            </template>

            <template x-if="count > 0">
                <ul class="divide-y divide-gray-100">
                    <template x-for="item in items" :key="item.id">
                        <li class="p-4 bg-white hover:bg-gray-50 transition">
                            <div class="flex justify-between items-start gap-3">
                                <div class="flex-1">
                                    <div class="text-xs font-bold text-primary-600 mb-0.5" x-text="item.pr_number"></div>
                                    <div class="text-sm font-medium text-gray-900" x-text="item.item_name"></div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Qty: <span class="font-semibold text-gray-700" x-text="item.quantity"></span> <span x-text="item.unit"></span>
                                        <span x-show="item.specification" class="mx-1">•</span>
                                        <span x-show="item.specification" x-text="item.specification"></span>
                                    </div>
                                </div>
                                <button @click="removeItem(item.id)" class="text-gray-400 hover:text-red-600 transition p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </li>
                    </template>
                </ul>
            </template>
        </div>

        <!-- Footer -->
        <div class="p-4 bg-white border-t border-gray-100 shrink-0" x-show="count > 0">
            <div class="flex gap-3">
                <button @click="clearCart()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Kosongkan
                </button>
                <form action="{{ route('po.create') }}" method="POST" class="flex-1">
                    @csrf
                    <!-- Hidden inputs for selected items -->
                    <template x-for="item in items" :key="item.id">
                        <input type="hidden" name="items[]" :value="item.id">
                    </template>
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700 shadow-md transition flex justify-center items-center gap-2">
                        <span>Buat PO</span>
                        <span class="bg-green-700 px-1.5 py-0.5 rounded text-xs" x-text="count"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function poCart() {
    return {
        isOpen: false,
        count: 0,
        items: [],
        init() {
            console.log('PO Cart Component Initialized');
            this.refresh();
            window.addEventListener('cart-updated', () => {
                console.log('Cart updated event received');
                this.refresh();
            });
        },
        async refresh() {
            try {
                const res = await fetch('{{ route('po.cart.data') }}');
                const data = await res.json();
                console.log('Cart data:', data);
                this.count = data.count;
                this.items = data.items;
            } catch (error) {
                console.error('Failed to fetch cart data', error);
            }
        },
        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.refresh();
            }
        },
        async removeItem(id) {
            if (!confirm('Hapus item ini?')) return;
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch('{{ route('po.cart.remove') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ pr_item_id: id })
                });
                
                if (res.ok) {
                    this.refresh();
                    window.dispatchEvent(new CustomEvent('item-removed-from-cart', { detail: { id: id } }));
                }
            } catch (error) {
                console.error('Failed to remove item', error);
            }
        },
        async clearCart() {
            if (!confirm('Kosongkan semua item di keranjang?')) return;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch('{{ route('po.cart.clear') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                
                if (res.ok) {
                    this.refresh();
                    this.isOpen = false;
                    window.dispatchEvent(new CustomEvent('cart-cleared'));
                }
            } catch (error) {
                console.error('Failed to clear cart', error);
            }
        }
    }
}
</script>
