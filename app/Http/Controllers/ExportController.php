<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\DataExport;
use App\Services\ExcelExport;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;
use ZipArchive;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'format' => ['nullable', 'in:csv,xlsx'],
        ]);

        return view('exports.index', [
            'clients' => Client::orderBy('company_name')->get(['id', 'company_name']),
            'clientId' => $data['client_id'] ?? null,
            'types' => DataExport::TYPES,
            'format' => $data['format'] ?? 'xlsx',
        ]);
    }

    public function download(Request $request, string $type, DataExport $export, ExcelExport $excel)
    {
        abort_unless($type === 'all' || array_key_exists($type, DataExport::TYPES), 404);
        $data = $request->validate([
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'format' => ['nullable', 'in:csv,xlsx'],
        ]);
        $clientId = isset($data['client_id']) ? (int) $data['client_id'] : null;
        $suffix = ($clientId ? '-klijent-'.$clientId : '').'-'.now()->format('Y-m-d-His');
        $headers = ['Cache-Control' => 'private, no-store, max-age=0'];

        if (($data['format'] ?? 'csv') === 'xlsx') {
            abort_unless(class_exists(ZipArchive::class), 503, 'Excel izvoz zahteva PHP zip ekstenziju.');
            $path = tempnam(sys_get_temp_dir(), 'poslovi-excel-');
            if ($path === false) {
                throw new RuntimeException('Privremeni fajl nije moguće kreirati.');
            }
            try {
                $excel->write($path, $type === 'all' ? array_keys(DataExport::TYPES) : [$type], $clientId, $export);

                return response()->download($path, ($type === 'all' ? 'svi-podaci' : $type).$suffix.'.xlsx', $headers + [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])->deleteFileAfterSend(true);
            } catch (Throwable $exception) {
                @unlink($path);
                throw $exception;
            }
        }

        if ($type !== 'all') {
            return response()->streamDownload(function () use ($export, $type, $clientId) {
                $stream = fopen('php://output', 'wb');
                try {
                    $export->writeCsv($stream, $type, $clientId);
                } finally {
                    fclose($stream);
                }
            }, $type.$suffix.'.csv', $headers + ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        abort_unless(class_exists(ZipArchive::class), 503, 'ZIP izvoz zahteva PHP zip ekstenziju. Pojedinačni CSV izvoz je dostupan.');
        $path = tempnam(sys_get_temp_dir(), 'poslovi-export-');
        if ($path === false) {
            throw new RuntimeException('Privremeni fajl nije moguće kreirati.');
        }

        $zip = new ZipArchive;
        $files = [];
        $opened = false;
        try {
            if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('ZIP arhivu nije moguće kreirati.');
            }
            $opened = true;
            foreach (array_keys(DataExport::TYPES) as $dataset) {
                $csvPath = tempnam(sys_get_temp_dir(), 'poslovi-csv-');
                if ($csvPath === false) {
                    throw new RuntimeException('CSV fajl nije moguće kreirati.');
                }
                $files[] = $csvPath;
                $stream = fopen($csvPath, 'wb');
                try {
                    $export->writeCsv($stream, $dataset, $clientId);
                } finally {
                    fclose($stream);
                }
                if (! $zip->addFile($csvPath, $dataset.'.csv')) {
                    throw new RuntimeException('CSV fajl nije moguće dodati u arhivu.');
                }
            }
            $closed = $zip->close();
            $opened = false;
            if (! $closed) {
                throw new RuntimeException('ZIP arhivu nije moguće sačuvati.');
            }

            return response()->download($path, 'svi-podaci'.$suffix.'.zip', $headers + ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
        } catch (Throwable $exception) {
            if ($opened) {
                $zip->close();
            }
            @unlink($path);
            throw $exception;
        } finally {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}
