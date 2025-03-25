<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriberEmailResource\Pages;
use App\Filament\Resources\SubscriberEmailResource\RelationManagers;
use App\Models\SubscriberEmail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;

class SubscriberEmailResource extends Resource
{
    protected static ?string $model = SubscriberEmail::class;

    // Navigation settings
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Subscribers';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\DateTimePicker::make('verified_at')
                    ->label('Verified At')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'verified' => 'Verified',
                        'unverified' => 'Unverified',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['value'] === 'active',
                                fn (Builder $query) => $query->where('is_active', true)
                            )
                            ->when(
                                $data['value'] === 'inactive',
                                fn (Builder $query) => $query->where('is_active', false)
                            )
                            ->when(
                                $data['value'] === 'verified',
                                fn (Builder $query) => $query->whereNotNull('verified_at')
                            )
                            ->when(
                                $data['value'] === 'unverified',
                                fn (Builder $query) => $query->whereNull('verified_at')
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate selected')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
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
            'index' => Pages\ListSubscriberEmails::route('/'),
            'create' => Pages\CreateSubscriberEmail::route('/create'),
            'view' => Pages\ViewSubscriberEmail::route('/{record}'),
            'edit' => Pages\EditSubscriberEmail::route('/{record}/edit'),
        ];
    }

    // Make this always return true for troubleshooting
    public static function shouldRegisterNavigation(): bool
    {
        return true; // Temporarily force navigation visibility
    }
}
