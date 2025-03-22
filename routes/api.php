<?php

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Routes publiques
Route::post('/login', [ApiController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [ApiController::class, 'getAllUsers']);
    Route::get('/users/{id}', [ApiController::class, 'getUserById']);
    Route::get('/dashboard/stats', [ApiController::class, 'getDashboardStats']);
    Route::get('/clients', [ApiController::class, 'getClients']);
    Route::get('/invoices', [ApiController::class, 'getInvoices']);
    Route::get('/leads', [ApiController::class, 'getLeads']);
    Route::put('/leads/{id}/budget', [ApiController::class, 'updateLeadBudget']);
    Route::delete('/leads/{id}', [ApiController::class, 'deleteLead']);
    Route::put('/leads/{id}/alert', [ApiController::class, 'updateBudgetAlert']);
});
