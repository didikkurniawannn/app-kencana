<?php

namespace App\Http\Controllers;

use App\Models\Sp2d;
use App\Models\Realisasi;
use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;

class ArchiveLabelController extends Controller
{
    public function printSp2dLabel(Request $request, $id)
    {
        $record = Sp2d::findOrFail($id);
        
        // Security check
        $user = auth()->user();
        if (!$user || ($user->hasRole('admin_instansi') && $record->instansi_id !== $user->instansi_id)) {
            abort(403, 'Unauthorized access to this archive.');
        }

        return $this->generatePdfLabel($record, 'SUMBER DANA (SP2D)');
    }

    public function printRealisasiLabel(Request $request, $id)
    {
        $record = Realisasi::findOrFail($id);

        // Security check
        $user = auth()->user();
        if (!$user || ($user->hasRole('admin_instansi') && $record->instansi_id !== $user->instansi_id)) {
            abort(403, 'Unauthorized access to this archive.');
        }

        return $this->generatePdfLabel($record, 'REALISASI ANGGARAN');
    }

    protected function generatePdfLabel($record, $type)
    {
        // Custom Page Size: 100mm x 60mm (Common Archive Label Size)
        $pageLayout = array(100, 60);
        $pdf = new Fpdi('L', 'mm', $pageLayout, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Kencana Archival System');
        $pdf->SetAuthor('Developed by DIDIK KURNIAWAN');
        $pdf->SetTitle('Label Arsip - ' . $record->nomor_register);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(TRUE, 5);

        // Add a page
        $pdf->AddPage();

        // --- DRAW BORDER ---
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(2, 2, 96, 56);

        // --- HEADER ---
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(0, 7, 'LABEL ARSIP KENCANA', 0, 1, 'C', 1);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(0, 5, $type, 'B', 1, 'C');

        $pdf->Ln(2);

        // --- REGISTER NUMBER (Highlight) ---
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, $record->nomor_register ?: 'BELUM TERIGISTER', 0, 1, 'C');
        
        $pdf->Ln(1);

        // --- CONTENT GRID ---
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(25, 4, 'Instansi', 0, 0);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell(0, 4, ': ' . ($record->instansi->name ?? '-'), 0, 1);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(25, 4, 'Sampul/Berkas', 0, 0);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->MultiCell(0, 4, ': ' . ($record->arsip_sampul ?? '-'), 0, 'L');

        $pdf->Ln(1);

        // --- STORAGE LOCATION ---
        $pdf->SetFillColor(255, 255, 200); // Light Yellow
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell(0, 4, ' LOKASI PENYIMPANAN FISIK', 0, 1, 'L', 1);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(15, 4, ' Ruang', 0, 0);
        $pdf->Cell(35, 4, ': ' . ($record->arsip_ruang ?? '-'), 0, 0);
        $pdf->Cell(15, 4, ' Box', 0, 0);
        $pdf->Cell(0, 4, ': ' . ($record->arsip_box ?? '-'), 0, 1);

        $pdf->Cell(15, 4, ' Rak', 0, 0);
        $pdf->Cell(35, 4, ': ' . ($record->arsip_rak_type . ' ' . ($record->arsip_rak ?? '-')), 0, 0);
        $pdf->Cell(15, 4, ' Cabinet', 0, 0);
        $pdf->Cell(0, 4, ': ' . ($record->arsip_filing_cabinet ?? '-'), 0, 1);

        // --- FOOTER ---
        $pdf->SetY(-8);
        $pdf->SetFont('helvetica', 'I', 5);
        $pdf->Cell(0, 4, 'Dicetak pada: ' . date('d/m/Y H:i') . ' | Developed by DIDIK KURNIAWAN — KENCANA', 0, 0, 'R');

        // Output PDF
        return response($pdf->Output('label_'.$record->nomor_register.'.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="label_'.$record->nomor_register.'.pdf"'
        ]);
    }

    public function printArchiveRegister(\Illuminate\Http\Request $request)
    {
        $idsStr = $request->query('ids', '');
        if (empty($idsStr)) {
            abort(404, 'Tidak ada data yang dipilih untuk laporan.');
        }

        $ids = explode(',', $idsStr);
        $records = Realisasi::whereIn('id', $ids)
            ->with(['detailBelanja.rekening.subKegiatan.kegiatan.program', 'instansi', 'sp2d'])
            ->orderBy('nomor_register', 'asc')
            ->get();

        $user = auth()->user();
        if (!$user) abort(403);

        return $this->generatePdfRegisterTable($records);
    }

    protected function generatePdfRegisterTable($records)
    {
        $pdf = new Fpdi('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage();

        // --- HEADER ---
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'BUKU REGISTRASI ARSIP TERPADU', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'Sistem Informasi Kearsipan KENCANA', 0, 1, 'C');
        $pdf->Ln(5);

        // --- TABLE HEADER ---
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(230, 230, 230);
        
        $pdf->Cell(10, 10, 'No', 1, 0, 'C', 1);
        $pdf->Cell(35, 10, 'No. Register', 1, 0, 'C', 1);
        $pdf->Cell(25, 10, 'Tanggal', 1, 0, 'C', 1);
        $pdf->Cell(80, 10, 'Program / Kegiatan / Sampul', 1, 0, 'C', 1);
        $pdf->Cell(30, 10, 'Sumber Dana', 1, 0, 'C', 1);
        $pdf->Cell(45, 10, 'Lokasi (Ruang/Box/Rak)', 1, 0, 'C', 1);
        $pdf->Cell(50, 10, 'Klasifikasi', 1, 1, 'C', 1);

        // --- TABLE BODY ---
        $pdf->SetFont('helvetica', '', 7);
        $i = 1;
        foreach ($records as $row) {
            $h = 8; // Row height
            $pdf->Cell(10, $h, $i++, 1, 0, 'C');
            $pdf->Cell(35, $h, $row->nomor_register, 1, 0, 'C');
            $pdf->Cell(25, $h, $row->tanggal_realisasi ? $row->tanggal_realisasi->format('d/m/Y') : '-', 1, 0, 'C');
            
            $progKeg = ($row->detailBelanja?->rekening?->subKegiatan?->kegiatan?->nama_kegiatan ?? '-') . "\n" . ($row->arsip_sampul ?? '-');
            $pdf->MultiCell(80, $h, $progKeg, 1, 'L', 0, 0);

            $pdf->Cell(30, $h, $row->sp2d?->sumber_dana ?? '-', 1, 0, 'C');
            
            $lokasi = ($row->arsip_ruang ?? '-') . " / " . ($row->arsip_box ?? '-') . " / " . ($row->arsip_rak_type ?? '-');
            $pdf->Cell(45, $h, $lokasi, 1, 0, 'C');
            
            $pdf->Cell(50, $h, $row->kode_klasifikasi ?? '-', 1, 1, 'C');
        }

        // --- FOOTER ---
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->Cell(0, 5, 'Dicetak pada: ' . date('d/m/Y H:i') . ' | Oleh: ' . auth()->user()->name, 0, 0, 'R');

        return response($pdf->Output('daftar_registrasi_arsip.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="daftar_registrasi_arsip.pdf"'
        ]);
    }
}
