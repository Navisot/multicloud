<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Aws\Ec2\Ec2Client;

class AWSRestController extends Controller
{
    protected $version = 'latest';
    protected $region = 'eu-central-1';
    protected $aws_access_key;
    protected $aws_secret_key;

    public function __construct(){
        $this->aws_access_key = env('AES_ACCESS_KEY');
        $this->aws_secret_key = env('AES_SECRET_KEY');
    }

    public function getAWSVMs(){

        $ec2 = new Ec2Client([
            'region' => $this->region,
            'version' => $this->version,
            'credentials' => array('key' => $this->aws_access_key, 'secret' => $this->aws_secret_key)
        ]);

        $result = $ec2->describeInstances();

        $vms = [];
        $instances = $result["Reservations"][0]["Instances"];
        foreach ($instances as $index => $instance) {
            $vms[$index]["name"] = $instance["Tags"][0]["Value"];
            $vms[$index]["status"] = $instance["State"]["Name"];
            $vms[$index]["ip_address"] = $instance["PublicIpAddress"];
            $vms[$index]["type"] = $instance["InstanceType"];
            $vms[$index]["zone"] = $instance["Placement"]["AvailabilityZone"];
        }

        dd($vms);

    }
}
