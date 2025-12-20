<?php

namespace App\Filament\Traits;

use App\Exports\GenericExport;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;

trait ExportableTable
{
    /**
     * Get the export action for the table header
     */
    protected function getExportAction(): Action
    {
        return Action::make('export')
            ->label('تصدير Excel')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(function () {
                $query = $this->getFilteredTableQuery();
                $data = $query->get();

                $columns = $this->getExportColumns();
                $headings = $this->getExportHeadings();

                $filename = $this->getExportFilename();

                return Excel::download(
                    new GenericExport($data, $columns, $headings),
                    $filename
                );
            });
    }

    /**
     * Get columns to export - override in resource
     */
    protected function getExportColumns(): array
    {
        return [];
    }

    /**
     * Get headings for export - override in resource
     */
    protected function getExportHeadings(): array
    {
        return [];
    }

    /**
     * Get filename for export
     */
    protected function getExportFilename(): string
    {
        return 'export-' . now()->format('Y-m-d-His') . '.xlsx';
    }
}
