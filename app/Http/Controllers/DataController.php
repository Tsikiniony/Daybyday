<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GenericImport;

class DataController extends Controller
{
    protected $importableTables = [
        'clients' => 'Clients',
        'projects' => 'Projects',
        'tasks' => 'Tasks',
        'leads' => 'Leads',
        'users' => 'Users',
        'departments' => 'Departments',
        'industries' => 'Industries',
        'products' => 'Products',
        'invoices' => 'Invoices',
        'appointments' => 'Appointments',
        'orders' => 'Orders',
        'payments' => 'Payments',
        'shipments' => 'Shipments',
        'reviews' => 'Reviews'
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole(['administrator', 'owner'])) {
                return redirect()->back()->with('flash_message_warning', 'Access denied. Administrator rights required.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $importableTables = [
            'clients' => 'Clients',
            'projects' => 'Projects',
            'tasks' => 'Tasks',
            'leads' => 'Leads',
            'users' => 'Users',
            'departments' => 'Departments',
            'industries' => 'Industries',
            'products' => 'Products',
            'invoices' => 'Invoices',
            'appointments' => 'Appointments',
            'orders' => 'Orders',
            'payments' => 'Payments',
            'shipments' => 'Shipments',
            'reviews' => 'Reviews'
        ];
        return view('pages.datagenerate', compact('importableTables'));
    }

    public function importFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'table' => 'required|string',
            'has_headers' => 'nullable'
        ]);

        try {
            $file = $request->file('file');
            $table = $request->input('table');
            $hasHeaders = $request->has('has_headers');

            // Vérifier si la table existe
            if (!Schema::hasTable($table)) {
                return redirect()->back()->with('flash_message_warning', 'La table spécifiée n\'existe pas.');
            }

            // Vérifier le type de fichier
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['csv', 'xlsx', 'xls'])) {
                return redirect()->back()->with('flash_message_warning', 
                    'Format de fichier invalide: .' . $extension . '. Utilisez un fichier CSV ou Excel (.csv, .xlsx, .xls).');
            }

            // Traiter le fichier CSV
            if ($extension === 'csv') {
                $importCount = $this->importCsv($file, $table, $hasHeaders);
                return redirect()->back()->with('flash_message', "Import réussi de {$importCount} enregistrements dans {$table}!");
            } else {
                return redirect()->back()->with('flash_message_warning', 'L\'import Excel n\'est pas encore implémenté. Veuillez utiliser le format CSV.');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('flash_message_warning', 'Erreur lors de l\'importation: ' . $e->getMessage());
        }
    }

    protected function importCsv($file, $table, $hasHeaders = true)
    {
        $handle = fopen($file->getPathname(), 'r');
        $importCount = 0;
        $headers = [];
        $lineNumber = 0;
        
        // Obtenir les colonnes de la table
        $tableColumns = Schema::getColumnListing($table);
        \Log::info('Colonnes de la table ' . $table . ': ' . json_encode($tableColumns));
        
        // Détecter le délimiteur
        $firstLine = fgets($handle);
        rewind($handle);
        
        $delimiter = ',';
        if (strpos($firstLine, ';') !== false) {
            $delimiter = ';';
        } elseif (strpos($firstLine, "\t") !== false) {
            $delimiter = "\t";
        }
        
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;
            
            if (count($data) <= 1 && empty($data[0])) {
                continue;
            }
            
            if ($lineNumber === 1 && $hasHeaders) {
                $headers = array_map(function($header) {
                    return trim(str_replace(["\r", "\n", "\t"], '', $header));
                }, $data);
                
                $headerMapping = [];
                foreach ($headers as $index => $header) {
                    if (in_array($header, $tableColumns)) {
                        $headerMapping[$index] = $header;
                    }
                }
                
                \Log::info('En-têtes détectés: ' . json_encode($headers));
                \Log::info('Mapping des en-têtes: ' . json_encode($headerMapping));
                continue;
            }
            
            if (empty($headers)) {
                $headers = $tableColumns;
                $headerMapping = array_combine(array_keys($headers), $headers);
            }
            
            $row = [];
            foreach ($data as $index => $value) {
                if (isset($headerMapping[$index])) {
                    $value = trim($value);
                    if (!in_array($headerMapping[$index], ['id', 'created_at', 'updated_at'])) {
                        $row[$headerMapping[$index]] = $value;
                    }
                }
            }
            
            if (in_array('external_id', $tableColumns) && empty($row['external_id'])) {
                $row['external_id'] = Uuid::uuid4()->toString();
            }
            
            if (in_array('created_at', $tableColumns)) {
                $row['created_at'] = now();
            }
            if (in_array('updated_at', $tableColumns)) {
                $row['updated_at'] = now();
            }
            
            if (!empty($row)) {
                try {
                    \Log::info('Tentative d\'insertion: ' . json_encode($row));
                    DB::table($table)->insert($row);
                    $importCount++;
                } catch (\Exception $e) {
                    \Log::error('Erreur lors de l\'importation: ' . $e->getMessage());
                    \Log::error('Données: ' . json_encode($row));
                }
            } else {
                \Log::warning('Ligne ignorée car tableau vide après traitement: ' . json_encode($data));
            }
        }
        
        fclose($handle);
        return $importCount;
    }

    public function deleteAll()
    {
        try {
            Artisan::call('migrate:fresh --seed');
            return redirect()->back()->with('flash_message', 'All data has been reset successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('flash_message_warning', 'Error resetting data: ' . $e->getMessage());
        }
    }

    public function generateTestData()
    {
        try {
            Artisan::call('db:seed --class=DummyDatabaseSeeder');
            return redirect()->back()->with('flash_message', 'Demo data generated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('flash_message_warning', 'Error generating demo data: ' . $e->getMessage());
        }
    }

}