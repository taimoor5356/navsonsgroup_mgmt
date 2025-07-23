<?php

namespace App\Exports;

use App\Models\Billing;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Schema;

class BillingExport implements FromCollection, WithHeadings
{
    protected $received_date;
    protected $created_date;
    protected $claim_status;

    protected $excludedColumns = ['deleted_at', 'created_at', 'updated_at'];

    public function __construct($received_date, $created_date, $claim_status)
    {
        $this->received_date = $received_date;
        $this->created_date = $created_date;
        $this->claim_status = $claim_status;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $billing = Billing::query();
        if (!empty($this->received_date)) {
            $rdates = explode('-', $this->received_date);
            $billing = $billing->whereDate('received_date', '>=', Carbon::parse($rdates[0]))->whereDate('received_date', '<=', Carbon::parse($rdates[1]));
        }
        if (!empty($this->created_date)) {
            $cdates = explode('-', $this->created_date);
            $billing = $billing->whereDate('created_at', '>=', Carbon::parse($cdates[0]))->whereDate('created_at', '<=', Carbon::parse($cdates[1]));
        }
        if (!empty($this->claim_status)) {
            $billing = $billing->where('claim_status', '=', $this->claim_status);
        }
        $billings = $billing->limit(2000)->get();
        $filteredColumns = $billings->map(function ($bill) {
            $data = $bill->toArray();
            foreach ($this->excludedColumns as $column) {
                unset($data[$column]);
            }
            return $data;
        });
        return $filteredColumns;
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        return Schema::getColumnListing((new Billing())->getTable());
    }
}
