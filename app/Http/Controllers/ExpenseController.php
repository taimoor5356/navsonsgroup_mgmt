<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use App\Models\Expense;
use App\Models\ExpenseType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
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
            ->addColumn('expense_type', function ($row) {
                return ucwords(str_replace('_', ' ', $row->expenseType?->name));
            })
            ->addColumn('name', function ($row) {
                return ucwords(str_replace('_', ' ', $row->name));
            })
            ->addColumn('expense_amount', function ($row) {
                return $row->amount;
            })
            ->addColumn('description', function ($row) {
                return $row->description;
            })
            ->addColumn('actions', function ($row) use ($trashed) {
                $btns = '
                    <div class="actionb-btns-menu d-flex justify-content-center">';
                    if ($trashed == null) {
                        // $btns .= '<a class="btn btns m-0 p-1" data-user-id="'.$row->id.'" href="expenses/edit/'.$row->id.'">
                        //         <i class="align-middle text-primary" data-feather="edit">
                        //         </i>
                        //     </a>
                        //     <a class="btn btns m-0 p-1 delete-user" data-user-id="'.$row->id.'" href="#">
                        //         <i class="align-middle text-danger" data-feather="trash-2">
                        //         </i>
                        //     </a>
                        // </div>';
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
        $data['header_title'] = 'Expenses List';
        if ($request->ajax()) {
            $data['records'] = Expense::with('expenseType')->orderBy('created_at', 'desc');
            return $this->datatables($request, $data);
        }
        return view('admin.expenses.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $data['header_title'] = 'Add New Expense';
        $data['expenseTypes'] = ExpenseType::get();
        return view('admin.expenses.create', $data);
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
            'amount' => 'required|integer',
            'description' => 'required',
            'payment_mode_id' => 'required',
        ]);

        DB::beginTransaction();

        try {
            Expense::create([
                'name' => $request->name,
                'user_id' => !empty($request->user_id) ? $request->user_id : null,
                'expense_type_id' => $request->expense_type_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'payment_mode_id' => $request->payment_mode_id,
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
