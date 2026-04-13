<?php

namespace App\Filament\Resources\PpobProducts\Pages;

use App\Filament\Resources\PpobProducts\PpobProductResource;
use App\Services\PpobCatalogService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPpobProducts extends ListRecords
{
    protected static string $resource = PpobProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncPrepaid')
                ->label(__('filament.resources.ppob_products.actions.sync_prepaid'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->authorize(fn (): bool => auth()->user()?->can('Update:PpobProduct') ?? false)
                ->action(function (): void {
                    $count = app(PpobCatalogService::class)->syncFromDigiflazz('prepaid');

                    Notification::make()
                        ->title(__('filament.resources.ppob_products.actions.sync_success_title'))
                        ->body(trans('filament.resources.ppob_products.actions.sync_success_body', ['count' => $count, 'type' => 'prepaid']))
                        ->success()
                        ->send();
                }),
            Action::make('syncPostpaid')
                ->label(__('filament.resources.ppob_products.actions.sync_postpaid'))
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->authorize(fn (): bool => auth()->user()?->can('Update:PpobProduct') ?? false)
                ->action(function (): void {
                    $count = app(PpobCatalogService::class)->syncFromDigiflazz('postpaid');

                    Notification::make()
                        ->title(__('filament.resources.ppob_products.actions.sync_success_title'))
                        ->body(trans('filament.resources.ppob_products.actions.sync_success_body', ['count' => $count, 'type' => 'postpaid']))
                        ->success()
                        ->send();
                }),
        ];
    }
}
