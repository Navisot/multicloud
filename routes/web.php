<?php

use GuzzleHttp\Client;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', function(){

    return view('chat');

});


Route::get('/call', function(){


//    $ch = curl_init();
//    $vars = array(
//        'grant_type' => 'client_credentials',
//        'client_id' => '1f138372-99c6-4cf9-acd7-8e859b75603a',
//        'client_secret' => 'ZJVDQW0IbMxFn5GMCz3U+hbmw5fDwk1gvy3fGvuZZIA=',
//        'resource' => 'https://management.azure.com/'
//    );
//    curl_setopt($ch, CURLOPT_URL,"https://login.microsoftonline.com/73aabc2e-223c-45aa-9792-856119013d16/oauth2/token");
//    curl_setopt($ch, CURLOPT_POST, 1);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);  //Post Fields
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    $server_output = curl_exec($ch);
//    curl_close($ch);
//    print  $server_output;


    // Get Token
    $client = new Client(); //GuzzleHttp\Client
    $result = $client->post('https://login.microsoftonline.com/73aabc2e-223c-45aa-9792-856119013d16/oauth2/token', [
        'form_params' => [
            'grant_type' => 'client_credentials',
            'client_id' => '1f138372-99c6-4cf9-acd7-8e859b75603a',
            'client_secret' => 'ZJVDQW0IbMxFn5GMCz3U+hbmw5fDwk1gvy3fGvuZZIA=',
            'resource' => 'https://management.azure.com/',
        ]
    ]);

    $response = $result->getBody();

    $object = json_decode($response);


    dd($object);

});

Route::get('/shutdown', function(){

    $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6IjJLVmN1enFBaWRPTHFXU2FvbDd3Z0ZSR0NZbyIsImtpZCI6IjJLVmN1enFBaWRPTHFXU2FvbDd3Z0ZSR0NZbyJ9.eyJhdWQiOiJodHRwczovL21hbmFnZW1lbnQuYXp1cmUuY29tLyIsImlzcyI6Imh0dHBzOi8vc3RzLndpbmRvd3MubmV0LzczYWFiYzJlLTIyM2MtNDVhYS05NzkyLTg1NjExOTAxM2QxNi8iLCJpYXQiOjE1MDk1MjcwNjEsIm5iZiI6MTUwOTUyNzA2MSwiZXhwIjoxNTA5NTMwOTYxLCJhaW8iOiJZMk5nWURnd3hlVlFnaXVicisrQy8zbEdURW9XQUE9PSIsImFwcGlkIjoiMWYxMzgzNzItOTljNi00Y2Y5LWFjZDctOGU4NTliNzU2MDNhIiwiYXBwaWRhY3IiOiIxIiwiaWRwIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvNzNhYWJjMmUtMjIzYy00NWFhLTk3OTItODU2MTE5MDEzZDE2LyIsIm9pZCI6IjU2NDZjZDYyLWY0OTItNGQ1ZS05M2M1LTc4ZjM0YWM1NWU1MSIsInN1YiI6IjU2NDZjZDYyLWY0OTItNGQ1ZS05M2M1LTc4ZjM0YWM1NWU1MSIsInRpZCI6IjczYWFiYzJlLTIyM2MtNDVhYS05NzkyLTg1NjExOTAxM2QxNiIsInV0aSI6Ino4d1QxeTZKNUVLbFotYzU3bU4yQUEiLCJ2ZXIiOiIxLjAifQ.qTD5Q34S1vKfWaUJeweC4JfBVbM2RwOPg2_f8txJAbDG4iUC43OBa_pDuLKJBfhpuhMx7EKd0DED7qSUwiCy-FkDXPe87V2WVFQjhByka2WwNIFvjg8INfx0OVYjNUDrABemlHRkHkFvtwA_fBv2_49SnuwXDZTRms4RYxkUpcIuU7BF2PvpgkEEAEhYWiISllklDeUBYEfkOZyal2x5xlw9TNdzfvqIMKq27jFwbeuQ0X-mQDeZt2jwgK7YdoJzesK-PFB6r3KTZ0ptQ7nLi681rgB6CAGw-qb1wVwluK0jAPpQeW9laXt8Yo-SEJqfw3jwglBBGABzthjRUtlBEw';
    $client = new Client();
    $headers = [
        'Authorization' => 'Bearer ' .  $token,
        'Accept' => 'application/json'
    ];
    $result = $client->post('https://management.azure.com/subscriptions/6bd9a1c3-0316-42b3-8c89-6d2a6efe3d3a/resourceGroups/dockerBuild/providers/Microsoft.Compute/virtualMachines/dockerizedVM/powerOff?api-version=2017-03-30', [
        'headers' => $headers
    ]);

    $response = $result->getStatusCode();

    dd($response);


});

