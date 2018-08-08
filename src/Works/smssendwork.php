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
            if($g_smsManger->getArraySize()>0)://�����ݲŴ�����
                $this->OpenSoap();
            endif;
            while ($g_smsManger->shiftArrayData($id, $array)):  
                $results = $g_dbQueryWork->queryReportItems($array['ReportID']);
                $ret = $this->sendSMS($array["phone"], $array["drawdate"], $results, $array['RemainderReports']);
                //���ͳɹ����Ӷ������Ƴ�������
                if ($ret):
                    //���ͳɹ���, ����Ϣ����ڱ����ѯ�����У�����д���ݿ�״̬
                    $g_dbQueryWork->updateSMSSendStat($id, SMS_SEND_RESULT_SUCCESS);
                else:
                    //$g_remoteFileManager->pushArrayData($id, $array);	//������ʧ�ܣ����ض��У��ȴ��´��ϴ�
                    $g_dbQueryWork->updateSMSSendStat($id, SMS_SEND_RESULT_FAILED);
                endif;
            endwhile;
            $this->CloseSoap();//�����ر�����
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
        $this->soap_client = new SoapClient(dirname(__FILE__)."/WebService1.wsdl");
    }

    /*     * ********************************************************
     * ���ƣ�sendSMS
     * ���ߣ�NIUX
     * ���ܣ�����SOAP�ӿڣ�����֪ͨ����
     * ������$phone �ֻ���, $drawdate ��Ѫ���� $results ������Ŀ���ƺͻ�������, $remainderReports ʣ�౨����
     * ����ʱ�䣺20180611
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
            $gb2312content = $patName.$chenghu."����".$strDate."�յļ��顾".$itemNames."���ѳ����棨��".$remainderReports."��δ��������ƾ��ִ����ȡ�� ������ơ�";
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
            CErrorLog::errorLogFile("����Ϣ����ʧ��:".$xmlstr);
            //reInitSoap();//���³�ʼ��SOAP�ӿ�
            //$this->soap_client->dxptsubmit($param);//�ٴε��ýӿ�
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
     * ���ƣ�OpenSoap
     * ���ߣ�NIUX
     * ���ܣ���webservice����
     * ��������
     * ����ʱ�䣺20180716
     * ********************************************************* */

    public function OpenSoap() {
        unset($this->soap_client);
        $this->soap_client = new SoapClient(dirname(__FILE__)."/WebService1.wsdl");    
    }
    
            /*     * ********************************************************
     * ���ƣ�CloseSoap
     * ���ߣ�NIUX
     * ���ܣ��ر�webservice����
     * ��������
     * ����ʱ�䣺20180717
     * ********************************************************* */

    public function CloseSoap() {
        unset($this->soap_client);      
    }
    
}
?>