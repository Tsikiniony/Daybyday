<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\Offer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'token' => $token,
                'user' => $user
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function getAllClients(){
        $clients = Client::all();
        return response()->json($clients);
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function getAllProjects(){
        $projects = Project::with('client')->get();
        return response()->json($projects);
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function getAllTasks(){
        $tasks = Task::with('project','client')->get();
        return response()->json($tasks);
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function getAllOffers(){
        $offers = Offer::with('client')->get();
        return response()->json($offers);
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function getAllInvoices(){
        $invoices = Invoice::all();
        return response()->json($invoices);
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function getAllPayments(){
        $payments = Payment::all();
        return response()->json($payments);
    }

    public function updatePayment(Request $request, $external_id)
    {
        $payment = Payment::where('external_id', $external_id)->firstOrFail();
        $payment->amount = $request->amount;
        $payment->save();
        return $this->addCorsHeaders(response()->json($payment));
    }

    public function deletePayment($external_id)
    {
        try {
            \Log::info('Tentative de suppression du paiement avec external_id: ' . $external_id);
            
            $payment = Payment::where('external_id', $external_id)->first();
            
            if (!$payment) {
                \Log::error('Paiement non trouvé avec external_id: ' . $external_id);
                return $this->addCorsHeaders(response()->json([
                    'message' => 'Paiement non trouvé',
                    'external_id' => $external_id
                ], 404));
            }

            \Log::info('Paiement trouvé, ID: ' . $payment->id);
            
            // Suppression du paiement
            $deleted = $payment->delete();
            
            if (!$deleted) {
                \Log::error('Échec de la suppression du paiement');
                return $this->addCorsHeaders(response()->json([
                    'message' => 'Échec de la suppression du paiement'
                ], 500));
            }

            \Log::info('Paiement supprimé avec succès');
            
            return $this->addCorsHeaders(response()->json([
                'message' => 'Paiement supprimé avec succès',
                'payment_id' => $payment->id,
                'external_id' => $external_id
            ]));
        } catch (\Exception $e) {
            \Log::error('Exception lors de la suppression: ' . $e->getMessage());
            return $this->addCorsHeaders(response()->json([
                'message' => 'Erreur lors de la suppression du paiement',
                'error' => $e->getMessage()
            ], 500));
        }
    }

    public function getPayment($external_id)
    {
        $payment = Payment::where('external_id', $external_id)->firstOrFail();
        return $this->addCorsHeaders(response()->json($payment));
    }

    private function addCorsHeaders($response)
    {
        return $response
            ->header('Access-Control-Allow-Origin', 'http://localhost:8080')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept');
    }

    public function optionsPayment($external_id)
    {
        return $this->addCorsHeaders(response()->json(['message' => 'OPTIONS']));
    }

    public function optionsUpdatePayment($external_id)
    {
        return $this->addCorsHeaders(response()->json(['message' => 'OPTIONS']));
    }

    public function optionsDeletePayment($external_id)
    {
        return $this->addCorsHeaders(response()->json(['message' => 'OPTIONS']));
    }
}