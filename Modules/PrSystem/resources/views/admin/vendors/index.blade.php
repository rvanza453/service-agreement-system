<x-prsystem::app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Management Supplier') }}
                </h2>
                @role('Admin')
                <a href="{{ route('vendors.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Add New Supplier
                </a>
                @endrole
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">No</th>
                                    <th class="px-6 py-3">Supplier</th>
                                    <th class="px-6 py-3">Keterangan</th>
                                    <th class="px-6 py-3">Kategori</th>
                                    <th class="px-6 py-3">Lokasi</th>
                                    <th class="px-6 py-3">Alamat</th>
                                    <th class="px-6 py-3">PIC / Kontak</th>
                                    <th class="px-6 py-3">Kontak Admin</th>
                                    <th class="px-6 py-3">Status</th>
                                    @role('Admin')
                                    <th class="px-6 py-3 text-center">Actions</th>
                                    @endrole
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vendors as $index => $vendor)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 font-bold text-gray-900">{{ $vendor->name }}</td>
                                        <td class="px-6 py-4">{{ Str::limit($vendor->description, 30) }}</td>
                                        <td class="px-6 py-4">{{ $vendor->category ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ $vendor->location ?? '-' }}</td>
                                        <td class="px-6 py-4">{{ Str::limit($vendor->address, 30) ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            <div class="text-gray-900">{{ $vendor->pic_name ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ $vendor->phone ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $vendor->admin_phone ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $statusColor = match($vendor->status) {
                                                    'Pernah Transaksi' => 'bg-green-100 text-green-800',
                                                    'Belum Transaksi' => 'bg-gray-100 text-gray-800',
                                                    'Di Ajukan' => 'bg-yellow-100 text-yellow-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <span class="{{ $statusColor }} text-xs font-semibold px-2.5 py-0.5 rounded">
                                                {{ $vendor->status }}
                                            </span>
                                        </td>
                                        @role('Admin')
                                        <td class="px-6 py-4 text-center space-x-2">
                                            <a href="{{ route('vendors.edit', $vendor) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <form action="{{ route('vendors.destroy', $vendor) }}" method="POST" class="inline" onsubmit="return confirm('Delete this supplier?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                        @endrole
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center">No suppliers found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-prsystem::app-layout>
