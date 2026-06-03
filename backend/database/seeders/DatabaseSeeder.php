<?php

namespace Database\Seeders;

use App\Models\AdvanceRequest;
use App\Models\AppNotification;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Jeu de données de démonstration (2 entreprises, 10 employés, demandes exemple).
     * Idempotent : ne recrée pas les comptes si le seed a déjà été exécuté (redémarrage Docker).
     */
    public function run(): void
    {
        if (User::where('email', 'admin@mivoopay.bj')->exists()) {
            return;
        }

        $superadmin = User::create([
            'name' => 'Super Admin MivooPay',
            'email' => 'admin@mivoopay.bj',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'company_id' => null,
        ]);

        $companiesData = [
            [
                'name' => 'Société Agro Bénin',
                'sector' => 'Agroalimentaire',
                'contact_email' => 'rh@agrobenin.bj',
            ],
            [
                'name' => 'TechHub Cotonou',
                'sector' => 'Technologie',
                'contact_email' => 'rh@techhub.bj',
            ],
        ];

        foreach ($companiesData as $index => $data) {
            $company = Company::create([
                ...$data,
                'is_active' => true,
            ]);

            $hr = User::create([
                'name' => 'RH ' . $company->name,
                'email' => 'rh' . ($index + 1) . '@mivoorh.test',
                'password' => Hash::make('password'),
                'role' => 'hr',
                'company_id' => $company->id,
            ]);

            for ($i = 1; $i <= 5; $i++) {
                $user = User::create([
                    'name' => "Employé {$i} — {$company->name}",
                    'email' => "emp{$index}_{$i}@mivoorh.test",
                    'password' => Hash::make('password'),
                    'role' => 'employee',
                    'company_id' => $company->id,
                ]);

                $employee = Employee::create([
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'monthly_salary' => 150000 + ($i * 25000),
                    'advance_limit_pct' => 40,
                    'is_active' => true,
                ]);

                if ($i <= 2) {
                    AdvanceRequest::create([
                        'employee_id' => $employee->id,
                        'amount' => 50000 * $i,
                        'reason' => 'Frais médicaux urgents',
                        'status' => 'pending',
                    ]);
                }

                if ($i === 3) {
                    AdvanceRequest::create([
                        'employee_id' => $employee->id,
                        'amount' => 75000,
                        'reason' => 'Scolarité des enfants',
                        'status' => 'approved',
                        'reviewed_by' => $hr->id,
                        'reviewed_at' => now()->subDays(2),
                    ]);

                    AppNotification::create([
                        'user_id' => $user->id,
                        'message' => 'Votre demande d\'avance de 75 000 FCFA a été approuvée.',
                        'is_read' => false,
                    ]);
                }

                if ($i === 4) {
                    AdvanceRequest::create([
                        'employee_id' => $employee->id,
                        'amount' => 120000,
                        'reason' => 'Réparation véhicule',
                        'status' => 'refused',
                        'reviewed_by' => $hr->id,
                        'reviewed_at' => now()->subDay(),
                    ]);

                    AppNotification::create([
                        'user_id' => $user->id,
                        'message' => 'Votre demande d\'avance de 120 000 FCFA a été refusée.',
                        'is_read' => true,
                    ]);
                }
            }
        }

        unset($superadmin);
    }
}
