<?php

namespace App\Filament\Resources\SubscriberEmailResource\Pages;

use App\Filament\Resources\SubscriberEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscriberEmail extends EditRecord
{
    protected static string $resource = SubscriberEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
