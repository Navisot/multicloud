<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AWSvm extends Model
{
    protected $guarded = [];
    protected $table = 'awsvms';

    public function getAWSAttribute()
    {
        return true;
    }

    public function getAzureAttribute()
    {
        return false;
    }

    protected $appends = ['aws','azure'];
}
