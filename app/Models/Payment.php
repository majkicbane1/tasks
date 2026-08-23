<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'client_id', 'project_id', 'paid_on', 'amount', 'type', 'method',
    'status', 'reference', 'note', 'visible_to_client',
])]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'paid_on' => 'date',
            'amount' => 'decimal:2',
            'visible_to_client' => 'boolean',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function isPendingInvoice(): bool
    {
        return $this->type === 'payment' && $this->status === 'pending_invoice';
    }

    public function isPaidPayment(): bool
    {
        return $this->type === 'payment' && $this->status === 'paid';
    }
}
