<?php

/**
 * Routes API MivooRH (préfixe /api appliqué par Laravel).
 *
 * - Public : santé, liste entreprises actives, login, register
 * - auth:sanctum : profil, notifications
 * - role:superadmin|hr|employee : espaces admin, RH, employé
 */

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdvanceRequestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\Company;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

// Entreprises actives (inscription employé)
Route::get('/companies', fn () => Company::where('is_active', true)->select('id', 'name', 'sector')->orderBy('name')->get());

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user()->load('company', 'employee'));
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    Route::middleware('role:superadmin')->prefix('admin')->group(function () {
        Route::get('/companies', [CompanyController::class, 'index']);
        Route::get('/stats', [CompanyController::class, 'stats']);
        Route::post('/companies', [CompanyController::class, 'store']);
        Route::put('/companies/{company}', [CompanyController::class, 'update']);
        Route::patch('/companies/{company}/toggle', [CompanyController::class, 'toggle']);
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy']);

        Route::get('/employees', [AdminController::class, 'employeesIndex']);
        Route::post('/employees', [AdminController::class, 'storeEmployee']);
        Route::put('/employees/{employee}', [AdminController::class, 'updateEmployee']);
        Route::delete('/employees/{employee}', [AdminController::class, 'destroyEmployee']);

        Route::get('/advance-requests', [AdminController::class, 'advanceRequestsIndex']);
        Route::post('/advance-requests', [AdminController::class, 'storeAdvanceRequest']);
        Route::put('/advance-requests/{advanceRequest}', [AdminController::class, 'updateAdvanceRequest']);
        Route::delete('/advance-requests/{advanceRequest}', [AdminController::class, 'destroyAdvanceRequest']);
        Route::patch('/advance-requests/{advanceRequest}/approve', [AdminController::class, 'approveAdvanceRequest']);
        Route::patch('/advance-requests/{advanceRequest}/refuse', [AdminController::class, 'refuseAdvanceRequest']);
    });

    Route::middleware('role:hr')->prefix('hr')->group(function () {
        Route::get('/stats', [EmployeeController::class, 'hrStats']);
        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::post('/employees', [EmployeeController::class, 'store']);
        Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
        Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
        Route::patch('/employees/{employee}/deactivate', [EmployeeController::class, 'deactivate']);
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
        Route::get('/advance-requests', [AdvanceRequestController::class, 'hrIndex']);
        Route::patch('/advance-requests/{advanceRequest}/approve', [AdvanceRequestController::class, 'approve']);
        Route::patch('/advance-requests/{advanceRequest}/refuse', [AdvanceRequestController::class, 'refuse']);
        Route::get('/export/csv', [ExportController::class, 'csv']);
    });

    Route::middleware('role:employee')->prefix('employee')->group(function () {
        Route::get('/balance', [AdvanceRequestController::class, 'employeeBalance']);
        Route::get('/advance-requests', [AdvanceRequestController::class, 'employeeIndex']);
        Route::post('/advance-requests', [AdvanceRequestController::class, 'store']);
    });
});
