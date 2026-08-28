<?php

namespace Tests\Feature;

use App\Models\KhataCustomer;
use App\Models\KhataTransaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KhataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@gmail.com')->first();
    }

    public function test_admin_can_view_khata_index(): void
    {
        $this->actingAs($this->admin);

        KhataCustomer::create([
            'name' => 'Ali Khan',
            'phone' => '03001234567',
            'address' => 'Karachi',
            'opening_balance' => 1500.00,
            'status' => 'active',
        ]);

        $response = $this->get(route('admin.khata.index'));
        $response->assertOk();
        $response->assertSee('Ali Khan');
        $response->assertSee('Customers Khata');
    }

    public function test_admin_can_create_customer_via_ajax(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.khata.customers.store'), [
            'name' => 'Usman Trading',
            'phone' => '03123456789',
            'address' => 'Lahore Mall Road',
            'opening_balance' => 5000.00,
            'status' => 'active',
            'notes' => 'Old business client',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'message' => 'Customer added to Khata successfully.',
        ]);

        $this->assertDatabaseHas('khata_customers', [
            'name' => 'Usman Trading',
            'phone' => '03123456789',
            'opening_balance' => 5000.00,
        ]);
    }

    public function test_admin_can_record_pese_liye_and_pese_diye_transactions(): void
    {
        $this->actingAs($this->admin);

        $customer = KhataCustomer::create([
            'name' => 'Tariq Mehmood',
            'phone' => '03331112233',
            'opening_balance' => 2000.00,
            'status' => 'active',
        ]);

        // 1. Give money / goods on credit (Pese Diye: 3000)
        $responseDiye = $this->postJson(route('admin.khata.transactions.store'), [
            'khata_customer_id' => $customer->id,
            'type' => 'pese_diye',
            'amount' => 3000.00,
            'transaction_date' => '2026-08-27',
            'description' => 'Goods delivered on credit',
        ]);
        $responseDiye->assertStatus(201);

        // 2. Receive payment (Pese Liye: 1500)
        $responseLiye = $this->postJson(route('admin.khata.transactions.store'), [
            'khata_customer_id' => $customer->id,
            'type' => 'pese_liye',
            'amount' => 1500.00,
            'transaction_date' => '2026-08-28',
            'description' => 'Cash received from customer',
        ]);
        $responseLiye->assertStatus(201);

        // Check DB
        $this->assertDatabaseCount('khata_transactions', 2);

        // Check calculations:
        // Opening = 2000
        // Pese Diye = 3000
        // Pese Liye = 1500
        // Balance = 2000 + 3000 - 1500 = 3500 (Receivable)
        $customer->refresh();
        $this->assertEquals(1500.00, $customer->total_pese_liye);
        $this->assertEquals(3000.00, $customer->total_pese_diye);
        $this->assertEquals(3500.00, $customer->current_balance);
        $this->assertEquals('receivable', $customer->balance_status);
    }

    public function test_admin_can_view_individual_khata_ledger_with_running_balance(): void
    {
        $this->actingAs($this->admin);

        $customer = KhataCustomer::create([
            'name' => 'Bilal Autos',
            'opening_balance' => 1000.00,
            'status' => 'active',
        ]);

        KhataTransaction::create([
            'khata_customer_id' => $customer->id,
            'type' => 'pese_diye',
            'amount' => 2000.00,
            'transaction_date' => '2026-08-20',
            'description' => 'Spare parts bill',
            'created_by' => $this->admin->id,
        ]);

        KhataTransaction::create([
            'khata_customer_id' => $customer->id,
            'type' => 'pese_liye',
            'amount' => 500.00,
            'transaction_date' => '2026-08-22',
            'description' => 'Bank transfer payment',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->get(route('admin.khata.show', $customer));
        $response->assertOk();
        $response->assertSee('Bilal Autos');
        $response->assertSee('Spare parts bill');
        $response->assertSee('Bank transfer payment');
        $response->assertSee('Rs. 2,500.00'); // Final balance: 1000 + 2000 - 500 = 2500
    }

    public function test_customer_can_be_updated_and_deleted(): void
    {
        $this->actingAs($this->admin);

        $customer = KhataCustomer::create([
            'name' => 'Old Name',
            'phone' => '03000000000',
            'opening_balance' => 0.00,
            'status' => 'active',
        ]);

        $updateResponse = $this->putJson(route('admin.khata.customers.update', $customer), [
            'name' => 'Updated Name',
            'phone' => '03111111111',
            'opening_balance' => 500.00,
            'status' => 'inactive',
        ]);
        $updateResponse->assertOk();

        $this->assertDatabaseHas('khata_customers', [
            'id' => $customer->id,
            'name' => 'Updated Name',
            'phone' => '03111111111',
            'status' => 'inactive',
        ]);

        $deleteResponse = $this->deleteJson(route('admin.khata.customers.destroy', $customer));
        $deleteResponse->assertOk();

        $this->assertDatabaseMissing('khata_customers', [
            'id' => $customer->id,
        ]);
    }
}

