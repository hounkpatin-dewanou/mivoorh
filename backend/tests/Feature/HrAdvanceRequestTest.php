<?php

namespace Tests\Feature;

use App\Models\AdvanceRequest;
use App\Models\Employee;
use Tests\TestCase;

class HrAdvanceRequestTest extends TestCase
{
    public function test_hr_can_list_pending_requests(): void
    {
        $token = $this->apiToken('rh1@mivoorh.test');

        $response = $this->asBearer($token)->getJson('/api/hr/advance-requests?status=pending');

        $response->assertOk();
        $this->assertNotEmpty($response->json());
    }

    public function test_hr_can_approve_request_with_comment(): void
    {
        $pending = AdvanceRequest::where('status', 'pending')
            ->whereHas('employee', fn ($q) => $q->where('company_id', 1))
            ->first();

        $this->assertNotNull($pending);

        $token = $this->apiToken('rh1@mivoorh.test');

        $response = $this->asBearer($token)->patchJson(
            "/api/hr/advance-requests/{$pending->id}/approve",
            ['review_comment' => 'Validé pour paie']
        );

        $response->assertOk()
            ->assertJsonPath('status', 'approved');

        $this->assertDatabaseHas('advance_requests', [
            'id' => $pending->id,
            'status' => 'approved',
        ]);
    }

    public function test_hr_export_csv_returns_file(): void
    {
        $token = $this->apiToken('rh1@mivoorh.test');

        $response = $this->asBearer($token)->get('/api/hr/export/csv?month=6&year='.now()->year);

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type') ?? '');
    }

    public function test_employee_can_view_balance(): void
    {
        $token = $this->apiToken('emp1_1@mivoorh.test');

        $response = $this->asBearer($token)->getJson('/api/employee/balance');

        $response->assertOk()
            ->assertJsonStructure(['available_balance', 'monthly_salary', 'advance_limit_pct']);
    }

    public function test_hr_can_delete_employee_from_own_company(): void
    {
        $token = $this->apiToken('rh1@mivoorh.test');

        $created = $this->asBearer($token)->postJson('/api/hr/employees', [
            'name' => 'À supprimer',
            'email' => 'delete-me@mivoorh.test',
            'password' => 'password',
            'monthly_salary' => 100000,
            'advance_limit_pct' => 40,
        ]);

        $created->assertCreated();
        $employeeId = $created->json('id');

        $response = $this->asBearer($token)->deleteJson("/api/hr/employees/{$employeeId}");

        $response->assertOk();
        $this->assertDatabaseMissing('employees', ['id' => $employeeId]);
    }
}
