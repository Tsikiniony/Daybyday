<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GenericImport implements ToCollection, WithHeadingRow
{
    protected $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function collection(Collection $rows)
    {
        $tableColumns = Schema::getColumnListing($this->table);
        
        foreach ($rows as $row) {
            $data = [];
            foreach ($tableColumns as $column) {
                if (isset($row[$column])) {
                    $data[$column] = $row[$column];
                }
            }
            
            if (!empty($data)) {
                // Si c'est la table users, on ajoute les champs requis
                if ($this->table === 'users' && !isset($data['password'])) {
                    $data['password'] = bcrypt('password');  // Mot de passe par défaut
                }
                if ($this->table === 'users' && !isset($data['remember_token'])) {
                    $data['remember_token'] = Str::random(10);
                }
                
                DB::table($this->table)->insert($data);
            }
        }
    }
}