<?php

namespace App\Http\Controllers;

use App\AWSvm;
use Illuminate\Http\Request;
use App\User;
use App\Http\Libraries\CustomHelper;
use App\AwsApplication;

class DeployController extends RestController
{

    protected $application_code;
    protected $deployment_group_name;
    protected $user;
    protected $ec2;

    public function __construct() {
        $this->ec2 = $this->getAWSCodeDeployClient();
    }

    public function deployCode(Request $request) {

        $application_code = $request->app_code;
        $selected_vms = $request->selected_vms;

        $code = CustomHelper::saveCodeLocal($application_code);

        if(!$code['correct_file']) {
            return response()->json(['status' => 'Not Found'], 404);
        }

        CustomHelper::uploadCodeToS3($code['aws_file'], $code['local_path']);

        $deployment = $this->createAWSDeployment($code['zip_file'], $selected_vms);

        return response()->json(['deployment' => $deployment], 200);

    }

    public function createAWSDeployment($zip_file, $vms) {

        $user = User::find(1);

        if( !$user->application ) {

            // User DOES NOT Have Available Applications

            // Initialize Variables
            $this->application_name = 'user'.$user->id.'application';
            $this->deployment_group_name = 'user'.$user->id.'application_dg';

            // Create Application
            $this->createNewAWSApplication();


        } else {

            // User Has Available Applications
            $this->application_name = $user->application->application_name;
            $this->deployment_group_name = $user->application->deployment_group;
        }

        // Create or Update Deployment Group
        $this->createNewAWSDeploymentGroup($vms);

        // Publish New Deployment
        $result = $this->createNewAWSDeployment($zip_file, $vms);

        // First Application? Save to DB.
        if(!$user->application) {
            $this->saveNewApplication($user->id);
        }

        return $result;

    }

    public function saveNewApplication($user_id) {

        $application = new AwsApplication();
        $application->application_name = $this->application_name;
        $application->deployment_group = $this->deployment_group_name;
        $application->user_id = $user_id;
        $application->save();
    }

    public function createNewAWSApplication() {

        $this->ec2->createApplication([
            'applicationName' => $this->application_name,
            'computePlatform' => 'Server',
        ]);

    }

    public function createNewAWSDeploymentGroup($vms) {

            $instances = [];

            foreach ($vms as $vm){
                $vm = AWSvm::find($vm)->vm;
                $temp_array = array('Key' => 'Name', 'Type'=>'KEY_AND_VALUE', 'Value' => $vm);
                array_push($instances, $temp_array);
            }

            $exists = $this->checkIfGroupExistsAndUpdateGroup($this->deployment_group_name, $instances);

            if (!$exists) {
                // We don't have any deployment group yet.
                $this->ec2->createDeploymentGroup
                ([
                    'alarmConfiguration' => [
                        'enabled' => false,
                    ],
                    'applicationName' => $this->application_name,
                    'autoRollbackConfiguration' => [
                        'enabled' => false,
                    ],
                    'deploymentConfigName' => 'CodeDeployDefault.AllAtOnce',
                    'deploymentGroupName' => $this->deployment_group_name,
                    'deploymentStyle' => [
                        'deploymentOption' => 'WITHOUT_TRAFFIC_CONTROL',
                        'deploymentType' => 'IN_PLACE',
                    ],
                    'ec2TagFilters' => $instances,
                    'alarmConfiguration' => [
                        'alarms' => [
                            [
                                'name' => 'no',
                            ]
                        ],
                        'enabled' => false,
                        'ignorePollAlarmFailure' => false,
                    ],
                    'serviceRoleArn' => 'arn:aws:iam::477898490023:role/CodeDeployRole',

                ]);
            }
    }

    public function checkIfGroupExistsAndUpdateGroup($group_name, $instances) {

        $exists = false;

        if ( !is_null( AwsApplication::where('deployment_group',$group_name)->first() ) ) {

            // We Have deployment group In DB so...update
            $exists = true;

                    $this->ec2->updateDeploymentGroup([
                        'alarmConfiguration' => [
                            'enabled' => false,
                        ],
                        'applicationName' => $this->application_name,
                        'autoRollbackConfiguration' => [
                            'enabled' => false,
                        ],
                        'deploymentConfigName' => 'CodeDeployDefault.AllAtOnce',
                        'currentDeploymentGroupName' => $this->deployment_group_name,
                        'deploymentStyle' => [
                            'deploymentOption' => 'WITHOUT_TRAFFIC_CONTROL',
                            'deploymentType' => 'IN_PLACE',
                        ],
                        'ec2TagFilters' => $instances,
                        'alarmConfiguration' => [
                            'alarms' => [
                                [
                                    'name' => 'no',
                                ]
                            ],
                            'enabled' => false,
                            'ignorePollAlarmFailure' => false,
                        ],
                        'serviceRoleArn' => 'arn:aws:iam::477898490023:role/CodeDeployRole',

                    ]);



        }

        return $exists;

    }

    public function createNewAWSDeployment($zip_file, $vms)
    {

        $instances = [];

        foreach ($vms as $vm){
            $vm = AWSvm::find($vm)->vm;
            $temp_array = array('Key' => 'Name', 'Type'=>'KEY_AND_VALUE', 'Value' => $vm);
            array_push($instances, $temp_array);
        }

            $this->ec2->createDeployment([
                'applicationName' => $this->application_name,
                'autoRollbackConfiguration' => [
                    'enabled' => false,
                ],
                'deploymentGroupName' => $this->deployment_group_name,
                'deploymentConfigName' => 'CodeDeployDefault.AllAtOnce',
                'fileExistsBehavior' => 'OVERWRITE',
                'revision' => [

                    'revisionType' => 'S3',
                    's3Location' => [
                        'bucket' => 'navisotuserdata',
                        'bundleType' => 'zip',
                        'key' => 'users/'.$zip_file
                    ]
                ],
                'targetInstances' => [
                    'tagFilters' => $instances
                ]
            ]);


    }
}
