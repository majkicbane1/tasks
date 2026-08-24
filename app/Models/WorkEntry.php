<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'project_id', 'payment_id', 'sort_order', 'worked_on', 'title', 'description', 'hours', 'hourly_rate',
    'fixed_amount', 'amount', 'status', 'visible_to_client', 'internal_note',
])]
class WorkEntry extends Model
{
    protected function casts(): array
    {
        return [
            'worked_on' => 'date',
            'hours' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'fixed_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'visible_to_client' => 'boolean',
            'invoiced_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
