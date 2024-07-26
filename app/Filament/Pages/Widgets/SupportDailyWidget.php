<?php

namespace App\Filament\Pages\Widgets;

use App\Models\Reporting;
use Guava\Calendar\ValueObjects\Event;
use Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;

class SupportDailyWidget extends CalendarWidget
{
    // protected static string $view = 'filament.widgets.support-daily';

    // public function getEventsProperty()
    // {
    //     return Reporting::with('user')->get();
    // }

    protected bool $eventClickEnabled = true;

    public function getEvents(array $fetchInfo = []): Collection | array
    {
        // Ambil semua event dari model Reporting dan transformasikan sesuai kebutuhan
        $events = Reporting::with('user.team')->get()->map(function ($reporting) {
            // dd($reporting->user->team->color);
            return [
                'title' => $reporting->user->firstname .': '. $reporting->outstanding->location->name,
                'start' => $reporting->date_visit,
                'end' => $reporting->date_visit, // Jika date_visit juga berfungsi sebagai end date
                // 'textColor' => '#000'
                'backgroundColor' => $reporting->user->team->color,
            ];
        });

        return $events;
    }

    // public function getEvents(array $fetchInfo = []): Collection | array
    // {
    //     return [
    //         $reporting = Reporting::find(1),
    //         // Chainable object-oriented variant
    //         Event::make($reporting)
    //             ->title('sd')
    //             ->start(today())
    //             ->end(today()),

    //         // Array variant
    //         // ['title' => 'My second event', 'start' => today()->addDays(3), 'end' => today()->addDays(3)],

    //         // Eloquent model implementing the `Eventable` interface
    //         // MyEvent::find(1),
    //     ];
    // }

    public function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction(),
            $this->editAction(),
            // $this->deleteAction(),
        ];
    }
}
