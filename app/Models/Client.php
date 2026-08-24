<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'company_name', 'contact_name', 'email', 'phone', 'website', 'tax_number', 'registration_number',
    'address', 'default_hourly_rate', 'currency', 'notes', 'is_active', 'share_token',
])]
class Client extends Model
{
    protected function casts(): array
    {
        return [
            'default_hourly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalWorkAmountAttribute(): float
    {
        return (float) $this->projects()->withSum('workEntries', 'amount')->get()->sum('work_entries_sum_amount');
    }

    public function getTotalPaymentsAttribute(): float
    {
        return (float) $this->payments()->where('type', 'payment')->where('status', 'paid')->sum('amount');
    }

    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->payments()->where('type', 'expense')->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_work_amount + $this->total_expenses - $this->total_payments;
    }
}
