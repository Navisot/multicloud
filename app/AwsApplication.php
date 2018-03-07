<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AwsApplication extends Model
{
    protected $table = 'awsapplication';
    protected $guarded = [];

    public function user() {
        return $this->belongsTo('App\User','user_id', 'id');
    }
}
