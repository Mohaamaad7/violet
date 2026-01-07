<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportValidator
{
    protected string $mode; // 'create' or 'update'
    protected array $errors = [];
    protected array $validRows = [];
    protected int $totalRows = 0;

    public function __construct(string $mode = 'update')
    {
        $this->mode = $mode;
    }

    /**
     * Validate an Excel file before import
     * 
     * @param string $filePath Absolute path to the Excel file
     * @return array ['valid' => bool, 'errors' => array, 'validRows' => array, 'totalRows' => int]
     */
    public function validate(string $filePath): array
    {
        $this->errors = [];
        $this->validRows = [];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            $dataRows = array_slice($rows, 1);
            $this->totalRows = count($dataRows);

            foreach ($dataRows as $index => $row) {
                $rowNumber = $index + 2; // Excel row number (1-indexed, skip header)
                $rowErrors = $this->validateRow($row, $rowNumber);

                if (empty($rowErrors)) {
                    $this->validRows[] = [
                        'row' => $rowNumber,
                        'data' => $this->parseRow($row),
                    ];
                } else {
                    foreach ($rowErrors as $error) {
                        $this->errors[] = $error;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = [
                'row' => 0,
                'field' => 'file',
                'message' => 'خطأ في قراءة الملف: ' . $e->getMessage(),
            ];
        }

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'validRows' => $this->validRows,
            'totalRows' => $this->totalRows,
            'validCount' => count($this->validRows),
        ];
    }

    /**
     * Validate a single row
     */
    protected function validateRow(array $row, int $rowNumber): array
    {
        $errors = [];

        // Skip empty rows
        if ($this->isEmptyRow($row)) {
            return [];
        }

        // Skip example rows
        if ($this->isExampleRow($row)) {
            return [];
        }

        if ($this->mode === 'create') {
            $errors = array_merge($errors, $this->validateCreateRow($row, $rowNumber));
        } else {
            $errors = array_merge($errors, $this->validateUpdateRow($row, $rowNumber));
        }

        return $errors;
    }

    /**
     * Validate row for create mode
     */
    protected function validateCreateRow(array $row, int $rowNumber): array
    {
        $errors = [];

        // name (column 0) - required
        if (empty($row[0]) || $this->isPlaceholder($row[0])) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'الاسم',
                'message' => 'الاسم مطلوب',
            ];
        }

        // price (column 3) - required, numeric, positive
        $price = $this->cleanNumber($row[3] ?? '');
        if (empty($price) || !is_numeric($price) || $price <= 0) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'السعر',
                'message' => 'السعر مطلوب ويجب أن يكون رقم موجب',
            ];
        }

        // stock (column 6) - required, integer, >= 0
        $stock = $this->cleanNumber($row[6] ?? '');
        if ($stock === '' || !is_numeric($stock) || $stock < 0) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'المخزون',
                'message' => 'المخزون مطلوب ويجب أن يكون رقم صحيح >= 0',
            ];
        }

        // status (column 8) - required, valid value
        $status = strtolower(trim($row[8] ?? ''));
        if (empty($status) || !in_array($status, ['active', 'inactive', 'draft'])) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'الحالة',
                'message' => 'الحالة مطلوبة (active/inactive/draft)',
            ];
        }

        // category_id (column 2) - optional, but if provided must exist
        $categoryId = $row[2] ?? '';
        if (!empty($categoryId) && is_numeric($categoryId)) {
            if (!Category::find((int) $categoryId)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'التصنيف',
                    'message' => "التصنيف برقم {$categoryId} غير موجود",
                ];
            }
        }

        return $errors;
    }

    /**
     * Validate row for update mode
     */
    protected function validateUpdateRow(array $row, int $rowNumber): array
    {
        $errors = [];

        // id (column 0) - required, must exist
        $id = $row[0] ?? '';
        if (empty($id) || !is_numeric($id)) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'المعرف',
                'message' => 'المعرف (ID) مطلوب',
            ];
            return $errors; // Can't continue without ID
        }

        $product = Product::find((int) $id);
        if (!$product) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'المعرف',
                'message' => "المنتج برقم {$id} غير موجود",
            ];
            return $errors;
        }

        // price (column 4) - optional, but if provided must be positive
        $price = $row[4] ?? '';
        if (!empty($price) && !$this->isPlaceholder($price)) {
            $priceValue = $this->cleanNumber($price);
            if (!is_numeric($priceValue) || $priceValue <= 0) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'السعر',
                    'message' => 'السعر يجب أن يكون رقم موجب',
                ];
            }
        }

        // status (column 7) - optional, but if provided must be valid
        $status = $row[7] ?? '';
        if (!empty($status) && !$this->isPlaceholder($status)) {
            if (!in_array(strtolower(trim($status)), ['active', 'inactive', 'draft'])) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'الحالة',
                    'message' => 'الحالة يجب أن تكون (active/inactive/draft)',
                ];
            }
        }

        return $errors;
    }

    /**
     * Parse row data for import
     */
    protected function parseRow(array $row): array
    {
        if ($this->mode === 'create') {
            return [
                'name' => trim($row[0] ?? ''),
                'sku' => trim($row[1] ?? '') ?: null,
                'category_id' => !empty($row[2]) && is_numeric($row[2]) ? (int) $row[2] : null,
                'price' => $this->cleanNumber($row[3] ?? 0),
                'sale_price' => !empty($row[4]) ? $this->cleanNumber($row[4]) : null,
                'cost_price' => !empty($row[5]) ? $this->cleanNumber($row[5]) : null,
                'stock' => (int) $this->cleanNumber($row[6] ?? 0),
                'low_stock_threshold' => !empty($row[7]) ? (int) $this->cleanNumber($row[7]) : 10,
                'status' => strtolower(trim($row[8] ?? 'draft')),
                'description' => trim($row[9] ?? ''),
            ];
        }

        // Update mode
        return [
            'id' => (int) $row[0],
            'name' => trim($row[2] ?? ''),
            'price' => !empty($row[4]) ? $this->cleanNumber($row[4]) : null,
            'sale_price' => isset($row[5]) ? ($row[5] === '' || $row[5] === null ? null : $this->cleanNumber($row[5])) : null,
            'cost_price' => !empty($row[6]) ? $this->cleanNumber($row[6]) : null,
            'status' => !empty($row[7]) ? strtolower(trim($row[7])) : null,
        ];
    }

    /**
     * Check if row is empty
     */
    protected function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, fn($cell) => !empty(trim((string) $cell))));
    }

    /**
     * Check if row is an example row
     */
    protected function isExampleRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (is_string($cell) && (str_contains($cell, 'مثال') || str_contains($cell, 'اختياري'))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if value is a placeholder
     */
    protected function isPlaceholder(mixed $value): bool
    {
        if (!is_string($value))
            return false;
        return str_contains($value, 'مثال') || str_contains($value, 'للمعلومات') || str_contains($value, 'اختياري');
    }

    /**
     * Clean number string
     */
    protected function cleanNumber(mixed $value): float
    {
        if (!is_string($value))
            return (float) $value;
        $cleaned = str_replace([',', ' ', '٫'], ['.', '', '.'], $value);
        return (float) $cleaned;
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get valid rows
     */
    public function getValidRows(): array
    {
        return $this->validRows;
    }
}
