<?php

namespace models;

use models\User;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model {
    
    protected $table = 'users_permissions';

    protected $fillable = [
        'admin',
        'owner',
        'host',
        'artist',
        'user',
    ];

    public static $defaults = [
        'admin' => false,
        'owner' => false,
        'host' => false,
        'artist' => false,
        'user' => true,
    ];
}
