<?php

namespace App\Http\Controllers;

use DataTables;
use Carbon\Carbon;
use App\Models\Fine;
use App\Models\User;
use App\Models\ErrorLog;
use App\Models\Service;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FineController extends Controller
{
    public function datatables($request, $records, $trashed = null)
    {
        $records = $records['records'];
        if (!empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $records = $records->where(function ($query) use ($searchValue) {
                $query->where('name', 'LIKE', "%$searchValue%");
            });
        }
        $totalRecords = $records->count(); // Get the total number of records for pagination
        $data = $records->skip($request->start)
            ->take($request->length)
            ->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('sr_no', function ($row) {
                return $row->id;
            })
            ->addColumn('date', function ($row) {
                return Carbon::parse($row->created_at)->format('d M, Y');
            })
            ->addColumn('user', function ($row) {
                return $row->user?->name;
            })
            ->addColumn('vehicle', function ($row) {
                return $row->vehicle?->name;
            })
            ->addColumn('reason', function ($row) {
                return $row->reason;
            })
            ->addColumn('amount', function ($row) {
                return $row->amount;
            })
            ->addColumn('actions', function ($row) use ($trashed) {
                 
            })
            ->rawColumns(['sr_no', 'checkbox', 'email', 'name', 'role', 'actions'])
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
        $data['header_title'] = 'Fines List';
        if ($request->ajax()) {
            $data['records'] = Fine::with('user', 'vehicle')->orderBy('created_at', 'desc');
            return $this->datatables($request, $data);
        }
        return view('admin.fines.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $data['header_title'] = 'Add New Fine';
        $data['users'] = User::where('user_type', 3)->get();
        $data['vehicles'] = Service::with('vehicle')->where('complain', 1)->get();
        return view('admin.fines.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'user_id' => 'required',
            'vehicle_id' => 'required',
            'amount' => 'required|integer',
            'description' => 'required'
        ]);

        DB::beginTransaction();

        try {
            Fine::create([
                'user_id' => !empty($request->user_id) ? $request->user_id : null,
                'vehicle_id' => !empty($request->vehicle_id) ? $request->vehicle_id : null,
                'amount' => $request->amount,
                'reason' => $request->description,
                'created_at' => !empty($request->date) ? Carbon::parse($request->date) : Carbon::now(),
                'updated_at' => !empty($request->date) ? Carbon::parse($request->date) : Carbon::now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Entry added successfully');
            // return redirect()->route('admin.expenses.list')->with('success', 'Entry added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLog::create([
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Fine $fine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $data['header_title'] = 'Edit Fine';
        $data['record'] = Fine::where('id', $id)->first();
        return view('admin.fines.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'user_id' => 'required',
            'vehicle_id' => 'required',
            'amount' => 'required|integer',
            'reason' => 'required'
        ]);

        DB::beginTransaction();

        try {
            Fine::where('id', $id)->update([
                'user_id' => !empty($request->user_id) ? $request->user_id : null,
                'vehicle_id' => !empty($request->vehicle_id) ? $request->vehicle_id : null,
                'amount' => $request->amount,
                'reason' => $request->reason,
                'created_at' => !empty($request->date) ? Carbon::parse($request->date) : Carbon::now(),
                'updated_at' => !empty($request->date) ? Carbon::parse($request->date) : Carbon::now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Entry added successfully');
            // return redirect()->route('admin.expenses.list')->with('success', 'Entry added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLog::create([
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fine $fine)
    {
        //
    }
}
