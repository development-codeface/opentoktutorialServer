<?php
$LoginModel ='../model/LoginModel.php';
$LoginView  = '../view/LoginView.php';
require $LoginModel;
require $LoginView;
class LoginController
{
    private $model;
    
    public function __construct() {
    }
    public function invokeCall($json){
        $data = json_decode($json, true);
        $modelVal = new LoginModel($data['userid'],$data['pass'],'0000');
        $view = new LoginView($modelVal);
        return $view->output();
    }
}