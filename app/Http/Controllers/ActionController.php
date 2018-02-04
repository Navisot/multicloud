<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Azurevm;
use App\AWSvm;
use App\Http\Libraries\CustomHelper;

class ActionController extends RestController
{
    public function getVirtualMachines($user_id) {

        $azure_vms = Azurevm::where('user_id', $user_id)->get()->toArray();

        $aws_vms = AWSvm::where('user_id', $user_id)->get()->toArray();

        $map_azure = array_map(function($item){
            return [
                'azure' => $item['azure'],
                'aws' => $item['aws'],
                'vm' => $item['vm'],
                'vm_size' => $item['vm_size'],
                'host' => 'AZURE',
                'location' => $item['location'],
                'status' => $item['status'],
                'ip_address' => $this->getAzureVMPublicIpAddress($item['ip_label'], $item['id']),
            ];
        },$azure_vms);

        $map_aws = array_map(function($item){
            return [
                'azure' => $item['azure'],
                'aws' => $item['aws'],
                'vm' => $item['vm'],
                'vm_size' => $item['vm_size'],
                'host' => 'AWS',
                'location' => $item['location'],
                'status' => $item['status'],
                'ip_address' => $this->getAWSIPAddress($item['instance_id'])
            ];
        },$aws_vms);


        $all_vms = array_merge($map_azure, $map_aws);


        return response()->json($all_vms);

    }
}
