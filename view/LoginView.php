<?php
class LoginView
{
    private $loginModel;
    
    public function __construct($model) {
        $this->loginModel = $model;
    }
    
    public function output(){
        return json_encode($this->loginModel);
    }
}