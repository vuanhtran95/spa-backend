<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

/**
 * Class Address
 * @package App
 * @mixin Builder
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Find the user instance for the given username.
     *
     * @param string $username
     * @return \App\User
     */
    public function findForPassport($username)
    {
        return $this->where('email', $username)->first();
    }

//    /**
//     * Add a password validation callback
//     *
//     * @param type $password
//     * @return boolean Whether the password is valid
//     */
//    public function validateForPassportPasswordGrant($password)
//    {
    ////        var_dump(Hash::make($password, [
    ////            'rounds' => 12,
    ////        ]));
    ////        die(var_dump($this->password));
//
    ////        die(var_dump(Hash::needsRehash($this->password)));
//
//        return Hash::check($password, $this->password);
//
//        $hashedPassword = Hash::make($password);
//        return $hashedPassword == $this->password;
//    }

    public function employee()
    {
        return $this->hasOne('App\Employee');
    }
}
