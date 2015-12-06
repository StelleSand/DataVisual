<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\MerchandiseClass;
class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password','description'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function channels()
    {
        return $this->hasMany('App\Channel','create_user','id');
    }

    public function merchandiseClasses()
    {
        return $this->hasMany('App\MerchandiseClass','create_user','id');
    }

    public function units()
    {
        return $this->hasMany('App\Unit','create_user','id');
    }

    public function lines()
    {
        return $this->hasMany('App\Line','create_user','id');
    }

    public function charts()
    {
        return $this->hasMany('App\Chart','create_user','id');
    }

}
