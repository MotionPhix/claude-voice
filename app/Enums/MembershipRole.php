<?php

namespace App\Enums;

enum MembershipRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Manager = 'manager';
    case Accountant = 'accountant';
    case User = 'user';

    /**
     * Get all role values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the display name for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Administrator',
            self::Manager => 'Manager',
            self::Accountant => 'Accountant',
            self::User => 'User',
        };
    }

    /**
     * Get the description for the role.
     */
    public function description(): string
    {
        return match ($this) {
            self::Owner => 'Full access including billing, can delete organization',
            self::Admin => 'Manage users and full invoice access',
            self::Manager => 'Create and edit invoices, limited settings',
            self::Accountant => 'View all invoices and manage payments',
            self::User => 'View assigned invoices only',
        };
    }

    /**
     * Get permissions for this role.
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner => [
                'organization.update',
                'organization.delete',
                'organization.billing',
                'members.invite',
                'members.manage',
                'members.remove',
                'invoices.*',
                'clients.*',
                'payments.*',
                'recurring-invoices.*',
                'reports.*',
                'settings.*',
            ],
            self::Admin => [
                'members.invite',
                'members.manage',
                'invoices.*',
                'clients.*',
                'payments.*',
                'recurring-invoices.*',
                'reports.*',
                'settings.view',
            ],
            self::Manager => [
                'invoices.create',
                'invoices.update',
                'invoices.view',
                'invoices.send',
                'clients.create',
                'clients.update',
                'clients.view',
                'payments.create',
                'payments.view',
                'recurring-invoices.view',
                'reports.view',
            ],
            self::Accountant => [
                'invoices.view',
                'invoices.update',
                'clients.view',
                'payments.*',
                'reports.*',
            ],
            self::User => [
                'invoices.view',
                'clients.view',
                'payments.view',
            ],
        };
    }

    /**
     * Check if this role has a specific permission.
     */
    public function can(string $permission): bool
    {
        $permissions = $this->permissions();

        // Check for exact match
        if (in_array($permission, $permissions)) {
            return true;
        }

        // Check for wildcard permissions
        foreach ($permissions as $rolePermission) {
            if (str_ends_with($rolePermission, '.*')) {
                $prefix = substr($rolePermission, 0, -2);
                if (str_starts_with($permission, $prefix.'.')) {
                    return true;
                }
            }
        }

        return false;
    }
}
