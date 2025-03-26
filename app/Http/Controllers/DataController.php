<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

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

    public function deleteAll()
    {
        try {
            // Backup clients and contacts data
            $clients = DB::table('clients')->get();
            $contacts = DB::table('contacts')->get();

            // Perform fresh migration with seed
            Artisan::call('migrate:fresh --seed');

            // Restore clients data
            foreach ($clients as $client) {
                DB::table('clients')->insert((array)$client);
            }

            // Restore contacts data
            foreach ($contacts as $contact) {
                DB::table('contacts')->insert((array)$contact);
            }

            return redirect()->back()->with('flash_message', 'All data has been reset successfully (except clients and contacts)');
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

    public function import(Request $request)
    {
        if (!$request->hasFile('files')) {
            return redirect()->back()->with('error', ['message' => 'Aucun fichier sélectionné']);
        }

        $files = $request->file('files');
        $successCount = 0;
        $errors = [];

        foreach ($files as $file) {
            try {
                $result = $this->processImportFile($file);
                if ($result === true) {
                    $successCount++;
                } else {
                    $errors[] = $result;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'message' => $e->getMessage()
                ];
            }
        }

        if (count($errors) > 0) {
            return redirect()->back()->with('error', $errors[0]);
        }

        return redirect()->back()->with('success', "$successCount fichier(s) importé(s) avec succès");
    }

    protected function processImportFile($file)
    {
        if (!$file->isValid() || $file->getClientOriginalExtension() !== 'csv') {
            return [
                'file' => $file->getClientOriginalName(),
                'message' => 'Le fichier doit être au format CSV'
            ];
        }

        try {
            DB::beginTransaction();
            
            $path = $file->getRealPath();
            $handle = fopen($path, 'r');
            $headers = fgetcsv($handle);
            
            if (!$headers) {
                fclose($handle);
                DB::rollBack();
                return [
                    'file' => $file->getClientOriginalName(),
                    'message' => 'Le fichier est vide ou mal formaté'
                ];
            }

            // Nettoyer les en-têtes
            $headers = array_map(function($header) {
                return strtolower(trim($header));
            }, $headers);

            // Analyser les en-têtes pour détecter les colonnes de chaque table
            $tableColumns = $this->detectMultiTableColumns($headers);
            if (empty($tableColumns)) {
                fclose($handle);
                DB::rollBack();
                return [
                    'file' => $file->getClientOriginalName(),
                    'message' => 'Impossible de détecter les tables dans le fichier'
                ];
            }

            $lineNumber = 1;
            while (($data = fgetcsv($handle)) !== false) {
                $lineNumber++;
                
                if (count($data) !== count($headers)) {
                    fclose($handle);
                    DB::rollBack();
                    return [
                        'file' => $file->getClientOriginalName(),
                        'line' => $lineNumber,
                        'message' => 'Nombre de colonnes incorrect'
                    ];
                }

                try {
                    $row = array_combine($headers, $data);
                    
                    // Traiter chaque table détectée
                    foreach ($tableColumns as $tableName => $columns) {
                        $tableData = [];
                        foreach ($columns as $originalCol => $targetCol) {
                            if (isset($row[$originalCol])) {
                                $tableData[$targetCol] = $row[$originalCol];
                            }
                        }
                        
                        if (!empty($tableData)) {
                            // Validation spécifique à la table
                            $validationResult = $this->validateTableData($tableName, $tableData);
                            if ($validationResult !== true) {
                                throw new \Exception("Erreur dans la table $tableName: $validationResult");
                            }

                            // Générer les données manquantes
                            $completeData = $this->generateTableData($tableName, $tableData);
                            
                            // Insertion ou mise à jour
                            $this->insertOrUpdateTableData($tableName, $completeData);
                        }
                    }

                } catch (\Exception $e) {
                    fclose($handle);
                    DB::rollBack();
                    return [
                        'file' => $file->getClientOriginalName(),
                        'line' => $lineNumber,
                        'message' => $e->getMessage()
                    ];
                }
            }
            
            fclose($handle);
            DB::commit();
            return true;

        } catch (\Exception $e) {
            if (isset($handle)) {
                fclose($handle);
            }
            DB::rollBack();
            return [
                'file' => $file->getClientOriginalName(),
                'message' => $e->getMessage()
            ];
        }
    }

    private function detectMultiTableColumns($headers)
    {
        $tableColumns = [];
        
        $columnMappings = [
            'clients' => [
                'client_name' => 'company_name',
                'company_name' => 'company_name',
                'client_email' => 'email',
                'company' => 'company_name',
                'client_address' => 'address',
                'client_city' => 'city',
                'client_zipcode' => 'zipcode',
                'client_vat' => 'vat',
                'client_type' => 'company_type',
                'contact_name' => 'contact_name',
                'contact_email' => 'contact_email',
                'contact_phone' => 'contact_phone'
            ],
            'projects' => [
                'project_title' => 'title',
                'project_name' => 'title',
                'project_description' => 'description',
                'project_deadline' => 'deadline',
                'project_status' => 'status_id'
            ],
            'products' => [
                'product_name' => 'name',
                'produit' => 'name',
                'product' => 'name',
                'product_number' => 'number',
                'quantite' => 'number',
                'product_description' => 'description',
                'product_price' => 'price',
                'prix' => 'price',
                'product_type' => 'default_type'
            ],
            'offers' => [
                'offer_sent_at' => 'sent_at',
                'offer_status' => 'status',
                'offer_source_type' => 'source_type',
                'offer_source_id' => 'source_id'
            ],
            'invoices' => [
                'invoice_number' => 'invoice_number',
                'invoice_sent_at' => 'sent_at',
                'invoice_due_at' => 'due_at',
                'invoice_status' => 'status'
            ],
            'contacts' => [
                'contact_name' => 'name',
                'contact_email' => 'email',
                'contact_phone' => 'primary_number',
                'contact_mobile' => 'secondary_number',
                'contact_is_primary' => 'is_primary'
            ],
            'tasks' => [
                'task_title' => 'title'],
            'leads' => [
                'lead_title' => 'title'],
            'invoice_lines' => [
                'invoice_line_title' => 'title',
                'invoice_line_description' => 'description',
                'invoice_line_quantity' => 'quantity',
                'quantite' => 'quantity',
                'invoice_line_price' => 'price',
                'prix' => 'price',
                ]
        ];

        foreach ($headers as $header) {
            foreach ($columnMappings as $table => $mapping) {
                foreach ($mapping as $csvCol => $dbCol) {
                    if (strpos($header, $csvCol) !== false) {
                        $tableColumns[$table][$header] = $dbCol;
                    }
                }
            }
        }

        return $tableColumns;
    }

    private function validateTableData($tableName, $data)
    {
        switch ($tableName) {
            case 'clients':
                if (empty($data['company_name'])) {
                    return 'Le nom de la société est requis';
                }
                break;

            case 'projects':
            case 'tasks':
            case 'leads':
                if (empty($data['title'])) {
                    return 'Le titre est requis';
                }
                if (isset($data['deadline']) && !$this->isValidDate($data['deadline'])) {
                    return 'La date limite est invalide';
                }
                break;
        }

        return true;
    }

    private function generateTableData($tableName, $data)
    {
        $now = now();
        $external_id = Uuid::uuid4()->toString();
        
        // Valeurs communes
        $baseData = [
            'external_id' => $external_id,
            'created_at' => $now,
            'updated_at' => $now
        ];

        switch ($tableName) {
            case 'clients':
                return array_merge($baseData, [
                    'company_name' => $data['company_name'],
                    'address' => $data['address'] ?? null,
                    'zipcode' => $data['zipcode'] ?? null,
                    'city' => $data['city'] ?? null,
                    'vat' => $data['vat'] ?? null,
                    'company_type' => $data['company_type'] ?? 'company',
                    'client_number' => $this->generateClientNumber(),
                    'user_id' => auth()->id(),
                    'industry_id' => $this->getOrCreateIndustry('Other')
                ]);

            case 'projects':
                $clientId = $this->getOrCreateClient($data['company_name'] ?? null);
                return array_merge($baseData, [
                    'title' => $data['title'],
                    'description' => $data['description'] ?? '',
                    'status_id' => $this->getStatusId($data['status'] ?? 'open', 'project'),
                    'user_assigned_id' => auth()->id(),
                    'user_created_id' => auth()->id(),
                    'client_id' => $clientId,
                    'deadline' => $this->parseDate($data['deadline'] ?? null) ?? now()->addDays(30)
                ]);

            case 'tasks':
                $clientId = $this->getOrCreateClient($data['company_name'] ?? null);
                return array_merge($baseData, [
                    'title' => $data['title'],
                    'description' => $data['description'] ?? '',
                    'status_id' => $this->getStatusId($data['status'] ?? 'open', 'task'),
                    'user_assigned_id' => auth()->id(),
                    'user_created_id' => auth()->id(),
                    'client_id' => $clientId,
                    'project_id' => null,
                    'deadline' => $this->parseDate($data['deadline'] ?? null) ?? now()->addDays(7)
                ]);

            case 'leads':
                $clientId = $this->getOrCreateClient($data['company_name'] ?? null);
                return array_merge($baseData, [
                    'title' => $data['title'],
                    'description' => $data['description'] ?? '',
                    'status_id' => $this->getStatusId($data['status'] ?? 'new', 'lead'),
                    'user_assigned_id' => auth()->id(),
                    'user_created_id' => auth()->id(),
                    'client_id' => $clientId,
                    'qualified' => 0,
                    'deadline' => $this->parseDate($data['deadline'] ?? null) ?? now()->addDays(14)
                ]);
        }

        return $data;
    }

    private function insertOrUpdateTableData($tableName, $data)
    {
        // Vérifier si l'enregistrement existe déjà
        $existing = null;
        switch ($tableName) {
            case 'clients':
                $existing = DB::table($tableName)
                    ->where('company_name', $data['company_name'])
                    ->first();
                break;

            case 'projects':
            case 'tasks':
            case 'leads':
                $existing = DB::table($tableName)
                    ->where('title', $data['title'])
                    ->where('client_id', $data['client_id'])
                    ->first();
                break;
        }

        if ($existing) {
            // Mise à jour
            DB::table($tableName)
                ->where('id', $existing->id)
                ->update(array_merge($data, ['updated_at' => now()]));
            return $existing->id;
        } else {
            // Insertion
            return DB::table($tableName)->insertGetId($data);
        }
    }

    private function getOrCreateClient($companyName)
    {
        if (!$companyName) {
            return $this->getRandomClientId();
        }

        $client = DB::table('clients')->where('company_name', $companyName)->first();
        if (!$client) {
            return DB::table('clients')->insertGetId([
                'external_id' => Uuid::uuid4()->toString(),
                'company_name' => $companyName,
                'company_type' => 'company',
                'client_number' => $this->generateClientNumber(),
                'user_id' => auth()->id(),
                'industry_id' => $this->getOrCreateIndustry('Other'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        return $client->id;
    }

    private function generateClientNumber()
    {
        $lastClient = DB::table('clients')->orderBy('client_number', 'desc')->first();
        return $lastClient ? ($lastClient->client_number + 1) : 1000;
    }

    private function getOrCreateIndustry($name)
    {
        $industry = DB::table('industries')->where('name', $name)->first();
        if (!$industry) {
            return DB::table('industries')->insertGetId([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        return $industry->id;
    }

    private function getStatusId($status, $type)
    {
        $statusMap = [
            'task' => [
                'open' => 1,
                'completed' => 2,
                'pending' => 3,
                'closed' => 4
            ],
            'lead' => [
                'new' => 1,
                'in_progress' => 2,
                'completed' => 3,
                'closed' => 4
            ],
            'project' => [
                'open' => 1,
                'completed' => 2,
                'pending' => 3,
                'closed' => 4
            ]
        ];

        return $statusMap[$type][$status] ?? 1;
    }

    private function getRandomClientId()
    {
        $client = DB::table('clients')->inRandomOrder()->first();
        return $client ? $client->id : null;
    }

    private function parseDate($date)
    {
        if (!$date) return null;
        
        $formats = ['Y-m-d', 'd/m/Y', 'Y/m/d', 'd-m-Y'];
        foreach ($formats as $format) {
            $d = \DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) === $date) {
                return $d->format('Y-m-d H:i:s');
            }
        }
        return null;
    }

    private function isValidDate($date)
    {
        return $this->parseDate($date) !== null;
    }
}