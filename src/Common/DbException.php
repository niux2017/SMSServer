<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CDbException
 *
 * @author Administrator
 */
define ("EXCODE_DB_NOT_CONNECTED", 1000);
class CDbException extends Exception{
    
    protected $state;
    //put your code here
    public function __construct($message, $code = 0, $state = null){
        $this->state = $state;
        parent::__construct($message, $code, null);
    }
    
    
    public static function TrackDbErrors()
    {
        if( ($errors = sqlsrv_errors() ) != null) {
            foreach( $errors as $error ) {
                $state = $error['SQLSTATE'];
                $code= $error['code'];
                $message = $error['message'];
                throw new CDbException($message, $code, $state);
            }
        }
    }
    
        // Overrideable
    public function toString()               // ¿ÉÊä³öµÄ×Ö·û´®
    {
        return __CLASS__ . ": ($this->state) [{$this->code}]: <$this->message>\n";  
    }
}
