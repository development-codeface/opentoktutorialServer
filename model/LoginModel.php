<?php
class LoginModel
{
    public $username;
    public $password;
    public $statusCode;
    
    public function __construct($user,$pass,$status){
        $this->username = $user;
        $this->password = $pass;
        $this->statusCode  = $status;
    }
}