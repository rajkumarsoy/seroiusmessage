<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class UserController extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        $this->load->model('user_model');
    }

    public function signup_post()
    {
        try
        {
            $name = strip_tags(trim($this->post('name')));
            $email = strip_tags(trim($this->post('email')));
            $mobile = strip_tags(trim($this->post('mobile')));
            $password = strip_tags(trim($this->post('password')));
            $latitude = strip_tags(trim($this->post('latitude')));
            $longitude = strip_tags(trim($this->post('longitude')));
            $device_token = strip_tags(trim($this->post('device_token')));
            $device_type = strip_tags(trim($this->post('device_type')));

            if($name == ""||$email == ""||$mobile == ""||$password == "")
            {
                $this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'Name, email, mobile and password fields are required'
                ], 400);   
            } else
            {
            	$check_email = $this->user_model->check_entity('users','email',$email);
            	if(count($check_email) > 0){
            		$this->response([
	                    'responseCode' => 400,
	                    'responseMessage' => 'This email is already registered'
	                ], 400);
            	} else {
            		$check_mobile = $this->user_model->check_entity('users','mobile',$mobile);
            		if(count($check_mobile) > 0){
	            		$this->response([
		                    'responseCode' => 400,
		                    'responseMessage' => 'This mobile number is already registered'
		                ], 400);
	            	} else {
	            		$newuser = $this->user_model->insert_one_row('users', array('name'=>$name,'email'=>$email,'mobile'=>$mobile,'password'=>md5($password),'latitude'=>$latitude,'longitude'=>$longitude,'is_verified'=>0,'is_deleted'=>0,'created_at'=>strtotime('now'),'updated_at'=>strtotime('now')));
	            		//$this->db->delete_record_by_id('user_devices', array('user_id'=>$newuser));
	            		$this->user_model->insert_one_row('user_devices', array('user_id'=>$newuser,'device_token'=>$device_token,'device_type'=>$device_type,'created_at'=>date('Y-m-d H:i:s',strtotime('now')),'updated_at'=>date('Y-m-d H:i:s',strtotime('now'))));

	            		$userdata = $this->user_model->get_record_by_id('users', array('id'=>$newuser));
	            		$data['user_id'] = $userdata->id;
	            		$data['name'] = $userdata->name;
	            		$data['email'] = $userdata->email;
	            		$data['mobile'] = $userdata->mobile;
	            		$data['latitude'] = $userdata->latitude;
	            		$data['longitude'] = $userdata->longitude;
	            		$data['is_verified'] = $userdata->is_verified;

	            		$this->response([
		                    'responseCode' => 200,
		                    'responseMessage' => 'Signup successfully',
		                    'responseData' => $data
		                ], 200);	
	            	}            		
            	}            	
            }
        } catch(Exception $e)
        {
            $this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 503);
        }
    }

    public function login_post()
    {
        try
        {
            $email = strip_tags(trim($this->post('email')));
            $password = strip_tags(trim($this->post('password')));
            $latitude = strip_tags(trim($this->post('latitude')));
            $longitude = strip_tags(trim($this->post('longitude')));
            $device_token = strip_tags(trim($this->post('device_token')));
            $device_type = strip_tags(trim($this->post('device_type')));

            if($email == ""||$password == "")
            {
                $this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'Email and password fields are required'
                ], 400);   
            } else
            {
            	$user = $this->user_model->get_record_by_id('users', array('email'=>$email,'password'=>md5($password)));
            	if(isset($user)){
            		$this->user_model->update_record_by_id('users', array('latitude'=>$latitude,'longitude'=>$longitude,'updated_at'=>strtotime('now')), array('id'=>$user->id));
            		$this->user_model->delete_record_by_id('user_devices', array('user_id'=>$user->id));
            		$this->user_model->insert_one_row('user_devices', array('user_id'=>$user->id,'device_token'=>$device_token,'device_type'=>$device_type,'created_at'=>date('Y-m-d H:i:s',strtotime('now')),'updated_at'=>date('Y-m-d H:i:s',strtotime('now'))));
            		$userdata = $this->user_model->get_record_by_id('users', array('id'=>$user->id));
            		$data['user_id'] = $userdata->id;
            		$data['name'] = $userdata->name;
            		$data['email'] = $userdata->email;
            		$data['mobile'] = $userdata->mobile;
            		$data['latitude'] = $userdata->latitude;
            		$data['longitude'] = $userdata->longitude;
            		$data['is_verified'] = $userdata->is_verified;

            		$this->response([
	                    'responseCode' => 200,
	                    'responseMessage' => 'Login successfully',
	                    'responseData' => $data
	                ], 200);
            		
            	} else {
            		$this->response([
	                    'responseCode' => 400,
	                    'responseMessage' => 'Invalid credentials'
	                ], 400);
	            	            		
            	}            	
            }
        } catch(Exception $e)
        {
            $this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 503);
        }
    }

    public function change_password_post()
    {
        try
        {
            $user_id = strip_tags(trim($this->post('user_id')));
            $old_password = strip_tags(trim($this->post('old_password')));
            $new_password = strip_tags(trim($this->post('new_password')));

            if($user_id == ""||$old_password == ""||$new_password == "")
            {
                $this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'user id, old password and new password fields are required'
                ], 400);   
            } else
            {
            	$user = $this->user_model->get_record_by_id('users', array('id'=>$user_id,'password'=>md5($old_password)));
            	if(isset($user)){
            		$this->user_model->update_record_by_id('users', array('password'=>md5($new_password),'updated_at'=>strtotime('now')), array('id'=>$user_id));            		
            		$this->response([
	                    'responseCode' => 200,
	                    'responseMessage' => 'Password changed successfully'
	                ], 200);
            		
            	} else {
            		$this->response([
	                    'responseCode' => 400,
	                    'responseMessage' => 'Invalid user'
	                ], 400);
	            	            		
            	}            	
            }
        } catch(Exception $e)
        {
            $this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 503);
        }
    }

}
