<?php



Route::get('/', function () {
    return view('welcome');
});

Route::get('/vms', function(){

    return view('listAzure');

});

Route::prefix('azure')->group(function () {
    Route::get('/vm/stop/{vm_id}', ['uses' => 'RestController@stopAzureVM', 'as' => 'azure.vm.stop']);
    Route::get('/vm/start/{vm_id}', ['uses' => 'RestController@startAzureVM', 'as' => 'azure.vm.start']);
    Route::get('/create/ip', ['uses' => 'RestController@createIP', 'as' => 'azure.create.ip']);
    Route::get('/create/networkInterface/{want_ip_label}/{want_virtualNetworkName}', ['uses' => 'RestController@createAzureNetworkInterface', 'as' => 'azure.create.networkInterface']);
    Route::post('/create/vm/{want_vm_name}', ['uses' => 'RestController@createAzureVM', 'as' => 'azure.create.vm']);
    Route::post('/delete/vm/{vmname}', ['uses' => 'RestController@deleteAzureVM', 'as' => 'azure.delete.vm']);
});


Route::get('vms/user/{user_id}', ['uses' => 'ActionController@getVirtualMachines', 'as' => 'get.vms']);