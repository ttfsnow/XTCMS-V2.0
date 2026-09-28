<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin';

    protected $primaryKey = 'id';

    protected $fillable = ['username', 'password'];

    public function getAuthPassword(): string
    {
        return $this->password;
    }
}
