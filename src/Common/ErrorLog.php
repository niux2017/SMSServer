<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErrorLog
 *
 * @author Administrator
 */
require_once 'SoapException.php';

class CErrorLog {
    //put your code here
    
    public  static function errorLogFile($errorInfo)
    {
        $dir = dirname(__FILE__)."/../log";
        if (!file_exists($dir)){ 
            mkdir ($dir,0777,true);
        }
        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s'); 
        error_log("$time $errorInfo\r\n", 3, dirname(__FILE__)."/../log/error$date.log");          
    }
    
    public  static function errorLogEmail(Exception $e)
    {
        $errorInfo = "Error: [File]".$e->getFile()."[Line]".$e->getLine()."[Message]". $e->getMessage();
         error_log($errorInfo, 1, "116696521@qq.com");       
    }
    
    //回调函数，捕获错误， 该错误导致程序退出
    public static function catch_error(){
        $error = error_get_last();
        if($error){
            $msg = var_export($error,1);
            CErrorLog::errorLogFile($msg);
        }
    }
    
    //回调函数，将错误当成异常抛出 
    public static function throw_exception($type, $message, $file, $line){
        $ExString = "[Type] $type [File] $file [Line] $line [Message] $message"; 
        CErrorLog::errorLogFile($ExString);
        if($type == E_NOTICE && FALSE != strpos($message, "errno=10054")):
            throw new CSoapException($type,$message);
        else:
            throw new Exception($ExString);
        endif;              
    }
}








