<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratKeluarResource\Pages;
use App\Filament\Resources\SuratKeluarResource\RelationManagers;
use App\Models\SuratKeluar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class SuratKeluarResource extends Resource
{
    protected static ?string $model = SuratKeluar::class;

    protected static ?string $navigationGroup = 'Surat';

    // protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationLabel = 'Surat Keluar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->required()
                    ->maxLength(255)
                    ->unique(SuratKeluar::class, 'nomor_surat', ignoreRecord: true),
                Forms\Components\TextInput::make('tujuan')
                    ->label('Tujuan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('perihal')
                    ->label('Perihal')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tanggal_keluar')
                    ->label('Tanggal Keluar')
                    ->required(),
                Forms\Components\FileUpload::make('file_surat')
                    ->label('File Surat (Opsional)')
                    ->directory('surat-keluar')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(10240) // Maksimum 10MB
                    ->multiple(false) // Hanya satu file
                    ->getUploadedFileNameForStorageUsing(function ($file) {
                        return 'surat-keluar/' . uniqid() . '.' . $file->getClientOriginalExtension();
                    }),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'dikirim' => 'Dikirim',
                        'diterima' => 'Diterima',
                    ])
                    ->default('draft')
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
                Tables\Columns\TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('perihal')
                    ->label('Perihal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_keluar')
                    ->label('Tanggal Keluar')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_surat')
                    ->label('File')
                    ->formatStateUsing(fn (?string $state): string => $state 
                        ? '<a href="' . Storage::url($state) . '" target="_blank">Lihat File</a>' 
                        : '-')
                    ->html(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'dikirim' => 'warning',
                        'diterima' => 'success',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'dikirim' => 'Dikirim',
                        'diterima' => 'Diterima',
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
            'index' => Pages\ListSuratKeluars::route('/'),
            'create' => Pages\CreateSuratKeluar::route('/create'),
            'edit' => Pages\EditSuratKeluar::route('/{record}/edit'),
        ];
    }
}
