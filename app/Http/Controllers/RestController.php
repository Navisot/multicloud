<?php

namespace App\Http\Controllers;

use App\User;
use App\AwsApplication;
use Aws\CodeDeploy\CodeDeployClient;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\UserToken;
use Carbon\Carbon;
use App\Azurevm;
use App\AWSvm;
use \Aws\Ec2\Ec2Client;
use App\Http\Libraries\CustomHelper;
use WindowsAzure\Common\ServicesBuilder;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
use MicrosoftAzure\Storage\Blob\Models\CreateBlobOptions;
use MicrosoftAzure\Storage\Common\ServiceException;
use Auth;


class RestController extends Controller
{
    // Azure
    protected $azure_tenant_id = '75eef9f9-3a1c-4c9e-b8ea-192bb3740930';
    protected $azure_subscriptionId = '5c347aaf-e734-4faf-9545-499386d2e5cd';
    protected $azure_grant_type = 'client_credentials';
    protected $azure_client_id = 'aab2069b-eef0-4d87-a904-ca45299f26f4';
    protected $azure_client_secret = '+KrUwCZn7Q1CW6whNPTHfegPjcAJJDPd4EN7+ppMiDg=';
    protected $azure_resource = 'https://management.azure.com/';
    protected $azure_token_url = 'https://login.microsoftonline.com/75eef9f9-3a1c-4c9e-b8ea-192bb3740930/oauth2/token';
    protected $azure_resource_group = 'DockerResourceGroup';
    protected $azure_vm_name = 'dockerVM';
    protected $azure_api_version = '2017-03-30';
    protected $storage_key = 'SYBYeeUJyxdUa70e4Qhdr3cGcW/sYdSYMfrJn1JQEm9ROtYtTTUQzyxCJnHb+sNtdJwYEjdNLMTvAVYrwPDKSw==';
    protected $storage_name = 'navisotdata';

    // AWS
    protected $version = 'latest';
    protected $region = 'eu-central-1';
    protected $aws_access_key = 'AKIAJNJJ75WMLLNOL5PQ';
    protected $aws_secret_key = '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';

    public function createBlob(){
        $connectionString = "DefaultEndpointsProtocol=https;AccountName=".$this->storage_name.";AccountKey=".$this->storage_key;
        // Create blob REST proxy.
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($connectionString);

        $file = base_path('/public/user_data/application.zip');

        if(is_readable($file)){
            $content = fopen($file, "r");
        }else{
            return "File not found";
        }

        $blob_name = "deploydata";

        try {
            $options = new CreateBlobOptions();
            $options->setBlobContentType("application/zip");

            //Upload blob
            $blobRestProxy->createBlockBlob("navisotcontainer", $blob_name, $content, $options);

        }
        catch(ServiceException $e){
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }

        @unlink($file);

        return "file uploaded";
    }


    public function getAzureAccessToken() {

        $db_token = UserToken::where('user_id', Auth::user()->id)->orderBy('created_at','desc')->first();

        if($db_token != null && !empty($db_token)){

            $azure_access_token = $db_token->azure_access_token;

            $created_at = Carbon::parse($db_token->created_at);

            $now = Carbon::now();

            $diffInHours = $created_at->diffInHours($now);

            if ($diffInHours < 1) {
                return $azure_access_token;
            }

        }

        $client = new Client();

        $result = $client->post($this->azure_token_url, [
            'form_params' => [
                'grant_type' => $this->azure_grant_type,
                'client_id' => $this->azure_client_id,
                'client_secret' => $this->azure_client_secret,
                'resource' => $this->azure_resource,
            ]
        ]);

        $response = $result->getBody();

        $object = json_decode($response);

        $azure_access_token = $object->access_token;

        $ut = new UserToken();
        $ut->user_id = Auth::user()->id;
        $ut->azure_access_token = $azure_access_token;
        $ut->save();

        return $azure_access_token;

    }

    public function stopAzureVM($vm_id) {

        $vm_name = Azurevm::where('id', $vm_id)->first()->vm;

        $token = $this->getAzureAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' .  $token
        ];

        $client = new Client();

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.
            $this->azure_resource_group.'/providers/Microsoft.Compute/virtualMachines/'.$vm_name.'/powerOff?api-version=2017-03-30';

        $result = $client->post($url, [
            'headers' => $headers
        ]);

        $response = $result->getStatusCode();

