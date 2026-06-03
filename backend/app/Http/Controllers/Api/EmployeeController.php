<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** Gestion des employés et statistiques pour le responsable RH (périmètre company_id). */
class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::with('user')
            ->where('company_id', $request->user()->company_id)
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->get();

        return response()->json($employees);
    }

    public function show(Request $request, Employee $employee)
    {
        $this->authorizeCompany($request, $employee);

        return response()->json($employee->load('user', 'advanceRequests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'monthly_salary' => ['required', 'numeric', 'min:0'],
            'advance_limit_pct' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'employee',
            'company_id' => $request->user()->company_id,
        ]);

        $employee = Employee::create([
            'user_id' => $user->id,
            'company_id' => $request->user()->company_id,
            'monthly_salary' => $data['monthly_salary'],
            'advance_limit_pct' => $data['advance_limit_pct'] ?? 40,
            'is_active' => true,
        ]);

        return response()->json($employee->load('user'), 201);
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorizeCompany($request, $employee);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,'.$employee->user_id],
            'monthly_salary' => ['sometimes', 'numeric', 'min:0'],
            'advance_limit_pct' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['name']) || isset($data['email'])) {
            $employee->user->update(array_filter([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
            ]));
        }

        $fields = collect($data)->only(['monthly_salary', 'advance_limit_pct'])->filter()->all();
        if (array_key_exists('is_active', $data)) {
            $fields['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }
        $employee->update($fields);

        return response()->json($employee->fresh()->load('user'));
    }

    public function deactivate(Request $request, Employee $employee)
    {
        $this->authorizeCompany($request, $employee);
        $employee->update(['is_active' => false]);

        return response()->json($employee->load('user'));
    }

    public function destroy(Request $request, Employee $employee)
    {
        $this->authorizeCompany($request, $employee);

        $user = $employee->user;

        DB::transaction(function () use ($employee, $user) {
            $employee->advanceRequests()->delete();
            AppNotification::where('user_id', $user->id)->delete();
            $user->tokens()->delete();
            $employee->delete();
            $user->delete();
        });

        return response()->json(['message' => 'Employé supprimé.']);
    }

    public function hrStats(Request $request)
    {
        $companyId = $request->user()->company_id;

        $employees = Employee::where('company_id', $companyId);
        $requests = \App\Models\AdvanceRequest::whereHas('employee', fn ($q) => $q->where('company_id', $companyId));

        return response()->json([
            'employees_count' => $employees->count(),
            'employees_active' => (clone $employees)->where('is_active', true)->count(),
            'requests_pending' => (clone $requests)->where('status', 'pending')->count(),
            'requests_month_count' => (clone $requests)->whereMonth('created_at', now()->month)->count(),
            'disbursed_month' => (clone $requests)->where('status', 'approved')
                ->whereMonth('reviewed_at', now()->month)
                ->sum('amount'),
        ]);
    }

    private function authorizeCompany(Request $request, Employee $employee): void
    {
        if ($employee->company_id !== $request->user()->company_id) {
            abort(403, 'Employé hors de votre entreprise.');
        }
    }
}
