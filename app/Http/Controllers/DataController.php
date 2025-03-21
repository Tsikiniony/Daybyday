<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataController extends Controller
{
    public function deleteAll()
    {
        try {
            // Désactiver les contraintes de clé étrangère
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Liste des tables à ne pas vider
            $excludedTables = ['migrations', 'roles', 'permissions'];

            // Récupérer toutes les tables
            $tables = DB::select('SHOW TABLES');

            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                
                if (!in_array($tableName, $excludedTables)) {
                    DB::table($tableName)->truncate();
                }
            }

            // Réactiver les contraintes de clé étrangère
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return redirect()->back()->with('flash_message', 'All data has been deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('flash_message_warning', 'Error deleting data: ' . $e->getMessage());
        }
    }
}