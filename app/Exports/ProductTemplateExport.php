<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Illuminate\Support\Collection;

class ProductTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected Collection $products;
    protected string $mode; // 'create' or 'update'

    /**
     * @param string $mode 'create' for new products, 'update' for existing
     * @param Collection|null $products Products to export (for update mode)
     */
    public function __construct(string $mode = 'update', ?Collection $products = null)
    {
        $this->mode = $mode;

        if ($mode === 'update') {
            // For update mode, use provided products or get all
            $this->products = $products ?? Product::all();
        } else {
            // For create mode, empty collection
            $this->products = collect();
        }
    }

    public function collection()
    {
        if ($this->mode === 'create') {
            // Empty template with example row
            return collect([
                $this->getExampleRow(),
            ]);
        }

        // Update mode - export products
        return $this->products->map(function ($product) {
            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'category_name' => $product->category?->name ?? '-',
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'cost_price' => $product->cost_price,
                'status' => $product->status,
            ];
        });
    }

    protected function getExampleRow(): array
    {
        if ($this->mode === 'create') {
            return [
                'name' => '(مثال: اسم المنتج)',
                'sku' => '(اختياري: SKU-001)',
                'category_id' => '(مثال: 1)',
                'price' => '100.00',
                'sale_price' => '80.00',
                'cost_price' => '50.00',
                'stock' => '100',
                'low_stock_threshold' => '10',
                'status' => 'active',
                'description' => '(اختياري: وصف المنتج)',
            ];
        }

        return [];
    }

    public function headings(): array
    {
        if ($this->mode === 'create') {
            return [
                'الاسم *',
                'الكود',
                'رقم التصنيف',
                'السعر *',
                'سعر العرض',
                'التكلفة',
                'المخزون *',
                'حد التنبيه',
                'الحالة *',
                'الوصف',
            ];
        }

        // Update mode headings - clean and simple
        return [
            '#',           // ID - short
            'الكود',       // SKU
            'الاسم',
            'التصنيف',
            'السعر',
            'سعر العرض',
            'التكلفة',
            'الحالة',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // RTL support
        $sheet->setRightToLeft(true);

        $columnCount = $this->mode === 'create' ? 'J' : 'H';

        // Header row styling
        $sheet->getStyle("A1:{$columnCount}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $this->mode === 'create' ? '10b981' : '667eea'],
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

        // For update mode, mark read-only columns
        if ($this->mode === 'update') {
            $lastRow = $this->products->count() + 1;
            if ($lastRow > 1) {
                // ID and SKU columns - gray (read-only)
                $sheet->getStyle("A2:B{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'e5e7eb'],
                    ],
                ]);
                // Category column - gray (info only)
                $sheet->getStyle("D2:D{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'e5e7eb'],
                    ],
                ]);
            }

            // Status dropdown for update mode (column H)
            $this->addStatusValidation($sheet, 'H');
        } else {
            // Status dropdown for create mode (column I)
            $this->addStatusValidation($sheet, 'I');
        }

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    protected function addStatusValidation(Worksheet $sheet, string $column): void
    {
        $validation = $sheet->getCell("{$column}2")->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"active,inactive,draft"');
    }

    public function columnFormats(): array
    {
        if ($this->mode === 'create') {
            return [
                'D' => '#,##0.00', // Price
                'E' => '#,##0.00', // Sale Price
                'F' => '#,##0.00', // Cost Price
                'G' => '#,##0',    // Stock
                'H' => '#,##0',    // Low Stock Threshold
            ];
        }

        // Update mode
        return [
            'E' => '#,##0.00', // Price
            'F' => '#,##0.00', // Sale Price
            'G' => '#,##0.00', // Cost Price
        ];
    }

    /**
     * Get the export mode
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Get count of products being exported
     */
    public function getCount(): int
    {
        return $this->products->count();
    }
}
