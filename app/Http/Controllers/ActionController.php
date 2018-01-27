<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Azurevm;
use App\Http\Libraries\CustomHelper;

class ActionController extends RestController
{
    public function getVirtualMachines($user_id) {

        $vms = Azurevm::where('user_id', $user_id)->get();

        foreach ($vms as $vm) {

            $ip_label = $vm->ip_label;

            $current_ip = $this->getAzureVMPublicIpAddress($ip_label);

            $vm['ip_address'] = $current_ip;

            CustomHelper::updateIPAddress($vm->id, $current_ip);

        }

        return response()->json($vms);

    }
}
