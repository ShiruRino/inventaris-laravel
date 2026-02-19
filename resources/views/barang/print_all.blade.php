<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Semua Label</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ccc; /* Gray background for screen */
            margin: 0;
            padding: 20px;
        }

        /* The Sheet (A4 Representation) */
        .page {
            background: white;
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            padding: 10mm;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: grid;
            /* 2 Columns layout. Change to 'repeat(3, 1fr)' for 3 columns */
            grid-template-columns: repeat(2, 1fr); 
            grid-gap: 10px; /* Space between stickers */
            align-content: start; 
        }

        /* The Individual Sticker */
        .label-sticker {
            border: 1px dashed #333; /* Dashed line for cutting guide */
            height: 120px; /* Fixed height per sticker */
            padding: 10px;
            display: flex;
            align-items: center;
            background: white;
            page-break-inside: avoid; /* Prevent cutting sticker in half */
        }

        .qr-section {
            flex: 0 0 90px; /* Fixed width for QR */
            text-align: center;
        }

        .info-section {
            flex: 1;
            padding-left: 15px;
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
            margin-bottom: 3px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: bold;
        }

        /* Floating Print Button */
        .no-print {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
        }
        
        .btn-print {
            background: #007bff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 18px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }

        /* PRINT MODE SETTINGS */
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .page {
                width: 100%;
                height: auto;
                box-shadow: none;
                margin: 0;
                padding: 0;
                display: grid;
            }
            .no-print {
                display: none;
            }
            .label-sticker {
                border: 1px solid #ddd; /* Lighter border for actual print */
            }
        }
    </style>
</head>
<body>

    <div class="page">
        @foreach ($barang as $item)
            <div class="label-sticker">
                <div class="qr-section">
                    {{-- THIS IS THE GENERATED IMAGE --}}
                    {!! $item->qr_html !!}
                </div>
                <div class="info-section">
                    <div class="company-name">INVENTARIS PERUSAHAAN</div>
                    <div class="item-name">{{ $item->nama_barang }}</div>
                    <div class="item-code">{{ $item->kode_barcode }}</div>
                    
                    {{-- Optional: Show Location/User --}}
                    <div style="font-size: 10px; margin-top: 4px; color: #555;">
                        @if($item->id_karyawan)
                            User: {{ Str::limit($item->karyawan->nama_karyawan, 15) }}
                        @else
                            Loc: {{ Str::limit($item->lokasi_fisik, 15) }}
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Semua</button>
    </div>

</body>
</html>