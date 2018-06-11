<?php
/**********************************************************
*名称：CSmsSendWork 短信发送线程
*作者：NIUX
*功能：
1、接收dbquery线程丢过来的数据对象
2、绑定参数，调用SOAP接口发送短信
*创建时间：20180611
***********************************************************/
require_once (dirname(__FILE__)."/../commom/datamanager.php"); 
require_once ("threadbase.php");
require_once (dirname(__FILE__)."/../constants/constants.php");
require_once (dirname(__FILE__)."/../common/ErrorLog.php");

class CSmsSendWork extends CBaseWork {
    private $soap_client;
    private $xml;
    public function __construct() {
        $this->soap_client = new SoapClient(null, array(
      'location' => "http://172.30.0.81/WebService1.asmx",
      'uri'      => "http://172.30.0.81/WebService1.asmx",
      'trace'    => 1 ));
    }
    
    public function run()
    {
        try
        {
            global $g_smsManger;
            while(true):
                $ret = $g_smsManger->shiftArrayData($id, $array);
                if(!$ret):
                    break;//队列空退出循环
                endif;
                $ret = $this->sendSMS($array["phone"], $array["drawdate"]);
                //发送成功，从队列中移除该任务
                if($ret):
                    //发送成功后, 将消息存放在报告查询队列中，并改写数据库状态
                    $g_dbQueryWork->updateSMSSendStat($id, SMS_SEND_RESULT_SUCCESS);
                else:                   
                    //$g_remoteFileManager->pushArrayData($this->barcode, $filesArray);	//若发送失败，丢回队列，等待下次上传
                    $g_dbQueryWork->updateSMSSendStat($id, SMS_SEND_RESULT_FAILED);
                endif;
            endwhile;				
        }
        catch(Exception $e)
        {
           print_r("Error message: ".$e->getMessage());
        }
    }
/**********************************************************
*名称：start
*作者：NIUX
*功能：初始化函数，初始化XML文件等
*参数：无
*创建时间：20180611
***********************************************************/	    
      public function start(){
        
        $this->xml;
    } 
/**********************************************************
*名称：sendSMS
*作者：NIUX
*功能：调用SOAP接口，发送通知短信
*参数：$phone 手机号, $drawdate 抽血日期
*创建时间：20180611
***********************************************************/	
    public function sendSMS($phone, $drawdate){
        
        $this->xml;
        $this->soap_client->dxptsubmit("");
    }
 
}

?>