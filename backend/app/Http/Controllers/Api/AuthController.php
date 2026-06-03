<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Connexion, inscription (RH crée une entreprise / employé la rejoint) et déconnexion Sanctum.
 */
class AuthController extends Controller
{
    /** Vérifie identifiants, entreprise active et statut employé avant émission du token. */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        if (in_array($user->role, ['hr', 'employee'], true)) {
            if (! $user->company?->is_active) {
                throw ValidationException::withMessages([
                    'email' => ['L\'entreprise est désactivée. Contactez MivooPay.'],
                ]);
            }
            if ($user->role === 'employee' && (! $user->employee || ! $user->employee->is_active)) {
                throw ValidationException::withMessages([
                    'email' => ['Votre compte employé est inactif.'],
                ]);
            }
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user->load('company', 'employee'),
            'token' => $token,
        ]);
    }

    /** Inscription : rôle hr (nouvelle Company) ou employee (company_id + fiche salariale). */
    public function register(Request $request)
    {
        $base = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['hr', 'employee'])],
        ]);

        if ($base['role'] === 'hr') {
            if ($request->filled('company_id')) {
                throw ValidationException::withMessages([
                    'company_id' => ['Un responsable RH crée toujours sa propre entreprise à l\'inscription.'],
                ]);
            }

            $data = array_merge($base, $request->validate([
                'company_name' => ['required', 'string', 'max:255'],
                'company_sector' => ['nullable', 'string', 'max:255'],
                'company_contact_email' => ['nullable', 'email'],
            ]));

            $company = Company::create([
                'name' => $data['company_name'],
                'sector' => $data['company_sector'] ?? null,
                'contact_email' => $data['company_contact_email'] ?? $data['email'],
                'is_active' => true,
            ]);
        } else {
            if ($request->boolean('create_company') || $request->filled('company_name')) {
                throw ValidationException::withMessages([
                    'role' => ['Les employés rejoignent une entreprise existante.'],
                ]);
            }

            $data = array_merge($base, $request->validate([
                'company_id' => ['required', 'exists:companies,id'],
                'monthly_salary' => ['required', 'numeric', 'min:0'],
                'advance_limit_pct' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]));

            $company = Company::findOrFail($data['company_id']);
            if (! $company->is_active) {
                throw ValidationException::withMessages([
                    'company_id' => ['Cette entreprise n\'accepte pas de nouvelles inscriptions.'],
                ]);
            }
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'company_id' => $company->id,
        ]);

        if ($data['role'] === 'employee') {
            Employee::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'monthly_salary' => $data['monthly_salary'],
                'advance_limit_pct' => $data['advance_limit_pct'] ?? 40,
                'is_active' => true,
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user->load('company', 'employee'),
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }
}
