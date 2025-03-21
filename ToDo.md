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
_Clear db : php artisan migrate:fresh --seed
_Add admin (first) : php artisan db:seed 
_Dummy data : php artisan db:seed --class=DummyDatabaseSeeder

# Prise en main

## Debug

[x] Error 404 
_Delete Project from : http://127.0.0.1:8000/projects :
    -Function destroy in controller/ProjectsController :
        return redirect()->back(); => return redirect()->route('projects.index');

[x] Error upload file
_In config/filesystems :
    -default : 's3' => default : 'public'

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

## New functions
[] Data import
[] Reinit db









