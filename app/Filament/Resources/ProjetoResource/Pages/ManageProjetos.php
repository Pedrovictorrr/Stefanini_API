<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProjetos extends ManageRecords
{
    protected static string $resource = ProjetoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
