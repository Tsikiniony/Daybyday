@extends('layouts.master')
@section('heading')
    {{ __('Data Management') }}
@stop

@section('content')
<div class="row">
    <!-- Generate Data Section -->
    <div class="col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-magic"></i> {{ __('Generate Data') }}</h3>
            </div>
            <div class="panel-body">
                <a href="{{route('data.generate')}}"
                   onclick="return confirm('@lang('Are you sure you want to generate data?')')"
                   class="btn btn-primary btn-lg btn-block">
                   <i class="fa fa-magic"></i> {{ __('Generate Data') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Import Data Section -->
    <div class="col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-upload"></i> {{ __('Import Data') }}</h3>
            </div>
            <div class="panel-body">
                @if(session('flash_message'))
                    <div class="alert alert-success">
                        {{ session('flash_message') }}
                    </div>
                @endif
                @if(session('flash_message_warning'))
                    <div class="alert alert-warning">
                        {{ session('flash_message_warning') }}
                    </div>
                @endif

                <form action="{{ route('data.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="table">{{ __('Sélectionner une table') }}</label>
                        <select name="table" id="table" class="form-control" required>
                            <option value="">{{ __('Choisir une table...') }}</option>
                            <option value="users">Users</option>
                            <option value="clients">Clients</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="file">{{ __('Sélectionner un fichier (CSV)') }}</label>
                        <input type="file" class="form-control" name="file" accept=".csv,.xlsx,.xls" required>
                        <p class="help-block">
                            <i class="fa fa-info-circle"></i> 
                            {{ __('Formats acceptés: CSV, Excel (.csv, .xlsx, .xls). Assurez-vous que les colonnes correspondent à la structure de la table.') }}
                        </p>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="has_headers" checked> 
                            {{ __('Le fichier contient une ligne d\'en-têtes') }}
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-upload"></i> {{ __('Importer') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
input[type="file"] {
    position: relative;
}

input[type="file"]::-webkit-file-upload-button {
    width: 0;
    padding: 0;
    margin: 0;
    -webkit-appearance: none;
    border: none;
}

input[type="file"]::before {
    content: 'Select file';
    display: inline-block;
    background: #5bc0de;
    border: 1px solid #46b8da;
    border-radius: 3px;
    padding: 5px 8px;
    outline: none;
    white-space: nowrap;
    cursor: pointer;
    font-weight: 700;
    font-size: 10pt;
    color: white;
    margin-right: 10px;
}

input[type="file"]:hover::before {
    background: #31b0d5;
    border-color: #269abc;
}

input[type="file"]:active::before {
    background: #269abc;
}

input[type="file"]::after {
    content: attr(data-file);
}

.custom-file-label {
    background: white;
    cursor: inherit;
    display: block;
}
</style>
@endpush
@stop
