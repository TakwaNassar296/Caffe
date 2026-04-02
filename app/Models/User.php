<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Rank;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    /* edited by Mohamed Wlaa

    'edit_at' => '15 / 7 /2025',
    */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'password',
        'image',
        "total_points",
        "total_stars",
        "account_verified_at",
        'fcm_token',
        'stripe_customer_id',
        'stripe_payment_method',

    ];


    protected $appends = ['full_name'];

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
            'password' => 'hashed',
        ];
    }



    public function getGenerateCodeOtpAttribute(): string
    {
        return rand(1111, 9999);
    }
    public function otps()
    {
        return $this->hasMany(VerificationOtp::class);
    }

    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }
    public function locations()
    {
        return $this->hasMany(UserLocation::class);
    }
    public function rank()
    {
        $rank = Rank::where('min_stars', '<=', $this->total_stars)
            ->where('max_stars', '>=', $this->total_stars)
            ->first();

        if (!$rank) {
            $rank = Rank::orderByDesc('max_stars')->first();
        }

        return $rank;
    }

    public function getRankNameAttribute()
    {
        return $this->rank()?->name;
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Orders for this user where the order's branch is one of the user's linked branches (branch_user).
     */
    public function ordersOfBranch(): HasMany
    {
        return $this->hasMany(Order::class)
            ->whereIn('branch_id', function ($query) {
                $query->select('branch_id')
                    ->from('branch_user')
                    ->where('user_id', $this->getKey());
            });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user', 'branch_id', 'user_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }
}
