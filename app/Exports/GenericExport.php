<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class GenericExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected Collection $data;
    protected array $headings;
    protected array $columns;

    public function __construct(Collection $data, array $columns = [], array $headings = [])
    {
        $this->data = $data;
        $this->columns = $columns;
        $this->headings = $headings ?: $columns;
    }

    public function collection()
    {
        if (empty($this->columns)) {
            return $this->data;
        }

        return $this->data->map(function ($row) {
            $exportRow = [];
            foreach ($this->columns as $column) {
                $exportRow[] = data_get($row, $column, '-');
            }
            return $exportRow;
        });
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '667eea'],
            ],
        ]);

        // RTL support
        $sheet->setRightToLeft(true);

        return [];
    }
}
