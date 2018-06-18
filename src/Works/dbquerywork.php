<?php
/**********************************************************
*名称：dbquerywork 数据库查询线程
*作者：NIUX
*功能：1、该线程执行数据库查询，组装成DICOMINFO对象，将对象丢给readfilework线程进行处理；
2、对数据库连接状态进行判断，数据库掉线时断线重连
*创建时间：2018-03-31
***********************************************************/
require_once (dirname(__FILE__)."/../common/dbconn.php"); 
require_once (dirname(__FILE__)."/../common/datamanager.php"); 
require_once ("threadbase.php");

class CDbQueryWork extends CBaseWork {

    protected $maxid;//最大ID
    protected $dbConn;//数据库连接对象
//构造函数
    public function __construct() { 
        $this->maxid = 0;
        //创建数据库对象
        $this->dbConn = new CDbConn();
    }
    
/**********************************************************
*名称：run 线程入口函数
*作者：NIUX
*功能：
1、执行数据库查询，组装成数据对象，将对象丢给smssendwork线程进行处理；
2、对数据库连接状态进行判断，数据库掉线时断线重连
*创建时间：2018-03-31
***********************************************************/	
   public function run() {
        //执行数据库查询
        try
        {
            //查询待发送短信记录
            $this->queryNotSentSMS();
        }
        catch(Exception $e)
        {
            //如果数据库中断，则重连
            $this->dbConn->reconnDB();
            print_r("Error: [File]".$e->getFile()."[Line]".$e->getLine()."[Message]". $e->getMessage());
        }	
    }
/**********************************************************
*名称：updateSMSSendStat
*作者：NIUX
*参数：$id 序号,   $stat 状态
*功能：
1、更新短信发送状态 
2、对数据库进行安全访问控制
*创建时间：20180611
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
*名称：start 
*作者：NIUX
*功能：做一些初始化工作
*创建时间：2018-04-16
***********************************************************/	
   public function start() {
       
       $this->dbConn->initParam("172.30.0.35\LIS","SMS_AUTO_NOTIFY","SMS_AUTO_NOTIFY", "DBLIS50_TEST");
       $this->dbConn->connDB();
       var_dump($this->dbConn);
       $this->queryNotSentSMS();
   }

   /**********************************************************
*名称：queryNotSentSMS
*作者：NIUX
*功能：
1、读取数据库，查询未发送或发送失败（根据参数）的短信记录
2、将数据加入到查询管理器
*创建时间：20180611
***********************************************************/	
    public function queryNotSentSMS()
    {	
        global $g_smsManger;
        //获取前一天的时间
        //xdebug_start_trace();
        $phoneList = array();
        $ret = $this->dbConn->querySQL("exec usp_cyfsy_query_dfsdx $this->maxid", $phoneList);
	if($ret):
            foreach ($phoneList as $row):
                $phone = array('phone'=>$row['phone'], 'drawdate'=>$row['drawdate']);
                //号码存入全局队列
                $g_smsManger->pushArrayData($row["id"], $phone);
                if($row["id"] > $this->maxid):
                    $this->maxid = $row["id"];//更新最大id值
                endif;
            endforeach;
        endif;
    }	
}

?>