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
class ApiController extends REST_Controller {

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
            $type = strip_tags(trim($this->post('type')));
            $email = strip_tags(trim($this->post('email')));
            $mobile = strip_tags(trim($this->post('mobile')));
            $password = strip_tags(trim($this->post('password')));
            $latitude = strip_tags(trim($this->post('latitude')));
            $longitude = strip_tags(trim($this->post('longitude')));
            $device_token = strip_tags(trim($this->post('device_token')));
            $device_type = strip_tags(trim($this->post('device_type')));

            if($name == ""||$type == ""||$email == ""||$mobile == ""||$password == "")
            {
                $this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'Name, type, email, mobile and password fields are required'
                ], 200);   
            } else
            {
            	$check_email = $this->user_model->check_entity('users','email',$email);
            	if(count($check_email) > 0){
            		$this->response([
	                    'responseCode' => 400,
	                    'responseMessage' => 'This email is already registered'
	                ], 200);
            	} else {
            		$check_mobile = $this->user_model->check_entity('users','mobile',$mobile);
            		if(count($check_mobile) > 0){
	            		$this->response([
		                    'responseCode' => 400,
		                    'responseMessage' => 'This mobile number is already registered'
		                ], 200);
	            	} else {
	            		$is_verified = 0;
	            		if($type == "normal")
	            		{
	            			$is_verified = 1;
	            		} else 
	            		{
	            			$is_verified = 0;
	            		}
	            		$newuser = $this->user_model->insert_one_row('users', array('name'=>$name,'type'=>$type,'email'=>$email,'mobile'=>$mobile,'password'=>md5($password),'latitude'=>$latitude,'longitude'=>$longitude,'is_verified'=>$is_verified,'is_deleted'=>0,'created_at'=>strtotime('now'),'updated_at'=>strtotime('now')));
	            		//$this->db->delete_record_by_id('user_devices', array('user_id'=>$newuser));
	            		$this->user_model->insert_one_row('user_devices', array('user_id'=>$newuser,'device_token'=>$device_token,'device_type'=>$device_type,'created_at'=>date('Y-m-d H:i:s',strtotime('now')),'updated_at'=>date('Y-m-d H:i:s',strtotime('now'))));

	            		$userdata = $this->user_model->get_record_by_id('users', array('id'=>$newuser));
	            		$data['user_id'] = $userdata->id;
	            		$data['name'] = $userdata->name;
	            		$data['type'] = $userdata->type;
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
            ], 200);
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
                ], 200);   
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
            		$data['type'] = $userdata->type;
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
	                ], 200);
	            	            		
            	}            	
            }
        } catch(Exception $e)
        {
            $this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 200);
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
                ], 200);   
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
	                ], 200);
	            	            		
            	}            	
            }
        } catch(Exception $e)
        {
            $this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 200);
        }
    }

	public function send_message_post()
    {
        try
        {
            $user_id = strip_tags(trim($this->post('user_id')));
            $latitude = strip_tags(trim($this->post('latitude')));
            $longitude = strip_tags(trim($this->post('longitude')));
            $type = strip_tags(trim($this->post('type')));
            $title = strip_tags(trim($this->post('title')));
            $description = strip_tags(trim($this->post('description')));
            $image = strip_tags(trim($this->post('image')));


            if($user_id == ""||$latitude == ""||$longitude == ""||$type == ""||$title == ""||$description == "")
            {
                $this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'user id, latitude, longitude, type, title and description fields are required'
                ], 200);   
            } else
            {
            	$uploadedImage = "";
            	if($image != "")
            	{
            		$decoded = base64_decode($image);
            		$f = finfo_open();
					$mime_type = finfo_buffer($f, $decoded, FILEINFO_MIME_TYPE);
					$ext = "";
					if($mime_type == "image/jpeg"){
						$ext = "jpeg";
					} else if($mime_type == "image/jpg"){
						$ext = "jpg";
					} else if($mime_type == "image/png"){
						$ext = "png";
					} else {
						$ext = "jpg";
					}
					$uploadedImage = substr(md5(uniqid(rand(), true)),0,10).".".$ext;

					$this->load->library('upload');
					$config['upload_path'] = './uploads/';
					$config['allowed_types'] = 'jpeg|jpg|png';

            		file_put_contents(APPPATH . '../uploads/'.$uploadedImage,$decoded);	
            		$data = $this->upload->data();
            	}
            	

            	$miles = 1;
            	switch($type){
            		case 'country':
            		$miles = 100;
            		break;
            		case 'city':
            		$miles = 50;
            		break;
            		case 'neighbour':
            		$miles = 15;
            		break;
            	}

            	$send_to = $this->user_model->get_nearby_users($latitude, $longitude, $miles, $user_id);
            	if(count($send_to) > 0){
            		foreach ($send_to as $key => $value) {            			
            			$this->user_model->insert_one_row('message', array('send_to'=>$value['id'],'send_from'=>$user_id,'type'=>$type,'title'=>$title,'description'=>$description,'image'=>$uploadedImage));
            		}
            		            		
            		$this->response([
	                    'responseCode' => 200,
	                    'responseMessage' => 'Message send successfully'
	                ], 200);
            		
            	} else {
            		$this->response([
	                    'responseCode' => 200,
	                    'responseMessage' => 'Message send successfully'
	                ], 200);
	            	            		
            	}            	
            }
        } catch(Exception $e)
        {
            $this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 200);
        }
    }

    public function get_message_post()
    {
        try
        {
            $user_id = strip_tags(trim($this->post('user_id')));
            
            if($user_id == "")
            {
                $this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'user id is required'
                ], 200);   
            } else
            {

            	$getAllMessage = $this->user_model->check_entity('message','send_to',$user_id);
            	$data = array();
            	if(count($getAllMessage) > 0){            		
            		foreach ($getAllMessage as $key => $value) {
	            		$item['title'] = $value->title;
	            		$item['description'] = $value->description;
	            		$item['image'] = '';
	            		$item['created_date'] = date('M d Y',strtotime($value->created_at));
	            		$data[] = $item;
	            	}
            	}
            	$this->response([
                    'responseCode' => 200,
                    'responseMessage' => 'List fetched successfully',
                    'responseData' => $data
                ], 200);           	
            	           	
            }
        } catch(Exception $e)
        {
            $this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 200);
        }
    }   

    public function get_allusers_post()
    {
        try
        {
            $user_id = strip_tags(trim($this->post('user_id')));
            
            if($user_id == "")
            {
                $this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'user id is required'
                ], 200);   
            } else
            {

            	$getAllUsers = $this->user_model->check_entity('users', 'id !=',$user_id);
            	$data = array();
            	if(count($getAllUsers) > 0){            		
            		foreach ($getAllUsers as $key => $value) {
	            		$item['user_id'] = $value->id;
	            		$item['name'] = $value->name;
	            		$data[] = $item;
	            	}
            	}
            	$this->response([
                    'responseCode' => 200,
                    'responseMessage' => 'List fetched successfully',
                    'responseData' => $data
                ], 200);           	
            	           	
            }
        } catch(Exception $e)
        {
            $this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 200);
        }
    } 

    public function send_group_message_post()
    {
        try
        {
            $user_id = strip_tags(trim($this->post('user_id')));
            $send_to = $this->post('send_to');
            $title = strip_tags(trim($this->post('title')));
            $description = strip_tags(trim($this->post('description')));
            $image = strip_tags(trim($this->post('image')));


            if($user_id == ""||$title == ""||$description == "")
            {
                $this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'user id, title and description fields are required'
                ], 200);   
            } else
            {	
            	$uploadedImage = "";
            	if($image != "")
            	{
            		$decoded = base64_decode($image);
            		$f = finfo_open();
					$mime_type = finfo_buffer($f, $decoded, FILEINFO_MIME_TYPE);
					$ext = "";
					if($mime_type == "image/jpeg"){
						$ext = "jpeg";
					} else if($mime_type == "image/jpg"){
						$ext = "jpg";
					} else if($mime_type == "image/png"){
						$ext = "png";
					} else {
						$ext = "jpg";
					}
					$uploadedImage = substr(md5(uniqid(rand(), true)),0,10).".".$ext;

					$this->load->library('upload');
					$config['upload_path'] = './uploads/';
					$config['allowed_types'] = 'jpeg|jpg|png';

            		file_put_contents(APPPATH . '../uploads/'.$uploadedImage,$decoded);	
            		$data = $this->upload->data();
            	}
        		foreach ($send_to as $key => $value) {            			
        			$this->user_model->insert_one_row('message', array('send_to'=>$value,'send_from'=>$user_id,'title'=>$title,'description'=>$description,'image'=>$uploadedImage));
        		}
        		            		
        		$this->response([
                    'responseCode' => 200,
                    'responseMessage' => 'Message send successfully'
                ], 200);            		
            	            	
            }
        } catch(Exception $e)
        {
            $this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 200);
        }
    }

    public function forgot_password_post()
    {
    	try
    	{
    		$email = strip_tags(trim($this->post('email')));
    		if($email == "")
    		{
    			$this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'Email field is required'
                ], 200);
    		} else {
    			$check = $this->user_model->check_entity('users', 'email',$email);
    			if(count($check) > 0)
    			{
    				$new_password = substr(md5(uniqid(rand(), true)),0,8);
    				$this->user_model->update_record_by_id('users', array('password'=>md5($new_password),'updated_at'=>strtotime('now')), array('email'=>$email));  
    				$message = "This is your new password : ".$new_password;
	    			
	    			$this->load->library('email');
					$this->email->from('er.rajkumarsoy@gmail.com', 'SeriousMessage Team');
					$this->email->to($email);
					$this->email->subject('Forgot Password');
					$this->email->message($message);
					$this->email->send();

					$this->response([
	                    'responseCode' => 200,
	                    'responseMessage' => 'New password is send to this email address'
	                ], 200);
    			} else 
    			{
    				$this->response([
	                    'responseCode' => 400,
	                    'responseMessage' => 'This email is not registered'
	                ], 200);
    			}
    			
    		}

    	} catch(Exception $e)
    	{
    		$this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 200);	
    	}
    }

    public function logout_post()
    {
    	try
    	{
    		$user_id = strip_tags(trim($this->post('user_id')));
    		$device_type = strip_tags(trim($this->post('device_type')));
    		$device_token = strip_tags(trim($this->post('device_token')));
    		if($user_id == "")
    		{
    			$this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'user id field is required'
                ], 200);
    		} else 
    		{
    			
    			$this->user_model->delete_record_by_id('user_devices', array('user_id'=>$user_id,'device_token'=>$device_token,'device_type'=>$device_type));
    			$this->response([
	                    'responseCode' => 200,
	                    'responseMessage' => 'Logout successfully'
	                ], 200);
    		}
    	} catch(Exception $e)
    	{
    		$this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 200);	
    	}
    }

    public function contact_list_post()
    {
    	try
    	{
    		$contacts = $this->post('contacts');
    		if(count($contacts) == 0)
    		{
    			$this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'Contact field is required'
                ], 200);
    		} else 
    		{
    			$arr = array();
    			foreach ($contacts as $key => $value) {
    				if(!in_array($value['mobile'], $arr)){
    					array_push($arr, $value['mobile']);
    				}
    			}
    			$res = $this->user_model->get_contacts($arr);
    			$data = array();
    			foreach ($res as $key => $value) {
    				foreach ($contacts as $key2 => $value2) {
    					if($value['mobile'] == $value2['mobile'])
    					{
    						$item['user_id'] = $value['id'];
    						$item['name'] = $value2['name'];
    						$item['mobile'] = $value2['mobile'];
    						$data[] = $item;
    					}
    				}	
    			}

    			$this->response([
	                    'responseCode' => 200,
	                    'responseMessage' => 'List fetched successfully',
	                    'responseData' => $data
	                ], 200);
    		}
    	} catch(Exception $e)
    	{
    		$this->response([
                'responseCode' => 503,
                'responseMessage' => $e->getMessage(),
                'line' => $e->getLine()
            ], 200);	
    	}
    }

	function upload_image(){
		$img = array(); // return variable
		$this->load->helper(array('file','directory'));
		if (!empty($collection)) {
		$path="./uploads/";
		if( !is_dir($path) ) {
		mkdir($path);
		}
		$config['upload_path'] = $path; /* NB! create this dir! */
		$config['allowed_types'] = 'gif|jpg|png|bmp|jpeg';
		$config['file_name'] = 'image001';
		$config['overwrite']=TRUE;

		$this->load->library('upload', $config);


		$configThumb = array();
		$configThumb['image_library'] = 'gd2';
		$configThumb['source_image'] = '';
		$configThumb['create_thumb'] = FALSE;
		$configThumb['maintain_ratio'] = FALSE;

		/* Load the image library */
		$this->load->library('image_lib');

		/* We have 5 files to upload
		* If you want more - change the 6 below as needed
		*/
		/* Handle the file upload */
		if (isset($_FILES['image']['tmp_name'])) {
		$upload = $this->upload->do_upload('image');

		/* File failed to upload - continue */
		if($upload === FALSE){
			$error = array('error' => $this->upload->display_errors());
			$data['message']=$error['error'];
			   
		} 
		/* Get the data about the file */
		$data = $this->upload->data();
		$img['image']='/'.$data['file_name'];

		}

		}
		return $img;
	}

    function android_push_notification($registatoin_ids, $message) {

		// Set POST variables
		$url = 'https://android.googleapis.com/gcm/send';

		$fields = array(
		'registration_ids' => $registatoin_ids,
		'data' => $message,
		);

		$headers = array(
		'Authorization: key=' . GOOGLE_API_KEY,
		'Content-Type: application/json'
		);
		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Disabling SSL Certificate support temporarly
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute post
		$result = curl_exec($ch);
		if ($result === FALSE) {
		die('Curl failed: ' . curl_error($ch));
		}

		// Close connection
		curl_close($ch);
		echo $result;
	}


	function ios_push_notification($deviceToken,$message){
		$passphrase = 'PushChat';
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', path_of_file.'/your_pem_file.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
		// Open a connection to the APNS server
		$fp = stream_socket_client(
		'ssl://gateway.sandbox.push.apple.com:2195', $err,  // For development
		// 'ssl://gateway.push.apple.com:2195', $err, // for production
		$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		if (!$fp)
		exit("Failed to connect: $err $errstr" . PHP_EOL);
		//echo 'Connected to APNS' . PHP_EOL;
		// Create the payload body
		$body['aps'] = array(
		'alert' => trim($message),
		'sound' => 'default'
		);
		// Encode the payload as JSON
		$payload = json_encode($body);
		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', trim($deviceToken)) . pack('n', strlen($payload)) . $payload;
		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
		if (!$result){
		//echo 'Message not delivered' . PHP_EOL;
		}
		else
		{
		//echo 'Message successfully delivered' . PHP_EOL;
		return $result;
		}
		// Close the connection to the server
		fclose($fp);
	}

    public function whsignin_post()
    {
        try
        {
            $mobile = strip_tags(trim($this->post('mobile')));
            
            if($mobile == "")
            {
                $this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'Mobile no. is required'
                ], 400);   
            } else
            {
            	$otp = mt_rand(100000, 999999);
            	$user = $this->user_model->check_entity('wh_users','mobile',$mobile);
            	if(count($user) > 0){
            		$this->user_model->update_record_by_id('wh_users', array('mobile'=>$mobile,'otp'=>$otp,'is_verified'=>0), array('id'=>$user[0]->id));
            		
            		$this->response([
	                    'responseCode' => 200,
	                    'responseMessage' => 'OTP send successfully',
                        'responseData'=>array('otp'=>$otp)
	                ], 200);
            		
            	} else {
            		
            		$this->user_model->insert_one_row('wh_users', array('mobile'=>$mobile,'otp'=>$otp));

            		$this->response([
	                    'responseCode' => 200,
	                    'responseMessage' => 'OTP send successfully',
                        'responseData'=>array('otp'=>$otp)
	                ], 200);
	            	            		
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

    public function whverifyotp_post()
    {
        try
        {
            $mobile = strip_tags(trim($this->post('mobile')));
            $otp = strip_tags(trim($this->post('otp')));
            
            if($mobile == ""||$otp == "")
            {
                $this->response([
                    'responseCode' => 400,
                    'responseMessage' => 'Mobile no. and OTP are required'
                ], 400);   
            } else
            {
            	$user = $this->user_model->get_record_by_id('wh_users', array('mobile'=>$mobile,'otp'=>$otp));
            	if(isset($user)){
            		$this->user_model->update_record_by_id('wh_users', array('is_verified'=>1), array('id'=>$user->id));
            		$updateduser = $this->user_model->get_record_by_id('wh_users', array('id'=>$user->id));
            		$data['user_id'] = $updateduser->id;
            		$data['email'] = $updateduser->email;
            		$data['name'] = $updateduser->name;
            		$data['mobile'] = $updateduser->mobile;
            		$data['is_verified'] = $updateduser->is_verified;
            		$this->response([
	                    'responseCode' => 200,
	                    'responseMessage' => 'OTP verified successfully',
	                    'responseData' => $data
	                ], 200);
            		
            	} else {
            		
            		$this->response([
	                    'responseCode' => 400,
	                    'responseMessage' => 'Invalid OTP'
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
