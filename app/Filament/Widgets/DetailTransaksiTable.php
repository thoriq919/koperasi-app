<?php

namespace App\Filament\Widgets;

use App\Filament\Exports\DetailTransaksiExporter;
use App\Models\Akun;
use App\Models\DetailTransaksi;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class DetailTransaksiTable extends BaseWidget
{
    protected static ?string $heading = 'Detail Transaksi';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(DetailTransaksi::query())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('M j, Y'),
                Tables\Columns\TextColumn::make('akun.nama_akun')
                    ->label('Nama Akun')
                    ->searchable(),
                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit')
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('kredit')
                    ->label('Kredit')
                    ->money('IDR', true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kode_akun')
                    ->label('Kode Akun')
                    ->options(
                        Akun::pluck('kode_akun', 'id')->toArray() 
                    )
                    ->query(function ($query, array $data) {
                        if ($data['value']) {
                            $query->where('akun_id', $data['value']);
                        }
                    }),
                DateRangeFilter::make('created_at')->alwaysShowCalendar(),
            ])
            ->actions([
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportAction::make()->exporter(DetailTransaksiExporter::class)->label('Export Data')
                ]),
            ]);
    }

    
}
