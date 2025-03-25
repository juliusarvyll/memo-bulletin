<?php

namespace App\Filament\Resources\SubscriberEmailResource\Pages;

use App\Filament\Resources\SubscriberEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscriberEmail extends ViewRecord
{
    protected static string $resource = SubscriberEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
