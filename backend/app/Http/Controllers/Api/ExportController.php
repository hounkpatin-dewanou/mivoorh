<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdvanceRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        $companyId = $request->user()->company_id;
        $month = (int) ($request->query('month', now()->month));
        $year = (int) ($request->query('year', now()->year));

        $requests = AdvanceRequest::with('employee.user')
            ->whereHas('employee', fn ($q) => $q->where('company_id', $companyId))
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('created_at')
            ->get();

        $filename = sprintf('demandes-avance-%d-%02d.csv', $year, $month);

        return response()->streamDownload(function () use ($requests) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel Windows
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', 'Employé', 'Email', 'Montant', 'Motif', 'Statut', 'Traité le', 'Commentaire RH'], ';');

            foreach ($requests as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->employee->user->name,
                    $row->employee->user->email,
                    $row->amount,
                    $row->reason,
                    $row->status,
                    $row->reviewed_at?->format('Y-m-d H:i'),
                    $row->review_comment,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
