<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Services\OrderManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invoice')
                ->label('Print / PDF Invoice')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->url(fn () => route('admin.orders.invoice', $this->record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldStatus = $record->status;
        $oldPaymentStatus = $record->payment_status;
        $oldTracking = $record->tracking_code;

        $newStatus = $data['status'] ?? $oldStatus;
        $newPaymentStatus = $data['payment_status'] ?? $oldPaymentStatus;
        $newTracking = $data['tracking_code'] ?? $oldTracking;

        unset($data['status'], $data['payment_status'], $data['tracking_code'], $data['items']);
        $record->update($data);

        $mgr = app(OrderManager::class);
        if ($newTracking !== $oldTracking) {
            $mgr->updateTracking($record, $newTracking);
        }
        if ($newPaymentStatus !== $oldPaymentStatus) {
            $mgr->updatePaymentStatus($record, $newPaymentStatus);
        }
        if ($newStatus !== $oldStatus) {
            $mgr->transitionStatus($record, $newStatus, 'Updated on order edit page.', true);
        }

        return $record->fresh();
    }
}
