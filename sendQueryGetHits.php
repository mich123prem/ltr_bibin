<?php
print("<p>entering:", __FILE__, "</p>");
if (!isset($_SESSION))
	session_start();
$SESSION['configs']=include_once "admin/config.php";
print("session_config=" . $SESSION['configs']);
print_r($SESSION['configs']);
include_once "html.inc.php";
print("<p>after include html.inc.php</p>");
include_once "elastic.inc.php";
print("<p>after include elastic.inc.php</p>");

#include_once "memcache.inc.php";
include_once "mysql.inc.php";
print("<p>after include mysql.inc.php</p>");
if (!isset($_GET['fromDropDown']))
	die("no query number");
print("<p>included all");
$qid=$_GET['fromDropDown'];
$_SESSION['qid']=$qid;
$qt=qid2qry($qid);
print("<p>qt=$qt </p>");
$query_json = getQuery( $queryTerm = $qt, $nr_hits = $SESSION['configs']['hitsPerQuery'] +2);
print("<p>query_json=$query_json </p>");

//print("qj:" . $query_json);
$json=sendQuery( $query_json );
print("<p>query sent </p>");
$json = preg_replace('/(\'|&#0*39;)/', '', $json);
$res = json_decode($json , true );
$hits = $res[ 'hits' ][ 'hits' ];
print("<p>found hits:");
print(count($hits));
print("</p>");
/*
if (!($_SESSION['hitHash'] = $m -> get('hitHash'))) {
	  $hh = array();
	  $m -> set('hitHash', $hh);
	  $_SESSION['hitHash']=&$hh;
}
	else{
		$hh=&$_SESSION['hitHash'];
	}
*/
$hash=array(); // the associative array for avoiding repeat of $work_id . mediaType

$_SESSION['hitHash']=&$hash;
$hh=array(); // The sequential array with numbered hits.
$_SESSION['hitArray']=&$hh;
$i = 0;
foreach ( $hits AS $hit ) {
  //print(json_encode($hit)) ;
  if ($i ==  $SESSION['configs']['hitsPerQuery'])
      break;

  $obj = json_decode( json_encode( $hit, true ) );
  $obj= $obj->_source;

  $ky=$obj->work->id . $obj->mediaType;
  if (isset($hash[$ky]))
      continue;
  else
      $hash[$ky]=$obj;
  $hh[$i]=$obj;
  $i++;
}
// print("outside include: hh[0]=");
// print_r($hh[0]);
?>
<?php
include "call_func_row.php";	

?>