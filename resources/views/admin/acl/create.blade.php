@extends('layout.app')
@section('_styles')

@endsection
@section('content')

<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-xl">
            <form method="POST" action="{{url('admin/acl/role/store')}}" id="roles-form">
                @csrf
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Add New Role</h5>
                    </div>
                    <div class="card-body">
                        @csrf
                        @include('admin.acl._form')
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
<script>
    $(document).ready(function() {
        
        $(document).on('submit', '#roles-form', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = new FormData(form.get(0));
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.loader').addClass('d-none');
                    if (response.status == true) {
                        window.location = "{{url('admin/acl/roles/success')}}";
                    } else {
                        window.location = "{{url('admin/acl/roles/error')}}";
                    }
                },
                error: function(xhr, status, error) {
                    alert('Something went wrong');
                    return false;
                }
            });
        });

        $(document).on('click', '#select-all', function() {
            var _this = $(this);
            if (_this.is(':checked')) {
                $('.check-box').each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $('.check-box').each(function() {
                    $(this).prop('checked', false);
                });
            }
        });
        $(document).on('click', '#select-all-view', function() {
            if ($(this).is(':checked')) {
                $('.view').each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $('.view').each(function() {
                    $(this).prop('checked', false);
                });
            }
        });
        $(document).on('click', '#select-all-create', function() {
            if ($(this).is(':checked')) {
                $('.create').each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $('.create').each(function() {
                    $(this).prop('checked', false);
                });
            }
        });
        $(document).on('click', '#select-all-update', function() {
            if ($(this).is(':checked')) {
                $('.update').each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $('.update').each(function() {
                    $(this).prop('checked', false);
                });
            }
        });
        $(document).on('click', '#select-all-delete', function() {
            if ($(this).is(':checked')) {
                $('.delete').each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $('.delete').each(function() {
                    $(this).prop('checked', false);
                });
            }
        });
    });
</script>
@endsection