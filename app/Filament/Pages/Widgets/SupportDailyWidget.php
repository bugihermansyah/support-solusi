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

    protected string $calendarView = 'listWeek';

    // protected bool $eventClickEnabled = true;

    // public function getEvents(array $fetchInfo = []): Collection | array
    // {
    //     // Ambil semua event dari model Reporting dengan relasi users, team, outstanding, dan location
    //     $events = Reporting::with('users.team', 'outstanding.location')->get()->flatMap(function ($reporting) {
    //         $locationName = $reporting->outstanding && $reporting->outstanding->location ? $reporting->outstanding->location->name : 'No Location';

    //         // Iterasi melalui setiap pengguna yang terkait dengan Reporting
    //         return $reporting->users->map(function ($user) use ($locationName, $reporting) {
    //             return [
    //                 'user' => $user->firstname,
    //                 'location' => $locationName,
    //                 'start' => $reporting->date_visit,
    //                 'end' => $reporting->date_visit,
    //                 'backgroundColor' => $user->team->color,
    //             ];
    //         });
    //     });

    //     // Gabungkan event berdasarkan user dan location
    //     $groupedEvents = $events->unique(function ($event) {
    //         return $event['user'] . '|' . $event['location'];
    //     });

    //     return $groupedEvents->values()->map(function ($event) {
    //         return [
    //             'title' => $event['user'] . ': ' . $event['location'],
    //             'start' => $event['start'],
    //             'end' => $event['end'],
    //             'backgroundColor' => $event['backgroundColor'],
    //         ];
    //     });
    // }
    public function getEvents(array $fetchInfo = []): Collection | array
    {
        // Ambil semua event dari model Reporting dan transformasikan sesuai kebutuhan
        $events = Reporting::with('users.team', 'outstanding.location')->get()->flatMap(function ($reporting) {
            $statusValue = $reporting->status ? $reporting->status->value : null;
            switch ($statusValue) {
                case '1':
                    $status = 'S';
                    break;
                case '0':
                    $status = 'P';
                    break;
                default:
                    $status = '?';
                    break;
            }

            $locationName = $reporting->outstanding && $reporting->outstanding->location ? $reporting->outstanding->location->name : 'No Location';

            return $reporting->users->map(function ($user) use ($status, $locationName, $reporting) {
                $backgroundColor = $user->team ? $user->team->color : '#000000'; // Default color if team is null
                return [
                    'title' => $status .' | '. $user->firstname .': '. $locationName,
                    'start' => $reporting->date_visit,
                    'end' => $reporting->date_visit,
                    'backgroundColor' => $backgroundColor,
                ];
            });
        });

        return $events;
    }

    // public function getEventClickContextMenuActions(): array
    // {
    //     return [
    //         $this->viewAction(),
    //         $this->editAction(),
    //         // $this->deleteAction(),
    //     ];
    // }
}
