<?php

namespace App\Filament\Resources\RealisasiResource\Pages;

use App\Filament\Resources\RealisasiResource;
use App\Models\RealisasiDetail;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRealisasi extends EditRecord
{
    protected static string $resource = RealisasiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load relationships and hierarchy for hydration
        $record = $this->record;
        $detailBelanja = $record->detailBelanja;

        if ($detailBelanja) {
            $rekening = $detailBelanja->rekening;
            $subKegiatan = $rekening ? $rekening->subKegiatan : null;

            $data['sub_kegiatan_id'] = $subKegiatan ? $subKegiatan->id : null;
            $data['rekening_id'] = $rekening ? $rekening->id : null;

            // Set placeholders
            $data['harga_satuan'] = (float) $detailBelanja->harga;
            $data['pagu_awal'] = 'Rp ' . number_format((float) $detailBelanja->pagu, 0, ',', '.');
            $data['sisa_pagu_display'] = 'Rp ' . number_format((float) $detailBelanja->sisa_pagu, 0, ',', '.');
            $data['kuefisien_awal'] = number_format((float) $detailBelanja->kuefisien, 2, ',', '.') . ' ' . $detailBelanja->satuan;
            $data['sisa_kuefisien_display'] = number_format((float) $detailBelanja->sisa_kuefisien, 2, ',', '.') . ' ' . $detailBelanja->satuan;
        }

        // Hydrate SP2D info
        if (isset($data['sp2d_id'])) {
            $sp2d = \App\Models\Sp2d::find($data['sp2d_id']);
            if ($sp2d) {
                $tanggal = \Carbon\Carbon::parse($sp2d->tanggal_sp2d);
                $data['sp2d_info'] = "Sumber: {$sp2d->sumber_dana} | Tgl: {$tanggal->format('d/m/Y')} | Sisa: Rp " . number_format((float) $sp2d->sisa_jumlah, 0, ',', '.');
            }
        }

        // Load dynamic fields from realisasi_details
        $details = RealisasiDetail::where('realisasi_id', $this->record->id)->get();

        $dynamicFields = [];
        foreach ($details as $detail) {
            $dynamicFields[$detail->field_name] = $detail->field_value;
        }

        $data['dynamic_fields'] = $dynamicFields;

        return $data;
    }

    protected function afterSave(): void
    {
        // Update dynamic fields
        $dynamicFields = $this->data['dynamic_fields'] ?? [];

        // Delete existing and recreate
        RealisasiDetail::where('realisasi_id', $this->record->id)->delete();

        foreach ($dynamicFields as $fieldName => $fieldValue) {
            if ($fieldValue !== null && $fieldValue !== '') {
                RealisasiDetail::create([
                    'realisasi_id' => $this->record->id,
                    'field_name' => $fieldName,
                    'field_value' => is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue,
                ]);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
