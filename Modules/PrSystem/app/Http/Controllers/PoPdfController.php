<?php

namespace Modules\PrSystem\Http\Controllers;

use Modules\PrSystem\Models\PurchaseOrder;

class PoPdfController extends Controller
{
    public function export(PurchaseOrder $po)
    {
        $po->load([
            'items.prItem.product',
            'items.prItem.job',
            'purchaseRequest.department.site',
            'purchaseRequest.subDepartment'
        ]);

        $viewData = ['po' => $po];

        // Download PDF with safe filename
        $safeFilename = str_replace('/', '_', $po->po_number);
        $fileName = "PO_{$safeFilename}.pdf";

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('prsystem::pdf.po_export', $viewData);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download($fileName);
        }

        if (class_exists(\Dompdf\Dompdf::class)) {
            $html = view('prsystem::pdf.po_export', $viewData)->render();
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        }

        abort(500, 'PDF engine is not installed. Please install dompdf/dompdf or barryvdh/laravel-dompdf.');
    }
}
