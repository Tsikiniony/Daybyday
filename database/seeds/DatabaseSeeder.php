<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Désactiver temporairement les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Nettoyer les tables dans le bon ordre
        DB::table('clients')->truncate();
        DB::table('users')->truncate();
        DB::table('departments')->truncate();  // Ajout de cette ligne

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Exécuter les seeders une seule fois
        $this->call(StatusTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call('IndustriesTableSeeder');
        $this->call('DepartmentsTableSeeder');
        $this->call('SettingsTableSeeder');
        $this->call('PermissionsTableSeeder');
        $this->call('RolesTablesSeeder');
        $this->call('RolePermissionTableSeeder');
        $this->call('UserRoleTableSeeder');
    }
}
