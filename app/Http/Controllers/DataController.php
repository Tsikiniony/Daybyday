<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole(['administrator', 'owner'])) {
                return redirect()->back()->with('flash_message_warning', 'Access denied. Administrator rights required.');
            }
            return $next($request);
        });
    }

    // Add the new functions here
    public function showImport()
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
            'appointments' => 'Appointments'
        ];
        
        return view('init_data.import', compact('importableTables'));
    }

    public function showExport()
    {
        $exportableTables = [
            'clients' => 'Clients',
            'projects' => 'Projects',
            'tasks' => 'Tasks',
            'leads' => 'Leads',
            'users' => 'Users',
            'departments' => 'Departments',
            'industries' => 'Industries',
            'products' => 'Products',
            'invoices' => 'Invoices',
            'appointments' => 'Appointments'
        ];
        
        return view('init_data.export', compact('exportableTables'));
    }

    public function showReset()
    {
        $resettableTables = [
            'clients' => 'Clients',
            'projects' => 'Projects',
            'tasks' => 'Tasks',
            'leads' => 'Leads',
            'users' => 'Users (except current user)',
            'departments' => 'Departments',
            'products' => 'Products',
            'invoices' => 'Invoices',
            'appointments' => 'Appointments',
            'comments' => 'Comments',
            'invoice_lines' => 'Invoice Lines',
            'offers' => 'Offers'
        ];
        
        return view('init_data.reset', compact('resettableTables'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_type' => 'required|string',
            'file' => 'required|file',
            'has_headers' => 'nullable'
        ]);

        try {
            if (!Schema::hasTable($request->import_type)) {
                return redirect()->back()->with('flash_message_warning', 'Table not found: ' . $request->import_type);
            }
            
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, ['csv', 'xlsx', 'xls'])) {
                return redirect()->back()->with('flash_message_warning', 
                    'Invalid file format: .' . $extension . '. Please upload a CSV or Excel file (.csv, .xlsx, .xls).');
            }
            
            $hasHeaders = $request->has('has_headers');
            
            if ($extension === 'csv') {
                // Pass the correct number of arguments
                $importCount = $this->importCsv($file, $request->import_type, $hasHeaders);
                return redirect()->back()->with('flash_message', "Successfully imported {$importCount} records into {$request->import_type}!");
            } else {
                return redirect()->back()->with('flash_message_warning', 'Excel import is not implemented yet. Please use CSV format.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('flash_message_warning', 'Error importing data: ' . $e->getMessage());
        }
    }

    protected function importCsv($file, $table, $hasHeaders = true)
    {
        $handle = fopen($file->getPathname(), 'r');
        $importCount = 0;
        $headers = [];
        $lineNumber = 0;
        
        $tableColumns = Schema::getColumnListing($table);
        \Log::info('Colonnes de la table ' . $table . ': ' . json_encode($tableColumns));
        
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
                $headers = array_map('trim', $data);
                continue;
            }
            
            $data = array_combine($headers, $data);
            DB::table($table)->insert($data);
            $importCount++;
        }
        
        fclose($handle);
        return $importCount;
    }
}