<?php



Route::get('/', function () {
    return view('welcome');
});

Route::get('/vms', function(){

    return view('listAzure');

});

// Azure Actions

Route::prefix('azure')->group(function () {
    Route::get('/vm/stop/{vm_id}', ['uses' => 'RestController@stopAzureVM', 'as' => 'azure.vm.stop']);
    Route::get('/vm/start/{vm_id}', ['uses' => 'RestController@startAzureVM', 'as' => 'azure.vm.start']);
    Route::get('/create/ip', ['uses' => 'RestController@createIP', 'as' => 'azure.create.ip']);
    Route::get('/create/networkInterface/{want_ip_label}/{want_virtualNetworkName}', ['uses' => 'RestController@createAzureNetworkInterface', 'as' => 'azure.create.networkInterface']);
    Route::post('/create/vm/{want_vm_name}', ['uses' => 'RestController@createAzureVM', 'as' => 'azure.create.vm']);
    Route::post('/delete/vm/{vmname}', ['uses' => 'RestController@deleteAzureVM', 'as' => 'azure.delete.vm']);
});

// AWS Actions

Route::prefix('aws')->group(function(){

    Route::post('/create/vm/{name}', ['uses' => 'RestController@createAWSVM', 'as' => 'aws.create.vm']);

});


Route::get('vms/user/{user_id}', ['uses' => 'ActionController@getVirtualMachines', 'as' => 'get.vms']);

use App\AWSvm;
use App\Azurevm;
use App\Http\Libraries\CustomHelper;
use \Aws\Ec2\Ec2Client;

Route::get('testero', function(){

    $client = new Ec2Client([
        'region' => 'eu-central-1',
        'version' => 'latest',
        'credentials' => array('key' => env('AWS_ACCESS_KEY'), 'secret' => env('AWS_SECRET_KEY'))
    ]);

    $result = $client->describeInstances(['InstanceIds' => ['i-07bb40c91be5ab063']]);

    $ip_address = $result["Reservations"][0]["Instances"][0];

    dd($ip_address);


});