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
            while ($g_smsManger->shiftArrayData($id, $array)):          
                $ret = $this->sendSMS($array["phone"], $array["drawdate"]);
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
        $this->soap_client = new SoapClient("http://172.30.35.108/dxptfb/WebService1.asmx?wsdl");
    }

    /*     * ********************************************************
     * ���ƣ�sendSMS
     * ���ߣ�NIUX
     * ���ܣ�����SOAP�ӿڣ�����֪ͨ����
     * ������$phone �ֻ���, $drawdate ��Ѫ����
     * ����ʱ�䣺20180611
     * ********************************************************* */

    public function sendSMS($phone, $drawdate) {

        if(null == $this->xml || null == $this->soap_client)
        {
            return false;
        }
        $strDate =  $drawdate->format("Y-m-d");
        $this->xml->phones = $phone;
        $this->xml->content = "�ף����� $strDate �ڸ���Ժ�ļ��鱨���ѷ������뾡��ƾ��Ѫ��ִ����������A1����������������ȡ�� лл��";
        $xmlstr = $this->xml->asXML();
        var_dump($xmlstr);
        if (FALSE != $xmlstr):
            $rtStr = $this->soap_client->dxptsubmit($xmlstr);                          
            $rtXML = new SimpleXMLElement($rtStr);
            if ($rtXML->issuccess)
                return TRUE;
            CErrorLog::errorLogFile("[$rtXML->response->result]:<$rtXML->tsxx >");
        endif;

        return false;
    }

}

//$test = new CSmsSendWork();
//$test->start();
//$test->sendSMS("13340397452", "2018��06��15��");
//$test->_soapCall('Add',array(1000,2));
?>