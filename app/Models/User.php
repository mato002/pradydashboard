<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domain\Rbac\ActiveRoleService;
use App\Domain\Rbac\RbacGuard;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'password_changed_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isHardcodedSuperuser(): bool
    {
        return strcasecmp($this->email, (string) config('superuser.email')) === 0;
    }

    public function passwordExpired(): bool
    {
        if ($this->password_changed_at === null) {
            return true;
        }

        $expiryDays = max(1, (int) config('auth.password_expiry_days', 28));

        return $this->password_changed_at->lte(now()->subDays($expiryDays));
    }

    public function mustChangePassword(): bool
    {
        return $this->passwordExpired();
    }

    public function daysUntilPasswordExpires(): ?int
    {
        if ($this->password_changed_at === null) {
            return 0;
        }

        $expiryDays = max(1, (int) config('auth.password_expiry_days', 28));
        $expiresAt = $this->password_changed_at->copy()->addDays($expiryDays);

        return (int) now()->diffInDays($expiresAt, false);
    }

    public function markPasswordChanged(): void
    {
        $this->forceFill(['password_changed_at' => now()])->save();
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }

    public function activeRoleRecord(): HasOne
    {
        return $this->hasOne(UserActiveRole::class);
    }

    public function roleSwitchLogs(): HasMany
    {
        return $this->hasMany(RoleSwitchLog::class);
    }

    public function getActiveRoleAssignmentAttribute(): ?UserRoleAssignment
    {
        return app(ActiveRoleService::class)->getActiveAssignment($this);
    }

    public function canPermission(string $permission, array $scope = []): bool
    {
        return app(RbacGuard::class)->can($this, $permission, $scope);
    }
}
