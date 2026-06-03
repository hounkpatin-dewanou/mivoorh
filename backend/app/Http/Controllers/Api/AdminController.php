<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdvanceRequest;
use App\Models\AppNotification;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/** CRUD super-admin : employés et demandes d'avance sur tout le réseau. */
class AdminController extends Controller
{
    public function employeesIndex(Request $request)
    {
        $query = Employee::with(['user', 'company'])->orderByDesc('id');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        return response()->json($query->get());
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
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
            'company_id' => $data['company_id'],
        ]);

        $employee = Employee::create([
            'user_id' => $user->id,
            'company_id' => $data['company_id'],
            'monthly_salary' => $data['monthly_salary'],
            'advance_limit_pct' => $data['advance_limit_pct'] ?? 40,
            'is_active' => true,
        ]);

        return response()->json($employee->load('user', 'company'), 201);
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,'.$employee->user_id],
            'company_id' => ['sometimes', 'exists:companies,id'],
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

        if (isset($data['company_id'])) {
            $employee->user->update(['company_id' => $data['company_id']]);
        }

        $employee->update($this->employeeFieldsForUpdate($data));

        return response()->json($employee->fresh()->load('user', 'company'));
    }

    public function destroyEmployee(Employee $employee)
    {
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

    public function advanceRequestsIndex(Request $request)
    {
        $query = AdvanceRequest::with(['employee.user', 'employee.company'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('company_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('company_id', $request->integer('company_id')));
        }

        return response()->json($query->get());
    }

    public function storeAdvanceRequest(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'reason' => ['required', 'string', 'max:1000'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'refused'])],
        ]);

        $status = $data['status'] ?? 'pending';
        $advance = AdvanceRequest::create([
            'employee_id' => $data['employee_id'],
            'amount' => $data['amount'],
            'reason' => $data['reason'],
            'status' => $status,
            'reviewed_by' => in_array($status, ['approved', 'refused'], true) ? $request->user()->id : null,
            'reviewed_at' => in_array($status, ['approved', 'refused'], true) ? now() : null,
        ]);

        return response()->json($advance->load('employee.user', 'employee.company'), 201);
    }

    public function updateAdvanceRequest(Request $request, AdvanceRequest $advanceRequest)
    {
        $data = $request->validate([
            'amount' => ['sometimes', 'numeric', 'min:1000'],
            'reason' => ['sometimes', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'refused'])],
            'review_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if (isset($data['status']) && $data['status'] !== 'pending' && ! $advanceRequest->reviewed_at) {
            $data['reviewed_by'] = $request->user()->id;
            $data['reviewed_at'] = now();
        }

        if (isset($data['status']) && $data['status'] === 'pending') {
            $data['reviewed_by'] = null;
            $data['reviewed_at'] = null;
        }

        $advanceRequest->update($data);

        return response()->json($advanceRequest->fresh()->load('employee.user', 'employee.company'));
    }

    public function destroyAdvanceRequest(AdvanceRequest $advanceRequest)
    {
        $advanceRequest->delete();

        return response()->json(['message' => 'Demande supprimée.']);
    }

    public function approveAdvanceRequest(Request $request, AdvanceRequest $advanceRequest)
    {
        if ($advanceRequest->status !== 'pending') {
            abort(422, 'Cette demande a déjà été traitée.');
        }

        $data = $request->validate([
            'review_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $advanceRequest->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_comment' => $data['review_comment'] ?? null,
        ]);

        $this->notifyEmployee($advanceRequest, 'approuvée');

        return response()->json($advanceRequest->load('employee.user', 'employee.company'));
    }

    public function refuseAdvanceRequest(Request $request, AdvanceRequest $advanceRequest)
    {
        if ($advanceRequest->status !== 'pending') {
            abort(422, 'Cette demande a déjà été traitée.');
        }

        $data = $request->validate([
            'review_comment' => ['required', 'string', 'max:1000'],
        ]);

        $advanceRequest->update([
            'status' => 'refused',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_comment' => $data['review_comment'],
        ]);

        $this->notifyEmployee($advanceRequest, 'refusée');

        return response()->json($advanceRequest->load('employee.user', 'employee.company'));
    }

    private function employeeFieldsForUpdate(array $data): array
    {
        $fields = collect($data)->only(['company_id', 'monthly_salary', 'advance_limit_pct'])->filter()->all();
        if (array_key_exists('is_active', $data)) {
            $fields['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        return $fields;
    }

    private function notifyEmployee(AdvanceRequest $advanceRequest, string $label): void
    {
        $user = $advanceRequest->employee->user;
        AppNotification::create([
            'user_id' => $user->id,
            'message' => sprintf(
                'Votre demande d\'avance de %s FCFA a été %s.',
                number_format($advanceRequest->amount, 0, ',', ' '),
                $label
            ),
            'is_read' => false,
        ]);
    }
}
