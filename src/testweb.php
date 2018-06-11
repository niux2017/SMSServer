<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


ini_set('soap.wsdl_cache_enabled','0');//关闭缓存
//$soap=new SoapClient('http://localhost:8080/TXMY/WebService/Service.php?wsdl');

$client = new SoapClient(null, array(
      'location' => "http://localhost:8080/WebService/Service.php",
      'uri'      => "http://localhost:8080/WebService/Service.php",
      'trace'    => 1 ));

//$client = new SoapClient("http://localhost:8080/TXMY/WebService/Service.php?wsdl");

//echo  $client->__soapCall("Hello", array("world"));
//echo $client->Hello();
//echo $client->Hello();

//echo $client->Add(1,2);

//var_dump($client->getAIDesc("0000177249"));

echo $client->getAIDesc("0000182231");
//echo $client->_soapCall('Add',array(1000,2));//或者这样调用也可以
?>