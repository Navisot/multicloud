<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Azurevm;

class ActionController extends Controller
{
    public function getVirtualMachines($user_id) {

        $vms = Azurevm::where('user_id', $user_id)->get();

        return response()->json($vms);

    }
}
