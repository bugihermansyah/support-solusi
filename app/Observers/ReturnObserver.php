<?php

namespace App\Observers;

use App\Models\LoanUnit;
use App\Models\ReturnUnit;

class ReturnObserver
{
    /**
     * Handle the ReturnUnit "created" event.
     */
    public function created(ReturnUnit $returnUnit): void
    {
        //
    }

    /**
     * Handle the ReturnUnit "updated" event.
     */
    public function updated(ReturnUnit $returnUnit): void
    {
        if ($returnUnit->wasChanged('accepted_at')) {
            $return = LoanUnit::where('loan_id', $returnUnit->loan_id)
                                ->where('unit_id', $returnUnit->unit_id)
                                ->first();
            if ($return) {
                $return->return_qty += $returnUnit->qty;
                $return->save();
            }
        }
    }

    /**
     * Handle the ReturnUnit "deleted" event.
     */
    public function deleted(ReturnUnit $returnUnit): void
    {
        //
    }

    /**
     * Handle the ReturnUnit "restored" event.
     */
    public function restored(ReturnUnit $returnUnit): void
    {
        //
    }

    /**
     * Handle the ReturnUnit "force deleted" event.
     */
    public function forceDeleted(ReturnUnit $returnUnit): void
    {
        //
    }
}
