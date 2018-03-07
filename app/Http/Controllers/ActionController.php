<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Azurevm;
use App\AWSvm;
use App\User;
use App\Http\Libraries\CustomHelper;

class ActionController extends RestController
{
    public function getVirtualMachines() {

        $user_id = 1;

        $azure_vms = Azurevm::where('user_id', $user_id)->get()->toArray();

        $aws_vms = AWSvm::where('user_id', $user_id)->get()->toArray();

        $map_azure = array_map(function($item){
            return [
                'azure' => $item['azure'],
                'aws' => $item['aws'],
                'vm' => $item['vm'],
                'id' => $item['id'],
                'vm_size' => $item['vm_size'],
                'host' => 'AZURE',
                'location' => $item['location'],
                'status' => $item['status'],
                'ip_address' => $this->getAzureVMPublicIpAddress($item['ip_label'], $item['id']),
            ];
        },$azure_vms);

        $map_aws = array_map(function($item){

            $api_results = $this->getAWSIPAddressAndStatus($item['instance_id']);
            $status = $api_results['status'];
            $ip_address = $api_results['ip_address'];

            return [
                'azure' => $item['azure'],
                'aws' => $item['aws'],
                'vm' => $item['vm'],
                'id' => $item['id'],
                'vm_size' => $item['vm_size'],
                'host' => 'AWS',
                'location' => $item['location'],
                'status' => $status,
                'ip_address' => $ip_address
            ];
        },$aws_vms);


        $all_vms = array_merge($map_azure, $map_aws);


        return response()->json(['vms' => $all_vms], 200);

    }

    public function testMethod() {

        $instances = [];

        $vms = [4,5];

        foreach ($vms as $vm){
            $vm = AWSvm::find($vm);
            $temp_array = array('Key' => 'Name', 'Type'=>'KEY_AND_VALUE', 'Value' => $vm->vm);
            array_push($instances, $temp_array);
        }

        dd($instances);

    }
}
