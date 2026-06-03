<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdvanceRequest;
use App\Models\AppNotification;
use App\Models\Employee;
use Illuminate\Http\Request;

/**
 * Demandes d'avance sur salaire : solde employé, création, validation RH.
 */
class AdvanceRequestController extends Controller
{
    public function employeeBalance(Request $request)
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Profil employé introuvable.'], 404);
        }

        return response()->json([
            'available_balance' => $this->computeAvailableBalance($employee),
            'monthly_salary' => $employee->monthly_salary,
            'advance_limit_pct' => $employee->advance_limit_pct,
        ]);
    }

    public function employeeIndex(Request $request)
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json([]);
        }

        return response()->json(
            $employee->advanceRequests()->orderByDesc('created_at')->get()
        );
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        if (! $employee || ! $employee->is_active) {
            return response()->json(['message' => 'Compte employé inactif.'], 403);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $available = $this->computeAvailableBalance($employee);
        if ($data['amount'] > $available) {
            return response()->json([
                'message' => 'Montant supérieur au solde disponible.',
                'available_balance' => $available,
            ], 422);
        }

        $advance = AdvanceRequest::create([
            'employee_id' => $employee->id,
            'amount' => $data['amount'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        return response()->json($advance, 201);
    }

    public function hrIndex(Request $request)
    {
        $status = $request->query('status');

        $query = AdvanceRequest::with(['employee.user'])
            ->whereHas('employee', fn ($q) => $q->where('company_id', $request->user()->company_id))
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        return response()->json($query->get());
    }

    public function approve(Request $request, AdvanceRequest $advanceRequest)
    {
        $this->authorizeHr($request, $advanceRequest);

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

        return response()->json($advanceRequest->load('employee.user'));
    }

    public function refuse(Request $request, AdvanceRequest $advanceRequest)
    {
        $this->authorizeHr($request, $advanceRequest);

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

        return response()->json($advanceRequest->load('employee.user'));
    }

    /**
     * Solde = (salaire/30 × jours écoulés × plafond %) − avances pending/approved du mois en cours.
     */
    private function computeAvailableBalance(Employee $employee): float
    {
        $daysWorked = now()->day;
        $dailyRate = (float) $employee->monthly_salary / 30;
        $ceiling = $dailyRate * $daysWorked * ($employee->advance_limit_pct / 100);

        $used = AdvanceRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return max(0, round($ceiling - $used, 2));
    }

    private function authorizeHr(Request $request, AdvanceRequest $advanceRequest): void
    {
        if ($advanceRequest->status !== 'pending') {
            abort(422, 'Cette demande a déjà été traitée.');
        }

        if ($advanceRequest->employee->company_id !== $request->user()->company_id) {
            abort(403, 'Demande hors de votre entreprise.');
        }
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
