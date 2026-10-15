<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkEntry;
use App\Services\DataExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_access_exports(): void
    {
        $this->get('/exports')->assertRedirect('/login');
        $this->get('/exports/all')->assertRedirect('/login');
        $this->get('/exports/all?format=xlsx')->assertRedirect('/login');

        $this->actingAs(User::factory()->create(['role' => 'client']));
        $this->get('/exports')->assertForbidden();
        foreach (['all', ...array_keys(DataExport::TYPES)] as $type) {
            $this->get('/exports/'.$type)->assertForbidden();
            $this->get('/exports/'.$type.'?format=xlsx')->assertForbidden();
        }
    }

    public function test_admin_can_open_export_page_and_download_every_dataset(): void
    {
        $this->admin();
        $client = $this->records();
        $this->get('/exports')->assertOk()->assertSee('Preuzmi sve')->assertSee('Excel (.xlsx)');

        foreach (array_keys(DataExport::TYPES) as $type) {
            $response = $this->get('/exports/'.$type);
            $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
            $csv = $response->streamedContent();
            $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
            $this->assertStringContainsString($client->company_name, $csv);
            $this->assertCount($type === 'users' ? 3 : 2, $this->rows($csv));
            $this->assertStringNotContainsString('secret-share-token', $csv);
            $this->assertStringNotContainsString('password', $csv);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
    }

    public function test_client_filter_applies_to_every_dataset_and_zip(): void
    {
        $this->admin();
        $client = $this->records();
        $other = $this->records('Drugi klijent');

        foreach (array_keys(DataExport::TYPES) as $type) {
            $csv = $this->get('/exports/'.$type.'?client_id='.$client->id)->assertOk()->streamedContent();
            $this->assertCount(2, $this->rows($csv));
            $this->assertStringContainsString($client->company_name, $csv);
            $this->assertStringNotContainsString($other->company_name, $csv);
        }

        $response = $this->get('/exports/all?client_id='.$client->id)->assertOk();
        $response->assertHeader('Content-Type', 'application/zip');
        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        try {
            $this->assertTrue($zip->open($path));
            $this->assertSame(count(DataExport::TYPES), $zip->numFiles);
            foreach (array_keys(DataExport::TYPES) as $type) {
                $this->assertSame(
                    $this->get('/exports/'.$type.'?client_id='.$client->id)->streamedContent(),
                    $zip->getFromName($type.'.csv'),
                );
            }
        } finally {
            $zip->close();
            unlink($path);
        }
    }

    public function test_csv_preserves_serbian_text_multiline_fields_and_neutralizes_formulas(): void
    {
        $this->admin();
        $note = "Čćžšđ; \"opis\"\nNovi red \\ tekst";
        Client::create([
            'company_name' => '=HYPERLINK("https://example.com")',
            'contact_name' => '  +SUM(1,2)',
            'notes' => $note,
        ]);
        $rows = $this->rows($this->get('/exports/clients')->streamedContent());
        $record = array_combine($rows[0], $rows[1]);
        $this->assertSame($note, $record['Napomene']);
        $this->assertSame('\'=HYPERLINK("https://example.com")', $record['Firma']);
        $this->assertSame("'  +SUM(1,2)", $record['Kontakt']);
    }

    public function test_exports_include_all_records_and_empty_exports_still_have_headers(): void
    {
        $this->admin();
        $this->assertCount(1, $this->rows($this->get('/exports/tasks')->streamedContent()));
        for ($index = 0; $index < 18; $index++) {
            Client::create(['company_name' => 'Klijent '.$index]);
        }
        $this->assertCount(19, $this->rows($this->get('/exports/clients')->streamedContent()));
    }

    public function test_unknown_dataset_and_invalid_client_are_rejected(): void
    {
        $this->admin();
        $this->get('/exports/passwords')->assertNotFound();
        $this->getJson('/exports/clients?client_id=999')->assertUnprocessable();
        $this->getJson('/exports/all?client_id=invalid')->assertUnprocessable();
        $this->getJson('/exports?client_id=999')->assertUnprocessable();
        $this->getJson('/exports/clients?format=pdf')->assertUnprocessable();
        $this->getJson('/exports?format=pdf')->assertUnprocessable();
    }

    public function test_excel_exports_are_valid_workbooks_with_filtered_sheets_and_typed_cells(): void
    {
        $this->admin();
        $client = $this->records();
        $client->update(['tax_number' => '001234567', 'notes' => '=SUM(1,2)']);
        $other = $this->records('Drugi klijent');

        foreach (['all', ...array_keys(DataExport::TYPES)] as $type) {
            $response = $this->get('/exports/'.$type.'?format=xlsx&client_id='.$client->id)
                ->assertOk()
                ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
            $path = $response->baseResponse->getFile()->getPathname();
            $zip = new ZipArchive;
            try {
                $this->assertTrue($zip->open($path));
                for ($index = 0; $index < $zip->numFiles; $index++) {
                    $xml = new \DOMDocument;
                    $this->assertTrue($xml->loadXML($zip->getFromIndex($index)));
                }
                $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
                $sheets = $workbook->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main')->sheets->sheet;
                $this->assertCount($type === 'all' ? 6 : 1, $sheets);
                foreach ($sheets as $index => $sheet) {
                    $this->assertLessThanOrEqual(31, mb_strlen((string) $sheet['name']));
                }
                $types = $type === 'all' ? array_keys(DataExport::TYPES) : [$type];
                foreach ($types as $index => $dataset) {
                    $sheet = $zip->getFromName('xl/worksheets/sheet'.($index + 1).'.xml');
                    $this->assertStringContainsString($client->company_name, $sheet);
                    $this->assertStringNotContainsString($other->company_name, $sheet);
                    $this->assertStringNotContainsString('secret-share-token', $sheet);
                    $this->assertStringNotContainsString('password', $sheet);
                    $this->assertStringNotContainsString('<f>', $sheet);
                    $xml = new \DOMDocument;
                    $xml->loadXML($sheet);
                    $this->assertSame(2, $xml->getElementsByTagName('row')->length);
                    if ($dataset === 'clients') {
                        $this->assertStringContainsString('001234567', $sheet);
                        $this->assertStringContainsString('=SUM(1,2)', $sheet);
                        $this->assertStringContainsString('<v>20</v>', $sheet);
                    }
                    if ($dataset === 'tasks') {
                        $this->assertStringContainsString('<v>75</v>', $sheet);
                        $this->assertStringContainsString('Interno', $sheet);
                    }
                }
            } finally {
                $zip->close();
                unlink($path);
            }
        }
    }

    public function test_empty_excel_export_has_column_headers(): void
    {
        $this->admin();
        $response = $this->get('/exports/tasks?format=xlsx')->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        try {
            $zip->open($path);
            $xml = new \DOMDocument;
            $xml->loadXML($zip->getFromName('xl/worksheets/sheet1.xml'));
            $this->assertSame(1, $xml->getElementsByTagName('row')->length);
            $this->assertStringContainsString('Naslov', $xml->textContent);
        } finally {
            $zip->close();
            unlink($path);
        }
    }

    private function admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
    }

    private function records(string $name = 'Čačak firma'): Client
    {
        $client = Client::create(['company_name' => $name, 'currency' => 'EUR', 'share_token' => 'secret-share-token-'.$name]);
        $project = Project::create(['client_id' => $client->id, 'name' => 'Sajt', 'status' => 'active']);
        $payment = Payment::create([
            'client_id' => $client->id, 'paid_on' => '2026-10-16', 'amount' => 75,
            'type' => 'payment', 'status' => 'pending_invoice',
        ]);
        Payment::create([
            'client_id' => $client->id, 'project_id' => $project->id,
            'paid_on' => '2026-10-16', 'amount' => 10, 'type' => 'expense',
        ]);
        WorkEntry::create([
            'project_id' => $project->id, 'payment_id' => $payment->id, 'title' => 'Dizajn',
            'amount' => 75, 'visible_to_client' => false, 'internal_note' => 'Interno',
        ]);
        User::factory()->create(['client_id' => $client->id, 'role' => 'client']);

        return $client;
    }

    private function rows(string $csv): array
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, substr($csv, 3));
        rewind($stream);
        $rows = [];
        while (($row = fgetcsv($stream, null, ';', '"', '')) !== false) {
            $rows[] = $row;
        }
        fclose($stream);

        return $rows;
    }
}
