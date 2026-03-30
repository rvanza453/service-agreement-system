<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Capex - {{ $capex->capex_number }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt; /* Slightly larger base font */
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .bordered th, .bordered td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }
        .no-border td {
            border: none;
            padding: 2px;
            vertical-align: top;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .page-break { page-break-after: always; }
        
        /* Signature Area (Default for Page 1 & 3) */
        .sig-container {
            height: 60px; /* Restore height for standard sections */
            position: relative;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-img-wrapper {
            position: absolute;
            left: 50%;
            top: 50%; /* Center in container */
            transform: translate(-50%, -50%); /* Center on the anchor point */
            width: auto; 
            text-align: center;
            z-index: 5; 
        }
        .sig-img {
            max-height: 55px;
            max-width: 120px;
            opacity: 0.9;
        }
        .sig-placeholder {
            color: #888;
            font-size: 8pt;
            padding-top: 20px;
        }

        /* Approval Table Signatures (Smaller & Overwrite) */
        .approval-table .sig-container {
            height: 0px; 
            min-height: 0px;
            overflow: visible;
            vertical-align: top;
        }
        .approval-table .sig-img {
            max-height: 45px; /* Reduced specific to this table */
            max-width: 80px;
        }
        .approval-table .sig-img-wrapper {
            top: 10px; /* Move up, was 20px, before that 15px */
        }

        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 20px;
        }
        
        .question-box {
            border: 1px solid #000;
            padding: 5px;
            min-height: 40px;
            margin-bottom: 0px; /* Collapse borders if stacked */
        }
        
        .justifikasi-header {
            font-weight: bold;
            border: 1px solid #000;
            padding: 5px;
        }

        /* Page 2 Optimization */
        .page-2-content {
            font-size: 8pt;
        }
        .page-2-content .bordered th, .page-2-content .bordered td {
            padding: 2px;
        }
    </style>
</head>
<body>

    @php
        // Helper Signatures
        $getApproval = fn($index) => $approvals->where('column_index', $index)->first();
        $ttd1 = $getApproval(1); 
        $ttd2 = $getApproval(2); 
        $ttd3 = $getApproval(3); 
        $ttd4 = $getApproval(4); 
        $ttd5 = $getApproval(5); 
        
        // Function to get Role Label from Config if Approval is missing (Optional but good for dynamic labels)
        // For now, hardcoded roles in layout are used as per user request images.
        // User requested strict adherence to image layout, so we keep hardcoded labels like "KA. Keuangan", "KTU Group", etc.
        // However, if we wanted dynamic labels from config:
        // $getConfig = fn($index) => \Modules\PrSystem\Models\CapexColumnConfig::where('department_id', $capex->department_id)->where('column_index', $index)->first();

        // Helper Budget Figures
        $budgetAwal = ($capex->capexBudget->amount ?? 0) + ($capex->capexBudget->pta_amount ?? 0);
        $usulan     = $capex->amount;
        
        // Capex yang disetujui sebelumnya: Already computed in controller, just use it
        // $capexSebelumnya is passed from controller to avoid class resolution issues
        
        // Saldo Anggaran yang dapat dipakai (sehingga capex ini bisa diajukan)
        $saldoDapatDipakai = $capex->code_budget_ditanam ? ($budgetAwal - $capexSebelumnya) : 0;
        
        // Over / Under setelah usulan ini
        $overUnder = $capex->code_budget_ditanam ? ($saldoDapatDipakai - $usulan) : (0 - $usulan);
        $sisaAkhir = $overUnder;

        $answers = $capex->questionnaire_answers ?? [];
        
        // Optimally load signature images (Cache by path to avoid re-reading file for same user)
        $sigCache = [];
        $sigImages = [];
        
        foreach ($approvals as $approval) {
             if ($approval->status === 'Approved' && $approval->approver && !empty($approval->approver->signature_path)) {
                 $pathStr = $approval->approver->signature_path;
                 
                 if (!isset($sigCache[$pathStr])) {
                     $fullPath = storage_path('app/public/' . $pathStr);
                     if (file_exists($fullPath)) {
                         try {
                            $mime = mime_content_type($fullPath);
                            $content = file_get_contents($fullPath);
                            $sigCache[$pathStr] = 'data:' . $mime . ';base64,' . base64_encode($content);
                         } catch (\Exception $e) {
                            $sigCache[$pathStr] = null;
                         }
                     } else {
                         $sigCache[$pathStr] = null;
                     }
                 }
                 
                 $sigImages[$approval->column_index] = $sigCache[$pathStr];
             }
        }

        // Helper to get cached image
        $sigImg = fn($approval) => $approval ? ($sigImages[$approval->column_index] ?? null) : null;
        
        $logoPath = public_path('images/saraswantiLogo.png');
        $logoData = null;
        if (file_exists($logoPath)) {
            $logoData = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath));
        }


        // Estate Manager Logic (Static Mapping)
        $deptUpper = strtoupper($capex->department->name ?? '');
        $estateManager = match(true) {
            str_contains($deptUpper, 'PKS') => 'Iken Wahyudi',
            str_contains($deptUpper, 'MJE') => 'Ridwan',
            str_contains($deptUpper, 'KDE') => 'Mulia',
            str_contains($deptUpper, 'SIPIL') => 'Rumiansyah',
            str_contains($deptUpper, 'RO') => 'Rumiansyah',
            str_contains($deptUpper, 'TRAKSI') => 'Rumiansyah',
            str_contains($deptUpper, 'SAE') => 'M. Nasir Panjaitan',
            default => $capex->user->name
        };

        $isKtuDepartment = str_contains($deptUpper, 'SIPIL') || str_contains($deptUpper, 'RO') || str_contains($deptUpper, 'TRAKSI');
        $ktuApproval = $approvals->first(function ($approval) {
            $position = strtoupper($approval->approver->position ?? '');
            return str_contains($position, 'KTU');
        });
        $departmentHeadLabel = $isKtuDepartment ? 'KTU :' : 'Estate Manager/Department Head :';
        $departmentHeadName = $isKtuDepartment
            ? ($ktuApproval->approver->name ?? $ttd2->approver->name ?? 'KTU')
            : $estateManager;
    @endphp

    <!-- PAGE 1: JUSTIFIKASI -->
    
    <div style="border: 1px solid #000;">
        <div class="justifikasi-header">Justifikasi CAPEX</div>
        
        <!-- Q1 -->
        <div style="border-bottom: 1px solid #000; padding: 5px;">
            <div>Apa yang biasa dipakai selama ini ?</div>
            <br>
            <div style="min-height: 40px;">{{ $answers[1] ?? 'Tidak ada' }}</div>
        </div>

        <!-- Q2 -->
        <div style="border-bottom: 1px solid #000; padding: 5px;">
            <div>Mengapa pengeluaran diperlukan ?</div>
            <br>
            <div style="min-height: 40px;">{{ $answers[2] ?? '-' }}</div>
        </div>

        <!-- Q3 -->
        <div style="border-bottom: 1px solid #000; padding: 5px;">
            <div>Dapatkah pengeluaran ditunda pada tahun depan ? Jika tidak, mengapa ?</div>
            <br>
            <div style="min-height: 40px;">{{ $answers[3] ?? '-' }}</div>
        </div>

        <!-- Q4 -->
        <div style="border-bottom: 1px solid #000; padding: 5px;">
            <div>Apa konsekuensi jika pengeluaran ditolak?</div>
            <br>
            <div style="min-height: 40px;">{{ $answers[4] ?? '-' }}</div>
        </div>

        <!-- Q5 -->
        <div style="border-bottom: 1px solid #000; padding: 5px;">
            <div>Mungkinkah ada dampak buruk pada operasi yang ada (cth. Kekacauan, waktu, lingkungan)</div>
            <br>
            <div style="min-height: 40px;">{{ $answers[5] ?? 'Tidak ada' }}</div>
        </div>

        <!-- Q6 -->
        <div style="padding: 5px;">
            <div>Berapa lama proyek berlangsung ? Kapan proyek tersebut selesai ?</div>
            <br>
            <div style="min-height: 40px;">{{ $answers[6] ?? '-' }}</div>
        </div>
    </div>

    <!-- Signatures Page 1 -->
    <table style="width: 100%; margin-top: 50px; text-align: center;">
        <tr>
            <td width="33%">Dibuat oleh :</td>
            <td width="33%">Diperiksa oleh :</td>
            <td width="33%">Disetujui</td>
        </tr>
        <tr>
            <td style="padding-top: 20px;">
                <div class="sig-container">
                    @if($sigImg($ttd1))
                        <div class="sig-img-wrapper"><img src="{{ $sigImg($ttd1) }}" class="sig-img"></div>
                    @else
                        <div class="sig-placeholder">TTD 1</div>
                    @endif
                </div>
            </td>
            <td style="padding-top: 20px;">
                <div class="sig-container">
                    @if($sigImg($ttd2))
                        <div class="sig-img-wrapper"><img src="{{ $sigImg($ttd2) }}" class="sig-img"></div>
                    @else
                        <div class="sig-placeholder">TTD 2</div>
                    @endif
                </div>
            </td>
            <td style="padding-top: 20px;">
                <div class="sig-container">
                    @if($sigImg($ttd3))
                        <div class="sig-img-wrapper"><img src="{{ $sigImg($ttd3) }}" class="sig-img"></div>
                    @else
                        <div class="sig-placeholder">TTD 3</div>
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <b><u>{{ $ttd1->approver->name ?? $capex->user->name }}</u></b><br>
                KTU Group
            </td>
            <td>
                <b><u>{{ $ttd2->approver->name ?? '-' }}</u></b><br>
                KA Keuangan
            </td>
            <td>
                <b><u>{{ $ttd3->approver->name ?? '-' }}</u></b><br>
                General Manager
            </td>
        </tr>
    </table>

    <div style="text-align: right; margin-top: 50px; font-size: 9pt;">Page 1</div>
    
    <div class="page-break"></div>

    <!-- PAGE 2: OPERATING CAPEX -->
    <div class="page-2-content">
    
    <!-- Logo & Title -->
    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td width="15%">
                @if($logoData)<img src="{{ $logoData }}" style="max-width: 80px;">@endif
            </td>
            <td width="85%" style="text-align: center;">
                <h2 style="margin: 0;">Operating Capex</h2>
            </td>
        </tr>
    </table>

    <!-- Header Box -->
    <table class="bordered" style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td width="25%" class="font-bold">PT :</td>
            <td colspan="3" class="font-bold">SARASWANTI SAWIT MAKMUR</td>
        </tr>
        <tr>
            <td>Kebun/ Department :</td>
            <td>Regional Office / {{ $capex->department->name }}</td>
            <td width="15%">Cost Center</td>
            <td>Regional Office</td>
        </tr>
        <tr>
            <td>Project No.</td>
            <td></td>
            <td>Capex No.</td>
            <td>{{ $capex->capex_number }}</td>
        </tr>
        <tr>
            <td>{{ $departmentHeadLabel }}</td>
            <td>{{ $departmentHeadName }}</td>
            <td>Date</td>
            <td>{{ $capex->created_at->format('d-M-y') }}</td>
        </tr>
        <tr>
            <td>Tandatangan</td>
            <td class="text-center" style="height: 50px;">
                @if($sigImg($ttd1))
                    <img src="{{ $sigImg($ttd1) }}" style="max-height: 45px; max-width: 110px;">
                @endif
            </td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4">
                Penjelasan Project :<br>
                <b>{{ $capex->description }}</b>
            </td>
        </tr>
    </table>

    <!-- Checkboxes -->
    <table class="no-border" style="width: 100%; margin-bottom: 10px; text-align: center;">
        <tr>
            <td>Untuk Scan file kantor Regional Office</td>
        </tr>
    </table>

    <table class="no-border" style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td width="33%">
                <div style="border: 1px solid #000; width: 20px; height: 20px; display: inline-block; vertical-align: middle; text-align: center;">
                    {{ $capex->type == 'Baru' ? '✔' : '' }}
                </div> 
                Baru
            </td>
            <td width="33%">
                <div style="border: 1px solid #000; width: 60px; height: 20px; display: inline-block; vertical-align: middle; text-align: center;">
                     {{ $capex->type == 'Perbaikan' ? '✔' : '' }}
                </div> 
                Perbaikan
            </td>
            <td width="33%">
                <div style="border: 1px solid #000; width: 40px; height: 20px; display: inline-block; vertical-align: middle; text-align: center;">
                     {{ $capex->type == 'Penggantian' ? '✔' : '' }}
                </div> 
                Penggantian
            </td>
        </tr>
    </table>

    <!-- Item Table -->
    <table class="bordered" style="width: 100%; margin-bottom: 10px;">
        <tr>
            <th width="40%">Jenis barang & Penjelasan</th>
            <th width="15%">Quantity</th>
            <th width="10%">Unit</th>
            <th width="15%">Harga per Unit *</th>
            <th width="20%">IDR</th>
        </tr>
        <tr style="height: 100px;">
            <td>
                <b>{{ $capex->capexBudget->capexAsset->name ?? '' }} {{ $capex->description }}</b>
            </td>
            <td class="text-center">{{ $capex->quantity }}</td>
            <td class="text-center">Unit</td>
            <td class="text-right">{{ number_format($capex->price, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($capex->amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Budget Calculation -->
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td width="100%">
                <table class="bordered" style="width: 100%;">
                    <tr>
                        <th width="40%" style="text-align: left; font-size: 8pt; font-style: italic;">*) Diisi sesuai harga yang telah dibudgetkan</th>
                        <th class="text-center font-bold" width="30%">Total</th>
                        <th class="text-right font-bold" width="30%" style="font-size: 8pt;">{{ number_format($usulan, 0, ',', '.') }}</th>
                    </tr>
                    <tr>
                        <td rowspan="3" style="vertical-align: middle;">
                            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 0 10px;">
                                <div>
                                    Dianggarkan: 
                                    <span style="display: inline-block; border: 1px solid #000; width: 14px; height: 14px; text-align: center; margin-left: 5px; vertical-align: middle; line-height: 14px;">
                                        {{ $capex->is_budgeted ? '✔' : '' }}
                                    </span>
                                </div>
                                <div>
                                    Tidak Dianggarkan: 
                                    <span style="display: inline-block; border: 1px solid #000; width: 14px; height: 14px; text-align: center; margin-left: 5px; vertical-align: middle; line-height: 14px;">
                                        {{ !$capex->is_budgeted ? '✔' : '' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="text-right">Anggaran yang disetujui</td>
                        <td class="text-right">{{ number_format($budgetAwal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">Capex yang disetujui sebelumnya</td>
                        <td class="text-right">{{ number_format($capexSebelumnya, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">Saldo Anggaran yang dapat dipakai</td>
                        <td class="text-right">{{ number_format($saldoDapatDipakai, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Authorization Table (Page 2) -->
    <table class="bordered approval-table" style="width: 100%; margin-bottom: 20px;">
        <tr>
            <th>Business Unit</th>
            <th>Nama</th>
            <th>Tanda Tangan</th>
            <th>Tanggal</th>
        </tr>
        
        <!-- Mengetahui -->
        <tr><td colspan="4" class="font-bold">Mengetahui :</td></tr>
        
        <!-- TTD 3 (GM) -->
        <tr>
            <td>General Manager</td>
            <td>{{ $ttd3->approver->name ?? '-' }}</td>
            <td class="text-center sig-container">
                 @if($sigImg($ttd3)) <div class="sig-img-wrapper"><img src="{{ $sigImg($ttd3) }}" class="sig-img"></div> @else TTD 3 @endif
            </td>
            <td>{{ $ttd3 && $ttd3->signed_at ? $ttd3->signed_at->format('d-M-y') : '' }}</td>
        </tr>
        
        <!-- TTD 1 (KA Keu) -->
         <tr>
            <td>KA. Keuangan</td>
            <td>{{ $ttd1->approver->name ?? '-' }}</td>
            <td class="text-center sig-container">
                @if($sigImg($ttd1)) <div class="sig-img-wrapper"><img src="{{ $sigImg($ttd1) }}" class="sig-img"></div> @else TTD 1 @endif
            </td>
            <td>{{ $ttd1 && $ttd1->signed_at ? $ttd1->signed_at->format('d-M-y') : '' }}</td>
        </tr>

        <!-- Disetujui -->
        <tr><td colspan="4" class="font-bold">Disetujui :</td></tr>

        <!-- TTD 4 (Manager FAT) -->
        <tr>
            <td>Manager FAT</td>
            <td>{{ $ttd4->approver->name ?? '-' }}</td>
            <td class="text-center sig-container">
                 @if($sigImg($ttd4)) <div class="sig-img-wrapper"><img src="{{ $sigImg($ttd4) }}" class="sig-img"></div> @else TTD 4 @endif
            </td>
            <td>{{ $ttd4 && $ttd4->signed_at ? $ttd4->signed_at->format('d-M-y') : '' }}</td>
        </tr>

        <!-- TTD 5 (Head EPD) -->
        <tr>
            <td>Head Of EPD</td>
            <td>{{ $ttd5->approver->name ?? '-' }}</td>
            <td class="text-center sig-container">
                @if($sigImg($ttd5)) <div class="sig-img-wrapper"><img src="{{ $sigImg($ttd5) }}" class="sig-img"></div> @else TTD 5 @endif
            </td>
            <td>{{ $ttd5 && $ttd5->signed_at ? $ttd5->signed_at->format('d-M-y') : '' }}</td>
        </tr>

        <!-- Wet Signatures -->
        <tr>
            <td>Deputy COO. Divisi Perkebunan</td>
            <td>Mulyadi Heri Wibowo</td>
            <td class="text-center" style="color: #888;"></td>
            <td></td>
        </tr>
        <tr>
            <td>CEO PT. Saraswanti Group</td>
            <td>Ir. Hari Hardono</td>
            <td class="text-center" style="color: #888;"></td>
            <td></td>
        </tr>
    </table>

    <!-- Negotiation (Page 2) -->
    <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">HASIL NEGOSIASI **</div>
    <table class="bordered" style="width: 100%;">
        <tr>
            <th rowspan="2" style="vertical-align: middle; width: 25%;">Nama Supplier</th>
            <th rowspan="2" style="vertical-align: middle; width: 15%;">Harga (Rp./unit)</th>
            <th colspan="2" class="text-center" width="30%">Pemenang</th>
            <th rowspan="2" style="vertical-align: middle; width: 15%;">C E O</th>
            <th rowspan="2" style="vertical-align: middle; width: 15%;">Tanggal</th>
        </tr>
        <tr>
            <th width="15%">Supplier</th>
            <th width="15%">Rp./Unit</th>
        </tr>
        <tr>
            <td height="12"></td>
            <td class="text-center"></td>
            <td rowspan="4" class="text-center" style="vertical-align: bottom;"></td>
            <td rowspan="4" class="text-center" style="vertical-align: bottom;"></td>
            <td rowspan="4" class="text-center" style="vertical-align: bottom;"></td>
            <td rowspan="4" class="text-center" style="vertical-align: bottom;"></td>
        </tr>
        <tr>
            <td height="12"></td>
            <td></td>
        </tr>
        <tr>
             <td height="12"></td>
             <td></td>
         </tr>
         <tr>
             <td height="12"></td>
             <td></td>
         </tr>
    </table>
    
    <div style="font-size: 8pt; margin-top: 5px;">
        *) - Harga Exclude PPN,<br>
        - Franko PT. SSM Kerang Dayo<br>
        - Metode Pembayaran : Lunas sebelum pengiriman<br>
        **) Diisi oleh HO Group
    </div>
    
    </div>
    <div style="text-align: right; margin-top: 50px; font-size: 9pt;">Page 2</div>
    
    <div class="page-break"></div>

    <!-- PAGE 3: PERSETUJUAN CAPEX HEAD -->
    
    <div style="text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 20px;">
        PERSETUJUAN C A P E X
    </div>
    <div style="text-align: right; margin-bottom: 20px;">
        No. Ref : {{ $capex->capex_number }}
    </div>
    
    <div style="margin-bottom: 10px;">
        <b>PT. Saraswanti Sawit Makmur</b><br>
        Regional Office
    </div>

    <table class="bordered" style="width: 100%; height: 600px;">
        <tr>
            <td colspan="2" width="60%"></td>
            <td width="40%" class="text-center" style="vertical-align: middle;">KETERANGAN</td>
        </tr>
        
        <!-- Row 1 -->
        <tr>
            <td width="30%">Nilai Anggaran</td>
            <td width="30%">{{ number_format($budgetAwal, 0, ',', '.') }}</td>
            <td rowspan="4" style="vertical-align: bottom; text-align: right; padding-right: 10px;">
                 General Manager PT. SSM
                 <div style="height: 100px; text-align: right; position: relative;">
                    @if($sigImg($ttd3))
                        <div class="sig-img-wrapper" style="text-align: right; right: 0; left: auto; transform: none; top: 20px;">
                            <img src="{{ $sigImg($ttd3) }}" style="max-height: 80px;">
                        </div>
                    @endif
                 </div>
            </td>
        </tr>
        
        <!-- Row 2 -->
        <tr>
            <td>Sisa Saldo Anggaran yang dapat di pakai</td>
            <td>{{ number_format($saldoDapatDipakai, 0, ',', '.') }}</td>
        </tr>

        <!-- Row 3 -->
        <tr>
            <td>Nilai Usulan Pembelian</td>
            <td>{{ number_format($usulan, 0, ',', '.') }}</td>
        </tr>

         <!-- Row 4 -->
         <tr>
            <td>Over/Under</td>
            <td>{{ number_format($saldoDapatDipakai - $usulan, 0, ',', '.') }}</td>
        </tr>

        <!-- Row 5 -->
        <tr>
            <td>Sisa Saldo Anggaran</td>
            <td>{{ number_format($sisaAkhir, 0, ',', '.') }}</td>
            <td rowspan="6" style="vertical-align: top; padding: 10px;">
                KOMENTAR :
            </td>
        </tr>
        
        <!-- Row 6 (Deputy) -->
        <tr>
            <td>Persetujuan Deputy COO Divisi Perkebunan</td>
            <td class="text-center" style="height: 60px; color: #888; vertical-align: middle;"></td>
        </tr>

        <!-- Row 7 (CEO) -->
        <tr>
            <td>Persetujuan CEO<br>PT. Saraswanti Utama</td>
            <td class="text-center" style="height: 60px; color: #888; vertical-align: middle;"></td>
        </tr>

        <!-- Row 8 (Date) -->
        <tr>
            <td>Tanggal :</td>
            <td></td>
        </tr>
        
        <!-- Row 9 -->
        <tr>
            <td>Diterima,</td>
            <td></td>
        </tr>

        <!-- Row 10 -->
        <tr>
            <td>Kembali ke PT. SSM</td>
            <td></td>
        </tr>

    </table>

    <div style="text-align: right; margin-top: 50px; font-size: 9pt;">Page 3</div>

</body>
</html>
