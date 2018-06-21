<?php

namespace App\Http\Libraries;
use App\Azurevm;
use Zipper;
use Storage;
use File;

class CustomHelper {

    public static function updateIPAddress($vm_id, $new_ip) {

        $new_data['ip_address'] = $new_ip;

        if (Azurevm::where('id', $vm_id)->update($new_data)){
            return true;
        }

        return false;

    }

    public static function saveCodeLocal($application_code, $azure_vms) {

        $correct_file = false;

        $exploded = explode(',', $application_code);

        $decoded = base64_decode($exploded[1]);

        if(str_contains($exploded[0], 'zip')) {
            $correct_file = true;
            $extension = 'zip';
        }

        $filename = str_random(12) . '.' . $extension;

        $path = public_path() . '/user_data/' . $filename;

        file_put_contents($path, $decoded);

        if($azure_vms) {

            // Azure
            $azure_path = public_path() . '/user_data/application.zip';
            file_put_contents($azure_path, $decoded);

            // Extract Application Zip To /public/extract/
            Zipper::make($azure_path)->extractTo(public_path().'/extract/');

            @unlink($azure_path);

            $ip_txt = Storage::disk('local')->get('ip.txt');

            file_put_contents(public_path().'/extract/ip.txt', $ip_txt);

            $files = glob(public_path() . '/extract');

            Zipper::zip('user_data/application.zip')->add($files)->close();

            $extract_directory = public_path().'/extract';

            File::deleteDirectory($extract_directory);

            $azure_file = '/users/application.zip';
        }

        $aws_file = '/users/' . $filename;

        if($azure_vms) {
            return array('aws_file' => $aws_file, 'azure_file' => $azure_file, 'local_path' => $path, 'correct_file' => $correct_file, 'zip_file' => $filename);
        } else {
            return array('aws_file' => $aws_file, 'azure_file' => null, 'local_path' => $path, 'correct_file' => $correct_file, 'zip_file' => $filename);
        }

    }

    public static function uploadCodeToS3($file, $path) {

        $s3 = \Storage::disk('s3');

        $s3->put($file, file_get_contents($path), 'public');

        // Remove Local File
        unlink($path);
    }

}