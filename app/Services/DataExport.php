<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkEntry;
use Generator;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class DataExport
{
    public const TYPES = [
        'clients' => 'Klijenti',
        'projects' => 'Projekti',
        'tasks' => 'Taskovi / stavke posla',
        'invoices' => 'Fakture i uplate',
        'expenses' => 'Troškovi',
        'users' => 'Korisnički nalozi',
    ];

    public function query(string $type, ?int $clientId): Builder
    {
        $query = match ($type) {
            'clients' => Client::query(),
            'projects' => Project::with('client'),
            'tasks' => WorkEntry::with('project.client'),
            'invoices' => Payment::with('client', 'project')->where('type', 'payment'),
            'expenses' => Payment::with('client', 'project')->where('type', 'expense'),
            'users' => User::with('client'),
        };

        if ($clientId !== null) {
            match ($type) {
                'clients' => $query->whereKey($clientId),
                'tasks' => $query->whereHas('project', fn (Builder $projects) => $projects->where('client_id', $clientId)),
                default => $query->where('client_id', $clientId),
            };
        }

        return $query->orderBy('id');
    }

    public function columns(string $type): array
    {
        $columns = match ($type) {
            'clients' => [
                'ID' => 'id', 'Firma' => 'company_name', 'Kontakt' => 'contact_name',
                'Email' => 'email', 'Telefon' => 'phone', 'Sajt' => 'website',
                'PIB' => 'tax_number', 'Matični broj' => 'registration_number', 'Adresa' => 'address',
                'Satnica' => 'default_hourly_rate', 'Valuta' => 'currency',
                'Napomene' => 'notes', 'Aktivan' => 'is_active',
            ],
            'projects' => [
                'ID' => 'id', 'Klijent ID' => 'client_id', 'Klijent' => 'client.company_name',
                'Naziv' => 'name', 'Status' => 'status', 'Satnica' => 'hourly_rate',
                'Valuta' => 'client.currency', 'Opis' => 'description',
                'Početak' => 'started_at', 'Završetak' => 'finished_at',
            ],
            'tasks' => [
                'ID' => 'id', 'Projekat ID' => 'project_id', 'Projekat' => 'project.name',
                'Klijent ID' => 'project.client_id', 'Klijent' => 'project.client.company_name',
                'Faktura/uplata ID' => 'payment_id', 'Redosled' => 'sort_order',
                'Datum rada' => 'worked_on', 'Naslov' => 'title', 'Opis' => 'description',
                'Sati' => 'hours', 'Satnica' => 'hourly_rate', 'Fiksni iznos' => 'fixed_amount',
                'Iznos' => 'amount', 'Valuta' => 'project.client.currency', 'Status' => 'status',
                'Vidljivo klijentu' => 'visible_to_client', 'Fakturisano' => 'invoiced_at',
                'Plaćeno' => 'paid_at', 'Interna napomena' => 'internal_note',
            ],
            'invoices', 'expenses' => [
                'ID' => 'id', 'Klijent ID' => 'client_id', 'Klijent' => 'client.company_name',
                'Projekat ID' => 'project_id', 'Projekat' => 'project.name', 'Datum' => 'paid_on',
                'Iznos' => 'amount', 'Valuta' => 'client.currency', 'Tip' => 'type',
                'Status' => 'status', 'Način plaćanja' => 'method', 'Referenca' => 'reference',
                'Napomena' => 'note', 'Vidljivo klijentu' => 'visible_to_client',
            ],
            'users' => [
                'ID' => 'id', 'Klijent ID' => 'client_id', 'Klijent' => 'client.company_name',
                'Ime' => 'name', 'Email' => 'email', 'Uloga' => 'role', 'Email potvrđen' => 'email_verified_at',
            ],
        };

        return $columns + ['Kreirano' => 'created_at', 'Izmenjeno' => 'updated_at'];
    }

    /** @param resource $stream */
    public function writeCsv($stream, string $type, ?int $clientId): void
    {
        // UTF-8 BOM and semicolon delimiter preserve Serbian text in Excel.
        fwrite($stream, "\xEF\xBB\xBF");
        $columns = $this->columns($type);
        $this->writeRow($stream, array_keys($columns));

        foreach ($this->rows($type, $clientId) as $values) {
            $row = [];
            foreach ($values as $value) {
                $value = (string) ($value ?? '');
                // Text from users must not become a spreadsheet formula.
                if (preg_match('/^[\s\x{FEFF}]*[=+@-]/u', $value) || preg_match('/^[\t\r\n]/', $value)) {
                    $value = "'".$value;
                }
                $row[] = $value;
            }
            $this->writeRow($stream, $row);
        }
    }

    public function rows(string $type, ?int $clientId, bool $numeric = false): Generator
    {
        $columns = $this->columns($type);
        foreach ($this->query($type, $clientId)->lazyById(500) as $record) {
            $row = [];
            foreach ($columns as $path) {
                $value = data_get($record, $path);
                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format(in_array($path, ['worked_on', 'paid_on', 'started_at', 'finished_at'], true) ? 'Y-m-d' : 'Y-m-d H:i:s');
                } elseif (is_bool($value)) {
                    $value = $value ? '1' : '0';
                }

                // Only actual numeric fields become numbers; identifiers such as PIB stay text.
                if ($numeric && $value !== null && in_array($path, ['hours', 'hourly_rate', 'default_hourly_rate', 'fixed_amount', 'amount'], true)) {
                    $value = (float) $value;
                }
                $row[] = $value;
            }
            yield $row;
        }
    }

    private function writeRow($stream, array $row): void
    {
        if (fputcsv($stream, $row, ';', '"', '', "\r\n") === false) {
            throw new RuntimeException('CSV fajl nije moguće zapisati.');
        }
    }
}
