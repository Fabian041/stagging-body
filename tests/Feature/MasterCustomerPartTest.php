<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MasterCustomerPartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    public function test_same_part_number_is_allowed_for_different_customers_only(): void
    {
        $part = InternalPart::create(['part_number' => 'INTERNAL-001']);
        $firstCustomer = Customer::create(['name' => 'Customer A']);
        $secondCustomer = Customer::create(['name' => 'Customer B']);
        $admin = $this->admin();

        foreach ([$firstCustomer, $secondCustomer] as $customer) {
            $this->actingAs($admin)->post('/dashboard/master/customer-parts', [
                'internal_part_id' => $part->id,
                'customer_id' => $customer->id,
                'part_number' => ' customer-part-001 ',
                'back_number' => ' b01 ',
                'qty_per_kanban' => 10,
            ])->assertRedirect('/dashboard/master/customer-parts');
        }

        $this->assertSame(2, CustomerPart::count());
        $this->assertSame(
            ['CUSTOMER-PART-001', 'CUSTOMER-PART-001'],
            CustomerPart::orderBy('customer_id')->pluck('part_number')->all()
        );

        $this->actingAs($admin)->post('/dashboard/master/customer-parts', [
            'internal_part_id' => $part->id,
            'customer_id' => $firstCustomer->id,
            'part_number' => 'CUSTOMER-PART-001',
            'back_number' => 'B02',
            'qty_per_kanban' => 20,
        ])->assertSessionHasErrors('part_number');

        $this->assertSame(2, CustomerPart::count());
    }

    public function test_existing_mapping_can_be_updated_without_false_duplicate_error(): void
    {
        $part = InternalPart::create(['part_number' => 'INTERNAL-001']);
        $customer = Customer::create(['name' => 'Customer A']);
        $mapping = CustomerPart::create([
            'internal_part_id' => $part->id,
            'customer_id' => $customer->id,
            'part_number' => 'CUSTOMER-PART-001',
            'back_number' => 'B01',
            'qty_per_kanban' => 10,
        ]);

        $this->actingAs($this->admin())->put("/dashboard/master/customer-parts/{$mapping->id}", [
            'internal_part_id' => $part->id,
            'customer_id' => $customer->id,
            'part_number' => 'CUSTOMER-PART-001',
            'back_number' => 'B01',
            'qty_per_kanban' => 20,
        ])->assertRedirect('/dashboard/master/customer-parts');

        $this->assertSame(20, $mapping->fresh()->qty_per_kanban);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Administrator',
            'email' => uniqid('admin-', true).'@example.test',
            'npk' => uniqid('NPK-', true),
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('npk')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('internal_parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number');
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('customer_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('internal_part_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('part_number');
            $table->string('back_number')->nullable();
            $table->integer('qty_per_kanban');
            $table->timestamps();
        });
    }
}
