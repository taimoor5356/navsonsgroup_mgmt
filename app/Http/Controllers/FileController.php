<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use DataTables;

class FileController extends Controller
{
    public function datatables($request)
    {
        $roles = File::query();
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
            ->addColumn('path', function ($row) {
                return $row->path;
            })
            ->addColumn('download', function ($row) {
                return '<a href="'.'exports/'.$row->name.'" class="btn btn-primary btn-sm">Download</a>';
            })
            ->addColumn('read', function ($row) {
                return $row->read == 0 ? 'Unread' : 'Read';
            })
            ->addColumn('created_by', function ($row) {
                return $row->user?->name;
            })
            ->addColumn('actions', function ($row) {
                $btns = '
                    <div class="actionb-btns-menu d-flex justify-content-center">
                        <a class="btn btns m-0 p-1" href="#">
                            <i class="align-middle me-1 text-danger" data-feather="trash-2">
                            </i>
                        </a>
                    </div>';
                return $btns;
            })
            ->rawColumns(['read', 'download', 'actions'])
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($totalRecords) // For simplicity, same as totalRecords
            ->skipPaging()
            ->make(true);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $data['header_title'] = 'Files';
        $data['records'] = File::get();
        if ($request->ajax()) {
            return $this->datatables($request);
        }
        return view('admin.files.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        //
    }
}
