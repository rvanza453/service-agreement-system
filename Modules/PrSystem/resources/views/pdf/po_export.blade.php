<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PO {{ $po->po_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
        }
        
        .container {
            padding: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        /* Header Section */
        .header-table {
            margin-bottom: 10px;
        }
        
        .header-table td {
            vertical-align: top;
            padding: 5px;
        }
        
        .logo-cell {
            width: 120px;
            text-align: center;
            border: 1px solid #000;
        }
        
        .logo {
            width: 80px;
            height: auto;
        }
        
        .company-name {
            font-weight: bold;
            font-size: 9px;
            margin-top: 5px;
        }
        
        .company-info {
            border: 1px solid #000;
            padding: 8px;
            font-size: 9px;
        }
        
        .company-info div {
            margin-bottom: 2px;
        }
        
        .company-title {
            font-weight: bold;
            font-size: 11px;
            color: #0066cc;
        }
        
        .vendor-info {
            border: 1px solid #000;
            padding: 8px;
            font-size: 9px;
        }
        
        .vendor-label {
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        /* Title */
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            border: 2px solid #000;
            padding: 5px;
            background-color: #f0f0f0;
        }
        
        /* PO Details */
        .po-details {
            margin-bottom: 10px;
        }
        
        .po-details table {
            border: 1px solid #000;
        }
        
        .po-details td {
            padding: 4px 8px;
            border: 1px solid #000;
        }
        
        .po-label {
            background-color: #e0e0e0;
            font-weight: bold;
            width: 120px;
        }
        
        /* Items Table */
        .items-table {
            margin-bottom: 10px;
            border: 1px solid #000;
        }
        
        .items-table th {
            background-color: #e0e0e0;
            border: 1px solid #000;
            padding: 5px;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }
        
        .items-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        /* Totals Section */
        .totals-table {
            margin-left: auto;
            width: 400px;
            border: 1px solid #000;
        }
        
        .totals-table td {
            border: 1px solid #000;
            padding: 4px 8px;
            font-size: 9px;
        }
        
        .totals-label {
            background-color: #e0e0e0;
            font-weight: bold;
            width: 200px;
        }
        
        .totals-value {
            text-align: right;
            font-weight: bold;
        }
        
        .total-final {
            background-color: #d0d0d0;
            font-weight: bold;
            font-size: 10px;
        }
        
        /* Notes */
        .notes {
            margin: 10px 0;
            border: 1px solid #000;
            padding: 8px;
            min-height: 60px;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .notes-content {
            font-size: 9px;
            white-space: pre-line;
        }
        
        /* Signature */
        .signature-section {
            margin-top: 20px;
        }
        
        .signature-table td {
            text-align: center;
            vertical-align: bottom;
            padding: 5px;
        }
        
        .signature-space {
            height: 60px;
        }
        
        .signature-name {
            font-weight: bold;
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            padding-bottom: 2px;
        }
        
        .signature-title {
            font-size: 9px;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="logo-cell">
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
                </td>
                <td class="company-info">
                    <div class="company-title">PT. SARASWANTI SAWIT MAKMUR</div>
                    <div><strong>Jln. Untung Suropati No. 44 Desa Jone</strong></div>
                    <div><strong>Kec. Tanah Grogot, Kab. Paser, Kalimantan Timur</strong></div>
                    <div>HP: 0822 5188 6397 / 0821 5317 0433 (Riduan)</div>
                    <div>Email: ssm_purchasing@saraswanti.co.id</div>
                </td>
                <td class="vendor-info" rowspan="2">
                    <div style="text-align: right; margin-bottom: 5px;">Tanggal {{ $po->po_date ? $po->po_date->format('d M Y') : '-' }}</div>
                    <div class="vendor-label">Kepada:</div>
                    <div><strong>{{ $po->vendor_name }}</strong></div>
                    <div>{{ $po->vendor_address }}</div>
                    <div>KODE POS {{ $po->vendor_postal_code ?? '-' }}</div>
                    <div>Telp: {{ $po->vendor_phone }}</div>
                    <div style="margin-top: 5px;">Up: {{ $po->vendor_contact_person ?? '-' }}</div>
                    <div>HP: {{ $po->vendor_contact_phone ?? '-' }}</div>
                    <div>Email: {{ $po->vendor_email ?? '-' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0; border-top: none;">
                    <!-- Title -->
                    <div class="title" style="margin: 0;">PURCHASE ORDER</div>

                    <!-- PO Details -->
                    <div class="po-details" style="margin-bottom: 0;">
                        <table style="width: 100%;">
                            <tr>
                                <td class="po-label" style="width: 120px;">PO Number</td>
                                <td><strong>{{ $po->po_number }}</strong></td>
                            </tr>
                            <tr>
                                <td class="po-label">PO Date</td>
                                <td>{{ $po->po_date ? $po->po_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="po-label">Delivery Date</td>
                                <td>{{ $po->delivery_date ? $po->delivery_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="po-label">PR Number</td>
                                <td><strong>{{ $po->pr_number }}</strong></td>
                            </tr>
                            <tr>
                                <td class="po-label">PR Date</td>
                                <td>{{ $po->pr_date ? $po->pr_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">NO</th>
                    <th style="width: 40px;">KODE BARANG</th>
                    <th style="width: 110px;">NAMA BARANG</th>
                    <th>PART NUMBER / SPESIFIKASI</th>
                    <th style="width: 60px;">JUMLAH</th>
                    <th style="width: 50px;">SATUAN</th>
                    <th style="width: 90px;">HARGA SATUAN</th>
                    <th style="width: 100px;">JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $groupedItems = collect();

                    foreach($po->items as $item) {
                        $code = $item->prItem->product->code ?? '-';
                        $name = $item->prItem->item_name;
                        $unit = $item->unit;
                        // use a precise string representation to avoid float issues in key
                        $priceKey = number_format($item->unit_price, 2, '.', '');
                        
                        $key = $code . '|' . $name . '|' . $unit . '|' . $priceKey;

                        if (!$groupedItems->has($key)) {
                            $groupedItems->put($key, [
                                'code' => $code,
                                'name' => $name,
                                'specification' => $item->prItem->specification ?? '-',
                                'quantity' => 0,
                                'unit' => $unit,
                                'unit_price' => $item->unit_price,
                                'subtotal' => 0,
                            ]);
                        }

                        $group = $groupedItems->get($key);
                        $group['quantity'] += $item->quantity;
                        $group['subtotal'] += $item->subtotal;
                        
                        // Append specification if it's different and not just empty '-'
                        $currentSpec = $item->prItem->specification ?? '-';
                        if ($currentSpec !== '-' && $group['specification'] !== '-' && strpos($group['specification'], $currentSpec) === false) {
                             $group['specification'] .= ', ' . $currentSpec;
                        } elseif ($group['specification'] === '-' && $currentSpec !== '-') {
                             $group['specification'] = $currentSpec;
                        }

                        $groupedItems->put($key, $group);
                    }
                    $rowCount = 0;
                @endphp

                @foreach($groupedItems as $group)
                    @php $rowCount++; @endphp
                    <tr>
                        <td class="text-center">{{ $rowCount }}</td>
                        <td class="text-center">{{ $group['code'] }}</td>
                        <td>{{ $group['name'] }}</td>
                        <td>{{ $group['specification'] }}</td>
                        <td class="text-center">{{ $group['quantity'] }}</td>
                        <td class="text-center">{{ $group['unit'] }}</td>
                        <td class="text-right">{{ number_format($group['unit_price'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($group['subtotal'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                
                <!-- Empty rows for spacing -->
                @for($i = $groupedItems->count(); $i < 2; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td class="totals-label">Subtotal:</td>
                <td class="totals-value">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($po->discount_percentage > 0)
                <tr>
                    <td class="totals-label">Diskon ({{ $po->discount_percentage + 0 }}%):</td>
                    <td class="totals-value">- Rp {{ number_format($po->discount_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="totals-label">Jumlah Setelah Diskon:</td>
                    <td class="totals-value">Rp {{ number_format($po->subtotal - $po->discount_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($po->dpp_lainnya > 0)
                <tr>
                    <td class="totals-label">DPP Lainnya:</td>
                    <td class="totals-value">Rp {{ number_format($po->dpp_lainnya, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td class="totals-label">PPN {{ number_format($po->ppn_percentage, 0) }}%:</td>
                <td class="totals-value">Rp {{ number_format($po->ppn_amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-final">
                <td class="totals-label">TOTAL:</td>
                <td class="totals-value">Rp {{ number_format($po->final_amount, 0, ',', '.') }}</td>
            </tr>
        </table>

        <!-- Notes -->
        <div class="notes">
            <div class="notes-title">Catatan:</div>
            <div class="notes-content">{{ $po->notes }}</div>
        </div>

        <!-- Signature -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td style="width: 50%;">
                        &nbsp;
                    </td>
                    <td style="width: 50%; text-align: center;">
                        <div style="font-size: 9px; margin-bottom: 5px; text-align: center;">Disetujui oleh,</div>
                        <div class="signature-space"></div>
                        <div class="signature-name">Mulyadi Heri Wibowo</div>
                        <div class="signature-title">Direktur</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
