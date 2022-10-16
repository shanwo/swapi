<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class UserAttributeModel extends Authenticatable
{
    protected $table = 'sw_user_attribute';
    public $timestamps = false;
}
