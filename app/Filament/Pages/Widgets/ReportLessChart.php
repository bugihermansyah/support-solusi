<?php

namespace App\Filament\Pages\Widgets;

use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Reporting;
use App\Models\Team;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ReportLessChart extends ApexChartWidget
{
    protected static ?string $chartId = 'reportLessChart';

    protected static ?string $heading = 'Summary Reporting';

    protected int | string | array $columnSpan = 2;

    protected static ?string $pollingInterval = null;

    protected static ?string $loadingIndicator = 'Loading...';

    protected function getFormSchema(): array
    {
        return [
            Fieldset::make('All')
                ->schema([    
                    Select::make('year')
                        ->options([
                            '2024' => '2024',
                            '2025' => '2025'
                        ])
                        ->default('2024'),
                    Select::make('team')
                        ->placeholder('Select')
                        ->options(Team::all()->pluck('name', 'id')),
                    Select::make('lpm')
                        ->options([
                            '1' => 'Yes'
                        ])
                        ->columnSpanFull()
                ])
                ->columns('2'),
            Fieldset::make('Outstanding')
                ->schema([    
                    Select::make('reporter')
                        ->options([
                            'client' => 'Client',
                            'preventif' => 'Preventif',
                            'support' => 'Support'
                        ])
                        ->default('client'),
                ])
                ->columns('2'),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 550,
                'stacked' => true,
                'toolbar' => [
                    'show' => true,
                ],
                'zoom' => [
                    'enabled' => true,
                ]
            ],
            'series' => [
                [
                    'name' => 'Total Lokasi',
                    'data' => $this->getCumulativeLocationsPerMonth(),
                ],
                [
                    'name' => 'Total Outstanding',
                    'data' => $this->getTotalOutstandingPerMonth(),
                ],
                [
                    'name' => 'Total Lokasi Masalah',
                    'data' => $this->getUniqueLocationsPerMonth(),
                ],
                [
                    'name' => 'Total Aksi Visit',
                    'data' => $this->getTotalVisitData(),
                ],
                [
                    'name' => 'Total Laporan Awal Masuk',
                    'data' => $this->getLaporanAwalMasukData(),
                ],
                [
                    'name' => 'Total SLA Visit',
                    'data' => $this->getTotalSlaVisitData(),
                ],
                [
                    'name' => 'Total Aksi Remote',
                    'data' => $this->getTotalRemoteData(),
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                // 'offsetY' => 40
            ],
            'legend' => [
                'position' => 'right',
                'offsetY' => 40
            ],
            'xaxis' => [
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => [
                '#f59e0b', // Total Lokasi
                '#10b981', // Total Outstanding
                '#3b82f6', // Total Lokasi Masalah
                '#ef4444', // Total Aksi Visit
                '#8b5cf6', // Total Laporan Awal Masuk
                '#f97316', // Total SLA Visit
                '#22d3ee', // Total Aksi Remote
            ],
        ];
    }
    // Total Outstanding
    protected function getTotalOutstandingPerMonth(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;
        $teamId = $this->filterFormData['team'] ?? null;
        $lpm = $this->filterFormData['lpm'] ?? null;
        $reporter = $this->filterFormData['reporter'] ?? null;

        // Inisialisasi array data untuk 12 bulan
        $data = array_fill(0, 12, 0);

        // Mulai query
        $query = Outstanding::query()
            ->selectRaw('MONTH(date_in) as month, COUNT(*) as total_outstanding')
            ->join('locations', 'outstandings.location_id', '=', 'locations.id')
            ->whereIn('reporter', ['client', 'support'])
            ->whereYear('date_in', $year);

        // Tambahkan filter team jika ada
        if ($teamId) {
            $query->where('locations.team_id', $teamId);
        }

        if ($lpm) {
            $query->where('lpm', $lpm);
        }

        if ($reporter) {
            $query->where('reporter', $reporter);
        }

        // Kelompokkan berdasarkan bulan dan urutkan
        $results = $query
            ->groupByRaw('MONTH(date_in)')
            ->orderBy('month')
            ->get();

        // Mengisi array dengan hasil query
        foreach ($results as $result) {
            $data[$result->month - 1] = $result->total_outstanding;
        }

        return $data;
    }

    // Total Lokasi
    protected function getCumulativeLocationsPerMonth(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;
        $teamId = $this->filterFormData['team'] ?? null;
    
        // Inisialisasi array data untuk 12 bulan
        $data = array_fill(0, 12, 0);
    
        // Hitung total lokasi sebelum tahun yang dipilih
        $previousTotal = Location::query()
            ->whereYear('created_at', '<', $year)
            ->whereNot('locations.status', 'dismantle');
    
        if ($teamId) {
            $previousTotal->where('team_id', $teamId);
        }
    
        $previousTotal = $previousTotal->count(); // Total lokasi sebelum tahun yang dipilih
    
        // Perulangan untuk setiap bulan dalam tahun yang dipilih
        for ($month = 1; $month <= 12; $month++) {
            $query = Location::query()
                ->whereBetween('created_at', ["$year-01-01", "$year-$month-31"]) // Hitung lokasi dalam tahun ini sampai bulan tertentu
                ->whereNot('locations.status', 'dismantle');
    
            if ($teamId) {
                $query->where('team_id', $teamId);
            }
    
            // Hitung total lokasi dalam tahun yang dipilih sampai bulan tertentu
            $totalLocations = $query->count();
    
            // Akumulasi dengan total lokasi dari tahun sebelumnya
            $data[$month - 1] = $previousTotal + $totalLocations;
        }
    
        return $data;
    }
   

    // Total Lokasi Masalah
    protected function getUniqueLocationsPerMonth(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;
        $teamId = $this->filterFormData['team'] ?? null;
        $lpm = $this->filterFormData['lpm'] ?? null;
    
        // Inisialisasi array data dengan 0 untuk 12 bulan
        $data = array_fill(0, 12, 0);
    
        // Mulai query
        $query = Outstanding::query()
            ->selectRaw('MONTH(date_visit) as month, COUNT(DISTINCT locations.id) as unique_locations_count')
            ->join('locations', 'outstandings.location_id', '=', 'locations.id')
            ->whereYear('date_visit', $year);
    
        // Tambahkan filter team jika ada
        if ($teamId) {
            $query->where('locations.team_id', $teamId);
        }
    
        if ($lpm) {
            $query->where('lpm', $lpm);
        }
    
        // Kelompokkan berdasarkan bulan dan urutkan
        $results = $query
            ->groupByRaw('MONTH(date_visit)')
            ->orderBy('month')
            ->get();
    
        // Mengisi array dengan hasil query
        foreach ($results as $result) {
            $data[$result->month - 1] = $result->unique_locations_count;
        }
    
        return $data;
    }

    // Total Aksi Visit
    protected function getTotalVisitData(): array
    {
        $teamId = $this->filterFormData['team'] ?? null;
        $year = $this->filterFormData['year'] ?? now()->year;
        $lpm = $this->filterFormData['lpm'] ?? null;

        // Inisialisasi array data dengan 0 untuk 12 bulan
        $data = array_fill(0, 12, 0);

        // Ambil data visit berdasarkan work='visit', team, dan year
        $query = Reporting::selectRaw('MONTH(reportings.date_visit) as month, COUNT(*) as count')
            ->join('outstandings', 'reportings.outstanding_id', '=', 'outstandings.id')
            ->join('locations', 'outstandings.location_id', '=', 'locations.id')
            ->where('reportings.work', 'visit')
            ->whereYear('reportings.date_visit', $year);

        if ($teamId) {
            $query->where('locations.team_id', $teamId);
        }

        if ($lpm) {
            $query->where('lpm', $lpm);
        }

        $visits = $query->groupByRaw('MONTH(reportings.date_visit)')->get();

        // Mengisi array dengan hasil query
        foreach ($visits as $item) {
            $data[$item->month - 1] = $item->count;
        }

        return $data;
    }

    // Total Laporan Awal Masuk
    protected function getLaporanAwalMasukData(): array
    {
        $teamId = $this->filterFormData['team'] ?? null;
        $year = $this->filterFormData['year'] ?? now()->year;

        // Inisialisasi array data dengan 0 untuk 12 bulan
        $data = array_fill(0, 12, 0);

        // Ambil data laporan berdasarkan lpm=1, team, dan year
        $query = Outstanding::selectRaw('MONTH(date_in) as month, COUNT(*) as count')
            ->join('locations', 'outstandings.location_id', '=', 'locations.id')
            ->where('outstandings.lpm', 1)
            ->whereYear('outstandings.date_in', $year);

        if ($teamId) {
            $query->where('locations.team_id', $teamId);
        }

        $laporan = $query->groupByRaw('MONTH(date_in)')->get();

        // Mengisi array dengan hasil query
        foreach ($laporan as $item) {
            $data[$item->month - 1] = $item->count;
        }

        return $data;
    }

    // Total SLA Aksi Visit/Remote
    protected function getTotalSlaVisitData(): array
    {
        $teamId = $this->filterFormData['team'] ?? null;
        $year = $this->filterFormData['year'] ?? now()->year;
        $lpm = $this->filterFormData['lpm'] ?? null;

        // Inisialisasi array data dengan 0 untuk 12 bulan
        $data = array_fill(0, 12, 0);

        // Query untuk menghitung SLA visit yang mencapai 0 hingga 1 hari
        $query = Outstanding::selectRaw('MONTH(date_in) as month, SUM(CASE WHEN DATEDIFF(date_visit, date_in) BETWEEN 0 AND 1 THEN 1 ELSE 0 END) as sla1_count')
            ->join('locations', 'outstandings.location_id', '=', 'locations.id')
            ->whereYear('date_in', $year);

        if ($teamId) {
            $query->where('locations.team_id', $teamId);
        }

        if ($lpm) {
            $query->where('lpm', $lpm);
        }
        $slaVisits = $query->groupByRaw('MONTH(date_in)')->get();

        // Mengisi array dengan hasil query
        foreach ($slaVisits as $item) {
            $data[$item->month - 1] = $item->sla1_count;
        }

        return $data;
    }

    // Total Aksi Remote
    protected function getTotalRemoteData(): array
    {
        $teamId = $this->filterFormData['team'] ?? null;
        $year = $this->filterFormData['year'] ?? now()->year;

        // Inisialisasi array data dengan 0 untuk 12 bulan
        $data = array_fill(0, 12, 0);

        // Ambil data visit berdasarkan work='visit', team, dan year
        $query = Reporting::selectRaw('MONTH(reportings.date_visit) as month, COUNT(*) as count')
            ->join('outstandings', 'reportings.outstanding_id', '=', 'outstandings.id')
            ->join('locations', 'outstandings.location_id', '=', 'locations.id')
            ->where('reportings.work', 'remote')
            ->whereYear('reportings.date_visit', $year);

        if ($teamId) {
            $query->where('locations.team_id', $teamId);
        }

        $visits = $query->groupByRaw('MONTH(reportings.date_visit)')->get();

        // Mengisi array dengan hasil query
        foreach ($visits as $item) {
            $data[$item->month - 1] = $item->count;
        }

        return $data;
    }


}
