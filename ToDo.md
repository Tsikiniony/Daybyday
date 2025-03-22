# Installation

[x] Requires
_Node 16
_PHP 17

[x] Create an empty database
_Create database MySql : forge

[x] Config
_Change database config for Mysql : config/database
_Create file .env
_Copy .env.example to .env

[x] Commande
_Clear db : php artisan migrate:fresh
_insert default data (admin,...): php artisan db:seed 
_Insert data  : php artisan db:seed --class=DummyDatabaseSeeder

## Debug

[x] Error 404 
_Delete Project from : http://127.0.0.1:8000/projects :
    -Function destroy in controller/ProjectsController :
        return redirect()->back(); => return redirect()->route('projects.index');

[x] Error upload file
_In config/filesystems :
    -default : 's3' => default : 'public' or 'local'

[x] User duplicate entry
_In UsersController : 
    -Add a try/catch block :
        try{
            $user->save();
        }
        catch (Exception $e) {
            Session::flash('flash_message', __('Email '.$user->email.' already exists.'));
            return view('users.create')
            ->withRoles($this->allRoles()->pluck('display_name', 'id'))
            ->withDepartments(Department::pluck('name', 'id'));
        }

[x] Redirect error 
_Redirection error after absence detroy
    -return response("OK"); => Session::flash('flash_message', __('Absence      deleted'));
    return redirect()->back();

## functions
[] Delete all data
    -create new controller datacontroller for the function
    -create new route for the function
    -create button clear data 
[] Data import
    -install php extension
    -create function import file : csv, xlx, ...
    -create new navigation import data
    -create new page for data import
    -create button import function
[] Generate data
    -create function generate data in datacontroller
    -create new navigation generate data 
    -create new page for data generate
    -create button generate function 
    -configure root(redirection controller, get function in datacontroller)


### Create API configuration
[] Create API configuration
    -import routes/api.php

### Login with NewApp
[] Login with NewApp
    -create new page for login with NewApp
    -edit app.php (provider)
    -add middleware Sanctum in kernel.php
    -create class Model PersonalAccessToken
    -Modif AppServiceProvider

[x] Configuration de base
    - [x] Installation de Sanctum
    - [x] Publication des configurations
    - [x] Migration des tables

[x] Modèles et Controllers
    - [x] Création du modèle PersonalAccessToken
    - [x] Modification du modèle User
    - [x] Configuration du ApiController

[x] Configuration
    - [x] Configuration de Sanctum (sanctum.php)
    - [x] Modification de AppServiceProvider
    - [x] Configuration des routes API

[x] Sécurité
    - [x] Middleware Sanctum dans Kernel.php
    - [x] Configuration CORS pour l'API
    - [x] Gestion des tokens d'authentification



