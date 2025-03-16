<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiResource\Pages;
use App\Filament\Resources\TransaksiResource\RelationManagers;
use App\Models\Akun;
use App\Models\Transaksi;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()->toDateString()),
                        Forms\Components\Select::make('tipe_transaksi')
                            ->label('Tipe Transaksi')
                            ->options([
                                'Bank' => 'Bank',
                                'Kas' => 'Kas',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('keterangan')
                            ->label('Keterangan')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Detail Transaksi')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->relationship('details')
                            ->schema([
                                Forms\Components\Select::make('akun_id')
                                    ->label('Akun')
                                    ->options(Akun::all()->pluck('nama_akun', 'id'))
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('debit')
                                    ->label('Debit')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->live(onBlur: true, debounce: 100)
                                    ->dehydrateStateUsing(fn ($state) => $state ?? 0),
                                Forms\Components\TextInput::make('kredit')
                                    ->label('Kredit')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required()
                                    ->live(onBlur: true, debounce: 100)
                                    ->dehydrateStateUsing(fn ($state) => $state ?? 0),
                            ])
                            ->columns(3)
                            ->addActionLabel('Tambah Baris')
                            ->defaultItems(1)
                            ->itemLabel(fn (array $state): string => Akun::find($state['akun_id'])?->nama_akun ?? 'Baris Baru')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set, $livewire) {
                                $details = $get('details') ?? [];
                                $totalDebit = collect($details)->sum(fn ($item) => floatval($item['debit'] ?? 0));
                                $totalKredit = collect($details)->sum(fn ($item) => floatval($item['kredit'] ?? 0));
                                $selisih = $totalDebit - $totalKredit;

                                $set('total_debit', $totalDebit);
                                $set('total_kredit', $totalKredit);
                                $set('selisih', $selisih);

                                // Validasi selisih di form
                                if ($selisih != 0) {
                                    $livewire->addError('details', 'Total Debit dan Kredit harus sama. Selisih saat ini: Rp ' . number_format($selisih, 2));
                                } else {
                                    $livewire->resetErrorBag('details'); // Hapus error jika selisih 0
                                }
                            }),

                        Forms\Components\TextInput::make('total_debit')
                            ->label('Total Debit')
                            ->disabled()
                            ->default(0)
                            ->numeric()
                            ->prefix('Rp')
                            ->live(),
                        Forms\Components\TextInput::make('total_kredit')
                            ->label('Total Kredit')
                            ->disabled()
                            ->default(0)
                            ->numeric()
                            ->prefix('Rp')
                            ->live(),
                        Forms\Components\TextInput::make('selisih')
                            ->label('Selisih')
                            ->disabled()
                            ->default(0)
                            ->numeric()
                            ->prefix('Rp')
                            ->live()
                            ->rules([
                                fn () => function (string $attribute, $value, Closure $fail) {
                                    if ($value !== 0 && $value !== '0' && $value !== 0.0) {
                                        $fail("Selisih harus bernilai 0");
                                    }
                                },
                            ])
                            ->afterStateHydrated(function ($state, callable $set) {
                                $details = $state['details'] ?? [];
                                $totalDebit = collect($details)->sum(fn ($item) => floatval($item['debit'] ?? 0));
                                $totalKredit = collect($details)->sum(fn ($item) => floatval($item['kredit'] ?? 0));
                                $set('total_debit', $totalDebit);
                                $set('total_kredit', $totalKredit);
                                $set('selisih', $totalDebit - $totalKredit);
                            }),
                    ])
                    ->columns(1),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipe_transaksi')
                    ->label('Tipe Transaksi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('details_sum_debit')
                    ->label('Total Debit')
                    ->sum('details', 'debit')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('details_sum_kredit')
                    ->label('Total Kredit')
                    ->sum('details', 'kredit')
                    ->money('IDR'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe_transaksi')
                    ->label('Tipe Transaksi')
                    ->options([
                        'Bank' => 'Bank',
                        'Kas' => 'Kas',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }
}
