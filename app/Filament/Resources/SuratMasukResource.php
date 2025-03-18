<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratMasukResource\Pages;
use App\Filament\Resources\SuratMasukResource\RelationManagers;
use App\Models\SuratMasuk;
use App\Notifications\SuratMasukCreated;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SuratMasukResource extends Resource
{
    protected static ?string $model = SuratMasuk::class;

    protected static ?string $navigationGroup = 'Surat';

    protected static ?string $navigationLabel = 'Surat Masuk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->required()
                    ->maxLength(255)
                    ->unique(SuratMasuk::class, 'nomor_surat', ignoreRecord: true),
                Forms\Components\TextInput::make('pengirim')
                    ->label('Pengirim')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('perihal')
                    ->label('Perihal')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->required(),
                Forms\Components\FileUpload::make('file_surat')
                    ->label('File Surat')
                    ->directory('surat-masuk')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(10240) // Maksimum 10MB
                    ->multiple(false) // Pastikan hanya satu file
                    ->getUploadedFileNameForStorageUsing(function ($file) {
                        return 'surat-masuk/' . uniqid() . '.' . $file->getClientOriginalExtension();
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Jika $state adalah array, ambil elemen pertama
                        if (is_array($state)) {
                            $set('file_surat', $state[0] ?? null);
                        }
                    }),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'baru' => 'Baru',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                    ])
                    ->default('baru')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pengirim')
                    ->label('Pengirim')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('perihal')
                    ->label('Perihal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_surat')
                    ->label('File')
                    ->formatStateUsing(fn (string $state): string => $state 
                        ? '<a href="' . Storage::url($state) . '" target="_blank">Lihat File</a>' 
                        : '-')
                    ->html(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'baru' => 'gray',
                        'diproses' => 'warning',
                        'selesai' => 'success',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'baru' => 'Baru',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
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
            'index' => Pages\ListSuratMasuks::route('/'),
            'create' => Pages\CreateSuratMasuk::route('/create'),
            'edit' => Pages\EditSuratMasuk::route('/{record}/edit'),
        ];
    }

    protected function afterCreate(): void
    {
        Log::info('created');
        $user = Auth::user();
        Log::info($user);
        // if ($user) {
        //     Notification::send($user, new SuratMasukCreated($record));
        // }
    }
}
