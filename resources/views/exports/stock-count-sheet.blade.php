<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <title>ورقة جرد - {{ $stockCount->code }}</title>
    <style>
        * {
            font-family: 'dejavusans', sans-serif;
            box-sizing: border-box;
        }

        body {
            direction: rtl;
            padding: 0;
            margin: 0;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 4px 8px;
            font-size: 11px;
        }

        .info-label {
            font-weight: bold;
            color: #333;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        table.items th,
        table.items td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }

        table.items th {
            background-color: #333;
            color: white;
            font-weight: bold;
        }

        table.items tr:nth-child(even) {
            background-color: #fafafa;
        }

        .counted-cell {
            background-color: #fff;
            min-width: 80px;
            height: 25px;
        }

        .notes-cell {
            min-width: 120px;
        }
    </style>
</head>

<body>
    <!-- Purple Header Bar -->
    <div
        style="margin-bottom: 15px; padding: 15px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px;">
        <table style="width: 100%; border: none; color: white;">
            <tr>
                <!-- Right: Report Title + Code -->
                <td style="border: none; text-align: right; width: 35%; vertical-align: top;">
                    <div style="font-size: 16px; font-weight: bold; color: white;">ورقة جرد المخزون</div>
                    <div style="font-size: 11px; color: rgba(255,255,255,0.8); margin-top: 3px;">{{ $stockCount->code }}
                    </div>
                </td>
                <!-- Center: App Name -->
                <td style="border: none; text-align: center; width: 30%; vertical-align: middle;">
                    <div style="font-size: 22px; font-weight: bold; color: white;">{{ config('app.name') }}</div>
                </td>
                <!-- Left: Date + Status -->
                <td style="border: none; text-align: left; width: 35%; vertical-align: top;">
                    <div style="font-size: 12px; color: white;">{{ now()->format('Y/m/d H:i') }}</div>
                    <div style="font-size: 11px; color: rgba(255,255,255,0.8); margin-top: 3px;">
                        {{ $stockCount->status->label() }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">المستودع:</td>
            <td>{{ $stockCount->warehouse->name }}</td>
            <td class="info-label">نوع الجرد:</td>
            <td>{{ $stockCount->type->label() }}</td>
            <td class="info-label">إجمالي الأصناف:</td>
            <td>{{ $stockCount->total_items }}</td>
            <td class="info-label">أنشأه:</td>
            <td>{{ $stockCount->createdBy->name ?? '-' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">SKU</th>
                <th style="width: 35%;">اسم المنتج</th>
                <th style="width: 12%;">كمية النظام</th>
                <th style="width: 13%;">الكمية المعدودة</th>
                <th style="width: 20%;">ملاحظات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['sku'] }}</td>
                    <td style="text-align: right;">
                        {{ $item['product_name'] }}{{ $item['variant_name'] ? ' - ' . $item['variant_name'] : '' }}</td>
                    <td>{{ $item['system_quantity'] }}</td>
                    <td class="counted-cell"></td>
                    <td class="notes-cell"></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Inline Signatures -->
    <table style="width: 100%; margin-top: 40px; border: none;">
        <tr>
            <td style="width: 33%; text-align: center; border: none; padding: 10px;">
                <div
                    style="border-top: 1px solid #333; width: 150px; margin: 30px auto 5px auto; padding-top: 5px; font-size: 11px;">
                    أمين المخزن
                </div>
            </td>
            <td style="width: 33%; text-align: center; border: none; padding: 10px;">
                <div
                    style="border-top: 1px solid #333; width: 150px; margin: 30px auto 5px auto; padding-top: 5px; font-size: 11px;">
                    مراقب الجرد
                </div>
            </td>
            <td style="width: 33%; text-align: center; border: none; padding: 10px;">
                <div
                    style="border-top: 1px solid #333; width: 150px; margin: 30px auto 5px auto; padding-top: 5px; font-size: 11px;">
                    المدير المسؤول
                </div>
            </td>
        </tr>
    </table>
</body>

</html>