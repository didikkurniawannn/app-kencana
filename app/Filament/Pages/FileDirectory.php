<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class FileDirectory extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'Bukti SPJ';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.file-directory';

    public $currentPath = 'bukti-realisasi';
    public $directories = [];
    public $files = [];
    public $breadcrumbs = [];

    public function mount()
    {
        $path = request()->query('path');

        if (!$path) {
            $path = $this->currentPath;
        }

        // Clean path and ensure it starts with bukti-realisasi
        $path = trim($path, '/');
        if (!str_starts_with($path, 'bukti-realisasi')) {
            $path = 'bukti-realisasi';
        }

        $this->loadDirectory($path);
    }

    public function loadDirectory($path)
    {
        $this->currentPath = $path;
        $this->directories = Storage::disk('public')->directories($path);
        $this->files = Storage::disk('public')->files($path);
        $this->generateBreadcrumbs($path);
    }

    public function navigate($path)
    {
        $this->loadDirectory($path);
    }

    public function goUp()
    {
        if ($this->currentPath === 'bukti-realisasi') {
            return;
        }

        $parts = explode('/', $this->currentPath);
        array_pop($parts);
        $this->loadDirectory(implode('/', $parts));
    }

    protected function generateBreadcrumbs($path)
    {
        $parts = explode('/', $path);
        $current = '';
        $this->breadcrumbs = [];

        foreach ($parts as $part) {
            $current = $current ? $current . '/' . $part : $part;
            $this->breadcrumbs[] = [
                'name' => Str::title(str_replace('-', ' ', $part)),
                'path' => $current,
            ];
        }
    }

    public function getFileUrl($file)
    {
        return Storage::disk('public')->url($file);
    }

    public function getFileName($path)
    {
        return basename($path);
    }

    public function getDirName($path)
    {
        return basename($path);
    }

    public function download($path)
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }
    }

    public function downloadDirectory($path)
    {
        $zipName = basename($path) . '-' . now()->format('Y-m-d-His') . '.zip';
        $zipPath = storage_path('app/public/' . $zipName);
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $files = Storage::disk('public')->allFiles($path);

            foreach ($files as $file) {
                // Determine the relative path for the file inside the ZIP
                $relativePath = Str::after($file, dirname($path) . '/');
                $zip->addFile(Storage::disk('public')->path($file), $relativePath);
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
