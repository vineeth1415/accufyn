<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'created_by_user_id',
        'created_via_staff',
        'is_active',
        'avatar',
        'package_id',
        'package_expires_at',
        'kyc_status',
        'kyc_document_type',
        'kyc_document_number',
        'kyc_document_url',
        'address',
        'emergency_contact',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'package_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'created_via_staff' => 'boolean',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function hasActivePackage(): bool
    {
        return $this->package_id !== null && (!$this->package_expires_at || $this->package_expires_at->isFuture());
    }

    public function hasReachedListingLimit(): bool
    {
        if (!$this->package) {
            return false;
        }

        if ($this->package->isUnlimitedListings()) {
            return false;
        }

        $activeCount = $this->properties()->whereIn('status', ['PUBLISHED', 'PENDING_VERIFICATION'])->count();
        return $activeCount >= $this->package->listing_limit;
    }

    public function isSuperAdmin(): bool
    {
        return in_array(strtoupper($this->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN']);
    }

    public function isAdmin(): bool
    {
        return in_array(strtoupper($this->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN', 'ADMIN']);
    }

    public function isAccountant(): bool
    {
        return strtoupper($this->role ?? '') === 'ACCOUNTANT';
    }

    public function isAuditor(): bool
    {
        return strtoupper($this->role ?? '') === 'AUDITOR';
    }

    public function isStaff(): bool
    {
        return in_array(strtoupper($this->role ?? ''), ['STAFF', 'SELLER', 'BUYER']);
    }

    public function canViewReports(): bool
    {
        return in_array(strtoupper($this->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN', 'ADMIN', 'ACCOUNTANT', 'AUDITOR']);
    }

    public function canExportReports(): bool
    {
        return in_array(strtoupper($this->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN', 'ADMIN', 'ACCOUNTANT', 'AUDITOR']);
    }

    public function canManageAccounting(): bool
    {
        return in_array(strtoupper($this->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN', 'ADMIN', 'ACCOUNTANT', 'STAFF']);
    }

    public function canManageLedgers(): bool
    {
        return in_array(strtoupper($this->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN', 'ADMIN', 'ACCOUNTANT']);
    }

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    public function canManageSettings(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    public function canAccessAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    public function canAccessStaff(): bool
    {
        return $this->isAdmin() || $this->isAccountant() || $this->isStaff();
    }

    public function getRoleBadgeAttribute(): array
    {
        return match (strtoupper($this->role ?? 'ADMIN')) {
            'SUPER_ADMIN', 'SUPERADMIN' => [
                'label' => 'Super Administrator (Top Owner)',
                'icon'  => 'fa-crown',
                'class' => 'bg-amber-100 text-amber-900 border-amber-300 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-700 font-bold'
            ],
            'ADMIN'      => ['label' => 'Administrator', 'icon' => 'fa-shield-halved', 'class' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800'],
            'ACCOUNTANT' => ['label' => 'Accountant',    'icon' => 'fa-calculator',    'class' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800'],
            'AUDITOR'    => ['label' => 'Auditor (Reports & Excel Only)', 'icon' => 'fa-file-excel', 'class' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800'],
            'STAFF'      => ['label' => 'Staff / Sales', 'icon' => 'fa-user',          'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800'],
            default      => ['label' => ucfirst(strtolower($this->role ?? 'User')), 'icon' => 'fa-user', 'class' => 'bg-slate-50 text-slate-700 border-slate-200'],
        };
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by_user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
