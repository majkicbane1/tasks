<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['client_id', 'name', 'status', 'hourly_rate', 'description', 'started_at', 'finished_at'])]
class Project extends Model
{
    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'started_at' => 'date',
            'finished_at' => 'date',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function workEntries()
    {
        return $this->hasMany(WorkEntry::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getBillableTotalAttribute(): float
    {
        return (float) $this->workEntries()->sum('amount');
    }

    public function getPaymentsTotalAttribute(): float
    {
        return (float) $this->payments()->where('type', 'payment')->where('status', 'paid')->sum('amount');
    }

    public function getExpensesTotalAttribute(): float
    {
        return (float) $this->payments()->where('type', 'expense')->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->billable_total + $this->expenses_total - $this->payments_total;
    }
}
