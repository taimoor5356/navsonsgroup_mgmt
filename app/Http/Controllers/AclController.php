<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use DataTables;
use Illuminate\Support\Facades\Session;

class AclController extends Controller
{
    public function datatables($request)
    {
        $roles = Role::with('permissions');
        if (!empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $roles = $roles->where(function ($query) use ($searchValue) {
                $query->where('name', 'LIKE', "%$searchValue%");
            });
        }
        $totalRecords = $roles->count(); // Get the total number of records for pagination
        $data = $roles->skip($request->start)
            ->take($request->length)
            ->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('sr_no', function ($row) {
                return '1';
            })
            ->addColumn('name', function ($row) {
                return ucwords($row->name);
            })
            ->addColumn('guard_name', function ($row) {
                return $row->guard_name;
            })
            ->addColumn('total_permissions', function ($row) {
                return count($row->permissions);
            })
            ->addColumn('updated_at', function ($row) {
                return Carbon::parse($row->updated_at)->format('M d, Y H:i:s');
            })
            ->addColumn('actions', function ($row) {
                $btns = '
                    <div class="actionb-btns-menu d-flex justify-content-center">
                        <a class="btn btns m-0 p-1" href="'.route('admin.acl.role.edit', [$row->id]).'">
                            <i class="align-middle me-1 text-primary" data-feather="edit">
                            </i>
                        </a>
                        <a class="btn btns m-0 p-1" href="#">
                            <i class="align-middle me-1 text-danger" data-feather="trash-2">
                            </i>
                        </a>
                    </div>';
                return $btns;
            })
            ->rawColumns(['sr_no', 'name', 'actions'])
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($totalRecords) // For simplicity, same as totalRecords
            ->skipPaging()
            ->make(true);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $status = null)
    {
        //
        $data['header_title'] = 'ACL';
        $data['records'] = Role::get();
        $data['status'] = $status;
        if ($request->ajax()) {
            return $this->datatables($request);
        }
        if ($status == 'success') {
            Session::flash('success', 'Successfull');
        } else if ($status == 'error') {
            Session::flash('error', 'Something went wrong');
        }
        return view('admin.acl.roles', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $data['header_title'] = 'Add New Role';
        $data['permissionModule'] = Role::get();
        $data['permissions'] = Permission::get();
        return view('admin.acl.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
            $permissionIds = $request->permission_id;
            $permissions = Permission::whereIn('id', $permissionIds)->get();
            $role = Role::create([
                'name' => strtolower($request->name),
                'guard_name' => 'web'
            ]);
            $role->syncPermissions($permissions);
            return response()->json([
                'status' => true,
                'message' => 'Successfull'
            ]);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        //
        $data['header_title'] = 'Edit Role';
        $data['role'] = Role::find($id);
        $data['permissionModule'] = Role::get();
        $data['permissions'] = Permission::get();
        return view('admin.acl.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        try {
            $permissionIds = $request->permission_id;
            $permissions = Permission::whereIn('id', $permissionIds)->get();
            $role = Role::find($id);
            $role->name = strtolower($request->name);
            $role->save(); //remove all save
            $role->syncPermissions([]);
            $role->syncPermissions($permissions);
            return response()->json([
                'status' => true,
                'message' => 'Successfull'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
    }
}
