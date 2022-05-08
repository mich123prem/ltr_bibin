<?php
if (!isset($_SESSION))
	session_start();
$SESSION['configs']=include_once "admin/config.php";

include_once "html.inc.php";
include_once "elastic.inc.php";

#include_once "memcache.inc.php";
include_once "mysql.inc.php";
if (!isset($_GET['fromDropDown']))
	die("no query number");
$qid=$_GET['fromDropDown'];
$_SESSION['qid']=$qid;
$qt=qid2qry($qid);
$query_json = getQuery( $queryTerm = $qt, $nr_hits = $SESSION['configs']['hitsPerQuery'] +2);

$json=sendQuery( $query_json );
$json = preg_replace('/(\'|&#0*39;)/', '', $json);
$res = json_decode($json , true );
$hits = $res[ 'hits' ][ 'hits' ];

        /* IN CASE WE WISH TO EMPLOY MEMCACHE
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
  var_dump($obj);
  print($obj->languages);
  die();
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