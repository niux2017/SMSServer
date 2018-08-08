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
require_once (dirname(__FILE__) . "/../common/SoapException.php");

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
            if($g_smsManger->getArraySize()>0)://有内容才打开连接
                $this->OpenSoap();
            endif;
            while ($g_smsManger->shiftArrayData($id, $array)):  
                $results = $g_dbQueryWork->queryReportItems($array['ReportID']);
                $ret = $this->sendSMS($array["phone"], $array["drawdate"], $results, $array['RemainderReports']);
                //发送成功，从队列中移除该任务
                if ($ret):
                    //发送成功后, 将消息存放在报告查询队列中，并改写数据库状态
                    $g_dbQueryWork->updateSMSSendStat($id, SMS_SEND_RESULT_SUCCESS);
                else:
                    //$g_remoteFileManager->pushArrayData($id, $array);	//若发送失败，丢回队列，等待下次上传
                    $g_dbQueryWork->updateSMSSendStat($id, SMS_SEND_RESULT_FAILED);
                endif;
            endwhile;
            $this->CloseSoap();//用完后关闭连接
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
        $this->soap_client = new SoapClient(dirname(__FILE__)."/WebService1.wsdl");
    }

    /*     * ********************************************************
     * 名称：sendSMS
     * 作者：NIUX
     * 功能：调用SOAP接口，发送通知短信
     * 参数：$phone 手机号, $drawdate 抽血日期 $results 包含项目名称和患者姓名, $remainderReports 剩余报告数
     * 创建时间：20180611
     * ********************************************************* */

    public function sendSMS($phone, $drawdate, $results, $remainderReports) {

        if(null == $this->xml || null == $this->soap_client)
        {
            return false;
        }
        try
        {  
            $strDate =  $drawdate->format("m-d");
            $this->xml->phones = $phone;
            $itemNames = $results["itemnames"];
            $patName = $results["patname"];
            $chenghu = $results['chenghu'];
            $gb2312content = $patName.$chenghu."，您".$strDate."日的检验【".$itemNames."】已出报告（余".$remainderReports."份未出），请凭回执单领取！ 【检验科】";
            $this->xml->content  = $gb2312content;
            $xmlstr = $this->xml->asXML();
            $utf8xmlstr = iconv("gb2312","utf-8//IGNORE",$xmlstr);
            //var_dump($this->xml);
            $tsxx = "";
            $param = array();
            if (FALSE != $xmlstr):      
                $param = array('message' => $utf8xmlstr);
                $rtStr = $this->soap_client->dxptsubmit($param);                
                $rtUTF8XML = new SimpleXMLElement($rtStr->dxptsubmitResult);                
                //var_dump($rtGB2312XML);                           
                if ($rtUTF8XML->issuccess != 'true'):   
                    $tsxx = iconv("utf-8", "gb2312//IGNORE",$rtUTF8XML->tsxx);
                    CErrorLog::errorLogFile("failed! Request XML Coontent is:\n".$xmlstr);
                    CErrorLog::errorLogFile("failed! Response XML Coontent is:\n".$tsxx);
                    return FALSE;          
                endif;
            endif;
        }
        catch(CSoapException $e)
        {
            CErrorLog::errorLogFile("短消息发送失败:".$xmlstr);
            //reInitSoap();//重新初始化SOAP接口
            //$this->soap_client->dxptsubmit($param);//再次调用接口
            return FALSE;
        }
        catch(SOAPFault $e)
        {
            CErrorLog::errorLogFile($e->getMessage());
            CErrorLog::errorLogFile("soap error! Request XML Coontent is:\n".$xmlstr);
            CErrorLog::errorLogFile("soap error! Response XML Coontent is:\n".$tsxx);
            return FALSE;
        } 
        catch(Exception $e)
        {
            return FALSE;
        }
       
        return true;
    }
        /*     * ********************************************************
     * 名称：OpenSoap
     * 作者：NIUX
     * 功能：打开webservice连接
     * 参数：无
     * 创建时间：20180716
     * ********************************************************* */

    public function OpenSoap() {
        unset($this->soap_client);
        $this->soap_client = new SoapClient(dirname(__FILE__)."/WebService1.wsdl");    
    }
    
            /*     * ********************************************************
     * 名称：CloseSoap
     * 作者：NIUX
     * 功能：关闭webservice连接
     * 参数：无
     * 创建时间：20180717
     * ********************************************************* */

    public function CloseSoap() {
        unset($this->soap_client);      
    }
    
}
?>