<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_employee_can_update_profile(): void
    {
        $token = $this->apiToken('emp1_1@mivoorh.test');

        $response = $this->asBearer($token)->putJson('/api/profile', [
            'name' => 'Employé Modifié',
            'email' => 'emp1_1@mivoorh.test',
            'monthly_salary' => 250000,
            'advance_limit_pct' => 35,
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Employé Modifié')
            ->assertJsonPath('employee.monthly_salary', '250000.00');
    }

    public function test_hr_can_update_company_on_profile(): void
    {
        $token = $this->apiToken('rh1@mivoorh.test');

        $response = $this->asBearer($token)->putJson('/api/profile', [
            'company_name' => 'Agro Bénin Mise à jour',
            'company_contact_email' => 'rh@agrobenin.bj',
        ]);

        $response->assertOk()
            ->assertJsonPath('company.name', 'Agro Bénin Mise à jour');
    }
}
