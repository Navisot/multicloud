<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\UserToken;
use Carbon\Carbon;
use App\Azurevm;


class RestController extends Controller
{
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


    public function getAzureAccessToken() {

        $db_token = UserToken::where('user_id', 1)->orderBy('created_at','desc')->first();

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
        $ut->user_id = 1;
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

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Compute/virtualMachines/'.$vm_name.'/powerOff?api-version=2017-03-30';

        $result = $client->post($url, [
            'headers' => $headers
        ]);

        $response = $result->getStatusCode();

        if ($response == 200 || $response == 202){

            $update['status'] = 'down';

            Azurevm::where('id', $vm_id)->update($update);

            $vms = Azurevm::where('user_id', 1)->get();

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

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Compute/virtualMachines/'.$vm_name.'/start?api-version=2017-03-30';

        $result = $client->post($url, [
            'headers' => $headers
        ]);

        $response = $result->getStatusCode();

        if ($response == 200 || $response == 202){

            $update['status'] = 'up';

            Azurevm::where('id', $vm_id)->update($update);

            $vms = Azurevm::where('user_id', 1)->get();

            return response()->json(array('status' => 'OK', 'vms' => $vms));
        }

    }

    public function createAzureIP($ip_label) {

        $token = $this->getAzureAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $body = ['location' => 'westeurope'];


        $client = new Client();

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Network/publicIPAddresses/'.$ip_label.'?api-version=2017-09-01';

        $result = $client->put($url, [
            'headers' => $headers,
            'json' => $body
        ]);

        $response = $result->getBody();

        $object = json_decode($response);

        return $object->id;

    }

    public function createAzureVirtualNetwork($virtualNetworkName) {

        $token = $this->getAzureAccessToken();

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

        $client = new Client();

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Network/virtualNetworks/'.$virtualNetworkName.'?api-version=2017-09-01';

        $result = $client->put($url, [
            'headers' => $headers,
            'json' => $body
        ]);

        $response = $result->getBody();

        $object = json_decode($response);

        $subnet_body = ['location' => 'west europe', 'properties' => ['addressPrefix' => '10.0.0.0/16']];

        $subnet_url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Network/virtualNetworks/'.$virtualNetworkName.'/subnets/default?api-version=2017-09-01';

        $subnet_client = new Client();

        $subnet_result = $subnet_client->put($subnet_url, [
            'headers' => $headers,
            'json' => $subnet_body
        ]);

        $subnet_response = $subnet_result->getBody();

        $subnet_object = json_decode($subnet_response);

        $subnet_resource_id = $subnet_object->id;


        return array('vnet_name' => $object->name, 'subnet_id' => $subnet_resource_id);

    }


    public function createAzureNetworkInterface($want_ip_label, $want_virtualNetworkName) {

        $token = $this->getAzureAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $ip_address = $this->createAzureIP($want_ip_label);

        $vnet_info = $this->createAzureVirtualNetwork($want_virtualNetworkName);

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

        $client = new Client();

        $interface_label = $want_virtualNetworkName . '_interface';

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Network/networkInterfaces/'.$interface_label.'?api-version=2017-09-01';

        $result = $client->put($url,[

            'headers' => $headers,
            'json' => $body

        ]);

        $response = $result->getBody();

        $object = json_decode($response);

        return $object->id;

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

        $newNetworkInterface = $this->createAzureNetworkInterface($ipLabel, $virtual_network);

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
                        'id' => '/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Compute/images/dockerImage'
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
                            'id' => $newNetworkInterface
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

            $data['user_id'] = 1;
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

            $data['ip_address'] = $public_ip;

            $vm = Azurevm::create($data);

        }

        $vms = Azurevm::where('user_id', 1)->get();

        return response()->json(['vms' => $vms], 200);

    }


    public function getAzureVMPublicIpAddress($want_ip_label) {

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

        $object = json_decode($response);

        return $object->properties->ipAddress;

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

        $vms = Azurevm::where('user_id', 1)->get();

        return response()->json(['vms' => $vms], 200);


    }


}
