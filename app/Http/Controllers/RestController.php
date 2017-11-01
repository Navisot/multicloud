<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\UserToken;
use Carbon\Carbon;


class RestController extends Controller
{
    protected $azure_tenant_id = '73aabc2e-223c-45aa-9792-856119013d16';
    protected $azure_subscriptionId = '6bd9a1c3-0316-42b3-8c89-6d2a6efe3d3a';
    protected $azure_grant_type = 'client_credentials';
    protected $azure_client_id = '1f138372-99c6-4cf9-acd7-8e859b75603a';
    protected $azure_client_secret = 'ZJVDQW0IbMxFn5GMCz3U+hbmw5fDwk1gvy3fGvuZZIA=';
    protected $azure_resource = 'https://management.azure.com/';
    protected $azure_token_url = 'https://login.microsoftonline.com/73aabc2e-223c-45aa-9792-856119013d16/oauth2/token';
    protected $azure_resource_group = 'dockerBuild';
    protected $azure_vm_name = 'dockerizedVM';
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

    public function stopAzureVM() {

        $token = $this->getAzureAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' .  $token
        ];

        $client = new Client();

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Compute/virtualMachines/'.$this->azure_vm_name.'/powerOff?api-version=2017-03-30';

        $result = $client->post($url, [
            'headers' => $headers
        ]);

        $response = $result->getStatusCode();

        if ($response == 200 || $response == 202){
            return 'VM Stopped.';
        }

    }

    public function startAzureVM() {

        $token = $this->getAzureAccessToken();

        $headers = [
            'Authorization' => 'Bearer ' .  $token
        ];

        $client = new Client();

        $url = 'https://management.azure.com/subscriptions/'.$this->azure_subscriptionId.'/resourceGroups/'.$this->azure_resource_group.'/providers/Microsoft.Compute/virtualMachines/'.$this->azure_vm_name.'/start?api-version=2017-03-30';

        $result = $client->post($url, [
            'headers' => $headers
        ]);

        $response = $result->getStatusCode();

        if ($response == 200 || $response == 202){
            return 'VM Started.';
        }

    }


}
