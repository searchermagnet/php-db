<?php
//fundamental connect
$dbuser = '';
$dbpass = '';
$dbname = "";
//
$srvr = $_SERVER['HTTP_HOST'];
if($srvr == 'localhost') //magic dev
{
    $dbuser = 'root';
    $dbpass = '';
}
//
$dbhost = 'localhost';

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die                      ('Error connecting to mysql');
mysql_select_db($dbname) or die                      ('Error selecting database:'.$dbname);
//
//
function dbanalyze($function,$roger)
{
    mainlog($function."/Init");
    if(empty($roger)) mainlog($function."\EMPTY **WARNING");
    else mainlog($function."\Returned ".count($roger)." entries");
}
////
///
//
function MatthewMarsh($query,$mode='row') //simple queriy executor could be row or multiple, or multi count
{
    $resultarray = array();
    $roger = mysql_query($query) or DBerror(__FUNCTION__," MySQL Error!",$query);
    if($mode== 'row')
    {
        $resultarray = mysql_fetch_assoc($roger);
        return $resultarray;
    } else if($mode== 'multi')
    {
        $i =0;
        while($row = mysql_fetch_assoc($roger)){
            $resultarray[] = $row;
            $i++;
        }
        return $resultarray;
    } else if($mode== 'count') //must have id passed to work
    {
        $i =0;
        while($row = mysql_fetch_assoc($roger)){
            $resultarray[$i] = $row['id'];
            $i++;
        }
        return count($resultarray);
    } else { echo "error- no mode set in MatthewMarsh"; }    
}
function dSQL($tblname) //CORE function for describing tables. RETURNS an array of schema for the given table (in order)
{
    $query = 'DESCRIBE '.$tblname;
    $result = mysql_query($query) or die('WTF is this tablename Son???');
    $i=0;
	while($row = mysql_fetch_assoc($result))
    { $mydata[$i] = $row; $i++; }
    return $mydata;
}
function INSERTQuery($tblname,$schema,$data)
{
    $sql= array('list'=>'','values'=>''); $value='';
	foreach($schema as $fields)
    {
        foreach ($fields as $attribute => $value)
        {
            if($attribute == "Field") 
            { 
            $sql['list']   .= $value.", "; //add the schema to list in correct order
            $sql['values'] .= "'".@$data[$value]."', "; //crossrefernce into provided data for insertable value
            //could build in some error checking here!!!
            }
        }
    }
    $sql['list']     = trim(trim($sql['list'],    " "),",");
    $sql['values']     = trim(trim($sql['values'],    " "),",");
    return 'INSERT INTO '.$tblname.' ('.$sql['list'].')
    VALUES ('.$sql['values'].");";    
}
function UPDATEQuery($tblname,$schema,$data,$needle=0,$haystack=0)
{
    $c1 = $needle;
    $c2 = $haystack;
    $sql =array();
    $sql['list']   ='';
    $sql['values'] ='';
    foreach($schema as $fields)
    {
        foreach ($fields as $attribute => $value)
        {
            if($attribute == "Field" && @$value != 'id' && @$data[$value] != '') //trimming id and any empty values
            { 
            @$sql['str']   .= @$value."="; //add the schema to list in correct order
            @$sql['str']   .= "'".@$data[$value]."', "; //crossrefernce into provided data for insertable value
            //could build in some error checking here!!!
            }
        }
    }
    $sql['str']     = trim(trim($sql['str'],    " "),",");
    @$payload .= 'UPDATE '.$tblname.' SET '.$sql['str'];
    if($c1 !== 0 and $c2 !== 0) { $payload .= ' WHERE '.$c2."= '".$c1."';";    }
    return $payload;
}
//this is the right model
function SELECTQuery($c)
{
    // Straight FLush of mysql queries nigga, expects data to be an array as follows:
    // $c['SELECT']   array of items to select eg 0 => 'id', 1 => 'name' , [everyhthing = empty array]
    // $c['UNION']    if present add this to query
    // $c['FROM']     table
    // $c['WHERE']    array of key/value items to select from eg id => '7' [everyhthing = empty array] dates require special handling
    // $c['GROUP BY'] this makes the field sent a unique... returns no duplicated rows
    // $c['ORDER BY'] singe array key vale of column to sort by and order eg 'id' +. DESC
    // $c['LIMIT']    offset => amount
    // $c['OUTFILE']  filename => path
    // ostensibly, provided variables such as select what where order should be scribbed against schema...another day
    // in where clauses, a blank value in a kv pair will discard that statement, this is for form logic handling like the top of a drop
    $query = ''; $s= ' '; $l= "\n";
    foreach($c as $syn => $ins)
    {
        $prior = strlen($query);
        switch ($syn) 
        {
            case 'SELECT':
                $query .= $syn.$s;
                if(count($ins) == 0) $query .= "*";
                else $query .= implode(",", $ins);
                break;
            case 'UNION':
                $query .= $syn;
                break;
            case 'FROM':
                $query .= $syn.$s.$ins;    
                break;
            case 'WHERE':
                $i=0;
                foreach ($ins as $key => $value)
                {
                    if($value != "") 
                    { 
                        if($i==0) {    $query .= $syn.$s; $i++;  } //add the where clause
                        //
                        $dates = explode("--",$value); //look for dates
                        if(count($dates)  == 2)
                        {
                            $query .= $key." >= '".$dates[0]."' AND ". $key." <= '".$dates[1]."' AND "; 
                        }
                        else 
                        {
                            $query .= $key." = '".$value."' AND "; 
                        }
                    }
                }
                $query = trim($query, " AND ");
                break;
            case 'GROUP BY':
                if($ins[0] != '') $query .= $syn.$s.$ins[0]; //assumes there is only one pair int he arrya, else will act wierd
                break;
            case 'ORDER BY':
                $pkey = key($ins);
                $query .= $syn.$s.$pkey." ".$ins[$pkey]; //assumes there is only one pair in the array, else will act wierd
                break;
            case 'LIMIT':
                $pkey = key($ins);
                $query .= $syn.$s.$pkey.",".$ins[$pkey]; //assumes there is only one pair in the array, else will act wierd
                break;
            case 'INTO OUTFILE':
                $pkey = key($ins);
                $query .= $syn.$s.$pkey;
                if($ins[$pkey] != '') $query .= ' '.$ins[$pkey];
                break;
            default:
                DBerror(__FUNCTION__," Error Building Mysql Select!",$query);    //whoops
                break;
        }
        if(strlen($query) > $prior) $query .= $l; //if the query has been added to, add a line break as well
    }
    return $query;
}


function MetaSelect($commands,$dbug=FALSE)
{
    $query = SELECTquery($commands);
    //echo $query;
	if($dbug==FALSE) { return MatthewMarsh($query,'multi');}
	else { return $query; }
}
?>