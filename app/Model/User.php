<?php
namespace App\Model;

// use Illuminate\Database\Eloquent\Model;
// use Jenssegers\Mongodb\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DesignMyNight\Mongodb\Auth\User as MonogoAuth;

class User extends MonogoAuth
{
    use HasApiTokens, Notifiable;
    //
    protected $connection = 'mongodb';
    protected $collection = 'User';
    
    protected $fillable = [
        'first_name','last_name','email','username','confirmed','confirm_str','profile_pic_url','cover_pic_url','_token'
    ];
    protected $hidden = [
        'password'
    ];
}
