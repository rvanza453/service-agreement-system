<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Purchase Request - {{ $pr->pr_number }}</title>
    <style>
        @page {
            margin: 2cm 2.5cm; /* Vertikal 2cm, Horizontal 2.5cm */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #000;
            padding: 40px; /* Tambahan padding internal */
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        
        .header-left {
            display: table-cell;
            width: 15%;
            vertical-align: middle;
        }
        
        .header-left img {
            max-width: 80px;
            height: auto;
        }
        
        .header-center {
            display: table-cell;
            width: 70%;
            text-align: center;
            vertical-align: middle;
        }
        
        .header-center h1 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header-right {
            display: table-cell;
            width: 15%;
            text-align: right;
            vertical-align: top;
            font-size: 10pt;
        }
        
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-left {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
            vertical-align: top;
        }
        
        .info-right {
            display: table-cell;
            width: 50%;
            padding-left: 10px;
            vertical-align: top;
            border-left: 1px solid #000;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 2px 5px;
            font-size: 8pt;
        }
        
        .info-table .label {
            width: 40%;
            font-weight: normal;
        }
        
        .info-table .value {
            font-weight: bold;
        }
        
        .budget-info {
            font-size: 8pt;
        }
        
        .budget-info .label {
            font-weight: normal;
            text-align: right;
            padding-right: 5px;
        }
        
        .budget-info .colon {
            width: 10px;
            text-align: center;
        }
        
        .budget-info .value {
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8pt;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 7pt;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .items-table .no-col {
            width: 3%;
        }
        
        .items-table .code-col {
            width: 7%;
        }
        
        .items-table .name-col {
            width: 15%;
        }
        
        .items-table .spec-col {
            width: 15%;
        }
        
        .items-table .qty-col {
            width: 3%;
        }
        
        .items-table .price-col {
            width: 10%;
        }
        
        .items-table .note-col {
            width: 10%;
        }
        
        .sub-header {
            font-size: 6pt;
            padding: 1px 2px !important;
        }
        
        .footer-table {
            margin-top: 5px;
            font-size: 7pt;
        }
        
        .signatures {
            margin-top: 15px;
            page-break-inside: avoid;
        }
        
        .signature-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .signature-box {
            display: table-cell;
            text-align: center;
            padding: 3px;
            vertical-align: top;
            border: none;
            font-size: 6pt;
        }
        
        .signature-role {
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .signature-img-container {
            position: absolute;
            top: -15px; /* Offset to float above the line */
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            width: 100%;
            text-align: center;
        }
        
        .signature-img {
            max-height: 55px;
            max-width: 90%;
            opacity: 0.85; /* Keep text visible underneath */
        }
        
        .signature-name {
            font-weight: bold;
            border-top: 1px solid #000;
            display: inline-block;
            width: 90%;
            padding-top: 1px;
            font-size: 6pt;
            position: relative;
            z-index: 2;
            margin-top: 30px; /* Create space for the signature above it */
            line-height: 1.1;
        }

        .signature-position {
            font-size: 6pt;
            margin-top: 1px;
            position: relative;
            z-index: 2;
        }
        
        .signature-date {
            font-size: 5pt;
            color: #444;
            margin-top: 1px;
            position: relative;
            z-index: 2;
        }

        .footer-approved {
            position: fixed;
            bottom: 1cm;
            right: 2.5cm;
            font-size: 8pt;
            color: #000;
            text-align: right;
            font-style: italic;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            @php
                // 1. Definisikan path file secara internal (Server Path)
                $path = public_path('images/saraswantiLogo.png');
                
                $logoData = null;

                // 2. Cek apakah file benar-benar ada di server
                if (file_exists($path)) {
                    // 3. Baca file dan ubah ke Base64
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $logoData = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            @endphp

            @if($logoData)
                <img src="{{ $logoData }}" alt="Logo" class="logo">
            @else
                <span style="color:red; font-size:8px;">Logo file not found at: {{ $path }}</span>
            @endif
        </div>
        <div class="header-center">
            <h1>LEMBAR PERMINTAAN PEMBELIAN BARANG</h1>
        </div>
        <div class="header-right">
            <strong>No. PP: {{ $pr->pr_number }}</strong>
        </div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-left">
            <table class="info-table">
                <tr>
                    <td class="label">Departemen</td>
                    <td class="value">: {{ $pr->department->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Pengajuan</td>
                    <td class="value">: {{ $pr->request_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis/Pekerjaan/Unit/Stadium/Kategori</td>
                    <td class="value">: {{ $jobName }}</td>
                </tr>
                <tr>
                    <td class="label">No. PP</td>
                    <td class="value">: {{ $pr->pr_number }}</td>
                </tr>
            </table>
        </div>
        <div class="info-right">
            <table class="info-table budget-info" style="width: auto; margin-left: auto;">
                <tr>
                    <td class="label">Total Anggaran</td>
                    <td class="colon">:</td>
                    <td class="value">Rp {{ number_format($budgetInfo['total'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Actual Pengeluaran</td>
                    <td class="colon">:</td>
                    <td class="value">Rp {{ number_format($budgetInfo['actual'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Permintaan Saat Ini</td>
                    <td class="colon">:</td>
                    <td class="value">Rp {{ number_format($budgetInfo['current'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Saldo Anggaran</td>
                    <td class="colon">:</td>
                    <td class="value">Rp {{ number_format($budgetInfo['saldo'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    @php
        // Distinguish HO and Department approvals using GlobalApproverConfig
        $hoUserIds = \Modules\PrSystem\Models\GlobalApproverConfig::pluck('user_id')->toArray();
        
        $hoApprovals = $approvals->filter(function($a) use ($hoUserIds) {
            return in_array($a->approver_id, $hoUserIds);
        })->sortBy('level');
        
        $deptApprovals = $approvals->reject(function($a) use ($hoUserIds) {
            return in_array($a->approver_id, $hoUserIds);
        })->sortBy('level');
    @endphp

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th rowspan="2" class="no-col">No</th>
                <th rowspan="2" class="code-col">Kode<br>Barang</th>
                <th rowspan="2" class="name-col">Nama Barang</th>
                <th rowspan="2" class="spec-col">Detail Spesifikasi</th>
                <th colspan="{{ 3 + count($hoApprovals) }}">Kuantitas</th>
                <th colspan="3">Kuantitas Disetujui</th>
                <th rowspan="2" class="price-col">Total Harga (Rp)</th>
                <th rowspan="2" class="note-col">Keterangan</th>
            </tr>
            <tr>
                <th class="sub-header qty-col">Sat.</th>
                <th class="sub-header qty-col">Pengajuan</th>
                <th class="sub-header qty-col">Stock</th>
                @foreach($hoApprovals as $ho)
                    @php
                        // Display actual user position in table header instead of role_name
                        $roleToDisplay = $ho->approver->position ?? ($ho->role_name ?? 'HO Approver');
                    @endphp
                    <th class="sub-header qty-col">{{ $roleToDisplay }}</th>
                @endforeach
                <th class="sub-header qty-col">Anggaran</th>
                <th class="sub-header qty-col">Pengajuan</th>
                <th class="sub-header qty-col">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pr->items as $index => $item)
            @php
                $finalQty = $item->getFinalQuantity();
                
                // Karena input harga manual sudah dimatikan, harga pengajuan sudah pasti 
                // adalah harga master product saat PR dibuat.
                // Kita gunakan harga historis yang tersimpan agar tidak berubah jika master product diupdate.
                $basePrice = $item->price_estimation;
                
                $anggaranPrice = $basePrice;
                $pengajuanPrice = $basePrice;
                
                $totalPrice = $basePrice * $finalQty;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->product->code ?? '-' }}</td>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->specification ?? '-' }}</td>
                <td class="text-center">{{ $item->unit ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-center">-</td>
                @foreach($hoApprovals as $ho)
                    <td class="text-center">{{ $ho->adjusted_quantities[$item->id] ?? '-' }}</td>
                @endforeach
                <td class="text-right">{{ $anggaranPrice > 0 ? (is_numeric($anggaranPrice) ? number_format($anggaranPrice, 0, ',', '.') : '-') : '-' }}</td>
                <td class="text-right">{{ number_format($pengajuanPrice, 0, ',', '.') }}</td>
                <td class="text-center">{{ $finalQty }}</td>
                <td class="text-right">{{ number_format($totalPrice, 0, ',', '.') }}</td>
                <td>{{ $item->remarks ?? '-' }}</td>
            </tr>
            @endforeach
            
            <tr>
                <td colspan="{{ (10) + count($hoApprovals) }}" class="text-right" style="font-weight: bold;">
                    Total Harga yang disetujui : Rp {{ number_format($pr->items->sum(function($item) {
                        $est = $item->product->price_estimation ?? 0;
                        $price = $est > 0 ? $est : $item->price_estimation;
                        return $price * $item->getFinalQuantity();
                    }), 0, ',', '.') }}
                </td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <!-- Signatures -->
    <div class="signatures" style="margin-top: 20px;">
        
        <!-- Row 1: Site Approvals (Diperiksa Oleh) -->
        <div style="width: 100%; margin-bottom: 30px;">
            <div style="text-align: center; margin-bottom: 10px; font-weight: bold; font-size: 8pt;">Diperiksa Oleh,</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                @foreach($deptApprovals as $approval)
                    <td style="width: {{ 100 / max(count($deptApprovals), 1) }}%; text-align: center; vertical-align: top; padding: 2px; font-size: 6pt;">
                        @php
                            $config = \Modules\PrSystem\Models\ApproverConfig::where('department_id', $pr->department_id)->where('user_id', $approval->approver_id)->first();
                            $roleToDisplay = $config ? $config->role_name : $approval->role_name;
                        @endphp
                        <div class="signature-role" style="margin-bottom: 5px;">{{ $roleToDisplay }}</div>
                        <div style="height: 55px; position: relative;">
                            <div class="signature-img-container">
                                @if($approval->approver->signature_path)
                                    @php
                                        $signaturePath = storage_path('app/public/' . $approval->approver->signature_path);
                                        if(file_exists($signaturePath)) {
                                            $imageData = base64_encode(file_get_contents($signaturePath));
                                            $imageMime = mime_content_type($signaturePath);
                                            $base64Image = 'data:' . $imageMime . ';base64,' . $imageData;
                                        } else {
                                            $base64Image = null;
                                        }
                                    @endphp
                                    @if(isset($base64Image) && $base64Image)
                                        <img src="{{ $base64Image }}" alt="Signature" class="signature-img">
                                    @endif
                                @endif
                            </div>
                            <div class="signature-name">{{ $approval->approver->name }}</div>
                            <div class="signature-position">{{ $approval->approver->position }}</div>
                            <div class="signature-date">{{ $approval->approved_at->format('d/m/Y') }}</div>
                        </div>
                    </td>
                @endforeach
                </tr>
            </table>
        </div>

        <!-- Row 2: HO Approvals (Disetujui Oleh) -->
        <div style="width: 100%;">
            <div style="text-align: center; margin-bottom: 10px; font-weight: bold; font-size: 8pt;">Disetujui Oleh,</div>
            <table style="width: 100%; border-collapse: collapse;  margin-top: 10px;">
                <tr>
                @foreach($hoApprovals->sortBy('level') as $approval)
                    <td style="width: {{ 100 / max(count($hoApprovals), 1) }}%; text-align: center; vertical-align: top; padding: 2px; font-size: 6pt;">
                        @php
                            $config = \Modules\PrSystem\Models\GlobalApproverConfig::where('user_id', $approval->approver_id)->first();
                            $roleToDisplay = $config ? $config->role_name : $approval->role_name;
                        @endphp
                        <div style="height: 55px; position: relative;">
                            <div class="signature-img-container">
                                    @if($approval->approver->signature_path)
                                    @php
                                        $signaturePath = storage_path('app/public/' . $approval->approver->signature_path);
                                        if(file_exists($signaturePath)) {
                                            $imageData = base64_encode(file_get_contents($signaturePath));
                                            $imageMime = mime_content_type($signaturePath);
                                            $base64Image = 'data:' . $imageMime . ';base64,' . $imageData;
                                        } else {
                                            $base64Image = null;
                                        }
                                    @endphp
                                    @if(isset($base64Image) && $base64Image)
                                        <img src="{{ $base64Image }}" alt="Signature" class="signature-img">
                                    @endif
                                @endif
                            </div>
                            <div class="signature-name">{{ $approval->approver->name }}</div>
                            <div class="signature-position">{{ $approval->approver->position }}</div>
                            <div class="signature-date">{{ $approval->approved_at->format('d/m/Y') }}</div>
                        </div>
                    </td>
                @endforeach
                </tr>
            </table>
        </div>
    </div>

    <!-- Page Break for Reference Links -->
    @php
        $itemsWithLink = $pr->items->filter(fn($item) => !empty($item->url_link));
    @endphp

    @if($itemsWithLink->isNotEmpty())
        <div style="page-break-before: always;"></div>
        
        <div class="header">
            <div class="header-center">
                <h1>REFERENSI HARGA / LINK PRODUK</h1>
                <div style="font-size: 10pt;">Lampiran PR No: {{ $pr->pr_number }}</div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="no-col">No</th>
                    <th class="name-col" style="width: 30%;">Nama Barang</th>
                    <th class="qty-col" style="width: 10%;">Qty</th>
                    <th class="price-col" style="width: 15%;">Harga Satuan</th>
                    <th style="width: 40%;">Link / URL Referensi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemsWithLink as $index => $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td class="text-center">{{ $item->quantity }} {{ $item->unit }}</td>
                    <td class="text-right">Rp {{ number_format($item->price_estimation, 0, ',', '.') }}</td>
                    <td style="word-break: break-all; color: blue;">
                        <a href="{{ $item->url_link }}" target="_blank" style="text-decoration: none; color: blue;">{{ $item->url_link }}</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @php
        $lastHoApproval = $hoApprovals->sortByDesc('level')->first();
    @endphp

    @if($lastHoApproval)
    <div class="footer-approved">
        Approved : {{ $lastHoApproval->approved_at->format('d/m/Y') }}
    </div>
    @endif

</body>
</html>
