<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class DataController extends Controller
{
    public function deleteAll()
    {
        try {
            // Réinitialiser complètement la base de données et exécuter tous les seeders
            Artisan::call('migrate:fresh --seed');
            return redirect()->back()->with('flash_message', 'All data has been reset successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('flash_message_warning', 'Error resetting data: ' . $e->getMessage());
        }
    }
}