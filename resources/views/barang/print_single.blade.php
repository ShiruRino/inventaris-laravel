<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label - {{ $barang->kode_barcode }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* The Label Container (Physical Sticker Size) */
        .label-sticker {
            width: 300px; /* Adjust based on your sticker paper */
            height: 150px;
            background: white;
            border: 2px solid #000;
            padding: 10px;
            display: flex;
            flex-direction: row;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .qr-section {
            flex: 1;
            text-align: center;
        }

        .info-section {
            flex: 2;
            padding-left: 10px;
            font-size: 12px;
            line-height: 1.4;
        }

        .company-name {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            display: inline-block;
        }

        .item-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .item-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            font-weight: bold;
        }

        /* Button to trigger print */
        .no-print {
            position: fixed;
            bottom: 20px;
            right: 20px;
        }
        
        .btn-print {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        /* PRINT SETTINGS (Hides background, centers label) */
        @media print {
            body {
                background: none;
                height: auto;
                display: block;
            }
            .no-print {
                display: none;
            }
            .label-sticker {
                box-shadow: none;
                margin: 0;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="label-sticker">
        <div class="qr-section">
            {{-- QR Code --}}
            {!! $barang->qr_html !!}
        </div>
        <div class="info-section">
            <div class="company-name">INVENTARIS PERUSAHAAN</div>
            <div class="item-name">{{ Str::limit($barang->nama_barang, 25) }}</div>
            <div class="item-code">{{ $barang->kode_barcode }}</div>
            <div>Tgl: {{ date('d/m/Y') }}</div>
        </div>
    </div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Label</button>
    </div>

</body>
</html>