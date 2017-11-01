<?php



Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', function(){

    return view('chat');

});

Route::prefix('azure')->group(function () {
    Route::get('/vm/stop', ['uses' => 'RestController@stopAzureVM', 'as' => 'azure.vm.stop']);
    Route::get('/vm/start', ['uses' => 'RestController@startAzureVM', 'as' => 'azure.vm.start']);
});