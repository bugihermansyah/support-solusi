<x-filament::page>
    <div class="space-y-4">
        <h1 class="text-xl font-bold">Laporan Masalah Produk - Tahun 2024</h1>
        <x-filament::table>
            <x-slot name="header">
                <x-filament::table.header>
                    <x-filament::table.header.cell>Product ID</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Product Name</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Jan</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Feb</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Mar</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Apr</x-filament::table.header.cell>
                    <x-filament::table.header.cell>May</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Jun</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Jul</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Aug</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Sep</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Oct</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Nov</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Dec</x-filament::table.header.cell>
                    <x-filament::table.header.cell>Total</x-filament::table.header.cell>
                </x-filament::table.header>
            </x-slot>

            <x-slot name="body">
                @php
                    $totals = array_fill(0, 12, 0);
                    $grandTotal = 0;
                @endphp

                @foreach ($products as $product)
                    @php
                        $rowTotal = $product->total_problems_jan + $product->total_problems_feb + $product->total_problems_mar +
                                    $product->total_problems_apr + $product->total_problems_may + $product->total_problems_jun +
                                    $product->total_problems_jul + $product->total_problems_aug + $product->total_problems_sep +
                                    $product->total_problems_oct + $product->total_problems_nov + $product->total_problems_dec;

                        $totals[0] += $product->total_problems_jan;
                        $totals[1] += $product->total_problems_feb;
                        $totals[2] += $product->total_problems_mar;
                        $totals[3] += $product->total_problems_apr;
                        $totals[4] += $product->total_problems_may;
                        $totals[5] += $product->total_problems_jun;
                        $totals[6] += $product->total_problems_jul;
                        $totals[7] += $product->total_problems_aug;
                        $totals[8] += $product->total_problems_sep;
                        $totals[9] += $product->total_problems_oct;
                        $totals[10] += $product->total_problems_nov;
                        $totals[11] += $product->total_problems_dec;
                        $grandTotal += $rowTotal;
                    @endphp
                    <x-filament::table.row>
                        <x-filament::table.cell>{{ $product->product_id }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->product_name }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_jan }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_feb }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_mar }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_apr }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_may }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_jun }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_jul }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_aug }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_sep }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_oct }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_nov }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $product->total_problems_dec }}</x-filament::table.cell>
                        <x-filament::table.cell>{{ $rowTotal }}</x-filament::table.cell>
                    </x-filament::table.row>
                @endforeach

                <x-filament::table.row>
                    <x-filament::table.cell colspan="2" class="font-bold">Total</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[0] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[1] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[2] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[3] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[4] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[5] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[6] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[7] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[8] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[9] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[10] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $totals[11] }}</x-filament::table.cell>
                    <x-filament::table.cell class="font-bold">{{ $grandTotal }}</x-filament::table.cell>
                </x-filament::table.row>
            </x-slot>
        </x-filament::table>
    </div>
</x-filament::page>