        if ($response == 200 || $response == 202){

            $update['status'] = 'down';

            Azurevm::where('id', $vm_id)->update($update);

            $vms = Azurevm::where('user_id', Auth::user()->id)->get();

            return response()->json(array('status' => 'OK', 'vms' => $vms));
        }

    }

    public function startAzureVM($vm_id) {

        $vm_name = Azurevm::where('id', $vm_id)->first()->vm;

        $token = $this->getAzureAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' .  $token
        ];

        $client = new Client();

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.
            $this->azure_resource_group.'/providers/Microsoft.Compute/virtualMachines/'.$vm_name.'/start?api-version=2017-03-30';

        $result = $client->post($url, [
            'headers' => $headers
        ]);

        $response = $result->getStatusCode();

        if ($response == 200 || $response == 202){

            $update['status'] = 'up';

            Azurevm::where('id', $vm_id)->update($update);

            $vms = Azurevm::where('user_id', Auth::user()->id)->get();

            return response()->json(array('status' => 'OK', 'vms' => $vms));
        }

    }

    public function createAzureIP($client, $ip_label, $token) {

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $body = ['location' => 'westeurope'];

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Network/publicIPAddresses/'.$ip_label.'?api-version=2017-09-01';

        $promise = $client->requestAsync('PUT', $url, [
            'headers' => $headers,
            'json' => $body
        ]);

        $promise->then(function (ResponseInterface $response) {
            $object = json_decode($response->getBody());
            // Do something with the profile.
            return $object->id;
        });

        $object_id = $promise->wait();

        return $object_id;

    }

    public function createAzureVirtualNetwork($client, $virtualNetworkName, $token) {

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $body = [
            'location' => 'west europe',
            'properties' => [
                'addressSpace' => [
                    'addressPrefixes' => [
                        '10.0.0.0/16'
                    ]
                ]
            ]
        ];


        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Network/virtualNetworks/'.$virtualNetworkName.'?api-version=2017-09-01';

        $result = $client->put($url, [
            'headers' => $headers,
            'json' => $body
        ]);

        $response = $result->getBody();
        $object = json_decode($response);
        $subnet_body = ['location' => 'west europe', 'properties' => ['addressPrefix' => '10.0.0.0/16']];
        $subnet_url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Network/virtualNetworks/'.$virtualNetworkName.'/subnets/default?api-version=2017-09-01';

        $subnet_client = $client;

        $subnet_result = $subnet_client->put($subnet_url, [
            'headers' => $headers,
            'json' => $subnet_body
        ]);

        $subnet_response = $subnet_result->getBody();
        $subnet_object = json_decode($subnet_response);
        $subnet_resource_id = $subnet_object->id;

        return array('vnet_name' => $object->name, 'subnet_id' => $subnet_resource_id);
    }



    public function createAzureNetworkInterface($client, $want_ip_label, $want_virtualNetworkName, $token) {

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $ip_address = $this->createAzureIP($client, $want_ip_label, $token);

        $ip_address = json_decode($ip_address->getBody());

        $ip_address = $ip_address->id;

        $vnet_info = $this->createAzureVirtualNetwork($client, $want_virtualNetworkName, $token);

        sleep(10);

        $subnet_id = $vnet_info['subnet_id'];

        $body = [

            'location' => 'west europe',
            'properties' => [
                'enableAcceleratedNetworking' => false,
                'ipConfigurations' => [
                    [
                        'name' => 'ipconfiguraion',
                        'properties' => [
                            'publicIPAddress' => [
                                'id' => $ip_address
                            ],
                            'subnet' => [
                                'id' => $subnet_id
                            ]
                        ]
                    ]
                ]
            ]

        ];

        $interface_label = $want_virtualNetworkName . '_interface';

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Network/networkInterfaces/'.$interface_label.'?api-version=2017-09-01';

        $promise = $client->requestAsync('PUT', $url, [
            'headers' => $headers,
            'json' => $body
        ]);

        $promise->then(function (ResponseInterface $response) {
            $object = json_decode($response->getBody());
            return $object->id;
        });

        $object = $promise->wait();

        $answer = json_decode($object->getBody());

        if($answer->id){
            return array('status' => 'ok', 'id' => $answer->id);
        } else {
            return array('status' => 'error');
        }

    }


    public function createAzureVM($want_vm_name) {

        $token = $this->getAzureAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $client = new Client();

        $vm_os_disk_name = $want_vm_name .'_disk';
        $ipLabel = $want_vm_name . '_ip_address';
        $virtual_network = $want_vm_name . '_virtual_network';

        $newNetworkInterface = $this->createAzureNetworkInterface($client, $ipLabel, $virtual_network, $token);

        if($newNetworkInterface['status'] == 'error'){
            return response()->json(['error' => 'error'], 200);
        }

        $body = [
            'id' => '/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Compute/virtualMachines/'. $want_vm_name,
            'name' => $want_vm_name,
            'type' => 'Microsoft.Compute/virtualMachines',
            'location' => 'westeurope',
            'properties' => [
                'hardwareProfile' => [
                    'vmSize' => 'Standard_B2ms'
                ],
                'storageProfile' => [
                    'imageReference' => [
                        'id' => '/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Compute/images/dockerImg'
                    ],
                    'osDisk' => [
                        'name' => $vm_os_disk_name,
                        'osType' => 'Linux',
                        'createOption' => 'fromImage'
                    ],
                    'dataDisks' => []
                ],
                'osProfile' => [
                    'computerName' => $want_vm_name,
                    'adminUsername' => 'dockeruser',
                    'linuxConfiguration' => [
                        'disablePasswordAuthentication' => 'true',
                        'ssh' => [
                            'publicKeys' => [
                                [
                                    'path' => '/home/dockeruser/.ssh/authorized_keys',
                                    'keyData' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCjjZjwc+1NJQElFNVQajVtLpyg6BR3EwuXzLriPrVXKZ4/twcmRGb8hivDX014nxMzCWE4SNeIoabiBvFT6QtX1/W4NSTz0jpZxpklc1FhjyowzLplp0Fodyh5clTioSGKwz45Dfsev3jlwZddU69VFNDEwcJayKmy4meYp3dTDD+IFYa/D5eL3m6P7tgY/z/Rt5slzTSAXKK1p4OGTfTRrynpqBxelOBkCicOqCLW6YWnF470Vci31W3lUMZqbSJz8xlXE9NQXvHE9m36iXdBKss7MJcY/0dns3SBma/YKvPwiEn4W0c2ZbZZQ4TTwS5sQHAhgbsTt+SbUv8iCksv'
                                ]
                            ]
                        ]
                    ]
                ],
                'networkProfile' => [
                    'networkInterfaces' => [
                        [
                            'id' => $newNetworkInterface['id']
                        ]
                    ]
                ],
                'provisioningState' => 'succeeded'
            ]
        ];

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Compute/virtualMachines/'.$want_vm_name.'?api-version=2016-04-30-preview';

        $result = $client->put($url,[

            'headers' => $headers,
            'json' => $body

        ]);

        $response = $result->getBody();

        $object = json_decode($response);

        if($object->name == $want_vm_name) {

            $data['user_id'] = Auth::user()->id;
            $data['vm'] = $object->name;
            $data['admin_username'] = $object->properties->osProfile->adminUsername;
            $data['virtual_network'] = $virtual_network;
            $data['virtual_network_interface'] = $virtual_network . '_interface';
            $data['os_type'] = $object->properties->storageProfile->osDisk->osType;
            $data['os_disk'] = $object->properties->storageProfile->osDisk->name;
            $data['os_disk_size'] = $object->properties->storageProfile->osDisk->diskSizeGB;
            $data['vm_size'] = $object->properties->hardwareProfile->vmSize;
            $data['location'] = $object->location;
            $data['ip_label'] = $ipLabel;
            $data['status'] = 'up';

            // Sleep until associating new ip address
            sleep(25);

            $public_ip = $this->getAzureVMPublicIpAddress($ipLabel);

            if (!$public_ip){
                return response()->json(['error' => 'Microsoft Error'], 200);
            }

            $data['ip_address'] = $public_ip;

            $vm = Azurevm::create($data);

        }

        $vms = $this->getVirtualMachines();

        return response()->json(['vms' => $vms], 200);

    }


    public function getAzureVMPublicIpAddress($want_ip_label, $vm_id = null) {

        $token = $this->getAzureAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $client = new Client();

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Network/publicIPAddresses/'.$want_ip_label.'?api-version=2017-09-01';

        $result = $client->get($url,[

            'headers' => $headers,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'

        ]);

        $response = $result->getBody();

        if (!$response){
            return response()->json(['error' => 'Microsoft Error'], 200);
        }

        $object = json_decode($response);

        if($object->properties->ipAddress){
            $new_ip = $object->properties->ipAddress;
        } else {
            $new_ip = '0.0.0.0';
        }

        // If Exists
        CustomHelper::updateIPAddress($vm_id, $new_ip);

        return $new_ip;

    }

    public function deleteAzureVM($vm_id) {

        $vm = Azurevm::where('id', $vm_id)->first();

        $token = $this->getAzureAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $client = new Client();

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Compute/virtualMachines/'.$vm->vm.'?api-version=2016-04-30-preview';

        $result = $client->delete($url,[

            'headers' => $headers,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'

        ]);

        $status_code = $result->getStatusCode();

        if($status_code == 202 || $status_code == 200) {
            $vm->delete();
        }

        $vms = $this->getVirtualMachines();

        return response()->json(['vms' => $vms], 200);


    }

    public function deleteAwsVM($vm_id) {

        $vm = AWSvm::where('id', $vm_id)->first();

        $instance_id = $vm->instance_id;

        $ec2 = $this->getAWSClient();

        $result = $ec2->terminateInstances([
            'InstanceIds' => [$instance_id]
        ]);

        if($result) {
            $vm->delete();
        }

        $vms = $this->getVirtualMachines();

        return response()->json(['vms' => $vms], 200);


    }

    public function getAWSClient() {

        $client = new Ec2Client([
            'region' => $this->region,
            'version' => $this->version,
            'credentials' => array('key' => $this->aws_access_key, 'secret' => $this->aws_secret_key)
        ]);

        return $client;

    }

    public function getAWSCodeDeployClient() {

        $client = new CodeDeployClient([
            'region' => $this->region,
            'version' => $this->version,
            'credentials' => array('key' => $this->aws_access_key, 'secret' => $this->aws_secret_key)
        ]);

        return $client;

    }

    // AWS
    public function getAWSVMs(){

        $ec2 = $this->getAWSClient();

        $result = $ec2->describeInstances();

        $vms = [];
        $instances = $result["Reservations"][0]["Instances"];
        foreach ($instances as $index => $instance) {
            $vms[$index]["name"] = isset($instance["Tags"][0]["Value"]) ? $instance["Tags"][0]["Value"] : 'DEMOOOO' ;
            $vms[$index]["status"] = $instance["State"]["Name"];
            $vms[$index]["ip_address"] = isset($instance["PublicIpAddress"]) ? $instance["PublicIpAddress"] : "DEMOOO";
            $vms[$index]["type"] = $instance["InstanceType"];
            $vms[$index]["zone"] = $instance["Placement"]["AvailabilityZone"];
        }

       return $vms;

    }

    public function getAWSIPAddressAndStatus($vm_instance_id) {

        $ec2 = $this->getAWSClient();

        $result = $ec2->describeInstances(['InstanceIds' => [$vm_instance_id]]);

        $ip_address = isset( $result["Reservations"][0]["Instances"][0]["PublicIpAddress"] ) ? $result["Reservations"][0]["Instances"][0]["PublicIpAddress"] : '-';

        $status = ( $result["Reservations"][0]["Instances"][0]["State"]["Name"] == 'running' ) ? 'up' : 'down';

        return ['ip_address' => $ip_address, 'status' => $status];

    }

    public function createAWSVM($name) {

        $ec2Client = $this->getAWSClient();

        // Launch an instance with the key pair and security group
        $result = $ec2Client->runInstances(array(
            'ImageId'        => 'ami-01d6e7ea',
            'MinCount'       => 1,
            'MaxCount'       => 1,
            'InstanceType'   => 't2.micro',
            'KeyName'        => 'mac',
            'SecurityGroups' => array('api-sg'),
            'IamInstanceProfile' => [
                'Arn' => 'arn:aws:iam::477898490023:instance-profile/EC2CodeDeployRole',
            ],
            'TagSpecifications' => [
                [
                    'ResourceType' => 'instance',
                    'Tags' => [
                        [
                            'Key' => 'Name',
                            'Value' => $name,
                        ],
                    ],
                ],
            ],

        ));

        $instance = $result->get("Instances")[0];

        $new_aws_vm = new AWSvm();

        $new_aws_vm->vm = $instance["Tags"][0]["Value"];
        $new_aws_vm->ip_address = ''; // Get It Later
        $new_aws_vm->vm_size = $instance["InstanceType"];
        $new_aws_vm->instance_id = $instance["InstanceId"];
        $new_aws_vm->status = ($instance["State"]["Name"] == 'pending') ? 'down' : 'up';
        $new_aws_vm->location = $instance["Placement"]["AvailabilityZone"];
        $new_aws_vm->vpc_id = $instance["VpcId"];
        $new_aws_vm->image_id = $instance["ImageId"];
        $new_aws_vm->security_group_id = $instance["SecurityGroups"][0]["GroupId"];
        $new_aws_vm->user_id = Auth::user()->id;

        $new_aws_vm->save();

        $vms = $this->getVirtualMachines();

        return response()->json(['vms' => $vms], 200);

    }

    public function getVirtualMachines() {

        $user_id = Auth::user()->id;

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


        return $all_vms;

    }

    public function doActionAWSVM(Request $request, $vm_id) {

        $action = $request['action'];

        $ec2Client = $this->getAWSClient();

        $aws_instance_id = AWSvm::where('id', $vm_id)->first()->instance_id;

        $instanceIds = array($aws_instance_id);

        if ($action == 'START') {

            $update['status'] = 'up';

            AWSvm::where('id', $vm_id)->update($update);

            sleep(10);

            $result = $ec2Client->startInstances(array(
                'InstanceIds' => $instanceIds,
            ));


        } else {

            $update['status'] = 'down';

            AWSvm::where('id', $vm_id)->update($update);

            sleep(10);

            $result = $ec2Client->stopInstances(array(
                'InstanceIds' => $instanceIds,
            ));

        }

        return response()->json(['status' => 'OK'], 200);


    }


}
