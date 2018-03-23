<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Image extends MY_Controller {
    public function __construct() {
         parent::__construct();
    }
    public function index(){

         $config = [
                    [
                        'field' => 'customer_name',
                        'label' => 'Customer Name',
                        'rules' => 'trim|required'
                    ],
                    [
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => 'trim|required'
                    ]
        ];
        $this->form_validation->set_rules($config);
        if($this->form_validation->run() == TRUE){ 
            $postData = $this->input->post();
                $profile_pic='';
                $img_type ='';
                if(!empty($_FILES['profile_pic']['name'])){

                    $this->load->library('upload');

                    //$upload_path = '/var/www/qkangaroo-cdn.rkmarketing.net/web/uploads/profile_pictures/';
                    $upload_path = '/hosted-libraries/temp/';
                    $upload_path = $_SERVER['DOCUMENT_ROOT'] . $upload_path;
                    $upConfig['upload_path'] = $upload_path;
                    $upConfig['allowed_types'] = 'gif|jpg|jpeg|png';
                    $upConfig['remove_spaces'] = true;
                    $upConfig['encrypt_name'] = true;

                    $this->load->library('upload');
                    $this->upload->initialize($upConfig);

                    if ( ! $this->upload->do_upload('profile_pic')) {
                        $img_err = $this->upload->display_errors();
                        
                    } else {
                        $file_data = $this->upload->data();
                        $profile_pic =  $this->covertImage($file_data['full_path']);
                        $img_type = pathinfo($file_data['full_path'], PATHINFO_EXTENSION);
                        //$dprofile_pic =  $this->base64_to_jpeg($profile_pic);
                    }
                }
            
                $fields = array(
                        'customer_name' => $postData['customer_name'],
                        'email' => $postData['email'],
                        'profile_pic' => $profile_pic,
                        'img_type' => $img_type,

                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,"http://exmaple.com/customer/");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, "webapp");
                //curl_setopt($ch, CURLOPT_POSTFIELDS,$fields);

                // in real life you should use something like:
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
                // receive server response ...
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, "username: password"); //Your credentials goes here
                $server_output = curl_exec ($ch);
                curl_close ($ch);
               
                $server_output = (array)json_decode($server_output);
               
               if(isset($server_output['status']) && $server_output['status'] == 404){
                    return $server_output['error'];
               }
                else if (isset($server_output['code']) && $server_output['code'] == 200) { 
                    return (array)$server_output['data'];
                     
                    
                }
                else if (isset($server_output['data'])) { 
                 return (array)$server_output['data'];
                }
            
            }else if($this->form_validation->run() == FALSE){
            return str_replace("\n", '', strip_tags(validation_errors());
            }    
    }
    private function covertImage($path){

            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            return $base64;
    }  
    
}
