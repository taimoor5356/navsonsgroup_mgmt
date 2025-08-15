<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id', 'id');
    }
}
