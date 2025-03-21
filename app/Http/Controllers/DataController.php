<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Add this line
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DataController extends Controller
{
    public function __construct()
    {
        // Vérifier que l'utilisateur est admin pour toutes les méthodes
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole(['administrator', 'owner'])) {
                return redirect()->back()->with('flash_message_warning', 'Access denied. Administrator rights required.');
            }
            return $next($request);
        });
    }
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

    public function generateTestData()
    {
        try {
            // Générer les données de démonstration
            Artisan::call('db:seed --class=DummyDatabaseSeeder');
            
            return redirect()->back()->with('flash_message', 'Demo data generated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('flash_message_warning', 'Error generating demo data: ' . $e->getMessage());
        }
    }

    public function importCSV(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'csv_file' => 'required|file|mimes:csv,xls,xlsx|max:10240',
                'table_name' => 'required|string'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('flash_message_warning', $validator->errors()->first());
            }

            // Vérifier si la table existe
            $tableName = $request->input('table_name');
            if (!Schema::hasTable($tableName)) {
                return redirect()->back()->with('flash_message_warning', "Table '$tableName' does not exist");
            }

            $file = $request->file('csv_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Vérifier les en-têtes
            $headers = array_shift($rows);
            $tableColumns = Schema::getColumnListing($tableName);
            $invalidColumns = array_diff($headers, $tableColumns);
            
            if (!empty($invalidColumns)) {
                return redirect()->back()->with('flash_message_warning', 
                    'Invalid columns found: ' . implode(', ', $invalidColumns));
            }

            DB::beginTransaction();
            
            foreach ($rows as $rowIndex => $row) {
                try {
                    $rowNumber = $rowIndex + 2; // +2 car première ligne = en-têtes et index commence à 0
                    $data = array_combine($headers, $row);
                    
                    // Ajouter external_id si nécessaire
                    if (in_array('external_id', $tableColumns)) {
                        $data['external_id'] = Uuid::uuid4();
                    }
                    
                    // Hasher le mot de passe si c'est un utilisateur
                    if ($tableName === 'users' && isset($data['password'])) {
                        $data['password'] = bcrypt($data['password']);
                    }

                    DB::table($tableName)->insert($data);
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->back()->with('flash_message_warning', 
                        "Error at row $rowNumber: " . $e->getMessage());
                }
            }

            DB::commit();
            return redirect()->back()->with('flash_message', 'Data imported successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('flash_message_warning', 'Error: ' . $e->getMessage());
        }
    }
}