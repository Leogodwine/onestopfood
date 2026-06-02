<?php

namespace App\Services;

use App\Support\DompdfBootstrap;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfService
{
    public function download(string $view, array $data, string $fileName): Response
    {
        DompdfBootstrap::register();

        $html = view($view, $data)->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }
}
