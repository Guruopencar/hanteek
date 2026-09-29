<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name', 'email', 'phone', 'password',
        'role', 'active_profile', 'locale',
        'is_verified', 'is_active',
        'last_login_at', 'referral_code', 'referred_by',
        'stripe_customer_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'is_verified'       => 'boolean',
            'is_active'         => 'boolean',
            'password'          => 'hashed',
        ];
    }

    // ── Relationships ──────────────────────────────────────────

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function developerResume(): HasOne
    {
        return $this->hasOne(DeveloperResume::class);
    }

    public function recruiterProfile(): HasOne
    {
        return $this->hasOne(RecruiterProfile::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class, 'owner_id');
    }

    public function contractsAsOwner(): HasMany
    {
        return $this->hasMany(Contract::class, 'owner_id');
    }

    public function contractsAsDeveloper(): HasMany
    {
        return $this->hasMany(Contract::class, 'developer_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(VacancyApplication::class, 'applicant_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'participant_1')
            ->orWhere('participant_2', $this->id);
    }

    public function pushTokens(): HasMany
    {
        return $this->hasMany(PushToken::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function bansGiven(): HasMany
    {
        return $this->hasMany(UserBan::class, 'banner_id');
    }

    public function bansReceived(): HasMany
    {
        return $this->hasMany(UserBan::class, 'banned_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isProgrammer(): bool
    {
        return $this->role === 'programmer';
    }

    public function isProjectOwner(): bool
    {
        return $this->role === 'project_owner';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isBannedBy(int $userId): bool
    {
        return $this->bansReceived()
            ->where('banner_id', $userId)
            ->whereNull('unbanned_at')
            ->exists();
    }

    public function getFullNameAttribute(): string
    {
        return $this->profile
            ? trim($this->profile->first_name . ' ' . $this->profile->last_name)
            : $this->name;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }
}
