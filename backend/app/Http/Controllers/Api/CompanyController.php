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

/** Super-admin : entreprises, activation, statistiques globales. */
class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount(['employees', 'users'])
            ->orderBy('name')
            ->get();

        return response()->json($companies);
    }

    public function stats()
    {
        return response()->json([
            'companies_total' => Company::count(),
            'companies_active' => Company::where('is_active', true)->count(),
            'employees_total' => Employee::count(),
            'requests_pending' => AdvanceRequest::where('status', 'pending')->count(),
            'requests_month_amount' => AdvanceRequest::where('status', 'approved')
                ->whereMonth('reviewed_at', now()->month)
                ->whereYear('reviewed_at', now()->year)
                ->sum('amount'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['required', 'email'],
        ]);

        $company = Company::create([...$data, 'is_active' => true]);

        return response()->json($company, 201);
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['sometimes', 'email'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $company->update($data);

        return response()->json($company);
    }

    public function toggle(Company $company)
    {
        $company->update(['is_active' => ! $company->is_active]);

        return response()->json($company);
    }

    public function destroy(Company $company)
    {
        DB::transaction(function () use ($company) {
            $users = User::where('company_id', $company->id)->get();

            foreach ($users as $user) {
                if ($user->employee) {
                    $user->employee->advanceRequests()->delete();
                }
                AppNotification::where('user_id', $user->id)->delete();
                $user->tokens()->delete();
            }

            Employee::where('company_id', $company->id)->delete();
            User::where('company_id', $company->id)->delete();
            $company->delete();
        });

        return response()->json(['message' => 'Entreprise supprimée.']);
    }
}
