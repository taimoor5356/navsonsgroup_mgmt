<div class="mb-3">
    <label class="form-label" for="basic-icon-default-fullname">Name</label>
    <div class="input-group input-group-merge">
        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="bx bx-user"></i></span>
        <input type="text" name="name" value="{{isset($role) ? $role->name : ''}}" class="form-control" id="basic-icon-default-fullname" placeholder="Enter name" aria-label="name" aria-describedby="basic-icon-default-fullname2">
    </div>
</div>

<br>
<hr>
<br>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <h5 class="mb-4">Assign Permissions</h5><span class="text-lg font-weight-bold"> Assign All Permissions <input type="checkbox" class=" border-secondary" id="select-all"></span>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-4 col-6 font-weight-bold">
                Assign all view <input type="checkbox" class="check-box border-secondary" id="select-all-view">
                <hr>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-4 col-6 font-weight-bold">
                Assign all create <input type="checkbox" class="check-box border-secondary" id="select-all-create">
                <hr>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-4 col-6 font-weight-bold">
                Assign all update <input type="checkbox" class="check-box border-secondary" id="select-all-update">
                <hr>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-4 col-6 font-weight-bold">
                Assign all delete <input type="checkbox" class="check-box border-secondary" id="select-all-delete">
                <hr>
            </div>
        </div>
        <div class="row">
            <!-- <div class="">
                <div class="row">
                    <div class="col-12">
                    </div>
                </div>
            </div> -->
            @php
            $permissions = Spatie\Permission\Models\Permission::get();
            @endphp
            @foreach ($permissions as $permission)
            <div class="col-lg-3 col-md-3 col-sm-4 col-6 mb-2">
                <label for="name" class="font-weight-normal">
                    {{str_replace('_', ' ', ucfirst($permission->name))}}
                </label>
                <br>
                @php
                $words = explode(' ', str_replace('_', ' ', strtolower($permission->name)));
                $lastWord = end($words);
                $permissionIds = isset($role) ? $role->getAllPermissions()->pluck('id') : [];
                @endphp
                <input type="checkbox" @isset($permission) @if(!empty($permissionIds)) ? @if($permissionIds->contains($permission->id)) checked @endif @endif @endisset class=" border border-secondary check-box {{$lastWord}}" name="permission_id[]" value="{{$permission->id}}">
            </div>
            @endforeach
        </div>
    </div>
</div>