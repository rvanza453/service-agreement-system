<x-prsystem::app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoring Budget') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false, modalContent: 'Loading...', modalTitle: 'Detail Budget' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             <div class="bg-white text-gray-900 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 bg-white border-b border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Monitoring Budget (Tahun {{ $year }})</h3>
                    </div>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.budgets.monitoring') }}" class="mb-8 p-5 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                                <select name="year" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" onchange="this.form.submit()">
                                    @for($y = date('Y'); $y >= 2024; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site</label>
                                <select name="site_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" onchange="this.form.submit()">
                                    <option value="">Semua Site</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}" {{ $site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Departemen</label>
                                <select name="department_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" onchange="this.form.submit()">
                                    <option value="">Semua Departemen</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ $department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    <!-- View Toggle -->
                    <div class="flex justify-end mb-4" x-data="{ view: 'graph' }">
                        <div class="bg-gray-100 p-1 rounded-lg inline-flex">
                            <button @click="view = 'graph'; document.getElementById('chart-container').classList.remove('hidden'); document.getElementById('table-container').classList.add('hidden');" 
                                    :class="view === 'graph' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-900'"
                                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors">
                                Grafik
                            </button>
                            <button @click="view = 'table'; document.getElementById('chart-container').classList.add('hidden'); document.getElementById('table-container').classList.remove('hidden');"
                                    :class="view === 'table' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-900'"
                                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors">
                                Tabel
                            </button>
                        </div>
                    </div>

                    <!-- Chart Container -->
                    <div id="chart-container" class="mb-8 p-4 border border-gray-200 rounded-xl">
                        <div id="budgetChart" style="min-height: 400px;"></div>
                        <div class="mt-4 text-center text-sm text-gray-500">
                            @if(!$site_id)
                                <span class="font-medium text-blue-600">Klik pada bar Site untuk melihat detail Departemen</span>
                            @elseif(!$department_id)
                                <span class="font-medium text-blue-600">Klik pada bar Departemen untuk melihat detail Budget</span>
                            @else
                                <span>Menampilkan detail Budget per Item</span>
                            @endif
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div id="table-container" class="hidden overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <!-- Table Content Same as Before -->
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Entity (Job / Station)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Budget</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Used</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">% Used</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($budgets as $budget)
                                    @php
                                        if ($budget->amount > 0) {
                                            $percent = ($budget->used_amount / $budget->amount) * 100;
                                        } else {
                                            $percent = $budget->used_amount > 0 ? 100 : 0;
                                        }

                                        $colorClass = 'bg-green-500';
                                        if ($percent > 80) $colorClass = 'bg-yellow-500';
                                        if ($percent >= 100) $colorClass = 'bg-red-500';
                                        
                                        $displayPercent = number_format($percent, 0) . '%';
                                        if ($budget->amount == 0 && $budget->used_amount > 0) {
                                            $displayPercent = '>100%';
                                            $percent = 100; // Cap visual bar at 100
                                        }
                                        
                                        $name = '-';
                                        if ($budget->job) {
                                            $name = $budget->job->code . ' - ' . $budget->job->name;
                                            $dept = $budget->department->name ?? '-';
                                        } elseif ($budget->subDepartment) {
                                            $name = $budget->subDepartment->name;
                                            $dept = $budget->subDepartment->department->name ?? '-';
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $budget->category ?? 'Job Budget' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dept }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($budget->amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($budget->used_amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap align-middle">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                    <div class="h-full {{ $colorClass }}" style="width: {{ min($percent, 100) }}%"></div>
                                                </div>
                                                <span class="text-xs font-bold text-gray-600 w-12 text-right">{{ $displayPercent }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button @click="showModal = true; modalContent = '<div class=\'text-center py-4\'><div class=\'animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto\'></div></div>'; fetch('/admin/budgets/{{ $budget->id }}/details').then(r => r.json()).then(d => modalContent = d.html)" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-xs font-bold">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                            Tidak ada data budget ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alpine Modal -->
        <div x-show="showModal" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="showModal = false"
                     aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                    Detail Penggunaan Budget
                                </h3>
                                <div class="mt-2" x-html="modalContent"></div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" 
                                @click="showModal = false">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ApexCharts Rule -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var chartData = @json($chartData);
            
            var options = {
                series: [{
                    name: 'Budget',
                    data: chartData.budget
                }, {
                    name: 'Used',
                    data: chartData.used
                }],
                chart: {
                    type: 'bar',
                    height: 450,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            var index = config.dataPointIndex;
                            var id = chartData.ids[index];
                            var level = chartData.level;
                            var url = new URL(window.location.href);
                            
                            if (id) {
                                if (level === 'site') {
                                    url.searchParams.set('site_id', id);
                                    window.location.href = url.toString();
                                } else if (level === 'department') {
                                    url.searchParams.set('department_id', id);
                                    window.location.href = url.toString();
                                }
                            }
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '60%',
                        borderRadius: 6, // Rounded corners
                        borderRadiusApplication: 'end',
                        dataLabels: {
                            position: 'top',
                        },
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: chartData.labels,
                    labels: {
                        style: {
                            colors: '#64748B',
                            fontSize: '12px',
                            fontWeight: 600,
                        },
                        rotate: -45,
                        trim: true,
                        maxHeight: 120,
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#64748B',
                            fontSize: '12px',
                            fontWeight: 500,
                        },
                        formatter: function (value) {
                            // Shorten large numbers
                            if (value >= 1000000000) return (value / 1000000000).toFixed(1) + 'M';
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'jt';
                            return value;
                        }
                    }
                },
                grid: {
                    borderColor: '#E2E8F0',
                    strokeDashArray: 4,
                    yaxis: {
                        lines: { show: true }
                    },
                    xaxis: {
                        lines: { show: false }
                    },
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 10
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: "vertical",
                        shadeIntensity: 0.3,
                        inverseColors: false,
                        opacityFrom: 0.9,
                        opacityTo: 0.7,
                        stops: [0, 100]
                    }
                },
                colors: ['#3B82F6', '#EF4444'], // Premium Blue & Red
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function (val) {
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
                        }
                    },
                    shared: true,
                    intersect: false,
                    style: {
                        fontSize: '13px'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right', 
                    offsetY: -20,
                    markers: {
                        radius: 12,
                    },
                    itemMargin: {
                        horizontal: 10,
                        vertical: 5
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#budgetChart"), options);
            chart.render();
        });
    </script>
</x-prsystem::app-layout>
