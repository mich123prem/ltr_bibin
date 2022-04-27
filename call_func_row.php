<?php
if (!isset($_SESSION)) {
    session_start();
}
//print(__FILE__. " l.". __LINE__ . ": call_func_row:");
//var_dump($_GET);
include_once "html.inc.php";
$hh=$_SESSION['hitArray'];
// print("inside include");
// print_r($hh[0]);
$i=0;
if (isset($_GET['i'])){  // coming from index.htm. $i==0.
 	$i=$_GET['i']; 
}
$warn=null;
$relevanceValueTrueFalse=false;
foreach($_GET as $ky => $val){
	if (strpos($ky, "rn") === 0){
        $relevanceValueTrueFalse=true;
		$rel=$val;
		list($rn, $qid, $docid, $user) = explode("_", $ky);
	}
}
if( !($relevanceValueTrueFalse) ){ /*User did not select any relevance grade*/
    if (isset($_GET['next'])){
        //print ("next=" . isset($_GET['next']));
        $i=$_GET['next']-1; // stay where you are, dont proceed to next.
        $warn=1;
    }
    if (isset($_GET['previous'])) {
        $i = $_GET['previous'];
    }

}else {
    if (isset($_GET['previous'])) {
        //print("previous set" . $_GET['previous']);
        $i = $_GET['previous'];
    }
    if (isset($_GET['next'])) {
        $i = $_GET['next'];
    }
}

/*
print("list args");
echo $i; // Doc Number current qid 
echo "rn"; // radioName: carries a combination bookid_qid
echo "id" ; // $id carries a combination bookid_qid
echo "charlotte brontë";
print(";;;;;;;;;;;;;hitHash;;;;;;;;;;;;;;;;;;;;;;;;");
print("hh_".$i);
print_r( $hh[$i]);

var_dump($hh);
$cnt=count($hh);
print<<<DBG
i=$i,
qid=$qid 
hh[$i] = $hh[$i]
DBG;
*/
//print("i before func_row=$i");
print( func_row( $i, // \$i = Doc Number in current qid
                 $hh[$i],
  				 $qid, /*"title", "author", "bla bla bla"*/
                count($hh),
                $warning=$warn
			   ) 
);
?>