Route::get('/start', function(){

    $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6IjJLVmN1enFBaWRPTHFXU2FvbDd3Z0ZSR0NZbyIsImtpZCI6IjJLVmN1enFBaWRPTHFXU2FvbDd3Z0ZSR0NZbyJ9.eyJhdWQiOiJodHRwczovL21hbmFnZW1lbnQuYXp1cmUuY29tLyIsImlzcyI6Imh0dHBzOi8vc3RzLndpbmRvd3MubmV0LzczYWFiYzJlLTIyM2MtNDVhYS05NzkyLTg1NjExOTAxM2QxNi8iLCJpYXQiOjE1MDk1MjcwNjEsIm5iZiI6MTUwOTUyNzA2MSwiZXhwIjoxNTA5NTMwOTYxLCJhaW8iOiJZMk5nWURnd3hlVlFnaXVicisrQy8zbEdURW9XQUE9PSIsImFwcGlkIjoiMWYxMzgzNzItOTljNi00Y2Y5LWFjZDctOGU4NTliNzU2MDNhIiwiYXBwaWRhY3IiOiIxIiwiaWRwIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvNzNhYWJjMmUtMjIzYy00NWFhLTk3OTItODU2MTE5MDEzZDE2LyIsIm9pZCI6IjU2NDZjZDYyLWY0OTItNGQ1ZS05M2M1LTc4ZjM0YWM1NWU1MSIsInN1YiI6IjU2NDZjZDYyLWY0OTItNGQ1ZS05M2M1LTc4ZjM0YWM1NWU1MSIsInRpZCI6IjczYWFiYzJlLTIyM2MtNDVhYS05NzkyLTg1NjExOTAxM2QxNiIsInV0aSI6Ino4d1QxeTZKNUVLbFotYzU3bU4yQUEiLCJ2ZXIiOiIxLjAifQ.qTD5Q34S1vKfWaUJeweC4JfBVbM2RwOPg2_f8txJAbDG4iUC43OBa_pDuLKJBfhpuhMx7EKd0DED7qSUwiCy-FkDXPe87V2WVFQjhByka2WwNIFvjg8INfx0OVYjNUDrABemlHRkHkFvtwA_fBv2_49SnuwXDZTRms4RYxkUpcIuU7BF2PvpgkEEAEhYWiISllklDeUBYEfkOZyal2x5xlw9TNdzfvqIMKq27jFwbeuQ0X-mQDeZt2jwgK7YdoJzesK-PFB6r3KTZ0ptQ7nLi681rgB6CAGw-qb1wVwluK0jAPpQeW9laXt8Yo-SEJqfw3jwglBBGABzthjRUtlBEw';
    $client = new Client();
    $headers = [
        'Authorization' => 'Bearer ' .  $token,
        'Accept' => 'application/json'
    ];
    $result = $client->post('https://management.azure.com/subscriptions/6bd9a1c3-0316-42b3-8c89-6d2a6efe3d3a/resourceGroups/dockerBuild/providers/Microsoft.Compute/virtualMachines/dockerizedVM/start?api-version=2017-03-30', [
        'headers' => $headers
    ]);
    $response = $result->getStatusCode();

    dd($response);


});