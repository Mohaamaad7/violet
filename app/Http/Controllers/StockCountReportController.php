<?php

namespace App\Http\Controllers;

use App\Models\StockCount;
use App\Services\PdfService;
use App\Services\StockCountService;

class StockCountReportController extends Controller
{
    public function __construct(
        protected PdfService $pdfService,
        protected StockCountService $stockCountService
    ) {
    }

    /**
     * Print count sheet (before/during counting)
     */
    public function countSheet(StockCount $stockCount)
    {
        $data = $this->stockCountService->getCountSheetData($stockCount->id);

        // Fixed footer for all pages
        $footerHtml = '<div style="text-align: center; font-size: 9px; color: #666; border-top: 1px solid #ccc; padding-top: 5px;">Reyada E-commerce System - ' . config('app.name') . '</div>';

        $pdf = $this->pdfService
            ->create('P', 'A4')
            ->title("ورقة جرد - {$stockCount->code}")
            ->footer($footerHtml);

        $pdf->view('exports.stock-count-sheet', [
            'stockCount' => $stockCount->load(['warehouse', 'createdBy']),
            'items' => $data['items'],
        ]);

        return response(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"count-sheet-{$stockCount->code}.pdf\"",
            ]
        );
    }

    /**
     * Print count results report (after completion/approval)
     */
    public function results(StockCount $stockCount)
    {
        $data = $this->stockCountService->getCountReport($stockCount->id);

        // Get all items from stock_count
        $allItems = $data['stock_count']->items;

        // Calculate summary for template
        $summary = [
            'matched' => $allItems->where('difference', 0)->count(),
            'shortage' => $allItems->where('difference', '<', 0)->count(),
            'excess' => $allItems->where('difference', '>', 0)->count(),
            'total_value' => abs($allItems->sum('difference_value') ?? 0),
        ];

        // Fixed footer for all pages
        $footerHtml = '<div style="text-align: center; font-size: 9px; color: #666; border-top: 1px solid #ccc; padding-top: 5px;">Reyada E-commerce System - ' . config('app.name') . '</div>';

        $pdf = $this->pdfService
            ->create('L', 'A4')
            ->title("تقرير الجرد - {$stockCount->code}")
            ->footer($footerHtml);

        $pdf->view('exports.stock-count-results', [
            'stockCount' => $data['stock_count'],
            'items' => $allItems,
            'summary' => $summary,
        ]);

        return response(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"count-results-{$stockCount->code}.pdf\"",
            ]
        );
    }

    /**
     * Print shortage report (items with negative difference)
     */
    public function shortage(StockCount $stockCount)
    {
        $data = $this->stockCountService->getCountReport($stockCount->id);

        // Get shortage items only
        $shortageItems = $data['stock_count']->items->where('difference', '<', 0);

        // Calculate summary
        $summary = [
            'total_items' => $shortageItems->count(),
            'total_qty' => abs($shortageItems->sum('difference')),
            'total_value' => abs($shortageItems->sum('difference_value') ?? 0),
        ];

        $footerHtml = '<div style="text-align: center; font-size: 9px; color: #666; border-top: 1px solid #ccc; padding-top: 5px;">Reyada E-commerce System - ' . config('app.name') . '</div>';

        $pdf = $this->pdfService
            ->create('L', 'A4')
            ->title("تقرير العجز - {$stockCount->code}")
            ->footer($footerHtml);

        $pdf->view('exports.stock-count-variance', [
            'stockCount' => $data['stock_count'],
            'items' => $shortageItems,
            'summary' => $summary,
            'reportType' => 'shortage',
            'reportTitle' => 'تقرير العجز',
        ]);

        return response(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"shortage-{$stockCount->code}.pdf\"",
            ]
        );
    }

    /**
     * Print excess report (items with positive difference)
     */
    public function excess(StockCount $stockCount)
    {
        $data = $this->stockCountService->getCountReport($stockCount->id);

        // Get excess items only
        $excessItems = $data['stock_count']->items->where('difference', '>', 0);

        // Calculate summary
        $summary = [
            'total_items' => $excessItems->count(),
            'total_qty' => $excessItems->sum('difference'),
            'total_value' => abs($excessItems->sum('difference_value') ?? 0),
        ];

        $footerHtml = '<div style="text-align: center; font-size: 9px; color: #666; border-top: 1px solid #ccc; padding-top: 5px;">Reyada E-commerce System - ' . config('app.name') . '</div>';

        $pdf = $this->pdfService
            ->create('L', 'A4')
            ->title("تقرير الزيادة - {$stockCount->code}")
            ->footer($footerHtml);

        $pdf->view('exports.stock-count-variance', [
            'stockCount' => $data['stock_count'],
            'items' => $excessItems,
            'summary' => $summary,
            'reportType' => 'excess',
            'reportTitle' => 'تقرير الزيادة',
        ]);

        return response(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"excess-{$stockCount->code}.pdf\"",
            ]
        );
    }
}
