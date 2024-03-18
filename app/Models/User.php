<?php

namespace App\Models;

use Hashids\Hashids;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const HASH_SALT = 'BADC0DE'; // Just random string, nothing special
    public const MIN_HASH_LENGTH = 10;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Get users as associative array.
    // We will use this for dropdowns
    public static function getUsersAssc()
    {
        $hashids = new Hashids(self::HASH_SALT, self::MIN_HASH_LENGTH);

        $user = User::selectRaw("id, CONCAT_WS(', ', lastname, firstname) as user")->get();
        $dataset = [];

        foreach ($user as $row)
        {
            $key = $hashids->encode($row->id);
            $dataset[$row->user] = $key;
        }
        error_log(print_r($dataset, true));
        return $dataset;
    }
}
