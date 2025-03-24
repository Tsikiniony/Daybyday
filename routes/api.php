<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
Route::get('/clients', [ApiController::class, 'getAllClients']);
Route::get('/projects', [ApiController::class, 'getAllProjects']);
Route::get('/tasks', [ApiController::class, 'getAllTasks']);
Route::get('/offers', [ApiController::class, 'getAllOffers']);
Route::get('/invoices', [ApiController::class, 'getAllInvoices']);
Route::get('/payments', [ApiController::class, 'getAllPayments']);

// Gestion des CORS pour les requêtes OPTIONS
Route::options('/{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', 'http://localhost:8080')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Accept');
})->where('any', '.*');

// Routes pour les paiements
Route::get('/payments/{external_id}', [ApiController::class, 'getPayment']);
Route::put('/payments/{external_id}', [ApiController::class, 'updatePayment']);
Route::delete('/payments/{external_id}', [ApiController::class, 'deletePayment']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
});
