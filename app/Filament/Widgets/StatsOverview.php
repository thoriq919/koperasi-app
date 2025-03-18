<?php

namespace App\Filament\Widgets;

use App\Models\DetailTransaksi;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $totalKasDebit = DetailTransaksi::whereHas('akun', function ($query) {
            $query->where('nama_akun', 'like', '%Kas%');
        })->sum('debit');

        $totalKasKredit = DetailTransaksi::whereHas('akun', function ($query) {
            $query->where('nama_akun', 'like', '%Kas%');
        })->sum('kredit');

        $totalBankDebit = DetailTransaksi::whereHas('akun', function ($query) {
            $query->where('nama_akun', 'like', '%Bank%');
        })->sum('debit');

        $totalBankKredit = DetailTransaksi::whereHas('akun', function ($query) {
            $query->where('nama_akun', 'like', '%Bank%');
        })->sum('kredit');

        $startDate = Carbon::now()->subDays(6); 
        $dates = collect(range(0, 6))->map(fn ($day) => $startDate->copy()->addDays($day)->format('Y-m-d'));

        $kasDebitChart = $dates->map(function ($date) {
            return DetailTransaksi::whereHas('akun', function ($query) {
                $query->where('nama_akun', 'like', '%Kas%');
            })
                ->whereDate('created_at', $date)
                ->sum('debit');
        })->toArray();

        $kasKreditChart = $dates->map(function ($date) {
            return DetailTransaksi::whereHas('akun', function ($query) {
                $query->where('nama_akun', 'like', '%Kas%');
            })
                ->whereDate('created_at', $date)
                ->sum('kredit');
        })->toArray();

        $bankDebitChart = $dates->map(function ($date) {
            return DetailTransaksi::whereHas('akun', function ($query) {
                $query->where('nama_akun', 'like', '%Bank%');
            })
                ->whereDate('created_at', $date)
                ->sum('debit');
        })->toArray();

        $bankKreditChart = $dates->map(function ($date) {
            return DetailTransaksi::whereHas('akun', function ($query) {
                $query->where('nama_akun', 'like', '%Bank%');
            })
                ->whereDate('created_at', $date)
                ->sum('kredit');
        })->toArray();

        return [
            Stat::make('Total Kas (Kredit)',  number_format($totalKasKredit))
                ->description('Saldo Kas Kredit')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger')
                ->chart($kasKreditChart),
            Stat::make('Total Kas (Debit)',  number_format($totalKasDebit))
                ->description('Saldo Kas Debit')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($kasDebitChart),
            Stat::make('Total Bank (Kredit)',  number_format($totalBankKredit))
                ->description('Saldo Bank Kredit')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger')
                ->chart($bankKreditChart),
            Stat::make('Total Bank (Debit)',  number_format($totalBankDebit))
                ->description('Saldo Bank Debit')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($bankDebitChart),
        ];
    }
}
