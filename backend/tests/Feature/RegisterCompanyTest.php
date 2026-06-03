<?php

namespace Tests\Feature;

use App\Models\Company;
use Tests\TestCase;

class RegisterCompanyTest extends TestCase
{
    public function test_hr_register_always_creates_company(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Nouveau RH',
            'email' => 'nouveau-rh@test.bj',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'hr',
            'company_name' => 'Startup Test',
            'company_sector' => 'Tech',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'hr')
            ->assertJsonPath('user.company.name', 'Startup Test');

        $this->assertDatabaseHas('companies', ['name' => 'Startup Test']);
        $this->assertDatabaseHas('users', ['email' => 'nouveau-rh@test.bj', 'role' => 'hr']);
    }

    public function test_hr_cannot_join_existing_company(): void
    {
        $company = Company::first();

        $response = $this->postJson('/api/register', [
            'name' => 'RH Intrus',
            'email' => 'rh-intrus@test.bj',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'hr',
            'company_id' => $company->id,
            'company_name' => 'Should not matter',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id']);
    }

    public function test_employee_must_join_existing_company(): void
    {
        $company = Company::first();

        $response = $this->postJson('/api/register', [
            'name' => 'Nouvel Employé',
            'email' => 'nouvel-emp@test.bj',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee',
            'company_id' => $company->id,
            'monthly_salary' => 200000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'employee')
            ->assertJsonPath('user.company_id', $company->id);
    }
}
