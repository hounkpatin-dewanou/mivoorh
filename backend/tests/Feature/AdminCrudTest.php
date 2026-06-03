<?php

namespace Tests\Feature;

use App\Models\Company;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    public function test_superadmin_can_list_all_employees(): void
    {
        $token = $this->apiToken('admin@mivoopay.bj');

        $response = $this->asBearer($token)->getJson('/api/admin/employees');

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, count($response->json()));
    }

    public function test_superadmin_can_delete_company(): void
    {
        $token = $this->apiToken('admin@mivoopay.bj');
        $company = Company::create([
            'name' => 'À supprimer',
            'contact_email' => 'del@test.bj',
            'is_active' => true,
        ]);

        $response = $this->asBearer($token)->deleteJson("/api/admin/companies/{$company->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_admin_can_deactivate_employee(): void
    {
        $token = $this->apiToken('admin@mivoopay.bj');
        $employee = \App\Models\Employee::where('is_active', true)->first();

        $response = $this->asBearer($token)->putJson("/api/admin/employees/{$employee->id}", [
            'is_active' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('is_active', false);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'is_active' => false,
        ]);
    }
}
