<?php

namespace App\Filament\Exports;

use App\Models\DetailTransaksi;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Log;

class DetailTransaksiExporter extends Exporter
{
    protected static ?string $model = DetailTransaksi::class;

    public static function getColumns(): array
    {
        return [
            //
            ExportColumn::make('transaksi.tanggal')->label('Tanggal'),
            ExportColumn::make('akun.kode_akun')->label('Kode Akun'),
            ExportColumn::make('akun.nama_akun')->label('Nama Akun'),
            ExportColumn::make('debit'),
            ExportColumn::make('kredit'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your detail transaksi export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
