<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} - {{ $stockCount->code }}</title>
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

        .summary-box {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .summary-box table {
            width: 100%;
        }

        .summary-box td {
            padding: 5px;
            text-align: center;
        }

        .summary-value {
            font-size: 20px;
            font-weight: bold;
        }

        .summary-label {
            font-size: 10px;
            color: #666;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        table.items th,
        table.items td {
            border: 1px solid #333;
            padding: 5px;
            text-align: center;
        }

        table.items th {
            background-color:
                {{ $reportType === 'shortage' ? '#dc2626' : '#f59e0b' }}
            ;
            color: white;
            font-weight: bold;
        }

        table.items tr:nth-child(even) {
            background-color: #fafafa;
        }

        .shortage {
            color: #dc2626;
        }

        .excess {
            color: #f59e0b;
        }
    </style>
</head>

<body>
    <!-- Header Bar -->
    <div
        style="margin-bottom: 15px; padding: 15px 20px; background: linear-gradient(135deg, {{ $reportType === 'shortage' ? '#dc2626' : '#f59e0b' }} 0%, {{ $reportType === 'shortage' ? '#991b1b' : '#d97706' }} 100%); border-radius: 8px;">
        <table style="width: 100%; border: none; color: white;">
            <tr>
                <td style="border: none; text-align: right; width: 35%; vertical-align: top;">
                    <div style="font-size: 16px; font-weight: bold; color: white;">{{ $reportTitle }}</div>
                    <div style="font-size: 11px; color: rgba(255,255,255,0.8); margin-top: 3px;">{{ $stockCount->code }}
                    </div>
                </td>
                <td style="border: none; text-align: center; width: 30%; vertical-align: middle;">
                    <div style="font-size: 22px; font-weight: bold; color: white;">{{ config('app.name') }}</div>
                </td>
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
            <td class="info-label">تاريخ الانتهاء:</td>
            <td>{{ $stockCount->completed_at?->format('Y/m/d H:i') ?? '-' }}</td>
            <td class="info-label">اعتمده:</td>
            <td>{{ $stockCount->approvedBy->name ?? 'لم يتم اعتماده' }}</td>
        </tr>
    </table>

    <div class="summary-box">
        <table>
            <tr>
                <td>
                    <div class="summary-value {{ $reportType }}">{{ $summary['total_items'] }}</div>
                    <div class="summary-label">عدد الأصناف</div>
                </td>
                <td>
                    <div class="summary-value {{ $reportType }}">{{ $summary['total_qty'] }}</div>
                    <div class="summary-label">إجمالي الكمية</div>
                </td>
                <td>
                    <div class="summary-value {{ $reportType }}">{{ number_format($summary['total_value'], 2) }} ج.م
                    </div>
                    <div class="summary-label">إجمالي القيمة</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>SKU</th>
                <th>المنتج</th>
                <th>كمية النظام</th>
                <th>الكمية المعدودة</th>
                <th>{{ $reportType === 'shortage' ? 'العجز' : 'الزيادة' }}</th>
                <th>السبب</th>
                <th>المسؤول</th>
                <th>التكلفة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->sku }}</td>
                    <td style="text-align: right;">{{ Str::limit($item->display_name, 30) }}</td>
                    <td>{{ $item->system_quantity }}</td>
                    <td>{{ $item->counted_quantity ?? '-' }}</td>
                    <td class="{{ $reportType }}">
                        {{ abs($item->difference) }}
                    </td>
                    <td>{{ $item->movement?->reason_type?->label() ?? '-' }}</td>
                    <td>{{ $item->movement?->responsible?->name ?? '-' }}</td>
                    <td>{{ $item->difference_value ? number_format(abs($item->difference_value), 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signatures -->
    <table style="width: 100%; margin-top: 40px; border: none;">
        <tr>
            <td style="width: 50%; text-align: center; border: none; padding: 10px;">
                <div
                    style="border-top: 1px solid #333; width: 200px; margin: 30px auto 5px auto; padding-top: 5px; font-size: 11px;">
                    أمين المخزن: {{ $stockCount->createdBy->name ?? '____' }}
                </div>
            </td>
            <td style="width: 50%; text-align: center; border: none; padding: 10px;">
                <div
                    style="border-top: 1px solid #333; width: 200px; margin: 30px auto 5px auto; padding-top: 5px; font-size: 11px;">
                    المدير المعتمد: {{ $stockCount->approvedBy->name ?? '____' }}
                </div>
            </td>
        </tr>
    </table>
</body>

</html>