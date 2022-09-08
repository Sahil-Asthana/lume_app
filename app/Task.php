<?php

namespace App;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPassword ;


class Task extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 
        'description', 
        'creator',
        'assignee',
        'status',
        'due_date',
        'deleted_by'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        
    ];



 
//   protected static function boot()
//   {
//     parent::boot();
    
//     static::saved(function ($model) {
// /**
//        * If user email have changed email verification is required
//        */
//       if( $model->isDirty('due_date') ) {
//         $model->setAttribute('due_date', null);
//         $model->sendEmailVerificationNotification();
//                 }
//         });
//     }

}
