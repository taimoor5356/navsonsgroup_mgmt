<?php

namespace Database\Seeders;

use App\Models\Billing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddBillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $numberOfRecords = 500;
        $data = [];
        for ($i = 0; $i < $numberOfRecords; $i++) {
            $data[] = [
                'received_date' => date('Y-m-d', mt_rand(strtotime('2000-01-01'), time())),
                'hf_id' => 'HF_' . rand(1000, 9999),
                'guarantor_account' => 'GA_' . rand(1000, 9999),
                'guarantor_name' => 'Guarantor ' . rand(1, 100),
                'guarantor_address_line1' => 'Address Line 1 ' . rand(1, 100),
                'guarantor_address_line2' => 'Address Line 2 ' . rand(1, 100),
                'guarantor_address_line3' => 'Address Line 3 ' . rand(1, 100),
                'guarantor_city' => 'City ' . rand(1, 100),
                'guarantor_state' => 'State ' . rand(1, 100),
                'guarantor_zip' => rand(10000, 99999),
                'mrn' => 'MRN_' . rand(100000, 999999),
                'patient_name' => 'Patient ' . rand(1, 100),
                'date_of_birth' => date('Y-m-d', mt_rand(strtotime('1950-01-01'), strtotime('2005-12-31'))),
                'department' => 'Department ' . rand(1, 10),
                'pos' => 'POS_' . rand(1, 5),
                'pos_type' => 'POS Type ' . rand(1, 3),
                'service_provider' => 'Service Provider ' . rand(1, 100),
                'billing_provider' => 'Billing Provider ' . rand(1, 100),
                'date_of_service' => date('Y-m-d', mt_rand(strtotime('2000-01-01'), time())),
                'diagnosis_codes' => 'Diagnosis Codes ' . rand(1, 100),
                'procedure_code' => 'Procedure Code ' . rand(1, 100),
                'procedure_name' => 'Procedure Name ' . rand(1, 100),
                'rvu' => rand(1, 10) + (rand(1, 100) / 100),
                'charge_amount' => rand(100, 10000) + (rand(1, 100) / 100),
                'procedure_qty' => rand(1, 5),
                'modifiers' => 'Modifiers ' . rand(1, 100),
                'payer_name' => 'Payer ' . rand(1, 100),
                'plan_name' => 'Plan ' . rand(1, 100),
                'subscriber_number' => 'Subscriber Number ' . rand(10000, 99999),
                'subscriber_name' => 'Subscriber Name ' . rand(1, 100),
                'subscriber_dob' => date('Y-m-d', mt_rand(strtotime('1950-01-01'), strtotime('2005-12-31'))),
                'subscriber_ssn' => rand(100, 999) . '-' . rand(10, 99) . '-' . rand(1000, 9999),
                'group_number' => 'Group Number ' . rand(1000, 9999),
                'coverage_address' => 'Coverage Address ' . rand(1, 100),
                'coverage_city' => 'Coverage City ' . rand(1, 100),
                'coverage_state' => 'Coverage State ' . rand(1, 100),
                'coverage_zip' => rand(10000, 99999),
                'coverage_phone1' => rand(100, 999) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                'coverage_phone2' => rand(100, 999) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                'submission_date' => date('Y-m-d', mt_rand(strtotime('2000-01-01'), time())),
                'claim_status' => 'Claim Status ' . rand(1, 5),
                'primary_received_date' => date('Y-m-d', mt_rand(strtotime('2000-01-01'), time())),
                'primary_amount' => rand(100, 10000) + (rand(1, 100) / 100),
                'primary_payment_no' => 'Primary Payment No ' . rand(1000, 9999),
                'primary_ins_payment_date' => date('Y-m-d', mt_rand(strtotime('2000-01-01'), time())),
                'primary_ins_payment_cleared' => date('Y-m-d', mt_rand(strtotime('2000-01-01'), time())),
                'primary_payment_type' => 'Primary Payment Type ' . rand(1, 3),
                'ar_days' => rand(1, 100),
                'secondary_ins_received_date' => date('Y-m-d', mt_rand(strtotime('2000-01-01'), time())),
                'secondary_amount' => rand(100, 10000) + (rand(1, 100) / 100),
                'secondary_payment_no' => 'Secondary Payment No ' . rand(1000, 9999),
                'secondary_payment_date' => date('Y-m-d', mt_rand(strtotime('2000-01-01'), time())),
                'secondary_payment_cleared' => date('Y-m-d', mt_rand(strtotime('2000-01-01'), time())),
                'secondary_payment_type' => 'Secondary Payment Type ' . rand(1, 3),
                'selfpay_amount' => rand(100, 10000) + (rand(1, 100) / 100),
                'selfpay_payment_no' => 'Selfpay Payment No ' . rand(1000, 9999),
                'selfpay_payment_date' => date('Y-m-d', mt_rand(strtotime('2000-01-01'), time())),
                'selfpay_payment_type' => 'Selfpay Payment Type ' . rand(1, 3),
                'claim_comments' => 'Claim Comments ' . rand(1, 100),
                'rendering_provider' => 'Rendering Provider ' . rand(1, 100),
                'location_name' => 'Location Name ' . rand(1, 100),
                'responsible_payer' => 'Responsible Payer ' . rand(1, 100),
                'adjustment' => rand(100, 1000) + (rand(1, 100) / 100),
                'patient_responsibility' => rand(100, 1000) + (rand(1, 100) / 100),
                'write_off' => rand(100, 1000) + (rand(1, 100) / 100),
                'total_payment' => rand(1000, 10000) + (rand(1, 100) / 100),
                'over_payment' => rand(100, 1000) + (rand(1, 100) / 100),
                'insurance_balance' => rand(100, 1000) + (rand(1, 100) / 100),
                'patient_balance' => rand(100, 1000) + (rand(1, 100) / 100),
                'total_balance' => rand(100, 1000) + (rand(1, 100) / 100),
                'ar_aging_by_created_date' => 'AR Aging by Created Date ' . rand(1, 100),
                'ar_type' => 'AR Type ' . rand(1, 100),
                'primary_claim_id' => rand(1000, 9999),
                'secondary_claim_id' => rand(1000, 9999),
                'status' => 'Status ' . rand(1, 5),
                'rvu_status' => 'RVU Status ' . rand(1, 5),
            ];
            Billing::insert($data);
        }
    }
}
