<?php

namespace App\Http\Libraries;
use App\Azurevm;

class CustomHelper {

    public static function updateIPAddress($vm_id, $new_ip) {

        $new_data['ip_address'] = $new_ip;

        if ( Azurevm::where('id', $vm_id)->update($new_data) ){
            return true;
        }

        return false;

    }

    public static function saveCodeLocal($application_code) {

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

        $aws_file = '/users/' . $filename;

        return array('aws_file' => $aws_file, 'local_path' => $path, 'correct_file' => $correct_file, 'zip_file' => $filename);

    }

    public static function uploadCodeToS3($file, $path) {

        $s3 = \Storage::disk('s3');

        $s3->put($file, file_get_contents($path), 'public');

        // Remove Local File
        unlink($path);
    }

}