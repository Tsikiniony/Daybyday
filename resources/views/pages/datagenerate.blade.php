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
                <h3 class="panel-title"><i class="fa fa-database"></i> {{ __('Generate Demo Data') }}</h3>
            </div>
            <div class="panel-body">
                <p class="text-muted">{{ __('Generate sample data for testing.') }}</p>
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
                <form action="{{ route('data.import') }}" method="POST" enctype="multipart/form-data" class="dropzone" id="importForm">
                    @csrf
                    <div class="form-group">
                        <label for="table_name">{{ __('Select Table') }}</label>
                        <select name="table_name" id="table_name" class="form-control" required>
                            <option value="">{{ __('Choose a table...') }}</option>
                            <option value="users">Users</option>
                            <option value="clients">Clients</option>
                            <option value="projects">Projects</option>
                            <option value="tasks">Tasks</option>
                            <option value="departments">Departments</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="csv_file">{{ __('Select File') }}</label>
                        <div class="input-group">
                            <span class="input-group-btn">
                                <span class="btn btn-primary btn-file">
                                    {{ __('Browse') }} <input type="file" name="csv_file" id="csv_file" accept=".csv,.xls,.xlsx">
                                </span>
                            </span>
                            <input type="text" class="form-control" readonly>
                        </div>
                        <p class="help-block">
                            <i class="fa fa-info-circle"></i> 
                            {{ __('Accepted formats: CSV, XLS, XLSX files. Make sure columns match the selected table structure.') }}
                        </p>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fa fa-upload"></i> {{ __('Import Data') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $(document).on('change', '.btn-file :file', function() {
        var input = $(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
        input.trigger('fileselect', [numFiles, label]);
    });

    $('.btn-file :file').on('fileselect', function(event, numFiles, label) {
        var input = $(this).parents('.input-group').find(':text'),
        log = numFiles > 1 ? numFiles + ' files selected' : label;
        
        if(input.length) {
            input.val(log);
        }
    });
});
</script>
@endpush

@push('style')
<style>
.btn-file {
    position: relative;
    overflow: hidden;
}
.btn-file input[type=file] {
    position: absolute;
    top: 0;
    right: 0;
    min-width: 100%;
    min-height: 100%;
    font-size: 100px;
    text-align: right;
    filter: alpha(opacity=0);
    opacity: 0;
    outline: none;
    background: white;
    cursor: inherit;
    display: block;
}
</style>
@endpush
@stop


