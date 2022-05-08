<?php
if (!isset($_SESSION))
	session_start();
include_once "head.html";
include_once "mysql.inc.php";

function getTableHead($i){
    $thd = " <tr>";

    if ($i > 0) {
        $thd .= <<<TBL
    <th>
    Forrige
    </th>
TBL;
    }
    $thd .= <<<TBL
    
   <!-- <th>&nbsp;&nbsp;&nbsp;Søk&nbsp;&nbsp;&nbsp;</th>-->
    <th>Bok-info </th>
    <th>Omslagbilde</th>
    <th>Din vurdering</th>
    <th>Neste</th>
 </tr>   
TBL;
     return $thd;

}
function func_row($i,/*Doc ordinal-number current qid (eks. 0-19 for 20 books pr. query)*/
				  $hit,
				  $qid,
                  $length,
                  $warning=null
				 ){

	$n=$i+1; /*  == For turning on "previous" == */
	$p=$i-1; /*  ====== and "next" links ======= */
	$next=$n+1;
	$previous=$p+1;
	$relTableStyle=$vennligstVelgRelevansGrad="";
    if($warning != null){
        $relTableStyle=" warning";
        $vennligstVelgRelevansGrad='<div class="sentrer red">Vennligst velg relevansgrad</div>';
    }

	$user=$_SESSION["user"];
	
/*   var_dump($hit);
    die();*/


	$description="Ikke oppgitt";
	if (isset($hit->work->genres))
		$description= join(";", $hit->work->genres);

	$title =  "no title";
	if (isset($hit->fullTitle))
		$title =  $hit->fullTitle;
	
	$author="no author";
	if (isset($hit->author))
		$author = join(";", $hit->author);
    $format="Ikke oppgitt";
    if (isset($hit->mediaType))
        $format=$hit->mediaType;
    $audiences="Ikke oppgitt";
    if (isset($hit->work->audiences))
        $audiences= join(";",$hit->work->audiences);
    $languages="Ikke oppgitt";
    if (isset($hit->languages))
        $languages= join(";",$hit->languages);

    $docid=$hit->work->id . $hit->mediaType;

	doc2db($docid, json_encode($hit));
	if(!$qid)
	    $qid=$_SESSION['qid'];
    $query=qid2qry($qid);
	/* $radioName.='_'.$qid.'_'.$docid */
	/* $grade=SELECT grade from relevance WHERE doc= $docid and qid=$qid and assessor=$userid */
	$checkedArray=array_fill(0,6, "");
	$grade=getGrade($qid, $docid, $user);
	if (!$grade && !is_null($grade)) {
        $grade = 0; /*The "0" value is interpreted as false, which is unfortunate*/
    }
	$checkedArray[$grade]=" checked " ;
	$src="test.png";
	if (isset($hit->coverImage))// && isset($hit->imagesByWith->{'150'}))
		$src="https://deichman.no/api/images/resize/200".$hit->coverImage;
	//print($src."<br/>");
	$image=<<<IMG
		<img src="$src" alt="book_cover"/>
IMG;
	$thead=getTableHead($i);
	print <<<QUERY
<h2>Søk:"$query"</h2>
QUERY;

	$tbl=<<<TBL
		<form action="updateRelevance.php">
		<input type="hidden" name="user" value="$user"/>
	<table >
	$thead
<tr>
TBL;

	if($i>0)
		$tbl.=<<<TBL
	<td>
		<button type="submit" name="previous" value="$p">forrige treff<br/> (nr. $previous)</button>
	</td>
TBL;
	
	$tbl.=<<<TBL

	<!--<td style="padding:10px;white-space:nowrap">"$query"</td>-->
	<td class="infocolumn">Tittel:<br/> $title<br/>
	    <hr/>Forfatter:<br/>$author<br/>
	    <hr/>
	        Sjanger:<br/>$description
	    <hr/>    
	        Målgruppe:<br/>$audiences
	        <br/>
	        Språk:<br/>$languages
	        <br/>
	    <hr/>Format:<br/>$format
	 </td>
	 <td>$image</td>
	 <td >
<table class="sentrer$relTableStyle" id="vurdering" >
  <tr>
    <td >
		<label>
      <input  type="radio"  name="rn_${qid}_${docid}_$user" value="0" id="id_0" $checkedArray[0]/>
      <!--non_relevant--> Ikke relevant
		</label>
	</td>
  </tr>
  <tr>
    <td>
		<label>
      <input type="radio" name="rn_${qid}_${docid}_$user" value="1" id="id_1" $checkedArray[1]/>
      <!--weakly_assosiated--> Ikke helt
		</label>
	</td>
  </tr>
  <tr>
    <td>
		<label>
      <input type="radio" name="rn_${qid}_${docid}_$user" value="2" id="id_2" $checkedArray[2]/>
      <!-- associated --> ikke direkte
		</label>
	</td>
  </tr>
	<td>
		<label>
      <input type="radio" name="rn_${qid}_${docid}_$user" value="3" id="id_3" $checkedArray[3]/>
      <!-- relevant --> Riktig! ...men:
		</label>
	</td>
  </tr>
 
  <tr>
    <td>
		<label>
      <input type="radio" name="rn_${qid}_${docid}_$user" value="4" id="id_4" $checkedArray[4]>
      <!-- highly_relevant --> Noe sånt, ja!!
		</label></td>
  </tr>
   <tr>
    <td>
		<label>
      <input type="radio" name="rn_${qid}_${docid}_$user" value="5" id="id_5" $checkedArray[5]>
      <!-- 
       --> Vet ikke!!
		</label></td>
  </tr>
</table>
$vennligstVelgRelevansGrad
		</td>
	
TBL;
	if($i < count($_SESSION['hitHash']) - 1)
		$tbl.=<<<TBL
	<td><button type="submit" name="next" value="$n">neste treff <br/> (nr. $next av $length)</button></td>
TBL;
	else
		$tbl.=<<<TBL
	<td><button type="submit" name="last" value="$n">siste treff <br/> tilbake til dine søk</button></td>
	<!--<td> <a href="index.php?qid=$qid&user={$_SESSION['user']}">Velg neste søk</a> </td>-->
TBL;
	$tbl.=<<<TBL
	</tr>
	
	</table>
	<div >
        <div class="inline storpadding venstre" > <a href="index.php?user={$_SESSION['user']}">Tilbake til dine søk</a>  </div>
        <div class="inline storpadding hoyreflyt">
            <div ><span class="sterk">Ikke relevant</span>: Svarer definitivt ikke på søket </div>
            <div ><span class="sterk">Ikke helt</span> : (Kanhende... ville kanskje sett på den) </div>
            <div ><span class="sterk">Ikke direkte</span>: (Kanskje... men ville sikkert sett på den) </div>
            <div ><span class="sterk">Nesten! ...men</span>: (... For eksempel: ville foretrukket et annet format,<br/>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;en annen bok i samme serie, eller noe sånt. )</div>
            <div > <span class="sterk">Noe sånt, ja!!</span>: Merk: det kan bli flere som passer like bra ...</div>
        </div>
	
	</div>
		</form>
TBL;
	return $tbl;
}
?>