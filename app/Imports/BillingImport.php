<?php

namespace App\Imports;

use App\Models\Billing;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class BillingImport implements ToModel, WithHeadingRow, WithLimit, WithChunkReading, ToCollection
{
    use Importable;

    public function collection(Collection $collection)
    {
        //
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        try {
            $attributes = $row;
            // Validate and format specific date columns
            $attributes['received_date'] = $this->validateDate(!empty($row['received_date']) ? $row['received_date'] : now());
            $attributes['date_of_birth'] = $this->validateDate(!empty($row['date_of_birth']) ? $row['date_of_birth'] : now());
            $attributes['subscriber_dob'] = !empty($row['subscriber_dob']) ? Carbon::parse($row['subscriber_dob'])->format('Y-m-d') : now();
            $attributes['date_of_service'] = $this->validateDate(!empty($row['date_of_service']) ? $row['date_of_service'] : now());
            $attributes['submission_date'] = $this->validateDate(!empty($row['submission_date']) ? $row['submission_date'] : now());
            $attributes['primary_received_date'] = $this->validateDate(!empty($row['primary_received_date']) ? $row['primary_received_date'] : now());
            $attributes['primary_ins_payment_date'] = $this->validateDate(!empty($row['primary_ins_payment_date']) ? $row['primary_ins_payment_date'] : now());
            $attributes['secondary_payment_date'] = $this->validateDate(!empty($row['secondary_payment_date']) ? $row['secondary_payment_date'] : now());
            $attributes['selfpay_payment_date'] = $this->validateDate(!empty($row['selfpay_payment_date']) ? $row['selfpay_payment_date'] : now());

            $billing = Billing::updateOrCreate(
                ['sno' => $row['sno'] ?? null],
                $attributes
            );
            
            return $billing;
        } catch (\Exception $e) {
            // Log the error and continue
            \Log::error("Error importing file: " . $e->getMessage());
        }
    }
    private function validateDate($date)
    {
        // Attempt to parse the date
        try {
            return Carbon::parse(Date::excelToDateTimeObject(intval($date)))->format('Y-m-d');
        } catch (\Exception $e) {
            return null; // or handle as required
        }
    }

    public function chunkSize() : int
    {
        return 500; // Adjust chunk size as needed for your system and available memory. Be mindful that larger chunk sizes may consume more memory and slow down the import process.
    }

    public function limit(): int
    {
        return 2002;
    }
}
