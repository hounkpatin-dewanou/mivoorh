<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    public function test_login_returns_token_and_user(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'rh1@mivoorh.test',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'role']])
            ->assertJsonPath('user.role', 'hr');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'rh1@mivoorh.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_blocked_when_company_inactive(): void
    {
        $user = User::where('email', 'rh1@mivoorh.test')->first();
        Company::where('id', $user->company_id)->update(['is_active' => false]);

        $response = $this->postJson('/api/login', [
            'email' => 'rh1@mivoorh.test',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_blocked_when_employee_inactive(): void
    {
        $user = User::where('email', 'emp1_1@mivoorh.test')->first();
        Employee::where('user_id', $user->id)->update(['is_active' => false]);

        $response = $this->postJson('/api/login', [
            'email' => 'emp1_1@mivoorh.test',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $token = $this->apiToken('emp1_1@mivoorh.test');

        $response = $this->asBearer($token)->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('email', 'emp1_1@mivoorh.test')
            ->assertJsonPath('role', 'employee');
    }

    public function test_hr_cannot_access_admin_routes(): void
    {
        $token = $this->apiToken('rh1@mivoorh.test');

        $response = $this->asBearer($token)->getJson('/api/admin/companies');

        $response->assertForbidden();
    }

    public function test_superadmin_can_list_companies(): void
    {
        $token = $this->apiToken('admin@mivoopay.bj');

        $response = $this->asBearer($token)->getJson('/api/admin/companies');

        $response->assertOk();
        $this->assertGreaterThanOrEqual(2, count($response->json()));
    }
}
