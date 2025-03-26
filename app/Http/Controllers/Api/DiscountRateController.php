<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscountRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscountRateController extends Controller
{
    public function index()
    {
        $rates = DiscountRate::orderBy('created_at', 'desc')->get();
        return response()->json($rates);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Si on active ce taux, désactiver tous les autres
        if ($request->is_active) {
            DiscountRate::where('is_active', true)->update(['is_active' => false]);
        }

        $rate = DiscountRate::create([
            'rate' => $request->rate,
            'description' => $request->description,
            'is_active' => $request->is_active ?? false
        ]);

        return response()->json($rate, 201);
    }

    public function show($id)
    {
        $rate = DiscountRate::findOrFail($id);
        return response()->json($rate);
    }

    public function update(Request $request, $id)
    {
        $rate = DiscountRate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Si on active ce taux, désactiver tous les autres
        if ($request->is_active && !$rate->is_active) {
            DiscountRate::where('is_active', true)->update(['is_active' => false]);
        }

        $rate->update([
            'rate' => $request->rate,
            'description' => $request->description,
            'is_active' => $request->is_active ?? $rate->is_active
        ]);

        return response()->json($rate);
    }

    public function toggle($id)
    {
        $rate = DiscountRate::findOrFail($id);
        
        // Si on active ce taux, désactiver tous les autres
        if (!$rate->is_active) {
            DiscountRate::where('is_active', true)->update(['is_active' => false]);
        }

        $rate->is_active = !$rate->is_active;
        $rate->save();

        return response()->json($rate);
    }

    public function active()
    {
        $rate = DiscountRate::where('is_active', true)->first();
        return response()->json([
            'rate' => $rate ? $rate->rate : "0.00"
        ]);
    }
}