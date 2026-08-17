<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use XLSXWriter;

final class FileService
{
    public function createFilename(Form $form, string $extension, string $suffix = ''): string
    {
        $user = Auth::user();
        $directory = '/tmp/'.$user->id.'/';
        Storage::makeDirectory($directory);

        // Κάνε εκκαθάριση παλιών αρχείων
        foreach (Storage::files($directory) as $file) {
            Storage::delete($file);
        }

        return Storage::path($directory.Str::limit(Str::slug($form->title, '_'), 15).'-'.now()->timestamp.$suffix.$extension);
    }

    public function createCSV(Form $form, array $dataTable, ?array $dataTableColumns = null, string $suffix = ''): string
    {
        $fname = $this->createFilename($form, '.csv', $suffix);

        $fd = fopen($fname, 'w');
        if ($fd === false) {
            throw new \Exception('Failed to open temporary file');
        }

        if ($dataTableColumns) {
            fputcsv($fd, $dataTableColumns, escape: '\\');
        }

        foreach ($dataTable as $row) {
            fputcsv($fd, $row, escape: '\\');
        }

        fclose($fd);

        return $fname;
    }

    public function createXLSX(Form $form, array $dataTable, ?array $dataTableColumns = null, string $suffix = ''): string
    {
        $fname = $this->createFilename($form, '.xlsx', $suffix);

        $writer = new XLSXWriter;

        if ($dataTableColumns) {
            $data = array_merge([$dataTableColumns], $dataTable);
        } else {
            $data = $dataTable;
        }

        $writer->writeSheet($data);
        $writer->writeToFile($fname);

        return $fname;
    }
}
