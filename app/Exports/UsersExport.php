<?php
 
namespace App\Exports;
 
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
 
class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $userIds;

    public function __construct($userIds = null)
    {
        $this->userIds = $userIds;
    }

    public function query()
    {
        $user = auth()->user();
        $query = User::query()->with(['roles', 'instansi']);

        // 1. Filter by specific IDs if provided (Bulk Action)
        if ($this->userIds) {
            $query->whereIn('id', $this->userIds);
        }

        // 2. Multi-tenancy scoping (Same as UserResource::getEloquentQuery)
        if (!$user?->hasRole('super_admin')) {
            $tenantId = Filament::getTenant()?->id;
            if ($tenantId) {
                $query->whereHas('instansi', fn($q) => $q->where('instansis.id', $tenantId));
            } else {
                // If no tenant context, only show the user themselves
                $query->where('id', $user->id);
            }
        }

        return $query;
    }
 
    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'Email / Username',
            'Nomor Handphone',
            'Role',
            'Akses Instansi',
            'Tanggal Terdaftar',
        ];
    }
 
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone_number,
            $user->roles->pluck('name')->implode(', '),
            $user->instansi->pluck('name')->implode(', '),
            $user->created_at->format('d/m/Y H:i'),
        ];
    }
}
