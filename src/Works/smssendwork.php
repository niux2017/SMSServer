<?php

/* * ********************************************************
 * 名称：CSmsSendWork 短信发送线程
 * 作者：NIUX
 * 功能：
  1、接收dbquery线程丢过来的数据对象
  2、绑定参数，调用SOAP接口发送短信
 * 创建时间：20180611
 * ********************************************************* */
//exec('chcp 936'); 
require_once (dirname(__FILE__)."/../Common/datamanager.php");
require_once ("threadbase.php");
require_once ("constants.php");
require_once (dirname(__FILE__) . "/../common/ErrorLog.php");
class CSmsSendWork extends CBaseWork {

    private $soap_client;
    private $xml;

    public function __construct() {
        
    }

    public function run() {
        try {
            global $g_smsManger;
            global $g_dbQueryWork;
            $ret = FALSE;
            while ($g_smsManger->shiftArrayData($id, $array)):  
                $itemNames = $g_dbQueryWork->queryReportItems($array['ReportID']);
                $ret = $this->sendSMS($array["phone"], $array["drawdate"], $itemNames, $array['RemainderReports']);
                //发送成功，从队列中移除该任务
                if ($ret):
                    //发送成功后, 将消息存放在报告查询队列中，并改写数据库状态
                    $g_dbQueryWork->updateSMSSendStat($id, SMS_SEND_RESULT_SUCCESS);
                else:
                    //$g_remoteFileManager->pushArrayData($this->barcode, $filesArray);	//若发送失败，丢回队列，等待下次上传
                    $g_dbQueryWork->updateSMSSendStat($id, SMS_SEND_RESULT_FAILED);
                endif;
            endwhile;
        } catch (Exception $e) {
            print_r("Error message: " . $e->getMessage());
        }
    }

    /*     * ********************************************************
     * 名称：start
     * 作者：NIUX
     * 功能：初始化函数，初始化XML文件等
     * 参数：无
     * 创建时间：20180611
     * ********************************************************* */

    public function start() {

        $this->xml = simplexml_load_file(dirname(__FILE__)."/sms.xml");
        //$this->soap_client = new SoapClient("http://172.30.0.81/WebService1.asmx?wsdl");
        $this->soap_client = new SoapClient("http://172.30.35.108/dxptfb/WebService1.asmx?wsdl");
        //$this->soap_client = new SoapClient("WebService2.wsdl");
        //$this->soap_client = new SoapClient("WebService1.wsdl"); 
    }

    /*     * ********************************************************
     * 名称：sendSMS
     * 作者：NIUX
     * 功能：调用SOAP接口，发送通知短信
     * 参数：$phone 手机号, $drawdate 抽血日期 $ItemNames 项目名称, $remainderReports 剩余报告数
     * 创建时间：20180611
     * ********************************************************* */

    public function sendSMS($phone, $drawdate, $ItemNames, $remainderReports) {

        if(null == $this->xml || null == $this->soap_client)
        {
            return false;
        }
        $strDate =  $drawdate->format("Y-m-d");
        $this->xml->phones = $phone;
        $gb2312content = "亲，您于 $strDate 在附三院的检验报告【 $ItemNames 】已发出（还剩余 $remainderReports 份报告未出），请尽快凭抽血回执单，到门诊A1层检验科自助机上领取， 谢谢！";
        $this->xml->content  = iconv("gb2312","utf-8//IGNORE",$gb2312content);
        $xmlstr = $this->xml->asXML();
        //var_dump($this->xml);
        if (FALSE != $xmlstr):      
            try
            {          
                $param = array('message' => $xmlstr);
                $rtStr = $this->soap_client->dxptsubmit($param);                
                $rtUTF8XML = new SimpleXMLElement($rtStr->dxptsubmitResult);
                $rtGB2312XML = iconv("utf-8", "gb2312//IGNORE",$rtUTF8XML->asXML());
                var_dump($rtGB2312XML);                           
                if ($rtUTF8XML->issuccess != 'true'):                
                    CErrorLog::errorLogFile("failed! Request XML Coontent is:\n".$xmlstr);
                    CErrorLog::errorLogFile("failed! Response XML Coontent is:\n".$rtGB2312XML);
                    return FALSE;          
                endif;
            }           
            catch(SOAPFault $e)
            {
                CErrorLog::errorLogFile($e->getMessage());
                CErrorLog::errorLogFile("soap error! Request XML Coontent is:\n".$xmlstr);
                CErrorLog::errorLogFile("soap error! Response XML Coontent is:\n".$rtGB2312XML);
                return FALSE;
            }                  
        endif;

        return true;
    }

}
?>