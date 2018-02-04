<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Azurevm extends Model
{

    protected $guarded = [];
    protected $table = 'azurevms';

    public function getAWSAttribute()
    {
        return false;
    }

    public function getAzureAttribute()
    {
        return true;
    }

    protected $appends = ['aws','azure'];


}
