<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Libraries\CustomHelper;

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

        $code = CustomHelper::saveCodeLocal($application_code);

        if(!$code['correct_file']) {
            return response()->json(['status' => 'Not Found'], 404);
        }

        CustomHelper::uploadCodeToS3($code['aws_file'], $code['local_path']);

        $deployment = $this->createAWSDeployment();

        return response()->json(['deployment' => $deployment], 200);

    }

    public function createAWSDeployment() {

        $user = User::find(1);

        if( !$user->application ) {

            $this->saveNewApplication($user->id);
            $this->createNewAWSApplication();
            $this->createNewAWSDeploymentGroup();

        } else {

            $this->application_name = $user->application->application_name;
            $this->deployment_group_name = $user->application->deployment_group;
        }

        // Publish New Deployment In AWS
        $result = $this->createNewAWSDeployment();

        return $result;

    }

    public function saveNewApplication($user_id) {

        $this->application_name = 'user'.$user_id.'application';
        $this->deployment_group_name = 'user'.$user_id.'application_dg';

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

    public function createNewAWSDeploymentGroup() {


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
            'ec2TagFilters' => [
                [
                    'Key' => 'Name',
                    'Type' => 'KEY_AND_VALUE',
                    'Value' => 'demo',
                ],

            ],
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

    public function createNewAWSDeployment() {

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
                    'key' => 'users/x9B3ZH6E87Mo.zip'
                ]
            ],
            'targetInstances' => [
                'ec2TagSet' => [
                    'ec2TagSetList' => [
                        [
                            [
                                'Key' => 'Name',
                                'Type' => 'KEY_AND_VALUE',
                                'Value' => 'demo',
                            ],

                        ],
                    ],
                ],
            ]
        ]);

    }
}
