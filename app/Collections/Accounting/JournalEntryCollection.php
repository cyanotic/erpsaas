<?php

namespace App\Collections\Accounting;

use App\Enums\Accounting\JournalEntryType;
use App\Models\Accounting\JournalEntry;
use App\Utilities\Currency\CurrencyAccessor;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection;

class JournalEntryCollection extends Collection
{
    public function sumDebits(): Money
    {
        $total = $this->where('type', JournalEntryType::Debit)
            ->sum(static function (JournalEntry $item) {
                return $item->rawValue('amount');
            });

        return new Money($total, CurrencyAccessor::getDefaultCurrency());
    }

    public function sumCredits(): Money
    {
        $total = $this->where('type', JournalEntryType::Credit)
            ->sum(static function (JournalEntry $item) {
                return $item->rawValue('amount');
            });

        return new Money($total, CurrencyAccessor::getDefaultCurrency());
    }

    public function areBalanced(): bool
    {
        return $this->sumDebits()->getAmount() === $this->sumCredits()->getAmount();
    }
}
