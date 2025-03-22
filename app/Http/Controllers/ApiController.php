<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use Carbon\Carbon;

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

    public function getDashboardStats()
    {
        $stats = [
            'overview' => [
                'total_clients' => Client::count(),
                'total_invoices' => Invoice::count(),
                'total_leads' => Lead::count(),
                'total_amount' => Invoice::sum('total_amount')
            ],
            'invoices_status' => [
                'paid' => [
                    'count' => Invoice::where('status', 'paid')->count(),
                    'amount' => Invoice::where('status', 'paid')->sum('total_amount')
                ],
                'unpaid' => [
                    'count' => Invoice::where('status', 'unpaid')->count(),
                    'amount' => Invoice::where('status', 'unpaid')->sum('total_amount')
                ]
            ],
            'leads_status' => [
                'new' => Lead::where('status', 'new')->count(),
                'converted' => Lead::where('status', 'converted')->count(),
                'lost' => Lead::where('status', 'lost')->count(),
                'total_budget' => Lead::sum('budget'),
                'conversion_rate' => [
                    'percentage' => Lead::where('status', 'converted')->count() / Lead::count() * 100,
                    'total_converted' => Lead::where('status', 'converted')->count(),
                    'total_leads' => Lead::count()
                ]
            ]
        ];

        return response()->json($stats);
    }

    public function getClients()
    {
        $clients = Client::with(['invoices', 'leads'])->get();
        return response()->json($clients);
    }

    public function getInvoices()
    {
        $invoices = Invoice::with(['client'])->get();
        return response()->json($invoices);
    }

    public function getLeads()
    {
        $leads = Lead::with(['client'])->get();
        return response()->json($leads);
    }

    public function updateLeadBudget(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $lead->budget = $request->budget;
        $lead->save();

        return response()->json($lead);
    }

    public function deleteLead($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully']);
    }

    public function updateBudgetAlert(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $lead->budget_alert = $request->budget_alert;
        $lead->save();

        return response()->json($lead);
    }
}