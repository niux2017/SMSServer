<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


ini_set('soap.wsdl_cache_enabled','0');//关闭缓存
//$soap=new SoapClient('http://localhost:8080/TXMY/WebService/Service.php?wsdl');

$client = new SoapClient(null, array(
      'location' => "http://172.30.34.102:8080/WebService/Service.php",
      'uri'      => "http://172.30.34.102:8080/WebService/Service.php",
      'trace'    => 1 ));

  var_dump($client->__getFunctions());
//print_r($client->__getFunctions());
//print_r($client->__getTypes());
//$client = new SoapClient("http://172.30.34.102:8080/TXMY/WebService/Service.php?wsdl");

//echo  $client->__soapCall("Hello", array("world"));
//echo $client->Hello();
//echo $client->Hello();

//$client->Add(1,2);
$ret = $client->Hello("jybgxt","admin","13340397452", "test");
var_dump($ret);
//var_dump($client->getAIDesc("0000177249"));

//echo $client->getAIDesc("0000182231");
//echo $client->_soapCall('Add',array(1000,2));//或者这样调用也可以
?>