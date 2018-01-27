<?php

namespace App\Http\Libraries;
use App\Azurevm;

class CustomHelper {

    public static function updateIPAddress($vm_id, $new_ip) {

        $new_data['ip_address'] = $new_ip;

        if ( Azurevm::where('id', $vm_id)->update($new_data) ){
            return true;
        }

        return false;

    }

}