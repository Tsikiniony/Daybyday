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
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-upload"></i> {{ __('Import Data') }}</h3>
            </div>
            <div class="panel-body">
            <div class="col-sm-10">
        <form action="{{ route('database.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                {!! Form::label('csv_file1', __('Import Projects CSV'). ':', ['class' => 'control-label thin-weight']) !!}
                <input type="file" name="csv_file1" class="form-control" required accept=".csv">
                <small class="form-text text-muted">Please upload the projects CSV file</small>
            </div>
            
            <div class="form-group">
                {!! Form::label('csv_file2', __('Import Tasks CSV'). ':', ['class' => 'control-label thin-weight']) !!}
                <input type="file" name="csv_file2" class="form-control" required accept=".csv">
                <small class="form-text text-muted">Please upload the tasks CSV file</small>
            </div>
            
            <div class="form-group">
                {!! Form::label('csv_file3', __('Import Leads and invoices CSV'). ':', ['class' => 'control-label thin-weight']) !!}
                <input type="file" name="csv_file3" class="form-control" required accept=".csv">
                <small class="form-text text-muted">Please upload the leads and invoices CSV file</small>
            </div>

            <button type="submit" class="btn btn-md btn-success">
                <i class="fas fa-upload mr-2"></i> {{ __('Import All Files') }}
            </button>
        </form>
    </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function addFileInput() {
        const container = document.getElementById('file-inputs');
        
        const div = document.createElement('div');
        div.className = 'form-group';
        div.innerHTML = `
            <div class="col-sm-12">
                <div class="input-group">
                    <input type="file" name="files[]" class="form-control" accept=".csv">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-danger" onclick="this.closest('.form-group').remove()">
                            <i class="fa fa-trash"></i>
                        </button>
                    </span>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    }
</script>
@endpush
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
