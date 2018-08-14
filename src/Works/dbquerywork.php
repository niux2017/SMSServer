<?php
/**********************************************************
*���ƣ�dbquerywork ���ݿ��ѯ�߳�
*���ߣ�NIUX
*���ܣ�1�����߳�ִ�����ݿ��ѯ����װ��DICOMINFO���󣬽����󶪸�readfilework�߳̽��д���
2�������ݿ�����״̬�����жϣ����ݿ����ʱ��������
*����ʱ�䣺2018-03-31
***********************************************************/
require_once (dirname(__FILE__)."/../common/dbconn.php"); 
require_once (dirname(__FILE__)."/../common/datamanager.php"); 
require_once ("threadbase.php");

class CDbQueryWork extends CBaseWork {

    protected $maxid;//���ID
    protected $dbConn;//���ݿ����Ӷ���
//���캯��
    public function __construct() { 
        $this->maxid = 0;
        //�������ݿ����
        $this->dbConn = new CDbConn();
    }
    
/**********************************************************
*���ƣ�run �߳���ں���
*���ߣ�NIUX
*���ܣ�
1��ִ�����ݿ��ѯ����װ�����ݶ��󣬽����󶪸�smssendwork�߳̽��д���
2�������ݿ�����״̬�����жϣ����ݿ����ʱ��������
*����ʱ�䣺2018-03-31
***********************************************************/	
   public function run() {
        //ִ�����ݿ��ѯ
        try
        {
            //��ѯ�����Ͷ��ż�¼
            $this->queryNotSentSMS();
        }
        catch(Exception $e)
        {
            //������ݿ��жϣ�������
            $this->dbConn->reconnDB();
            print_r("Error: [File]".$e->getFile()."[Line]".$e->getLine()."[Message]". $e->getMessage());
        }	
    }
/**********************************************************
*���ƣ�updateSMSSendStat
*���ߣ�NIUX
*������$id ���,   $stat ״̬
*���ܣ�
1�����¶��ŷ���״̬ 
2�������ݿ���а�ȫ���ʿ���
*����ʱ�䣺20180611
***********************************************************/	
    public function updateSMSSendStat($id,  $stat)
    {
        try
        {
            $this->dbConn->execSQL("exec usp_cyfsy_sms_update_stat $id,$stat");
        }
        catch(Exception $e)
        {
            print_r("Error: [File]".$e->getFile()."[Line]".$e->getLine()."[Message]". $e->getMessage());
        }				
    }

/**********************************************************
*���ƣ�start 
*���ߣ�NIUX
*���ܣ���һЩ��ʼ������
*����ʱ�䣺2018-04-16
***********************************************************/	
   public function start() {
       
       $this->dbConn->initParam("172.30.0.35\LIS","SMS_AUTO_NOTIFY","SMS_AUTO_NOTIFY", "DBLIS50");
       $this->dbConn->connDB();
       var_dump($this->dbConn);
   }

   /**********************************************************
*���ƣ�queryNotSentSMS
*���ߣ�NIUX
*���ܣ�
1����ȡ���ݿ⣬��ѯδ���ͻ���ʧ�ܣ����ݲ������Ķ��ż�¼
2�������ݼ��뵽��ѯ������
*����ʱ�䣺20180611
***********************************************************/	
    public function queryNotSentSMS()
    {	
        global $g_smsManger;
        //��ȡǰһ���ʱ��
        //xdebug_start_trace();
        $phoneList = array();
        $ret = $this->dbConn->querySQL("exec usp_cyfsy_query_dfsdx $this->maxid", $phoneList);
	if($ret):
            foreach ($phoneList as $row):
                $phone = array('phone'=>$row['phone'], 'drawdate'=>$row['drawdate'], 'ReportID'=>$row['ReportID'], 'RemainderReports'=>$row['RemainderReports']);
                //�������ȫ�ֶ���
                $g_smsManger->pushArrayData($row["id"], $phone);
                if($row["id"] > $this->maxid):
                    $this->maxid = $row["id"];//�������idֵ
                endif;
            endforeach;
        endif;
    }	
    
       /**********************************************************
*���ƣ�queryReportItems
*���ߣ�NIUX
*���ܣ�
1����ȡ���ݿ⣬��ѯ�������Ŀ����
*����������ı��
*����ֵ���ñ����Ӧ����Ŀ������
*����ʱ�䣺20180704
***********************************************************/	
    public function queryReportItems($ApplyNo)
    {
        $results = array();
        $retValue = array();
        $ret = $this->dbConn->querySQL("exec usp_cyfsy_jybg_getreportinfo $ApplyNo", $results);
        $itemnames = '';
        $chenhu = '';
        
        if($ret):
            foreach($results as $result):
                $itemnames .= $result['HisOrderName'];
                $itemnames .="��";
                if(!isset($retValue["patname"])):
                    $retValue["patname"] = $result["PatName"];
                endif;               
                if(!isset($retValue['chenghu'])):
                    $retValue['chenghu'] = $result['chenghu'];
                endif;     
                if(!isset($retValue['ogtt']))://OGTT����ķ���
                    $retValue['ogtt'] = $result['ogtt'];
                endif;   
            endforeach;
            $itemnames = rtrim($itemnames, "��");//ɾ����ĩβ����Ķٺţ������򲻹�
        endif;
        unset($results);  
        $retValue["itemnames"] = $itemnames;
        
        return $retValue;
    }
}
?>