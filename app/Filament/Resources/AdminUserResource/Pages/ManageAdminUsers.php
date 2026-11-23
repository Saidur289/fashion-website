<?php

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Resources\AdminUserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAdminUsers extends ManageRecords
{
    protected static string $resource = AdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data): User {
                    $role = $data['role'] ?? 'staff';
                    $blocked = (bool) ($data['is_blocked'] ?? false);
                    unset($data['role'], $data['is_blocked']);
                    $user = new User($data);
                    $user->forceFill(['role' => $role, 'is_blocked' => $blocked])->save();
                    ActivityLog::record('created', "Created admin user {$user->name} (role: {$role})", $user);

                    return $user;
                }),
        ];
    }
}
