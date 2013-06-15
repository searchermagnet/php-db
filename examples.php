<?
require_once('inc.php');
/*-------------------------------------------*/
//Select usage Example
/*-------------------------------------------*/
$commands = array(
    'SELECT'    => array(),
    //'UNION'        => '',
    'FROM'        => $_REQUEST['Table'],
    'WHERE'		=> array('X_Accept'=>$_SESSION['valid'],$_SESSION['field']=>$_SESSION['search'],
						 'X_TimeStamp'=>$_SESSION['daterange']['begin']."--".$_SESSION['daterange']['end']),
    //'GROUP BY'    => array($_REQUEST['order']),
    'ORDER BY'    => array('id'=>'DESC'),
    'LIMIT'        => array('0'=>'100000000'),
    //'INTO OUTFILE' => array('report.csv'=>''),
    );

$ranges = MetaSelect($commands);
/*-------------------------------------------*/
//Insert Usage Example
/*-------------------------------------------*/
$data = array ('key'=>"value");
$tblname= 'campaigns';
$schema = dSQL($tblname);
$query= INSERTquery($tblname,$schema,$data);
$roger = mysql_query($query)  or DBerror(__FUNCTION__," MySQL Error!",$query);
dbanalyze(__FUNCTION__,$roger);

/*-------------------------------------------*/
//Update Usage Example
/*-------------------------------------------*/
$data = array ('key'=>"value");
$schema = dSQL($tablename);
$query= UPDATEquery($tablename,$schema,$data,$data['id'],'id'); 
$roger = mysql_query($query)  or DBerror(__FUNCTION__," MySQL Error!",$query);
dbanalyze(__FUNCTION__,$roger);

?>