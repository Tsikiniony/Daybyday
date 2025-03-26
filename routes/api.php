<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Api\DiscountRateController;
use App\Models\DiscountRate;
use App\Models\Invoice;

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
// Routes pour les clients
Route::get('/clients', [ApiController::class, 'getAllClients']);
Route::get('/clients/{external_id}', [ApiController::class, 'getClient']);
Route::get('/projects', [ApiController::class, 'getAllProjects']);
Route::get('/projects/{external_id}', [ApiController::class, 'getProject']);
Route::get('/tasks', [ApiController::class, 'getAllTasks']);
Route::get('/offers', [ApiController::class, 'getAllOffers']);
Route::get('/invoices', [ApiController::class, 'getAllInvoices']);
Route::get('/invoices/{external_id}', [ApiController::class, 'getInvoice']);
Route::get('/payments', [ApiController::class, 'getAllPayments']);
Route::get('/payments/{external_id}', [ApiController::class, 'getPayment']);
Route::get('/users', [ApiController::class, 'getAllUsers']);
Route::get('/users/{external_id}', [ApiController::class, 'getUser']);
// Gestion des CORS pour les requêtes OPTIONS
Route::options('/{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', 'http://localhost:8080')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Accept');
})->where('any', '.*');

// Routes pour les paiements
Route::put('/payments/{external_id}', [ApiController::class, 'updatePayment']);
Route::delete('/payments/{external_id}', [ApiController::class, 'deletePayment']);

Route::prefix('discount-rates')->group(function () {
    Route::get('/', [DiscountRateController::class, 'index']);
    Route::post('/', [DiscountRateController::class, 'store']);
    Route::get('/active', [DiscountRateController::class, 'active']);
    Route::get('/{id}', [DiscountRateController::class, 'show']);
    Route::put('/{id}', [DiscountRateController::class, 'update']);
    Route::put('/toggle/{id}', [DiscountRateController::class, 'toggle']);
});

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
});
