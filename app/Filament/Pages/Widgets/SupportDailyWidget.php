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

    // protected bool $eventClickEnabled = true;

    public function getEvents(array $fetchInfo = []): Collection | array
    {
        // Ambil semua event dari model Reporting dengan relasi users, team, outstanding, dan location
        $events = Reporting::with('users.team', 'outstanding.location')->get()->flatMap(function ($reporting) {
            $locationName = $reporting->outstanding && $reporting->outstanding->location ? $reporting->outstanding->location->name : 'No Location';

            // Iterasi melalui setiap pengguna yang terkait dengan Reporting
            return $reporting->users->map(function ($user) use ($locationName, $reporting) {
                return [
                    'title' => $user->firstname .': '. $locationName,
                    // 'user' => $user->firstname,
                    // 'location' => $locationName,
                    'start' => $reporting->date_visit,
                    'end' => $reporting->date_visit,
                    'backgroundColor' => $user->team->color,
                ];
            });
        });

        // Gabungkan event berdasarkan user dan location
        $groupedEvents = $events->unique(function ($event) {
            return $event['title'];
            // return $event['user'] . $event['location'];
        });

        return $groupedEvents->values();
    }
    // public function getEvents(array $fetchInfo = []): Collection | array
    // {
    //     // Ambil semua event dari model Reporting dan transformasikan sesuai kebutuhan
    //     $events = Reporting::with('users.team', 'outstanding.location')->get()->flatMap(function ($reporting) {
    //         $statusValue = $reporting->status ? $reporting->status->value : null;
    //         switch ($statusValue) {
    //             case '1':
    //                 $status = 'S';
    //                 break;
    //             case '0':
    //                 $status = 'P';
    //                 break;
    //             default:
    //                 $status = '?';
    //                 break;
    //         }

    //         $locationName = $reporting->outstanding && $reporting->outstanding->location ? $reporting->outstanding->location->name : 'No Location';

    //         // Iterasi melalui setiap pengguna yang terkait dengan Reporting
    //         return $reporting->users->map(function ($user) use ($status, $locationName, $reporting) {
    //             return [
    //                 'title' => $status .' | '. $user->firstname .': '. $locationName,
    //                 'start' => $reporting->date_visit,
    //                 'end' => $reporting->date_visit, // Jika date_visit juga berfungsi sebagai end date
    //                 'backgroundColor' => $user->team->color,
    //             ];
    //         });
    //     });

    //     return $events;
    // }

    // public function getEventClickContextMenuActions(): array
    // {
    //     return [
    //         $this->viewAction(),
    //         $this->editAction(),
    //         // $this->deleteAction(),
    //     ];
    // }
}
