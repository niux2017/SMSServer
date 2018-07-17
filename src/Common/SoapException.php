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

class CSoapException extends Exception{
    
    //put your code here
    private $type;
    public function __construct($type, $message){
        $this->$type = $type;
        parent::__construct($message, null, null);
    }
    
    
        // Overrideable
    public function toString()               // ¿ÉÊä³öµÄ×Ö·û´®
    {
        return __CLASS__ . ": ($this->$type) : <$this->message>\n";  
    }
}
