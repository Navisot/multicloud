<?php

Route::get('/', function () {
    return view('welcome');
});

Route::get('/vms', function(){

    return view('listVMs');

});

Route::get('/deploy', function(){
   return view('upload');
});

Route::post('/deploy/vm', ['uses' => 'DeployController@deployCode', 'as' => 'deploy.code']);

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
    Route::post('/vm/action/{vm_id}', ['uses' => 'RestController@doActionAWSVM', 'as' => 'aws.startstop.vm']);
    Route::post('/delete/vm/{vm_id}', ['uses' => 'RestController@deleteAwsVM', 'as' => 'aws.delete.vm']);

});


Route::get('vms/user', ['uses' => 'ActionController@getVirtualMachines', 'as' => 'get.vms']);


// DEMO

Route::get('/demo', ['uses' => 'ActionController@testMethod']);