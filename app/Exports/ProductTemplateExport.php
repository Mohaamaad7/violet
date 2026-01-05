<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class ProductTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected Collection $products;
    protected bool $emptyTemplate;

    /**
     * @param Collection|null $products Products to export, null for empty template
     */
    public function __construct(?Collection $products = null)
    {
        $this->products = $products ?? collect();
        $this->emptyTemplate = $products === null || $products->isEmpty();
    }

    public function collection()
    {
        if ($this->emptyTemplate) {
            // Return empty row with example data for reference
            return collect([
                [
                    'id' => '(مثال: 1)',
                    'sku' => '(مثال: SKU-001)',
                    'name' => '(مثال: اسم المنتج)',
                    'category_name' => '(للمعلومات فقط)',
                    'price' => '100.00',
                    'sale_price' => '80.00',
                    'cost_price' => '50.00',
                    'stock' => '100',
                    'low_stock_threshold' => '10',
                    'status' => 'active',
                ]
            ]);
        }

        return $this->products->map(function ($product) {
            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'category_name' => $product->category?->name ?? '-',
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'cost_price' => $product->cost_price,
                'stock' => $product->stock,
                'low_stock_threshold' => $product->low_stock_threshold,
                'status' => $product->status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'المعرف (ID) - لا تعدّل',
            'الكود (SKU) - لا تعدّل',
            'الاسم',
            'التصنيف (للمعلومات)',
            'السعر',
            'سعر التخفيض',
            'سعر التكلفة',
            'المخزون',
            'حد المخزون المنخفض',
            'الحالة (active/inactive/draft)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // RTL support
        $sheet->setRightToLeft(true);

        // Header row styling
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '667eea'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Read-only columns (ID, SKU, Category) - light gray background
        $lastRow = $this->products->count() + 1;
        if ($lastRow > 1) {
            $sheet->getStyle("A2:B{$lastRow}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'f3f4f6'],
                ],
            ]);
            $sheet->getStyle("D2:D{$lastRow}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'f3f4f6'],
                ],
            ]);
        }

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Add data validation for status column
        $validation = $sheet->getCell('J2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"active,inactive,draft"');

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0.00', // Price
            'F' => '#,##0.00', // Sale Price
            'G' => '#,##0.00', // Cost Price
            'H' => '#,##0',    // Stock
            'I' => '#,##0',    // Low Stock Threshold
        ];
    }
}
