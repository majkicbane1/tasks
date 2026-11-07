<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'portion', 'note'])]
class LeaveDay extends Model
{
    /** Allowed shares of a day with their label and short symbol. */
    public const PORTIONS = [
        '1.00' => ['label' => 'Ceo dan', 'symbol' => '1'],
        '0.50' => ['label' => 'Pola dana', 'symbol' => '½'],
        '0.25' => ['label' => 'Četvrtina dana', 'symbol' => '¼'],
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'portion' => 'decimal:2',
        ];
    }

    public function portionKey(): string
    {
        return number_format((float) $this->portion, 2, '.', '');
    }

    public function label(): string
    {
        return self::PORTIONS[$this->portionKey()]['label'] ?? $this->portionKey();
    }

    public function symbol(): string
    {
        return self::PORTIONS[$this->portionKey()]['symbol'] ?? $this->portionKey();
    }

    /** 12.5 -> "12,5", 3 -> "3" */
    public static function format(float $days): string
    {
        return rtrim(rtrim(number_format($days, 2, ',', ''), '0'), ',') ?: '0';
    }
}
