<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerTax extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];
}
