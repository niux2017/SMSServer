<?php
//header("Content-type: text/html; charset=utf-8");
require_once ("/common/datamanager.php");
require_once "/works/dbquerywork.php";
require_once "/works/smssendwork.php";
require_once "/common/ErrorLog.php";


//�������ݿ��ѯ�̡߳�����
$g_dbQueryWork = new CDbQueryWork();
$g_smsManger = new CDataArrayManager() ;
//�������ŷ����߳�
$g_smsSendWork = new CSmsSendWork();

register_shutdown_function("CErrorLog::catch_error");
set_error_handler("CErrorLog::throw_exception");

class CSMSServer
{
	
//�����߳�
    public function startWorks()
    {
        try
        {
            global $g_dbQueryWork;
            global $g_smsSendWork;
           
            //�����߳�
            $g_dbQueryWork->start();
            $g_smsSendWork->start();


            while(true):
                $g_dbQueryWork->run();       
                $g_smsSendWork->run();                      
                sleep(60);
            endwhile;
        }
        catch(Exception $e)
        {
            print_r("Error messgage:".$e->getMessage());
        }
    }
}
    $ai =  new CSMSServer();
    $ai->startWorks();
		
?>