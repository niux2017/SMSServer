<?php

/* * ********************************************************
 * ���ƣ�CSmsSendWork ���ŷ����߳�
 * ���ߣ�NIUX
 * ���ܣ�
  1������dbquery�̶߳����������ݶ���
  2���󶨲���������SOAP�ӿڷ��Ͷ���
 * ����ʱ�䣺20180611
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
                //���ͳɹ����Ӷ������Ƴ�������
                if ($ret):
                    //���ͳɹ���, ����Ϣ����ڱ����ѯ�����У�����д���ݿ�״̬
                    $g_dbQueryWork->updateSMSSendStat($id, SMS_SEND_RESULT_SUCCESS);
                else:
                    //$g_remoteFileManager->pushArrayData($this->barcode, $filesArray);	//������ʧ�ܣ����ض��У��ȴ��´��ϴ�
                    $g_dbQueryWork->updateSMSSendStat($id, SMS_SEND_RESULT_FAILED);
                endif;
            endwhile;
        } catch (Exception $e) {
            print_r("Error message: " . $e->getMessage());
        }
    }

    /*     * ********************************************************
     * ���ƣ�start
     * ���ߣ�NIUX
     * ���ܣ���ʼ����������ʼ��XML�ļ���
     * ��������
     * ����ʱ�䣺20180611
     * ********************************************************* */

    public function start() {

        $this->xml = simplexml_load_file(dirname(__FILE__)."/sms.xml");
        //$this->soap_client = new SoapClient("http://172.30.0.81/WebService1.asmx?wsdl");
        $this->soap_client = new SoapClient("http://172.30.35.108/dxptfb/WebService1.asmx?wsdl");
        //$this->soap_client = new SoapClient("WebService2.wsdl");
        //$this->soap_client = new SoapClient("WebService1.wsdl"); 
    }

    /*     * ********************************************************
     * ���ƣ�sendSMS
     * ���ߣ�NIUX
     * ���ܣ�����SOAP�ӿڣ�����֪ͨ����
     * ������$phone �ֻ���, $drawdate ��Ѫ���� $ItemNames ��Ŀ����, $remainderReports ʣ�౨����
     * ����ʱ�䣺20180611
     * ********************************************************* */

    public function sendSMS($phone, $drawdate, $ItemNames, $remainderReports) {

        if(null == $this->xml || null == $this->soap_client)
        {
            return false;
        }
        $strDate =  $drawdate->format("Y-m-d");
        $this->xml->phones = $phone;
        $gb2312content = "�ף����� $strDate �ڸ���Ժ�ļ��鱨�桾 $ItemNames ���ѷ�������ʣ�� $remainderReports �ݱ���δ�������뾡��ƾ��Ѫ��ִ����������A1����������������ȡ�� лл��";
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