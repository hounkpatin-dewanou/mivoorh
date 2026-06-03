<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/** Lecture / mise à jour du profil connecté (selon rôle : user, company, employee). */
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(
            $request->user()->load('company', 'employee')
        );
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'company_id' => ['sometimes', 'exists:companies,id'],
            'monthly_salary' => ['sometimes', 'numeric', 'min:0'],
            'advance_limit_pct' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'company_name' => ['sometimes', 'string', 'max:255'],
            'company_sector' => ['nullable', 'string', 'max:255'],
            'company_contact_email' => ['sometimes', 'email'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $userFields = collect($data)->only(['name', 'email', 'password'])->filter()->all();
        if ($userFields) {
            $user->update($userFields);
        }

        if ($user->role === 'employee') {
            $employee = $user->employee;
            if (! $employee) {
                throw ValidationException::withMessages([
                    'email' => ['Profil employé introuvable.'],
                ]);
            }

            if (isset($data['company_id'])) {
                $company = Company::findOrFail($data['company_id']);
                if (! $company->is_active) {
                    throw ValidationException::withMessages([
                        'company_id' => ['Cette entreprise n\'est pas active.'],
                    ]);
                }
                $user->update(['company_id' => $company->id]);
                $employee->update(['company_id' => $company->id]);
            }

            $fields = collect($data)->only(['monthly_salary', 'advance_limit_pct'])->filter()->all();
            if (array_key_exists('is_active', $data)) {
                $fields['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
            }
            $employee->update($fields);
        }

        if ($user->role === 'hr' && $user->company) {
            $companyUpdate = [];
            if (array_key_exists('company_name', $data)) {
                $companyUpdate['name'] = $data['company_name'];
            }
            if (array_key_exists('company_sector', $data)) {
                $companyUpdate['sector'] = $data['company_sector'];
            }
            if (array_key_exists('company_contact_email', $data)) {
                $companyUpdate['contact_email'] = $data['company_contact_email'];
            }
            if ($companyUpdate !== []) {
                $user->company->update($companyUpdate);
            }
        }

        if ($user->role === 'superadmin') {
            foreach (['company_id', 'monthly_salary', 'advance_limit_pct', 'is_active', 'company_name', 'company_sector', 'company_contact_email'] as $key) {
                if ($request->has($key)) {
                    throw ValidationException::withMessages([
                        $key => ['Non modifiable pour un super administrateur.'],
                    ]);
                }
            }
        }

        return response()->json($user->fresh()->load('company', 'employee'));
    }
}
