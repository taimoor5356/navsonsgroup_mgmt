<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use App\Models\ExpenseName;
use App\Models\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use DataTables;

class ExpenseNameController extends Controller
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
            ->addColumn('name', function ($row) {
                return ucwords(str_replace('_', ' ', $row->name));
            })
            ->addColumn('expense_type', function ($row) {
                return ucwords(str_replace('_', ' ', $row->expense_type?->name));
            })
            ->addColumn('actions', function ($row) use ($trashed) {
                $btns = '
                    <div class="actionb-btns-menu d-flex justify-content-center">';
                    if ($trashed == null) {
                        if (Auth::user()->id == 1) {
                            $btns .= '<a class="btn btns m-0 p-1" data-user-id="'.$row->id.'" href="edit/'.$row->id.'">
                                    <i class="align-middle text-primary" data-feather="edit">
                                    </i>
                                </a>
                                <a class="btn btns m-0 p-1 delete-user" data-user-id="'.$row->id.'" href="#">
                                    <i class="align-middle text-danger" data-feather="trash-2">
                                    </i>
                                </a>
                            </div>';
                        }
                    } else {
                        // $btns.= '<a class="btn btns m-0 p-1" href="restore/' . $row->id . '">
                        //         <i class="align-middle text-success" data-feather="refresh-cw">
                        //         </i>
                        //     </a>
                        // </div>';
                    }
                return $btns;
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
        $data['header_title'] = 'Expense Names List';
        if ($request->ajax()) {
            $data['records'] = ExpenseName::with('expense_type')->orderBy('created_at', 'desc');
            return $this->datatables($request, $data);
        }
        return view('admin.expenses.expense_names.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $data['header_title'] = 'Add New Expense Name';
        $data['expenseTypes'] = ExpenseType::get();
        return view('admin.expenses.expense_names.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required',
            'expense_type_id' => 'required',
        ]);

        DB::beginTransaction();

        try {
            ExpenseName::create([
                'name' => !empty($request->name) ? $request->name : null,
                'expense_type_id' => $request->expense_type_id,
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
    public function show(ExpenseName $expenseName)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseName $expenseName)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseName $expenseName)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseName $expenseName)
    {
        //
    }

    public function fetchExpenseNames(Request $request) {
        $expenseNames = ExpenseName::where('expense_type_id', $request->expense_type_id)->get();
        return response()->json([
            'status' => true,
            'data' => $expenseNames
        ]);
    }
}
