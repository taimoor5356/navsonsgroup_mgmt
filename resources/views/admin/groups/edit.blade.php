@extends('layout.app')
@section('_styles')

@endsection
@section('content')

<div class="container-fluid flex-grow-1 container-p-y">
    @include('_messages')
    <div class="row">
        <div class="col-xl">
            <form method="POST" action="{{url('admin/groups/update', [$record->id])}}">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Group</h5>
                    </div>
                    <div class="card-body">
                            @csrf
                            @include('admin.groups._form')
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

@endsection