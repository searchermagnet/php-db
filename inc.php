<?
//most basic inc file
error_reporting(0);
date_default_timezone_set('America/Los_Angeles');
require_once("functions/db.php");


function tag($text)
{ echo "<br><strong>||".$text."||</strong>"; }
//
function Gerror($error)
{ echo $error; exit; }
function DBerror($function,$error,$query)
{
    tag('error');
    echo " ".$error."(in~ pathtoinclude/db_functions.php)<br>";
    echo " Using PhP Database Function: <strong>".$function."</strong><br>";
    echo " Query: ".$query;
    tag('/error');
    exit;
}
function p($item_to_dbug,$exit=0) //custom print & exit function
{
    tag('print');
    if(is_array($item_to_dbug))
    {
    echo '<pre>';
    print_r($item_to_dbug);
    echo '</pre>';
    }
    if(is_string($item_to_dbug))
    {
    echo '<br>';
    echo $item_to_dbug;
    }
    //
    if($exit == 1) { echo tag('/print&run'); }
    else { echo tag('/print&exit'); exit; }
}
function mainlog($what,$status='',$id='')
{
	static $log;
    $log .= " | -> ".$what. "\n";
	
	if($status=='end')
	{
		$fd = fopen('logs/main.log', "a");
		fwrite($fd, $log);
		fclose($fd);
		//write the log
		$data= array(
		'id' => '',
		'X_LeadID' => $id,
		'Log' => mysql_real_escape_string($log)
		);
		$q = insertQUERY("logs",dSQL("logs"),$data);
		//echo $log;
		$roger = mysql_query($q)  or DBerror(__FUNCTION__," MySQL Error!",$q);
	}
	if($status=='dump')
	{
		return $log;	
	}
}
function Exception($event,$page)
{
	$to = 'aaron@searchermag.net';
	$subj = 'New Exception in Database at '.date("h:s");
	$message= 'Script reported: '.$event.' in  '.$page.'<br><br> Logfile<br>'.mainlog("RUN TERMINATED",'dump');
	mail($to, $subj, $message);
	die($event);
}
//Some Generic Date Functions
?>