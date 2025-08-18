@extends('layout.app')
@section('_styles')

<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<style>
    
    .select2-container .select2-selection--single {
        display: block;
        width: 100%;
        height: calc(2.25rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        box-shadow: inset 0 0 0 transparent;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        color: black !important;
        border: 1px solid #ced4da;
    }

    .select2-container--default .select2-selection--multiple,
    .select2-container--default .select2-selection--multiple:focus {
        border: 1px solid #ced4da;
    }
</style>
@endsection
@section('content')

<div class="container-fluid flex-grow-1 container-p-y">
    @include('_messages')
    <div class="row">
        <div class="col-xl">
            <form method="POST" action="{{url('admin/services/store')}}">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{$header_title}}</h5>
                    </div>
                    <div class="card-body">
                            @csrf
                            @include('admin.services._form')
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('_scripts')

<script src="{{asset('plugins/select2/js/select2.full.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select',
            allowClear: true
        });
    });
</script>
@endsection