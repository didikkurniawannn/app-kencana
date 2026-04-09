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
            else {
                $allowed = false;
            }

            if (!$allowed) {
                abort(403, 'Anda tidak memiliki otoritas untuk mengakses berkas ini.');
            }
        }

        $fullPath = Storage::disk('public')->path($path);
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // Find best password: nip (from related pegawai) or email
        $password = $user->email;
        $pegawai = \App\Models\Pegawai::where('nip', $user->phone_number)->first();
        if ($pegawai) {
            $password = $pegawai->nip;
        }

        // --- CASE 1: PDF (Direct Encryption) ---
        if ($ext === 'pdf') {
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

        // --- CASE 2: IMAGES (Convert to PDF then Encrypt) ---
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
            try {
                $pdf = new Fpdi();
                $ownerPass = bin2hex(random_bytes(8));
                $pdf->SetProtection(['print', 'copy'], $password, $ownerPass, 0, null);
                
                $pdf->AddPage();
                // Get page dimensions to fit image
                $pageWidth = $pdf->getPageWidth() - 20;
                $pageHeight = $pdf->getPageHeight() - 20;
                
                $imgSize = getimagesize($fullPath);
                if ($imgSize) {
                    $pdf->Image($fullPath, 10, 10, $pageWidth, 0, '', '', '', true, 300, '', false, false, 0, 'L');
                }

                $pdfContent = $pdf->Output('protected_image.pdf', 'S');
                $filename = pathinfo(basename($fullPath), PATHINFO_FILENAME) . '.pdf';

                return response($pdfContent, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Image to PDF Conversion Failed: ' . $e->getMessage());
                return response()->download($fullPath);
            }
        }

        // --- CASE 3: OTHER DOCUMENTS (Wrap in Password-Protected ZIP) ---
        // Note: PHP ZipArchive requires libzip >= 1.2.0 for password support
        if (class_exists('ZipArchive')) {
            try {
                $tempPath = tempnam(sys_get_temp_dir(), 'secure_zip');
                $zip = new \ZipArchive();
                
                if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                    $zip->addFile($fullPath, basename($fullPath));
                    
                    // Set password protection
                    if (method_exists($zip, 'setPassword')) {
                        $zip->setPassword($password);
                        $zip->setEncryptionName(basename($fullPath), \ZipArchive::EM_AES_256);
                    }
                    
                    $zip->close();
                    
                    $filename = pathinfo(basename($fullPath), PATHINFO_FILENAME) . '.zip';
                    return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('ZIP Encryption Failed: ' . $e->getMessage());
            }
        }

        // Fallback for cases where encryption fails or libzip is too old
        return response()->download($fullPath);
    }
}
