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

        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // --- SECURITY VALIDATION BY INSTANSI ---
        if (!$user->hasRole('super_admin')) {
            $allowed = false;

            // Check if it's a Realisasi file
            if (str_starts_with($path, 'bukti-realisasi/')) {
                $realisasi = \App\Models\Realisasi::where(function($query) use ($path) {
                        $query->whereJsonContains('bukti_file', $path)
                              ->orWhere('bukti_file', 'like', "%$path%");
                    })->first();

                if ($realisasi && $user->instansi->contains('id', $realisasi->instansi_id)) {
                    $allowed = true;
                }
            } 
            // Check if it's a Pegawai file
            else if (str_starts_with($path, 'pegawai/')) {
                $pegawai = \App\Models\Pegawai::where('file_perjanjian_kinerja', $path)->first();
                if ($pegawai && $user->instansi->contains('id', $pegawai->instansi_id)) {
                    $allowed = true;
                }
            }
            // Add other path checks if necessary
            else {
                // If path is unknown, we might want to deny by default or 
                // check if it's in a directory that implies instansi context if available
                // For now, let's be conservative.
                $allowed = false;
            }

            if (!$allowed) {
                abort(403, 'You do not have permission to access this file.');
            }
        }

        $fullPath = Storage::disk('public')->path($path);
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($ext !== 'pdf') {
            return response()->download($fullPath);
        }

        // Find best password column: nip (from related pegawai if linked) or email
        $password = $user->email;
        // If user is a pegawai, use their NIP for better security
        $pegawai = \App\Models\Pegawai::where('nip', $user->phone_number)->first(); // Assuming phone_number or similar might be NIP or linked
        if ($pegawai) {
            $password = $pegawai->nip;
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
