<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Tcpdf\Fpdi;

class SecureFileController extends Controller
{
    public function download(Request $request)
    {
        $path = $request->query('path');
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($path);
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            return response()->download($fullPath);
        }

        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Find best password column: nip, then email
        $password = $user->email;
        if (!empty($user->nip)) {
            $password = $user->nip;
        }

        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($fullPath);

            $ownerPass = bin2hex(random_bytes(8));
            $pdf->SetProtection(['print', 'copy'], $password, $ownerPass, 0, null);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }

            $pdfContent = $pdf->Output('protected.pdf', 'S');

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . basename($fullPath) . '"'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF Encryption Failed: ' . $e->getMessage());
            return response()->download($fullPath);
        }
    }
}
