<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Client;
use App\Models\ProformaInvoice;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProformaInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access',
            'color' => '#dc2626'
        ]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'status' => 'active'
        ]);

        $this->client = Client::create([
            'company_name' => 'Test Client Corp',
            'contact_person' => 'Jane Representative',
            'email' => 'client@example.com',
            'status' => 'active'
        ]);
    }

    public function test_admin_can_create_proforma_invoice(): void
    {
        $proformaData = [
            'proforma_number' => 'PRO-2026-0001',
            'proforma_date' => '2026-07-05',
            'due_date' => '2026-07-20',
            'client_id' => $this->client->id,
            'items' => [
                ['name' => 'Website Development', 'qty' => 1, 'price' => 50000, 'total' => 50000]
            ],
            'subtotal' => 50000,
            'tax_percentage' => 18,
            'tax_amount' => 9000,
            'discount' => 0,
            'total' => 59000,
            'notes' => 'Some notes'
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('proforma-invoices.store'), $proformaData);

        $this->assertDatabaseHas('proforma_invoices', [
            'proforma_number' => 'PRO-2026-0001',
            'client_id' => $this->client->id,
            'total' => 59000
        ]);

        $proforma = ProformaInvoice::where('proforma_number', 'PRO-2026-0001')->first();
        $response->assertRedirect(route('proforma-invoices.show', $proforma));
    }

    public function test_admin_can_convert_proforma_to_invoice(): void
    {
        $proforma = ProformaInvoice::create([
            'proforma_number' => 'PRO-2026-0002',
            'proforma_date' => '2026-07-05',
            'due_date' => '2026-07-20',
            'client_id' => $this->client->id,
            'items' => [
                ['name' => 'SEO Services', 'qty' => 2, 'price' => 15000, 'total' => 30000]
            ],
            'subtotal' => 30000,
            'tax_percentage' => 18,
            'tax_amount' => 5400,
            'discount' => 1000,
            'total' => 34400,
            'notes' => 'Seo setup notes',
            'status' => 'sent',
            'created_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('proforma-invoices.convert', $proforma));

        $invoice = Invoice::latest()->first();

        $response->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('invoices', [
            'client_id' => $this->client->id,
            'total' => 34400,
            'discount' => 1000,
            'subtotal' => 30000
        ]);

        $this->assertDatabaseHas('proforma_invoices', [
            'id' => $proforma->id,
            'status' => 'converted',
            'converted_invoice_id' => $invoice->id
        ]);
    }

    public function test_admin_can_download_pdf(): void
    {
        $proforma = ProformaInvoice::create([
            'proforma_number' => 'PRO-2026-0003',
            'proforma_date' => '2026-07-05',
            'due_date' => '2026-07-20',
            'client_id' => $this->client->id,
            'items' => [
                ['name' => 'SEO Services', 'qty' => 1, 'price' => 10000, 'total' => 10000]
            ],
            'subtotal' => 10000,
            'tax_percentage' => 18,
            'tax_amount' => 1800,
            'discount' => 0,
            'total' => 11800,
            'notes' => 'PDF download test',
            'status' => 'draft',
            'created_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('proforma-invoices.pdf', $proforma));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_send_proforma_invoice(): void
    {
        $proforma = ProformaInvoice::create([
            'proforma_number' => 'PRO-2026-0004',
            'proforma_date' => '2026-07-05',
            'due_date' => '2026-07-20',
            'client_id' => $this->client->id,
            'items' => [
                ['name' => 'SEO Services', 'qty' => 1, 'price' => 10000, 'total' => 10000]
            ],
            'subtotal' => 10000,
            'tax_percentage' => 18,
            'tax_amount' => 1800,
            'discount' => 0,
            'total' => 11800,
            'notes' => 'Send test',
            'status' => 'draft',
            'created_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('proforma-invoices.send', $proforma));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('proforma_invoices', [
            'id' => $proforma->id,
            'status' => 'sent'
        ]);
    }
}
