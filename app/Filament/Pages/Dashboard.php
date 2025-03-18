<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DetailTransaksiTable;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected function getWidgets(): array
    {
        return [
            DetailTransaksiTable::class,
        ];
    }
}
