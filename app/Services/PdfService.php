<?php

namespace App\Services;

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class PdfService
{
    protected Mpdf $mpdf;

    /**
     * Create a new PDF instance with Arabic support
     */
    public function create(string $orientation = 'P', string $format = 'A4'): self
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'orientation' => $orientation,
            'default_font_size' => 12,
            'default_font' => 'dejavusans',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 25, // Increased for footer
            'margin_header' => 5,
            'margin_footer' => 10,
            'tempDir' => storage_path('app/temp'),
            'fontDir' => array_merge($fontDirs, [
                public_path('fonts'),
            ]),
            'fontdata' => $fontData + [
                'cairo' => [
                    'R' => 'Cairo-Regular.ttf',
                    'B' => 'Cairo-Bold.ttf',
                ],
            ],
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        // Set RTL direction for Arabic
        $this->mpdf->SetDirectionality('rtl');

        return $this;
    }

    /**
     * Set document title
     */
    public function title(string $title): self
    {
        $this->mpdf->SetTitle($title);
        return $this;
    }

    /**
     * Set fixed footer on all pages
     */
    public function footer(string $html): self
    {
        $this->mpdf->SetHTMLFooter($html);
        return $this;
    }

    /**
     * Write HTML content
     */
    public function html(string $html): self
    {
        $this->mpdf->WriteHTML($html);
        return $this;
    }

    /**
     * Render a Blade view
     */
    public function view(string $view, array $data = []): self
    {
        $html = view($view, $data)->render();
        return $this->html($html);
    }

    /**
     * Output to browser (inline)
     */
    public function stream(string $filename = 'document.pdf'): void
    {
        $this->mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
    }

    /**
     * Download the PDF
     */
    public function download(string $filename = 'document.pdf'): void
    {
        $this->mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    }

    /**
     * Save to file
     */
    public function save(string $path): self
    {
        $this->mpdf->Output($path, \Mpdf\Output\Destination::FILE);
        return $this;
    }

    /**
     * Get raw PDF content
     */
    public function output(): string
    {
        return $this->mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }
}
