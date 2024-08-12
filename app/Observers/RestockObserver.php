<?php

namespace App\Observers;

use App\Models\Restock;
use App\Models\Unit;

class RestockObserver
{
    /**
     * Handle the Restock "created" event.
     */
    public function created(Restock $restock): void
    {
        $unit = Unit::find($restock->unit_id);

        if($unit)
        {
            $unit->stock += $restock->qty;
            $unit->save();
        }
    }

    /**
     * Handle the Restock "updated" event.
     */
    public function updated(Restock $restock): void
    {
        //
    }

    /**
     * Handle the Restock "deleted" event.
     */
    public function deleted(Restock $restock): void
    {
        //
    }

    /**
     * Handle the Restock "restored" event.
     */
    public function restored(Restock $restock): void
    {
        //
    }

    /**
     * Handle the Restock "force deleted" event.
     */
    public function forceDeleted(Restock $restock): void
    {
        //
    }
}
