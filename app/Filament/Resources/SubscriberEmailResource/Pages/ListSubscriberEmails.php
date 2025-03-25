<?php

namespace App\Filament\Resources\SubscriberEmailResource\Pages;

use App\Filament\Resources\SubscriberEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubscriberEmails extends ListRecords
{
    protected static string $resource = SubscriberEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
