<?php

require_once('phpses.inc.php');
require_once('db-config.php');

ini_set('session.auto_start', '0');

//ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7);
//session_save_path('/tmp');
//ini_set('session.save_path','/tmp');

if ($iuajksdv != 9846598234) {
    exit();
};

// ###############==- Database connection -==##############################
//error_reporting(E_ALL ^ E_NOTICE);
$adip='';

$fms_bandwidth_test = 'rtmp://your.fms.com/speedtest/';
$fms_main = 'rtmp://your.fms.com/webcam/';
$fms_preview = 'rtmp://your.fms.com/preview/';
//$connection = mysql_connect($server, $username);
$connection = mysql_pconnect(HOST, USER, PASSWORD);
if (!$connection) {
    die('Could not connect to MySQL database, the server return the error: ' . mysql_error());
}
$db = @mysql_select_db(DBNAME);

$tax = 'normal';
mysql_set_charset('utf8');
include_once "auth_engine.php";
// ########################################################################

header('Content-type: text/html; charset=utf-8');
switch ($_POST['functions']) {
    case "getChattersList" :
        getChattersList($_POST['viewid'], $_POST['usertype']);
        break;
    case "viewHistory" :
        viewHistory($_POST['viewid'], $_POST['searchid'], $_POST['usertype']);
        break;

}

function chekmemberbanned1($pid, $mid) {
	$res = mysql_query("SELECT pid FROM banned WHERE pid=$pid AND mid REGEXP '^$mid$|-$mid$|-$mid-|^$mid-'") or die(mysql_error());
//echo("$mid-SELECT pid FROM banned WHERE pid=$pid AND mid REGEXP '^$mid$|-$mid$|-$mid-|^$mid-'");
    if (mysql_num_rows($res) > 0)
        return false;
    else
        return true;
}
function getGalPicsModel1($galId, $ajax) {
	//print_r($_SESSION);
	$e=mysql_query("SELECT authorize.studio, performers.studioid, galeryphoto.pid, galeryphoto.price FROM galeryphoto, performers, authorize WHERE authorize.id=performers.id AND authorize.id=galeryphoto.pid AND galeryphoto.id=".mysql_real_escape_string($galId));
	if(mysql_num_rows($e)>0)
	{
		$price=mysql_result($e,0,3);
		$mst=mysql_result($e,0,0);
		$st=mysql_result($e,0,1);
		$pid=mysql_result($e,0,2);
	}
	$ty=0;
	switch($_SESSION['usertype'])
	{
		case 'guest': $ty=0;
		break;
		case 'admin': if($_SESSION['usertype']=="admin" && ($_SESSION['admintype']!=6 && $_SESSION['admintype']!=7))
						{
							$ty=1;
						}
						else
						{
							if($_SESSION['userid']==$mst)
							{
								$ty=1;
							}
							else
							{
								$e1=mysql_query("SELECT authorize.studio FROM authorize WHERE authorize.id=".mysql_real_escape_string($_SESSION['userid']));
								if(mysql_num_rows($e1)>0)
								{
									if(mysql_result($e1,0,0)==$mst)	
									{
										$ty=1;
									}									
								}
							}
						}
		break;
		case 'studios': if($_SESSION['userid']==$st)
							{
								$ty=1;
							}
		break;
		case 'studiomanager': if($_SESSION['userid']==$st)
							{
								$ty=1;
							}
		break;
		case AUTH_USER_TYPE_MODEL: if($_SESSION['userid']==$pid)
							{
								$ty=1;
							}
		break;		
	}
	
	if($ty==1 || ($price==0))
	{
		$gals = array();
		$query = mysql_query("SELECT photos.name, photos.id
								FROM photos
								WHERE photos.gid='$galId' AND adminchek=1 and deleted = '0'");
		if ($query != FALSE) {
			while ($row = mysql_fetch_row($query))
			{
				$pt=$row[0];			
				if(file_exists("profileimages/".str_replace(".jpg","-thumb.jpg",$row[0])))
				$pt = str_replace(".jpg","-thumb.jpg",$row[0]);		               
				$gals[] = array($row[0],$row[1],$pt);
			}
		}

		$galPics = json_encode($gals);
		#àé-äè_ãàëåðåè+ìàññèâ_ïóòåé_ê_ôàéëàì
		if ($ajax == false)
			return $gals;
		echo $galPics;
	}
	else
	{
		$gals = array();
	$galPics = json_encode($gals);
		#àé-äè_ãàëåðåè+ìàññèâ_ïóòåé_ê_ôàéëàì
		if ($ajax == false)
			return $gals;
		echo $galPics;
	}
	
}
function getGalleriesModel1($perfId, $ajax) {
    $gal = mysql_query("SELECT galeryphoto.id, galeryphoto.name, galeryphoto.price, galeryphoto.avatar
                    FROM galeryphoto
                    WHERE galeryphoto.deleted=0 AND galeryphoto.pid='$perfId'
                    ORDER BY galeryphoto.name") Or Die("MySQL error: " . mysql_error());
    $mas = array();
    if ($gal != FALSE) {

        while ($row = mysql_fetch_row($gal))
            {$tyg=mysql_query("SELECT adminchek FROM photos WHERE id=".$row[3]);
			if(mysql_num_rows($tyg)>0)
			{
				if(mysql_result($tyg,0,0)==0)
				{
					$row[3]=97;
				}
			}
			$mas[] = $row;}
    }

    $galleries = json_encode($mas);
    if ($ajax == false)
        return $mas;
    echo $galleries;
}
function findByKeyword1($keywordstr)
// принимает в качестве аргумента тип пользователя, вызывающего функцию, и строку с ключевыми словами
// ключевые слова должны быть разделены запятой и могут состоять из 2 или более слов
{
$datee=0;
$m=date("m");
$y=date("Y");																						
	if(($m-1)<1)
	{
		$y=$y-1;
		$m=12;
	}
	else
	{
		$m=$m-1;
	}												
	$datee=strtotime("1.".$m.".".$y." 00:00:00");									

include_once "inc.php";
$gi = geoip_open("./Scripts/GeoIP.dat", GEOIP_STANDARD);
$ips = explode(',',getRealIpAddr());
if(($_SESSION['userid']!=5 && $_SESSION['userid']!=1) && (isset($_SESSION['usertype']) && $_SESSION['usertype']!='admin'))
{
	if (count($ips)>1){
		$thiscntry=array();
		for($i=0;$i<count($ips);$i++){		
			$thiscntry[] = geoip_country_code_by_addr($gi, $ips[$i]);
		}
		$thiscntry=implode("','",$thiscntry);
	}else{
	$ip = getRealIpAddr();
	$thiscntry = geoip_country_code_by_addr($gi, $ip);
	}
}
//$htyop="AND IF (performers.studioid!='".mysql_real_escape_string($_SESSION['userid'])."', ((SELECT country_code FROM countries WHERE id=performers.country) NOT IN('$thiscntry') ) ,true) ";

if($_SESSION['usertype']=="admin")
{
	$mysql=mysql_query("SELECT type FROM authorize WHERE id='".mysql_real_escape_string($_SESSION['userid'])."'");
	if(mysql_num_rows($mysql)>0)
	{
		
		if(mysql_result($mysql,0,0)!=6 && mysql_result($mysql,0,0)!=7)
		{			
			$htyop="";
		}
		else
		{
			$htyop="AND ((SELECT country_code FROM countries WHERE id=performers.country) NOT IN('$thiscntry') ) ";	
			$uy=array();
			$ghhjg=mysql_query("SELECT id FROM countries WHERE country_code IN('$thiscntry')");
			if(mysql_num_rows($ghhjg)>0)
			{
				while($hrg=mysql_fetch_array($ghhjg))
				{
					$uy[]=$hrg[0];
				}
			}
		}
	}	
}
else {
	$htyop="AND ((SELECT country_code FROM countries WHERE id=performers.country) NOT IN('$thiscntry') ) ";
	$uy=array();
			$ghhjg=mysql_query("SELECT id FROM countries WHERE country_code IN('$thiscntry')");
			if(mysql_num_rows($ghhjg)>0)
			{
				while($hrg=mysql_fetch_array($ghhjg))
				{
					$uy[]=$hrg[0];
				}
			}
	}
	
	
  $usertype = $_SESSION['usertype'];
    if (strlen($keywordstr) < 1)
        return -1;


        $usertype1 = "performers";
        $c = "pid";
        $photos = "photos";
        $chatstatus = ", chatstatus";
        $onlinestatus = ", onlinestatus, firstonline";
        $camscore = ", camscore";
        $niks=",nickname1, nickname, nickactive, avaparam, (SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=0 LIMIT 1) AS awards,(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=1 LIMIT 1) AS awards1,birthdate, REPLACE((SELECT b.countries FROM banned b WHERE b.pid=performers.id),'-',',') AS repl";
		$nick=" AND performers.deleted=0 $htyop AND ((performers.nickname!='' AND performers.nickactive=0) OR (performers.nickname1!='' AND performers.nickactive=1))";

    $t = "tags".$usertype1;

    $users = array();
    $arr = explode(",", $keywordstr);
    $ids = array();
    $foundusers = array();
    $i = 0;
    foreach ($arr as $val)
    {
        $val =  mysql_real_escape_string(trim($val));
        $res = mysql_query("SELECT tagid FROM tags WHERE tag LIKE '%$val%'");
        if (mysql_num_rows($res) > 0)
        {
        while ($row = mysql_fetch_assoc($res))
        {
            $id = $row['tagid'];
            $result = mysql_query("SELECT $usertype1.id, first, last, countries.country_name, city, $photos.name AS avatar, $photos.adminchek
                                        $chatstatus $onlinestatus $camscore $niks
                                    FROM $usertype1, $t, countries, $photos
                                    WHERE $t.$c=$usertype1.id AND countries.id=$usertype1.country $nick
                                        AND $photos.id=$usertype1.avatar
                                        AND $t.tags REGEXP '^$id$|^$id-|-$id$|-$id-'
                                    ORDER BY $usertype1.id") or die(mysql_error());
            if (mysql_num_rows($result) > 0)
            {
                while ($u = mysql_fetch_assoc($result))
				{
                    $rtyuy=0;
					$adf=explode(",",$u['repl']);
					foreach($uy as $gh=>$ht)
					{
						if(in_array($ht,$adf))
						$rtyuy=$rtyuy+1;
					}
					if($rtyuy==0)
					{
					if (array_search($u['id'], $ids) === false)
                    {
                        $ids[] = $u['id'];
                        $users[$i]['id'] = $u['id'];
						if($u['adminchek']==0)
						$users[$i]['avatar'] = "no_avatar_120x120.JPG";
						else
                        $users[$i]['avatar'] = $u['avatar'];
						$hhht=0;
						if((int)$u['awards']!=0 && (int)$u['awards1']!=0)
						{
							if((int)$u['awards']>=(int)$u['awards1'])
							{
								$hhht=$u['awards1'];
							}
							else
							{
								$hhht=$u['awards'];
							}				
						}
						elseif((int)$u['awards']==0 && (int)$u['awards1']!=0)
						{
							$hhht=$u['awards1'];
						}
						elseif((int)$u['awards']!=0 && (int)$u['awards1']==0)
						{
							$hhht=$u['awards'];
						}
						if($hhht>=1 && $hhht<=3)
						{						
							$hhht=$hhht;
						}				
						else 
						{
							$hhht=0;
						}
						$users[$i]["awards"]=$hhht;
                        $users[$i]['fio'] = $u['first']." ".$u['last'];
                        $users[$i]['country'] = $u['country_name'];
                        $users[$i]['city'] = $u['city'];
						$users[$i]['avaparam'] = $u['avaparam'];
						$users[$i]['birthdate'] = $u['birthdate'];
                                $users[$i]['chatstatus'] = $u['chatstatus'];
                                $users[$i]['onlinestatus'] = $u['onlinestatus'];
								$users[$i]['firstonline'] = $u['firstonline'];
                                $users[$i]['camscore'] = $u['camscore'];
                                $users[$i]['nick']=$u['nickname'];
                                if($u['nickactive']==1) $users[$i]['nick']=$u['nickname1'];
                        $i++;
                    }
				}					
				}
            }
        }

        }
    }

    return $users;
}
/*
if(isset($_SESSION['userid']) && isset($_SESSION['usertype']) && isset($params0) && isset($params1) && $params1>0){
				$dates=0;
				$datee=0;
				if(date("d")<16)
				{
					$datee=strtotime("15.".date("m").".".date("Y")." 23:59:59")+1;
					$dates=strtotime("01.".date("m").".".date("Y")." 00:00:00");
				}
				else
				{
					$datee=strtotime(date("t").".".date("m").".".date("Y")." 23:59:59")+1;
					$dates=strtotime("16.".date("m").".".date("Y")." 00:00:00");
				}
				$res0e =& $this->dbHandler->query("SELECT id FROM sessions WHERE userid=? AND date BETWEEN ? AND ?",array($_SESSION['userid'],$dates,$datee));
                if(PEAR::isError($res0e)){
                    return ChatEvents::createEvent(ChatEvents::$USER_LIKE_ERROR,0);
                }
                else
				{
						if($res0e->numRows()>0)
						{
							$res01 =& $this->dbHandler->query("SELECT date FROM like WHERE uid=? AND pid=? ORDER BY date DESC",array($_SESSION['userid'],$params1));
							if(PEAR::isError($res01)){
								return ChatEvents::createEvent(ChatEvents::$USER_LIKE_ERROR,0);
							}
							else{
									$row0 = null;
									$res01->fetchInto($row0);
									$lastY = date("Y",$row0[0]);
									$lastM = date("m",$row0[0]);
									$lastD = date("d",$row0[0]);
									$nowY = date("Y");
									$nowM = date("m");
									$nowD = date("d");
									if($lastY==$nowY && $lastM==$nowM && $lastD==$nowD)
									{
										return ChatEvents::createEvent(ChatEvents::$USER_LIKE_ERROR,0);
									}
									else
									{
										$res =& $this->dbHandler->query("INSERT INTO like(date,uid,pid) VALUES()",array(time(), $_SESSION['userid'],$params1));
											if(PEAR::isError($res)){
												return ChatEvents::createEvent(ChatEvents::$USER_LIKE_ERROR,0);
											}
											else return ChatEvents::createEvent(ChatEvents::$USER_LIKE_OK,0);
									}
								}
						}else return ChatEvents::createEvent(ChatEvents::$USER_LIKE_ERROR,0);
				}
		}else return ChatEvents::createEvent(ChatEvents::$USER_LIKE_ERROR,0);
*/
function birthdatenow($date)
{
	if($date)
	{
		$f=explode("-",$date);
		$nd=date("d");
		$nm=date("m");		
		if(isset($f[1]) && isset($f[2]) && $f[1]==$nm && ($f[2]==$nd || $f[2]==($nd-1) || $f[2]==($nd+1)))
		{
			return "bd";
		}
		else return "";
	}
	else return false;
}
function get_count_week($rt=0,$rt1=0)
{
	//echo($rt."++".$rt1."+");
	$we=$rt1-$rt;
	//echo($we."--");
	//echo(($we/604800)."--");
	$t=floor($we/604800);
	return $t;
}
function get_now_period()
{
	$dates=0;
	$datee=0;
	if(date("d")<16)
	{
		$datee=strtotime("15.".date("m").".".date("Y")." 23:59:59")+1;									
		$dates=strtotime("01.".date("m").".".date("Y")." 00:00:00");
		
	}
	else
	{
		$datee=strtotime(date("t").".".date("m").".".date("Y")." 23:59:59")+1;									
		$dates=strtotime("16.".date("m").".".date("Y")." 00:00:00");									
	}
	return array($dates,$datee);
}
function get_startend_period($ty=0)
{
	$dates=0;
	$datee=0;
	if(date("d",$ty)<16)
	{
		$datee=strtotime("15.".date("m",$ty).".".date("Y",$ty)." 23:59:59")+1;									
		$dates=strtotime("01.".date("m",$ty).".".date("Y",$ty)." 00:00:00");
		
	}
	else
	{
		$datee=strtotime(date("t",$ty).".".date("m",$ty).".".date("Y",$ty)." 23:59:59")+1;									
		$dates=strtotime("16.".date("m",$ty).".".date("Y",$ty)." 00:00:00");									
	}
	return array($dates,$datee);
}
function awards($dates=1350363600, $datee=1351746000)
{
	include_once "inc.php";
	$gi = geoip_open("./Scripts/GeoIP.dat", GEOIP_STANDARD);
	$ips = explode(',',getRealIpAddr());
	//print_r($ips);
	if(($_SESSION['userid']!=5 && $_SESSION['userid']!=1) && (isset($_SESSION['usertype']) && $_SESSION['usertype']!='admin'))
	{
		if (count($ips)>1){
			$thiscntry=array();
			for($i=0;$i<count($ips);$i++){		
				$thiscntry[] = geoip_country_code_by_addr($gi, $ips[$i]);
			}
			$thiscntry=implode("','",$thiscntry);
		}else{
			$ip = getRealIpAddr();
			$thiscntry = geoip_country_code_by_addr($gi, $ip);
		}
	}
	//print_r($thiscntry);
	$performers=array();
	$perf=mysql_query("SELECT performers.id, performers.nickname, performers.nickname1, performers.nickactive, photos.name, countries.country_code FROM performers, authorize, photos, countries WHERE performers.id=authorize.id  AND performers.id!=208 AND countries.id=performers.country AND performers.deleted=0 AND authorize.status!=2 AND  performers.avatar=photos.id AND photos.deleted=0  AND ((performers.nickname!='' AND performers.nickactive=0) OR (performers.nickname1!='' AND performers.nickactive=1))");
	if(mysql_num_rows($perf)>0)
	{
		while($gt=mysql_fetch_array($perf))
		{
			$hgh=true;
			if(isset($thiscntry))
			{
				if(is_array($thiscntry))
				{
					foreach($thiscntry as $v=>$k)
					{
						if($k==$gt[5])
						{
							$hgh=false;
						}
					}
				}
				else
				{
					if($thiscntry==$gt[5])
						{
							$hgh=false;
						}
				}
			}
			
			$n=$gt[1];
			if($gt[3]==1) $n=$gt[2];
			if(isset($_SESSION['userid']) && isset($_SESSION['usertype']) && $_SESSION['userid']==$gt[0])
			{
				$n=$n;
			}
			else
			{
				if(!$hgh) {$n=substr($n,0,3)."***"; $gt[4]="no_avatar_120x120.jpg";}
			}
			$performers[$gt[0]]=array("id"=>$gt[0],"nickname"=>$n,"avatar"=>$gt[4],"points"=>0);
		}
		
		$ses=mysql_query("SELECT performer, SUM(billedchips) FROM sessions WHERE date BETWEEN ".mysql_real_escape_string($dates)." AND ".mysql_real_escape_string($datee)." GROUP BY performer");
		if(mysql_num_rows($ses)>0)
		{
			while($fr=mysql_fetch_array($ses))
			{
				if($performers[$fr[0]])
				{
					$performers[$fr[0]]["points"]=$performers[$fr[0]]["points"]+(ceil($fr[1]*100)/100);
				}
			}
		}
		return $performers;
	}
	else return false;
}
function awards1($dates=1350363600, $datee=1351746000)
{
	include_once "inc.php";
	$gi = geoip_open("./Scripts/GeoIP.dat", GEOIP_STANDARD);
	$ips = explode(',',getRealIpAddr());	
	if(($_SESSION['userid']!=5 && $_SESSION['userid']!=1) && (isset($_SESSION['usertype']) && $_SESSION['usertype']!='admin'))
	{
		if (count($ips)>1){
			$thiscntry=array();
			for($i=0;$i<count($ips);$i++){		
				$thiscntry[] = geoip_country_code_by_addr($gi, $ips[$i]);
			}
			$thiscntry=implode("','",$thiscntry);
		}else{
			$ip = getRealIpAddr();
			$thiscntry = geoip_country_code_by_addr($gi, $ip);
		}
	}
	$performers=array();
	//echo("SELECT p.id, p.procentage, p.studioid,((SELECT IFNULL(SUM(t.sec),0) FROM timeinchat t WHERE t.pid=p.id AND t.type=0 AND t.date BETWEEN ".mysql_real_escape_string($dates)." AND ".mysql_real_escape_string($datee).")/60 + (SELECT IFNULL(COUNT(l.id),0) FROM `like` l WHERE l.pid=p.id AND l.date BETWEEN ".mysql_real_escape_string($dates)." AND ".mysql_real_escape_string($datee).")*10) AS `summ1`, p.nickname, p.nickname1, p.nickactive, pp.name, countries.country_code FROM performers p, authorize a, photos pp, countries WHERE p.id=a.id AND p.id!=208 AND countries.id=p.country AND a.status!=2 AND  p.avatar=pp.id AND pp.deleted=0  AND p.deleted=0 AND ((p.nickname!='' AND p.nickactive=0) OR (p.nickname1!='' AND p.nickactive=1)) ORDER BY `summ1` DESC, p.id LIMIT 0,20");
	$perf=mysql_query("SELECT p.id, p.procentage, p.studioid,((SELECT IFNULL(SUM(t.sec),0) FROM timeinchat t WHERE t.pid=p.id AND t.type=0 AND t.date BETWEEN ".mysql_real_escape_string($dates)." AND ".mysql_real_escape_string($datee).")/60 + (SELECT IFNULL(COUNT(l.id),0) FROM `like` l WHERE l.pid=p.id AND l.date BETWEEN ".mysql_real_escape_string($dates)." AND ".mysql_real_escape_string($datee).")*10) AS `summ1`, p.nickname, p.nickname1, p.nickactive, pp.name, countries.country_code FROM performers p, authorize a, photos pp, countries WHERE p.id=a.id AND p.id!=208 AND countries.id=p.country AND a.status!=2 AND  p.avatar=pp.id AND pp.deleted=0  AND p.deleted=0 AND ((p.nickname!='' AND p.nickactive=0) OR (p.nickname1!='' AND p.nickactive=1)) ORDER BY `summ1` DESC, p.id LIMIT 0,20");
	if(mysql_num_rows($perf)>0)
	{
		while($gt=mysql_fetch_array($perf))
		{
			$hgh=true;
			if(isset($thiscntry))
			{
				if(is_array($thiscntry))
				{
					foreach($thiscntry as $v=>$k)
					{
						if($k==$gt[8])
						{
							$hgh=false;
						}
					}
				}
				else
				{
					if($thiscntry==$gt[8])
						{
							$hgh=false;
						}
				}
			}
			$n=$gt[4];
			if($gt[6]==1) $n=$gt[5];
			
			if(isset($_SESSION['userid']) && isset($_SESSION['usertype']) && $_SESSION['userid']==$gt[0])
			{
				$n=$n;
			}
			else
			{
				if(!$hgh) {$n=substr($n,0,3)."***"; $gt[7]="no_avatar_120x120.jpg";}
			}			
			$performers[$gt[0]]=array("id"=>$gt[0],"nickname"=>$n,"avatar"=>$gt[7],"points"=>$gt[3]);
		}
		
		return $performers;
	}
	else return false;
}
function operationwithmoney($m=0, $pid,$type='')
{
	$pid=(int)$pid;
	$m=(float)$m;
	//$b=(int)$b;
	//echo("----".$m);
	$procm=-1;
	$procp=-1;
	$procs=-1;
	$procsr=0;
	$procaff=0;
	$hg="";
	if((int)$pid>0 && (float)$m>0 && isset($_SESSION['userid']) && $_SESSION['userid']>0 && isset($_SESSION['usertype']) && $_SESSION['usertype']=='users')
	{
		$hg.="SESSION: id:".$_SESSION['userid'].", type:".$_SESSION['usertype']."| pid:".$pid.", ";
		//echo("SELECT users.referrer, users.referrersite, users.bonuscredits, users.chips FROM users WHERE users.id=".mysql_real_escape_string($_SESSION['userid']));
	   $us=mysql_query("SELECT users.referrer, users.referrersite, users.bonuscredits, users.chips, authorize.referrer AS aff, (SELECT affiliates.percentage FROM affiliates WHERE id=authorize.referrer) AS affprec FROM users, authorize  WHERE users.id='".mysql_real_escape_string($_SESSION['userid'])."' AND authorize.id=users.id");
	   //echo("<br>".mysql_num_rows($us));
	   if(mysql_num_rows($us)>0)
		{			
			$gt=mysql_query("SELECT a.studio, performers.studioid, performers.procentage, performers.firstonline, a.msref, (SELECT aa.msrefproc FROM authorize aa WHERE aa.id=a.msref) AS msrefp   FROM authorize a,performers WHERE a.id=".mysql_real_escape_string($pid)." AND performers.id=a.id AND a.user_type='performers' AND performers.deleted=0");
			if(mysql_num_rows($gt)>0)
			{
				$hg.="ms:".mysql_result($gt,0,0).", %p:".mysql_result($gt,0,2).", st:".mysql_result($gt,0,1).",";
				$procp=mysql_result($gt,0,2);
				if(mysql_result($gt,0,0)>0)
				{
					$rt=mysql_query("SELECT mstprocent FROM authorize WHERE id=".mysql_real_escape_string(mysql_result($gt,0,0))." AND user_type='admin' AND type=6");
					if(mysql_num_rows($rt)>0)
					{
						$hg.="%ms:".mysql_result($rt,0,0).",";
						$procm=mysql_result($rt,0,0);
					}
				}
				if(mysql_result($gt,0,1)>0)
				{
					$rt=mysql_query("SELECT studios.percentage FROM studios, authorize WHERE authorize.id=studios.id AND studios.id=".mysql_real_escape_string(mysql_result($gt,0,1))." AND authorize.user_type='studios'");
					if(mysql_num_rows($rt)>0)
					{
						$hg.="%st:".mysql_result($rt,0,0).", ";
						$procs=mysql_result($rt,0,0);
					}
				}
				if(mysql_result($gt,0,5)>0)
				{
					$hg.="%msref:".mysql_result($gt,0,5).", ";
					$procsr=mysql_result($gt,0,5);
				}

				$ernm=-1;//mega
				$ernp=-1;//performer
				$erns=-1;//studio
				$ernsr=-1;//studior
				$ernaff=0;//aff
				$bc=0;
				$nc=$m;
				$row=mysql_fetch_row($us);
				//echo($pid." -- ".$nc);
				//print_r($row);
				$hg.="ref:".$row[0].", refs:".$row[1].", bonuscr:".$row[2].", cr:".$row[3].",  aff:".$row[4].", affp:".$row[5].", ";
				if($row[0]==$pid && $row[1]==0)
				{
					if($procm>0) $procm=0;
					if($procs>0)
					$procs=getconf("proc_ref_member");
					else $procp=getconf("proc_ref_member");
				}
				else
				{
					if($row[4]>0 && $row[5]>0)
					{
						$procaff=$row[5];
						
					}
				}
				if($procs<=0)
				{
					$procs=$procp;
					$procp=100;
				}
				//echo(mysql_result($gt,0,3));
				if(mysql_result($gt,0,3)<=129600)
				{
					if(mysql_result($us,0,2)>0)
					{
						if(mysql_result($us,0,2)>=$m)
						{
							$ernm=0;
							$ernsr=0;
							$erns=$m*($procs/100);
							$ernp=$erns*($procp/100);
							$bc=$m;
							$nc=0;
						}
						elseif((mysql_result($us,0,2)+mysql_result($us,0,3))>=$m)
						{
							$mc=$m-mysql_result($us,0,2);
							if($procm>0)
							{
								$ernm=$mc*($procm/100);
								$ernsr=$ernm*($procsr/100);
								$ernm=$ernm-$ernsr;
							}
							else {$ernm=0;	$ernsr=0;}
							$erns=$m*($procs/100);
							$ernp=$erns*($procp/100);
							$bc=mysql_result($us,0,2);
							$nc=$mc;
							if($procaff>0)
							{
								$ernaff=$nc*($procaff/100);
							}
						}
						else
						{
							$ernm=-1;
							$ernp=-1;
							$erns=-1;
							$ernsr=-1;
						}
					}
					elseif(mysql_result($us,0,3)>=$m)
					{
						if($procm>0)
						{
							$ernm=$m*($procm/100);
							$ernsr=$ernm*($procsr/100);
							$ernm=$ernm-$ernsr;
						}
						else {$ernm=0;	$ernsr=0;}
							$erns=$m*($procs/100);
							$ernp=$erns*($procp/100);
							if($procaff>0)
							{
								$ernaff=$m*($procaff/100);
							}
					}
					else
					{
						$ernm=-1;
						$ernp=-1;
						$erns=-1;
						$ernsr=-1;
					}
				}
				elseif(mysql_result($us,0,3)>=$m)
				{
					if($procm>0)
					{
						$ernm=$m*($procm/100);
						$ernsr=$ernm*($procsr/100);
						$ernm=$ernm-$ernsr;
					}
					else {$ernm=0;	$ernsr=0;}
						$erns=$m*($procs/100);
						$ernp=$erns*($procp/100);
						if($procaff>0)
						{
							$ernaff=$m*($procaff/100);
						}
				}
				else
				{
					$ernm=-1;
					$ernp=-1;
					$erns=-1;
				}
				//echo(" --- procm: ".$procm." --- procp: ".$procp." --- procs: ".$procs."<br>");
				//echo("--- money: ".$m." --- ernm: ".$ernm." --- ernp: ".$ernp." --- erns: ".$erns."<br>");
				if($ernm!=-1 && $erns!=-1 && $ernp!=-1)
				{
					$ernaff=(floor($ernaff*100)/100);
					//echo("INSERT INTO sessions (`id`, `userid`, `video_sess_type`, `performer`, `date`, `earnedchips`, `billedchips`, `paymentstatus`,`megastuderned`,`paymentstatus2`,`studioerned`,`paymentstatus3`)	VALUES (NULL, ".mysql_real_escape_string($_SESSION['userid']).", '".$type."', ".mysql_real_escape_string($pid).", ".time().", ".$erns.", ".$m.", 0,".$ernm.",0,'".$erns-$ernp."',0)"."<br>");
					//echo("UPDATE users SET chips=(chips-".$nc."), bonuscredits=(bonuscredits-".$bc.") WHERE id=".mysql_real_escape_string($_SESSION['userid'])."<br>");
					//echo("UPDATE performers SET chips=chips+".$ernp." WHERE id=".mysql_real_escape_string($pid)."<br>");
					logoperation(777,0,"operationwithmoney params",$hg,$_SERVER['REMOTE_ADDR']);
					mysql_query("UPDATE users SET chips=(chips-".$nc."), bonuscredits=(bonuscredits-".$bc.") WHERE id=".mysql_real_escape_string($_SESSION['userid']));
					mysql_query("UPDATE performers SET chips=chips+".$ernp." WHERE id=".mysql_real_escape_string($pid));
					mysql_query("INSERT INTO sessions (`id`, `userid`, `video_sess_type`, `performer`, `date`, `earnedchips`, `billedchips`, `paymentstatus`,`megastuderned`,`paymentstatus2`,`studioerned`,`paymentstatus3`,`msreferned`,`paymentstatus4`,`afferned`,`paymentstatus5`)
					VALUES (NULL, ".mysql_real_escape_string($_SESSION['userid']).", '".$type."', ".mysql_real_escape_string($pid).", ".time().", ".$ernp.", ".$m.", 0,".$ernm.",0,'".($erns-$ernp)."',0,".$ernsr.",0,".$ernaff.",0)") or die(mysql_error());
					//echo("--- money: ".$m." --- ernm: ".$ernm." --- ernp: ".$ernp." --- erns: ".$erns."<br>");
					logoperation(777,0,"operationwithmoney","-type: ".$type." pid:".$pid.", userid:".$_SESSION['userid'].", -money: ".$m." -ernm: ".$ernm." -ernmr: ".$ernsr." -ernp: ".$ernp." -erns: ".$erns." -ernaff: ".$ernaff." -procaff: ".$procaff." -procm: ".$procm." -procp: ".$procp." -procs: ".$procs." -bonuscredits: ".$bc." -normalcredits: ".$nc,$_SERVER['REMOTE_ADDR']);
				}
				else return "0";
			}
			else return "1";
	    }
		else return "2";
	}
	else {return "3";logoperation(777,0,"operationwithmoney","Error Invalide parametrs m:".$m.", pid:".$pid.", type:".$type='',$_SERVER['REMOTE_ADDR']);}
}
function findModelByName1($perfname) {
   // ищет модель по активному нику
    $datee=0;
	$m=date("m");
	$y=date("Y");																						
	if(($m-1)<1)
	{
		$y=$y-1;
		$m=12;
	}
	else
	{
		$m=$m-1;
	}												
	$datee=strtotime("1.".$m.".".$y." 00:00:00");									
	
	include_once "inc.php";
$gi = geoip_open("./Scripts/GeoIP.dat", GEOIP_STANDARD);
$ips = explode(',',getRealIpAddr());
if(($_SESSION['userid']!=5 && $_SESSION['userid']!=1) && (isset($_SESSION['usertype']) && $_SESSION['usertype']!='admin'))
	{
	if (count($ips)>1){
		$thiscntry=array();
		for($i=0;$i<count($ips);$i++){		
			$thiscntry[] = geoip_country_code_by_addr($gi, $ips[$i]);
		}
		$thiscntry=implode("','",$thiscntry);
	}else{
	$ip = getRealIpAddr();
	$thiscntry = geoip_country_code_by_addr($gi, $ip);
	}
}
//$htyop="AND IF (performers.studioid!='".mysql_real_escape_string($_SESSION['userid'])."', ((SELECT country_code FROM countries WHERE id=performers.country) NOT IN('$thiscntry') ) ,true) ";
$uy=array();
if($_SESSION['usertype']=="admin")
{
	$mysql=mysql_query("SELECT type FROM authorize WHERE id='".mysql_real_escape_string($_SESSION['userid'])."'");
	if(mysql_num_rows($mysql)>0)
	{
		
		if(mysql_result($mysql,0,0)!=6 && mysql_result($mysql,0,0)!=7)
		{			
			$htyop="";
		}
		else
		{
			$htyop="AND ((SELECT country_code FROM countries WHERE id=performers.country) NOT IN('$thiscntry') ) ";	
			$uy=array();
			$ghhjg=mysql_query("SELECT id FROM countries WHERE country_code IN('$thiscntry')");
			if(mysql_num_rows($ghhjg)>0)
			{
				while($hrg=mysql_fetch_array($ghhjg))
				{
					$uy[]=$hrg[0];
				}
			}
		}
	}	
}
else {
	$htyop="AND ((SELECT country_code FROM countries WHERE id=performers.country) NOT IN('$thiscntry') ) ";
	$uy=array();
			$ghhjg=mysql_query("SELECT id FROM countries WHERE country_code IN('$thiscntry')");
			if(mysql_num_rows($ghhjg)>0)
			{
				while($hrg=mysql_fetch_array($ghhjg))
				{
					$uy[]=$hrg[0];
				}
			}
	}
	
	
	
    if($perfname=='') return false;
    $perfname=  mysql_real_escape_string(trim($perfname));
    $n = mysql_query("SELECT nickactive, avatar FROM performers WHERE  performers.deleted=0 $htyop AND ((performers.nickname!='' AND performers.nickactive=0) OR (performers.nickname1!='' AND performers.nickactive=1)) AND nickname LIKE '%$perfname%' OR nickname1 LIKE '%$perfname%'");
     if(mysql_num_rows($n)>0){
    $avatar = array();
    $z = 0;
    while ($activeNick = mysql_fetch_assoc($n))
    {
    if ($activeNick['nickactive'] == 0)
        $column = 'nickname';
    elseif ($activeNick['nickactive'] == 1)
        $column = 'nickname1';
            $g = mysql_query("SELECT photos.name,photos.adminchek FROM photos WHERE photos.id='$activeNick[avatar]'");
            $filo = mysql_fetch_assoc($g);
            if ($filo['name'] == null)
                $avatar[$z] = 'noavatar.jpg';
			else if($filo['adminchek']==0)
				$avatar[$z] = "no_avatar_120x120.JPG";
            else
                $avatar[$z] = $filo['name'];
            $z++;
    }
    $res = mysql_query("SELECT pid FROM banned WHERE mid REGEXP '^0$|-0$|-0-|^0-'") or die(mysql_error());
    $models = "";
    while ($data = mysql_fetch_row($res)) {
        if ($models == "") {
            $models+=$data[0];
        } else {
            $models = $models . ", " . $data[0];
        }
    }
    if ($models != "")
        $models = " AND performers.id NOT IN(" . $models . ")";
		$rrsss = mysql_query("SELECT performers.id, performers.$column AS nick, performers.chatstatus,performers.onlinestatus,performers.firstonline, performers.avaparam, 
		(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=0 LIMIT 1) AS awards,(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=1 LIMIT 1) AS awards1,birthdate, REPLACE((SELECT b.countries FROM banned b WHERE b.pid=performers.id),'-',',') AS repl
                            FROM performers
                            WHERE  performers.deleted=0 $htyop AND ((performers.nickname!='' AND performers.nickactive=0) OR (performers.nickname1!='' AND performers.nickactive=1)) AND performers.$column LIKE '%$perfname%'$models") Or Die(mysql_error());

    $i = 1;
    $models = array();
    while ($row = mysql_fetch_assoc($rrsss)) {        
		$hhht=0;
		$rtyuy=0;
		$adf=explode(",",$row['repl']);
		foreach($uy as $gh=>$ht)
		{
			if(in_array($ht,$adf))
			$rtyuy=$rtyuy+1;
		}
		if($rtyuy==0)
		{
		if((int)$row['awards']!=0 && (int)$row['awards1']!=0)
		{
			if((int)$row['awards']>=(int)$row['awards1'])
			{
				$hhht=$row['awards1'];
			}
			else
			{
				$hhht=$row['awards'];
			}				
		}
		elseif((int)$row['awards']==0 && (int)$row['awards1']!=0)
		{
			$hhht=$row['awards1'];
		}
		elseif((int)$row['awards']!=0 && (int)$row['awards1']==0)
		{
			$hhht=$row['awards'];
		}
		if($hhht>=1 && $hhht<=3)
		{						
			$hhht=$hhht;
		}				
		else 
		{
			$hhht=0;
		}		
		unset($row['awards1']);
		$models[$i] = $row;
        $models[$i]['avatar'] = $avatar[$i];
		$models[$i]['awards'] = $hhht;
        $i++;
    }
	}
    return $models;}
    else{return false;}
}
function getRealIpAddr(){
  if (!empty($_SERVER['HTTP_CLIENT_IP'])){
    $ip=$_SERVER['HTTP_CLIENT_IP'];
  }elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
    $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
  }else{
    $ip=$_SERVER['REMOTE_ADDR'];
  }
  return $ip;
}
function simpleDetectProxy(){
    return isset($_SERVER['HTTP_X_FORWARDED_FOR ']) || isset($_SERVER['HTTP_VIA']) || (count(explode(',',getRealIpAddr()))>1);
}
function testSetings1($mid, $status, $cat) {
$datee=0;
$m=date("m");
$y=date("Y");
$uy=array();																						
if(($m-1)<1)
{
	$y=$y-1;
	$m=12;
}
else
{
	$m=$m-1;
}												
	$datee=strtotime("1.".$m.".".$y." 00:00:00");									

$mid=(int)$mid;
$cat=(int)$cat;
$perioddates=get_now_period()[0];
$perioddatee=get_now_period()[1];
include_once "inc.php";
$gi = geoip_open("./Scripts/GeoIP.dat", GEOIP_STANDARD);
$ips = explode(',',getRealIpAddr());
//print_r($ips);
if(($_SESSION['userid']!=5 && $_SESSION['userid']!=1) && (isset($_SESSION['usertype']) && $_SESSION['usertype']!='admin'))
{
	if (count($ips)>1){
		$thiscntry=array();
		for($i=0;$i<count($ips);$i++){		
			$thiscntry[] = geoip_country_code_by_addr($gi, $ips[$i]);
		}
		$thiscntry=implode("','",$thiscntry);
	}else{
	$ip = getRealIpAddr();
	$thiscntry = geoip_country_code_by_addr($gi, $ip);
	}
}
//$htyop="AND IF (performers.studioid!='".mysql_real_escape_string($_SESSION['userid'])."', ((SELECT country_code FROM countries WHERE id=performers.country) NOT IN('$thiscntry') ) ,true) ";
if($_SESSION['usertype']=="admin")
{
	$mysql=mysql_query("SELECT type FROM authorize WHERE id='".mysql_real_escape_string($_SESSION['userid'])."'");
	if(mysql_num_rows($mysql)>0)
	{
		
		if(mysql_result($mysql,0,0)!=6 && mysql_result($mysql,0,0)!=7)
		{			
			$htyop="";
			$ghhjg=array();
		}
		else
		{
			$htyop="AND ((SELECT country_code FROM countries WHERE id=performers.country) NOT IN('$thiscntry') ) ";	
			$uy=array();
			$ghhjg=mysql_query("SELECT id FROM countries WHERE country_code IN('$thiscntry')");
			if(mysql_num_rows($ghhjg)>0)
			{
				while($hrg=mysql_fetch_array($ghhjg))
				{
					$uy[]=$hrg[0];
				}
			}
		}
	}	
}
else { 
	$htyop="AND ((SELECT country_code FROM countries WHERE id=performers.country) NOT IN('$thiscntry') ) ";	
		$uy=array();		
		$ghhjg=mysql_query("SELECT id FROM countries WHERE country_code IN('$thiscntry')");
		if(mysql_num_rows($ghhjg)>0)
		{
			while($hrg=mysql_fetch_array($ghhjg))
			{
				$uy[]=$hrg[0];
			}
		}
	}
//echo($htyop);
    /////////////////////banned models
    //$res = mysql_query("SELECT pid FROM banned WHERE mid LIKE $mid") or die(mysql_error());
//echo("SELECT pid FROM banned WHERE mid LIKE $mid");
    //$models="";
    //while($data = mysql_fetch_row($res))
    //{
    //if($models=="") $models+=$data[0];
    //$models+=", ".$data[0];
    //}
    $res = mysql_query("SELECT pid FROM banned WHERE mid REGEXP '^$mid$|-$mid$|-$mid-|^$mid-'")or die(mysql_error()) ;
    $models = "";
    if(mysql_num_rows($res)>0){
    while ($data = mysql_fetch_row($res)) {
        if ($models == "") {
            $models+=$data[0];
        } else {
            $models = $models . ", " . $data[0];
        }
    }}
    if ($models != "")
        $models = " AND performers.id NOT IN(" . $models . ")";

        $display = 0;
        $order = 0;
        $group = 0;
        $friend = 0;

    ////////////////
    $country = "";

    /////////////////
    $in = "";
    if ($display == 0) {
        $res = mysql_query("SELECT blockedmodels FROM banned_members WHERE memberid=$mid") or die(mysql_error());
        if(mysql_num_rows($res)>0){
        $data = mysql_fetch_row($res);
        if ($data[0] != "") {
            $bannedmodels = explode("-", $data[0]);
        }
        if (count($bannedmodels) > 0) {
            $in = "AND performers.id NOT IN(" . implode(",", array_unique($bannedmodels)) . ")";
        }
        }
    } else if ($display == 2) {
        $res = mysql_query("SELECT favs FROM users WHERE id=$mid") ;
        if(mysql_num_rows($res)>0){
        $data = mysql_fetch_row($res);
        if ($data[0] != "") {
            $bannedmodels = explode("-", $data[0]);
            if (count($bannedmodels) > 0) {
                $in = "AND performers.id IN(" . implode(",", array_unique($bannedmodels)) . ")";
            }
        }
        }
    }
    $orderby = "";
    if ($order == 0) {
        $orderby = " ORDER BY rating DESC ";
    } else if ($order == 2) {
        $orderby = " ORDER BY performers.signupdate DESC";
    } else if ($order == 3) {
        $orderby = " ORDER BY performers.lastlogindate DESC";
    }

    $mas = array();
    $mast = array();
    if ($cat != 0)
        $categories = " AND performers.idcategories='$cat'";
    if ($status == 'online')
        $stat = "performers.onlinestatus='online' AND ";
    if ($status == 'offline')
        $stat = "performers.onlinestatus='offline' AND ";
    if ($group == 1) {
        $mas1 = array();
        $mas2 = array();
        $mas3 = array();
		$res = mysql_query("SELECT performers.id, photos.name, performers.nickname, performers.nickname1,
        performers.nickactive, performers.chatstatus, (SELECT SUM(billedchips) FROM sessions WHERE sessions.performer=performers.id AND date BETWEEN $perioddates AND $perioddatee GROUP BY performer)AS rating,performers.onlinestatus,performers.firstonline,photos.adminchek, performers.avaparam, 
		(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=0 LIMIT 1) AS awards,(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=1 LIMIT 1) AS awards1,performers.birthdate, REPLACE((SELECT b.countries FROM banned b WHERE b.pid=performers.id),'-',',') AS repl
        FROM performers, photos
        WHERE $stat performers.avatar=photos.id $categories $in $models AND performers.chatstatus='privat' AND performers.deleted=0 $htyop AND ((performers.nickname!='' AND performers.nickactive=0) OR (performers.nickname1!='' AND performers.nickactive=1)) $country $orderby")or die(mysql_error());
        while ($data = mysql_fetch_row($res)) {  
			$nick = $data[2];
            $rtyuy=0;			
			$adf=explode(",",$data[14]);
			foreach($uy as $gh=>$ht)
			{
				if(in_array($ht,$adf))
				$rtyuy=$rtyuy+1;				
			}
			if($rtyuy==0)
			{
			if ($data[4] == 1)
                $nick = $data[3];
			if($data[9]==0)
				$data[1]="no_avatar_120x120.JPG";
				$hhht=0;
				if((int)$data[11]!=0 && (int)$data[12]!=0)
				{
					if((int)$data[11]>=(int)$data[12])
					{
						$hhht=$data[12];
					}
					else
					{
						$hhht=$data[11];
					}				
				}
				elseif((int)$data[11]==0 && (int)$data[12]!=0)
				{
					$hhht=$data[12];
				}
				elseif((int)$data[11]!=0 && (int)$data[12]==0)
				{
					$hhht=$data[11];
				}
				if($hhht>=1 && $hhht<=3)
				{						
					$hhht=$hhht;
				}				
				else 
				{
					$hhht=0;
				}
				
            $mas1[] = array("rating" => $data[6], "id" => $data[0], "avatar" => $data[1], "nick" => $nick, "chatstatus" => $data[5],"onlinestatus"=>$data[7],"firstonline"=>$data[8],"avaparam"=>$data[10],"awards"=>$hhht,"birthdate"=>$data[13]);

        }
		}
        //order by alpha
        if ($order == 1) {
            for ($i = (count($mas1) - 1); $i >= 0; $i--) {
                for ($j = 0; $j <= ($i - 1); $j++)
                    if ($mas1[$j]['nick'] > $mas1[$j + 1]['nick']) {
                        $tmp = $mas1[$j];
                        $mas1[$j] = $mas1[$j + 1];
                        $mas1[$j + 1] = $tmp;
                    }
            }
        }
        //nude
		$res = mysql_query("SELECT performers.id, photos.name, performers.nickname, performers.nickname1,
        performers.nickactive, performers.chatstatus, (SELECT SUM(billedchips) FROM sessions WHERE sessions.performer=performers.id AND date BETWEEN $perioddates AND $perioddatee GROUP BY performer)AS rating, performers.onlinestatus,performers.firstonline,photos.adminchek, performers.avaparam, 
		(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=0 LIMIT 1) AS awards,(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=1 LIMIT 1) AS awards1,performers.birthdate, REPLACE((SELECT b.countries FROM banned b WHERE b.pid=performers.id),'-',',') AS repl
        FROM performers, photos
        WHERE $stat performers.avatar=photos.id $categories $in $models AND performers.chatstatus='nude' AND performers.deleted=0 $htyop AND ((performers.nickname!='' AND performers.nickactive=0) OR (performers.nickname1!='' AND performers.nickactive=1)) $country $orderby") or die(mysql_error());
        while ($data = mysql_fetch_row($res)) {  
			$nick = $data[2];
			$rtyuy=0;
			$adf=explode(",",$data[14]);
			foreach($uy as $gh=>$ht)
			{
				if(in_array($ht,$adf))
				$rtyuy=$rtyuy+1;
			}
			if($rtyuy==0)
			{
            if ($data[4] == 1)
                $nick = $data[3];
			if($data[9]==0)
				$data[1]="no_avatar_120x120.JPG";
				$hhht=0;
				if((int)$data[11]!=0 && (int)$data[12]!=0)
				{
					if((int)$data[11]>=(int)$data[12])
					{
						$hhht=$data[12];
					}
					else
					{
						$hhht=$data[11];
					}				
				}
				elseif((int)$data[11]==0 && (int)$data[12]!=0)
				{
					$hhht=$data[12];
				}
				elseif((int)$data[11]!=0 && (int)$data[12]==0)
				{
					$hhht=$data[11];
				}
				if($hhht>=1 && $hhht<=3)
				{						
					$hhht=$hhht;
				}				
				else 
				{
					$hhht=0;
				}
            $mas2[] = array("rating" => $data[6], "id" => $data[0], "avatar" => $data[1], "nick" => $nick, "chatstatus" => $data[5],"onlinestatus"=>$data[7],"firstonline"=>$data[8],"avaparam"=>$data[10],"awards"=>$hhht,"birthdate"=>$data[13]);
			}
        }
        //order by alpha
        if ($order == 1) {
            for ($i = (count($mas2) - 1); $i >= 0; $i--) {
                for ($j = 0; $j <= ($i - 1); $j++)
                    if ($mas2[$j]['nick'] > $mas2[$j + 1]['nick']) {
                        $tmp = $mas2[$j];
                        $mas2[$j] = $mas2[$j + 1];
                        $mas2[$j + 1] = $tmp;
                    }
            }
        }
		$res = mysql_query("SELECT performers.id, photos.name, performers.nickname, performers.nickname1,
        performers.nickactive, performers.chatstatus, (SELECT SUM(billedchips) FROM sessions WHERE sessions.performer=performers.id AND date BETWEEN $perioddates AND $perioddatee GROUP BY performer)AS rating,performers.onlinestatus, performers.firstonline,photos.adminchek, performers.avaparam, 
		(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=0 LIMIT 1) AS awards,(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=1 LIMIT 1) AS awards1,performers.birthdate, REPLACE((SELECT b.countries FROM banned b WHERE b.pid=performers.id),'-',',') AS repl
        FROM performers, photos
        WHERE $stat performers.avatar=photos.id $categories $in $models AND performers.chatstatus NOT IN ('nude','privat') AND performers.deleted=0 $htyop AND ((performers.nickname!='' AND performers.nickactive=0) OR (performers.nickname1!='' AND performers.nickactive=1)) $country $orderby") or die(mysql_error());
        while ($data = mysql_fetch_row($res)) {
			$nick = $data[2];
            $rtyuy=0;
			$adf=explode(",",$data[14]);
			foreach($uy as $gh=>$ht)
			{
				if(in_array($ht,$adf))
				$rtyuy=$rtyuy+1;
			}
			if($rtyuy==0)
			{
			if ($data[4] == 1)
                $nick = $data[3];
			if($data[9]==0)
				$data[1]="no_avatar_120x120.JPG";
				$hhht=0;
				if((int)$data[11]!=0 && (int)$data[12]!=0)
				{
					if((int)$data[11]>=(int)$data[12])
					{
						$hhht=$data[12];
					}
					else
					{
						$hhht=$data[11];
					}				
				}
				elseif((int)$data[11]==0 && (int)$data[12]!=0)
				{
					$hhht=$data[12];
				}
				elseif((int)$data[11]!=0 && (int)$data[12]==0)
				{
					$hhht=$data[11];
				}
				if($hhht>=1 && $hhht<=3)
				{						
					$hhht=$hhht;
				}				
				else 
				{
					$hhht=0;
				}
            $mas3[] = array("rating" => $data[6], "id" => $data[0], "avatar" => $data[1], "nick" => $nick, "chatstatus" => $data[5],"onlinestatus"=>$data[7],"firstonline"=>$data[8],"avaparam"=>$data[10],"awards"=>$hhht,"birthdate"=>$data[13]);
        }
		}
        //order by alpha
        if ($order == 1) {
            for ($i = (count($mas3) - 1); $i >= 0; $i--) {
                for ($j = 0; $j <= ($i - 1); $j++)
                    if ($mas3[$j]['nick'] > $mas3[$j + 1]['nick']) {
                        $tmp = $mas3[$j];
                        $mas3[$j] = $mas3[$j + 1];
                        $mas3[$j + 1] = $tmp;
                    }
            }
        }
        $mas = array_merge_recursive($mas1, $mas2, $mas3);
    } else {    
		$res = mysql_query("SELECT performers.id, photos.name, performers.nickname, performers.nickname1,
        performers.nickactive, performers.chatstatus, (SELECT SUM(billedchips) FROM sessions WHERE sessions.performer=performers.id AND date BETWEEN $perioddates AND $perioddatee GROUP BY performer)AS rating,performers.onlinestatus,performers.firstonline,photos.adminchek, performers.avaparam, 
		(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=0 LIMIT 1) AS awards,(SELECT awards.position FROM awards WHERE awards.date=$datee AND awards.pid=performers.id AND type=1 LIMIT 1) AS awards1,performers.birthdate, REPLACE((SELECT b.countries FROM banned b WHERE b.pid=performers.id),'-',',') AS repl
        FROM performers, photos
        WHERE $stat performers.avatar=photos.id AND performers.deleted=0 $htyop AND ((performers.nickname!='' AND performers.nickactive=0) OR (performers.nickname1!='' AND performers.nickactive=1)) $categories $in $models $country $orderby") or die(mysql_error());
        while ($data = mysql_fetch_row($res)) {  
			$nick = $data[2];
            $rtyuy=0;
			$adf=explode(",",$data[14]);
			foreach($uy as $gh=>$ht)
			{
				if(in_array($ht,$adf))
				$rtyuy=$rtyuy+1;
			}
			if($rtyuy==0)
			{
			if ($data[4] == 1)
                $nick = $data[3];
			if($data[9]==0)
				$data[1]="no_avatar_120x120.JPG";
				$hhht=0;
				if((int)$data[11]!=0 && (int)$data[12]!=0)
				{
					if((int)$data[11]>=(int)$data[12])
					{
						$hhht=$data[12];
					}
					else
					{
						$hhht=$data[11];
					}				
				}
				elseif((int)$data[11]==0 && (int)$data[12]!=0)
				{
					$hhht=$data[12];
				}
				elseif((int)$data[11]!=0 && (int)$data[12]==0)
				{
					$hhht=$data[11];
				}
				if($hhht>=1 && $hhht<=3)
				{						
					$hhht=$hhht;
				}				
				else 
				{
					$hhht=0;
				}
            $mas[] = array("rating" => $data[6], "id" => $data[0], "avatar" => $data[1], "nick" => $nick, "chatstatus" => $data[5],"onlinestatus"=>$data[7],"firstonline"=>$data[8],"avaparam"=>$data[10],"awards"=>$hhht,"birthdate"=>$data[13]);
        }
		}
//order by alpha
        if ($order == 1) {
            for ($i = (count($mas) - 1); $i >= 0; $i--) {
                for ($j = 0; $j <= ($i - 1); $j++)
                    if ($mas[$j]['nick'] > $mas[$j + 1]['nick']) {
                        $tmp = $mas[$j];
                        $mas[$j] = $mas[$j + 1];
                        $mas[$j + 1] = $tmp;
                    }
            }
        }
    }
//firstfriends
    if ($friend == 1) {
        $res = mysql_query("SELECT favs FROM users WHERE id=$mid") or die(mysql_error());
        $data = mysql_fetch_row($res);
        $bannedmodels = explode("-", $data[0]);
        if (count($bannedmodels) > 0) {
            $mass = array();
            for ($i = 0; $i < count($mas); $i++) {

                for ($j = 0; $j < count($bannedmodels); $j++) {
                    if ($mas[$i]['id'] == $bannedmodels[$j]) {
                        $mass[] = $mas[$i];
                        $mas[$i] = NULL;
                    }
                }
            }
            for ($i = 0; $i < count($mas); $i++) {
                if ($mas[$i] != NULL)
                    $mass[] = $mas[$i];
            }
        }
        $mas = $mass;
    }
	
    if ($ajax)
        echo(json_encode($mas));
    else
        return $mas;
}
function camscore($perfid) {
    $res = mysql_query("SELECT performers.rating, performers.ratingchat, SUM(sessions.usedtime) AS minutes,
                                    (SELECT SUM(earnedchips)
                                    FROM sessions
                                    WHERE performer='$perfid' AND video_sess_type='tips') AS tips,
                                SUM(sessions.earnedchips) AS earned
                            FROM performers, sessions
                            WHERE performers.id='$perfid' AND sessions.performer='$perfid'")or die(mysql_error());
    if (mysql_num_rows($res) == 0) {
        return -1;
    }

    $data = mysql_fetch_assoc($res);

    $earnedtominutes = ($data['minutes'] == 0) ? 0 : $data['earned'] / $data['minutes'];
    $secondstotips = ($data['tips'] == 0) ? 0 : $data['minutes'] * 60 / $data['tips'];
    $ratingtominutes = ($data['minutes'] == 0) ? 0 : $data['rating'] / $data['minutes'];
    $earnedtorating = ($data['rating'] + $data['ratingchat']) == 0 ? 0 : $data['earned'] / ($data['rating'] + $data['ratingchat']);
    $tipstominutes = ($data['minutes'] == 0) ? 0 : $data['tips'] / $data['minutes'];

    $camscore = $data['ratingchat'] + $earnedtominutes + $secondstotips + $ratingtominutes + $earnedtorating + $tipstominutes;
    $camscore = round($camscore, 2);
    mysql_query("UPDATE performers SET camscore='$camscore' WHERE id='$perfid'");

    return $camscore;
}

function getChattersList($id, $usertype) {
// принимает ай-ди того, кто запрашивает историю и его тип (из сессии)
// возвращает массив айдишек и ников тех, с кем велась переписка
    $array = array();
    $i = 0;
    if ($usertype == 'performers') {
        $query = "SELECT users.id, users.username FROM users, chats WHERE chats.from='$id'
                        AND users.id=chats.to AND chats.deletedp='0' AND chats.fromto='0' GROUP BY chats.to";
		//echo($query);
        $res = mysql_query($query) Or Die(mysql_error());
        while ($row = mysql_fetch_assoc($res)) {
            $array[$i]['id'] = $row['id'];
            $array[$i]['username'] = $row['username'];
            $i++;
        }
    } elseif ($usertype == 'users') {

        $query = "SELECT performers.id, performers.nickname, performers.nickname1, performers.nickactive FROM performers, chats
                    WHERE (chats.from='$id' AND performers.id=chats.to AND chats.deletedm='0' AND chats.fromto='0') OR (chats.to='$id' AND performers.id=chats.from AND chats.deletedm='0' AND chats.fromto='1') GROUP BY performers.id";
        //echo($query);
        $res = mysql_query($query) Or Die(mysql_error());
        while ($row = mysql_fetch_assoc($res)) {
            $array[$i]['id'] = $row['id'];
            if ($row['nickactive'] == 0)
                $array[$i]['username'] = $row['nickname'];
            elseif ($row['nickactive'] == 1)
                $array[$i]['username'] = $row['nickname1'];
            $i++;
        }
    }

    return $array;
}

function lim_studio_menu() {
  global $lang;
    /*$res = mysql_query("SELECT `username` FROM `studios` WHERE `email`='{$_SESSION['email']}'");
    $arr = mysql_fetch_assoc($res);
    echo <<< MENU

                <a href="logout.php">Logout</a>
            <div class="menuBox">
                <ul>
                    <li><a href="manageraccount.php">Start Here</a></li>
                    <li><a>News</a></li>
                    <li><a>Rules</a></li>
                    <li><a>Information for Models</a></li>
                </ul>
            </div>
            <div class="menuBox">
                <h3>My Studio</h3>
                <ul>
                    <li><a href="managerperformersignup.php">Add A Model</a></li>
                    <li><a href="managerperformers.php">All Models</a></li>
                </ul>
            </div>
            <div class="menuBox">
                <h3>Other</h3>
                <ul>
                   <li><a>Contact Support</a></li>
                </ul>
            </div>
MENU;*/
?>
    <script language="JavaScript" type="text/javascript" src="Scripts/show-submenu.js"></script>
    <table  id="top_menu" cellpadding="0" cellspacing="0">

                            <td class="extended"><a class="nonea blacktext" href="manageraccount.php">Home page</a>
                            </td>


                            <td class="extended"><a class="nonea blacktext" href="managerperformersignup.php">Add models</a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="managerperformers.php">All models</a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="managernews.php">News</a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studiomanagerrules.php">Rules</a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="performerhints.php" style="display: block; "><?=$lang["models"]["Hints"]?></a>
                            </td>

                            <td class="extended1"><a href="studiotechsuportonline.php"><?=$lang["models"]["Tech support"]?></a>
                                <ul>
                                <li><a class="nonea " href="studiotechsuportonline.php">&nbsp;<?=$lang["models"]["Online support"]?></a></li>
                                <li><a class="nonea " href="limstudiotechsuport.php">&nbsp;<?=$lang["models"]["Message to support"]?></a></li>
                                </ul>
                            </td>

                    </table><?php

}

// ########################################################################
function top_member() {
   ?>
<div>
    <div>
        <!--<a href="#" class="online">Online</a>&nbsp;-->
        <a href="onlinemember.php" target="_blank">Online</a>&nbsp;
        <a href="#">Group Show</a>&nbsp;
        <a href="memberperformerprofile.php?id=<?= $_GET['id'] ?>">Biopage</a>&nbsp;
        <a id="tips" href="#">Tips</a>&nbsp;
        <a href="contact-performer-u.php?id=<?= $_GET['id'] ?>">Private message</a>&nbsp;
        <a href="schedule.php?id=<?= $_GET['id'] ?>">Schedule</a>&nbsp;
    </div>
    <input type="hidden" id="pid" value="<?= $_GET['id'] ?>">
    <input type="hidden" id="mid" value="<?= $_SESSION['userid'] ?>">
    <div class="tips">
        <div style="float: right;" class="close" ><a href="">close(x)</a></div>
        <div style="text-align: center;"> Leave a tip for Mullty!</div>
        <div style="margin:0 10px 10px 0;">Number of tokens to tip:
            <input type="text" name="count"></div>
        <div><div style="float: left;">Include a message (optional)&nbsp;</div>
            <div><textarea id="texttip" cols="20" rows="3"></textarea></div>
            <input type="button" value="continue">
        </div>
    </div>
    <div class="chat">
        <div>This chat is not free, your account will be charged per seconds.</div><br>
        <div>Standard show: (<a id="standard" href="#">?</a>) <?= $res['multiplechips'] ?> credits/min</div>
        <div>One-on-one show: (<a id="one-on-one" href="#">?</a>) <?= $res['privatechips'] ?> credits/min</div><br>
        <div><input type="button" value="Standart">
            <input type="button" value="One-on-one">
            <input type="button" class="close" value="close"></div>
    </div>
    <div class="standard">Use this option to jump in to action. Please note<br>
        that during this session other members can also join<br>
        the performers show.</div>
    <div class="one-on-one">Use this option to get more intimate with this<br>
        performer. During one-on-one sesion no other<br>
        members can join private nor voyeur.</div>
</div>
<div class="error1">Не коректно введено число чаевых</div>
<div class="error2">У вас не достаточно средств на счету</div>
<div class="error3">Извените на сервере произошла ошибка</div>
<div class="error4">Чаевые отправлены</div>
    <?php
}

function zapr($tab, $idP, $name) {
    $r = @mysql_query("select * from $tab WHERE id='$idP'") or die(mysql_error());
    while ($rr = mysql_fetch_assoc($r))
        return $rr[$name];
}

function count_perf($uid, $kid, $stat) {
    /* $r = mysql_query("SELECT count(performers.id) FROM performers,photos WHERE performers.idcategories='$kid'
      AND performers.onlinestatus='$stat'
      AND performers.avatar=photos.id");
      $re = @mysql_fetch_array($r); */
    return count(testSetings($uid, $stat, $kid));
}

function left_bar() {
    ?>



    <?php
    if ($_SESSION[usertype] == 'performers')
        performer_menu();
    if ($_SESSION[usertype] == 'studios')
        studio_menu();
    if ($_SESSION[usertype] == 'users')
        user_menu();
}
function user_menu()
{
  global $lang;
 ?>
    <script language="JavaScript" type="text/javascript" src="Scripts/show-submenu.js"></script>
                    <table id="top_menu"  style="cellpadding="0" cellspacing="0">

                            <td class="extended"><a href="index.php"><?=$lang["Home page"]?></a>
                            </td>

                            <td class="extended"><a href="catalog.php"><?=$lang["member"]["Search"]?></a>
                            </td>

                            <td class="extended"><a  href="messenger-up.php"><?=$lang["member"]["My messages"]?></a>
                            </td>

                            <td class="extended"><a  href="awards.php"><?=$lang["member"]["Awards"]?></a>
                            </td>

                            <td class="extended">
                            <a href="memberprofile.php"><?=$lang["member"]["My profile"]?></a>
                                <ul>
                                <li><a href="memberprofile.php">&nbsp;<?=$lang["member"]["View my profile"]?></a></li>
                                <li><a  href="memberdetails.php">&nbsp;<?=$lang["member"]["Edit my profile"]?></a></li>
                               <!-- <li><a  href="memberprofilestyle.php">&nbsp;<?//=$lang["member"]["My page style"]?></a></li>-->
                                <li><a  href="memberchangepass.php">&nbsp;<?=$lang["member"]["Change password"]?></a></li>
                                <li><a  href="memberchangeemail.php">&nbsp;<?=$lang["member"]["Change email"]?></a></li>
                                </ul>
                            </td>

                            <td class="extended"><a  href="memberpurchasedgalleries.php"><?=$lang["member"]["Photo galleries"]?></a>
                                <ul>
                                    <li><a href="memberpurchasedgalleries.php">&nbsp;<?=$lang["member"]["Purchased galleries"]?></a></li>
                                    <li><a href="memberphotogallery.php">&nbsp;<?=$lang["member"]["My galleries"]?></a></li>
                                </ul>
                            </td>

                            <td class="extended"><a href="memberprivacyctr.php"><?=$lang["member"]["Privacy options"]?></a>
                                <ul>
                                    <li><a href="memberprivacyctr.php">&nbsp;<?=$lang["member"]["Block models"]?></a></li>
                                    <li><a href="memberperformersettings.php">&nbsp;<?=$lang["member"]["Performer view settings"]?></a></li>
                                    <li><a href="membercashview.php">&nbsp;<?=$lang["member"]["My setting cash view"]?></a></li>
                                    <li><a href="memberperalerts.php">&nbsp;<?=$lang["Email Alerts"]?></a></li>
                                </ul>
                            </td>

                            <td class="extended"><a  href="history-u.php"><?=$lang["member"]["Chat history"]?></a>
                            </td>
                            <!--
                            <td class="extended"><a  href="membergroupshow.php"><?=$lang["member"]["Group show"]?></a>
                            </td>
                            -->
                            <td class="extended"><a  href="membernews.php"><?=$lang["studio"]["News"]?></a>
                            </td>

                            <td class="extended"><a href="memberrules.php"><?=$lang["studio"]["Rules"]?></a>
                            </td>

                            <td class="extended"><a  href="memberforum.php"><?=$lang["models"]["Forum"]?></a>
                            </td>

                            <td class="extended1"><a href="userfaq.php"><?=$lang["models"]["Tech support"]?></a>
                                <ul>
								    <li><a href="userfaq.php">&nbsp;<?=$lang["FAQ"]?></a></li>
								    <li><a href="usertechsuportonline.php">&nbsp;<?=$lang["models"]["Online support"]?></a></li>
                                    <li><a  href="usertechsuport.php">&nbsp;<?=$lang["models"]["Message to support"]?></a></li>
                                </ul>
                            </td>
                        <!--<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="index.php" style="display: block; width: 150px;">Logout</a></h3>
                            </td>
                        </tr>-->
                        <!--<tr>
                            <td class="extended" align="center"><button class="memberStatus btn" style="width: 165px;margin-top: -3px; display: block;"><?=$lang["member"]["Status"]?></button>&nbsp;
                            </td>
                        </tr>-->
                    </table>
<?php
}
/*
function user_menu() {

    $res = mysql_query("SELECT `chips` FROM `users` WHERE `id`='$_SESSION[userid]'");
    $arr = mysql_fetch_array($res);
    ?>
    <h3>
        <div>Your: <?= $_SESSION[usertype] ?></div>
    </h3>
    <h3><div>Name: <?= $_SESSION[first] ?></div></h3>
    <div class="user-info right-u">
        <div class="ballance">
            You have <b><?= $arr['chips'] ?></b> credits.
        </div>
        <div class="messageSats">
                            <?php
                            $count = countMsgs(0);
                            if ($count == 0)
                                echo "You have <sapn>no</span> new messages.";
                            else
                                echo "You have <a href=\"messenger-u.php\"><span><b>$count</b></span> new</a> messages.";
                            ?>
                        </div>
        <a href="memberaddcredit.php" class="addCredit">Add credit</a>
    </div>
    <br>
    <a href="logout.php">Logout</a>
    <h3>Welcome</h3>
    <div class="menuBox">
        <ul>
            <li><a href="memberpage.php?stat=on">Who's online</a></li>
            <li><a href="memberpage.php?stat=off">Who's offline</a></li>
            <li><a href="membertags.php">Tags</a></li>
            <li><a href="memberprofile.php">View My Profile</a></li>
            <li><a href="memberdetails.php">Member Details</a></li>
            <li><a href="memberphotogallery.php">PhotoGalery</a></li>
            <li><a href="memberprivacyctr.php">Piracy Options</a></li>
            <li><a href="memberperformersettings.php">Performer View Setting</a></li>
            <li><a href="memberfutoansver.php">Away Setting</a></li>
            <li><a href="memberprofilestyle.php">My page style</a></li>
            <li><a href="messenger-u.php">Messenger</a></li>
            <li><a href="memberstats.php">Stats</a></li>
            <li><a href="history-u.php">History</a></li>
            <li><a href="memberpurchasedgalleries.php">Purchased galleries</a></li>
            <li><a href="membersoundsettings.php">Sound settings</a></li>
            <li><a href="membergroupshow.php">Group show</a></li>
        </ul>
        <table>
            <tr>
                <td>
                    <div class="menuBox">
                        <div class="menuTitle">Online friends</div>
                        <ul id="onlineModels">
                            <?php
                            $result = mysql_query("SELECT `favs` FROM `users` WHERE `id`='{$_SESSION['userid']}'");
                            $rows = mysql_fetch_row($result);
                            $favs = str_replace('-', ',', $rows[0]);
                            $result = mysql_query("SELECT `id`, `nickname`, `nickname1`,`nickactive`,`onlinestatus` FROM `performers` WHERE `id` IN ($favs) ");
                            $models = array();
                            $models['online'] = array();
                            $models['offline'] = array();
                            while ($rows = mysql_fetch_assoc($result)) {
                                $nick = $rows['nickactive'] == 0 ? $rows['nickname'] : $rows['nickname1'];
                                if ($rows['onlinestatus'] == 'online')
                                    $models['online'][] = array('id' => $rows['id'], 'nick' => $nick);
                                else
                                    $models['offline'][] = array('id' => $rows['id'], 'nick' => $nick);
                            }
                            for ($i = 0; $i < count($models['online']); $i++)
                                echo "<li><a href=\"memberperformerprofile.php?id=" . $models['online'][$i]['id'] . "\">" . $models['online'][$i]['nick'] . "</a></li>";
                            ?>
                        </ul>
                    </div>
                    <div class="menu-spacer">&nbsp;</div>
                    <div class="menuBox">
                        <div class="menuTitle">Offline friends</div>
                        <ul id="offlineModels">
                            <?php
                            for ($i = 0; $i < count($models['offline']); $i++)
                                echo "<li><a href=\"memberperformerprofile.php?id=" . $models['offline'][$i]['id'] . "\">" . $models['offline'][$i]['nick'] . "</a></li>";
                            ?>
                        </ul>
                    </div>
                    <div class="menu-spacer">&nbsp;</div>
                    <div class="menuBox">
                        <div class="menuTitle">Recently visited</div>
                        <ul id="recentryVisited" style="margin-left: 25px;padding: 0;">
                            <?php
                            $last = lastmodelsview();
                            for ($i = 0; $i < count($last); $i++)
                                echo "<li><img src=\"profileimages/" . $last[$i]['avatar'] . "\" width=\"60\" alt=\"\">
                  <a href=\"memberperformerprofile.php?id=" . $last[$i]['id'] . "\">" . $last[$i]['nick'] . "</a></li>";
                            ?>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>
    </div>




    <?php
} */

function performer_menu()
{global $lang;
 ?>
                    <script language="JavaScript" type="text/javascript" src="Scripts/show-submenu.js"></script>
                    <table    cellpadding="0" cellspacing="0">
<tr>
                            <td class="extended"><a  href="index.php"><?=$lang["Home page"]?></a>
                            </td>

                            <td class="extended"><a  href="modelAccaunt.php"><?=$lang["models"]["Start here"]?></a>
                            </td>

                            <td class="extended"><a href="performernews.php"><?=$lang["studio"]["News"]?></a>
                            </td>
                            <!--
                            <td class="extended"><a href="performerphotos.php"><?=$lang["models"]["Documents"]?></a>
                            </td>

                            <td class="extended"><a href="searchuser.php"><?=$lang["models"]["Search users"]?></a></h3>
                            </td>
                                -->
                            <td class="extended"><a href="awards.php"><?=$lang["member"]["Awards"]?></a>
                            </td>

                            <td class="extended"><a href="performerreferrerprogram.php"><?=$lang["models"]["Want Earn More"]?></a>
                            </td>

                            <td class="extended1">
                           <a href="performerstatistic.php"><?=$lang["models"]["Statistic"]?></a>
                                <ul>
                                    <li><a href="performerstatistic.php">&nbsp;<?=$lang["models"]["My statistic"]?></a></li>
                                    <li><a href="performerstatisticpayments.php">&nbsp;<?=$lang["models"]["Previous payments"]?></a></li>
								<?php 
								$yt=mysql_query("SELECT studioid FROM performers WHERE id='".mysql_real_escape_string($_SESSION['userid'])."'");
								if(mysql_num_rows($yt)>0)
								{
									if(mysql_result($yt,0,0)==0)
									{
										?>
										<p class="global_p1"><a class="nonea " href="performerpaymentmethode.php" style="display: block; ">&nbsp;<?=$lang["studio"]["Payment method"]?></a></p>
										<?php 
									}
								}
								
								?>
								
                                <li><a href="performerstatisticbymember.php">&nbsp;<?=$lang["models"]["Member spends"]?></a></li>
								<li><a href="performermymembers.php">&nbsp;<?=$lang["models"]["My members"]?></a></li>
                                    <!--
								<li><a href="performerreferrerprogram.php">&nbsp;<?=$lang["models"]["Referrer programs"]?></a></li>
                                        -->
                                </ul>
                            </td>
                        <!--<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="performerstatistic.php">My statistic</a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="performerstatisticpayments.php">Previous payments</a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="performerstatisticbymember.php">Member spends</a></h3>
                            </td>
                        </tr>-->
                        <!--<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="performerfriends.php">Friends</a></h3>
                            </td>
                        </tr>-->

                            <td class="extended"><a  href="messenger-up.php" style="display: block; "><?=$lang["models"]["My messages"]?></a>
                            </td>

                            <td class="extended1">
                                <a href="performerprofile.php"><?=$lang["models"]["My profile"]?></a>
                                <ul>
                                    <li><a href="performerprofile.php">&nbsp;<?=$lang["models"]["View my profile"]?></a></li>
                                    <li><a href="performerdetails.php">&nbsp;<?=$lang["models"]["Edit my profile"]?></a></li>
                                    <li><a href="performerdetails1.php">&nbsp;<?=$lang["models"]["Edit public details"]?></a></li>
                                    <li><a href="performertags.php">&nbsp;<?=$lang["models"]["My tags"]?></a></li>
                                    <li><a href="performerdesires.php">&nbsp;<?=$lang["models"]["Wish list"]?></a></li>
                                    <li><a href="performerchangeemail.php">&nbsp;<?=$lang["models"]["Change email"]?></a></li>
                                    <li><a href="performerchangepass.php">&nbsp;<?=$lang["models"]["Change password"]?></a></li>
                                    <li><a href="performerprivacyctrl.php"><?=$lang["models"]["Privacy options"]?></a></li>
                                </ul>
                            </td>

                            <td class="extended"><a  href="performerphotogallery.php"><?=$lang["models"]["Manage photo"]?></a>
                            </td>

                            <td class="extended"><a  href="performervideogallery.php"><?=$lang["models"]["Manage video"]?></a>
                            </td>

                            <td class="extended"><a href="performerschedule.php" ><?=$lang["models"]["My schedule"]?></a>
                            </td>

                            <td class="extended"><a href="history-perf.php"><?=$lang["models"]["My chat history"]?></a>
                            </td>
                            <!--
                            <td class="extended1"><a href="performerprofilestyle.php"><?=$lang["models"]["Settings"]?></a>
                                <ul>
                                    <li><a href="performerprofilestyle.php"><?=$lang["models"]["My page style"]?></a></li>

                                    <li><a  href="wall.php"><?=$lang["models"]["My wall"]?></a></li>
                                </ul>
                            </td>
                                -->
                            <td class="extended"><a href="performerrules.php"><?=$lang["studio"]["Rules"]?></a>
                            </td>
                            <!--
                            <td class="extended"><a  href="performerhints.php" ><?=$lang["models"]["Hints"]?></a>
                            </td>
                                -->
                            <td class="extended"><a href="performerforum.php"><?=$lang["models"]["Forum"]?></a>
                            </td>

                            <td class="extended1"><a href="performerfaq.php"><?=$lang["models"]["Tech support"]?></a>
                                <ul>
								<li><a href="performerfaq.php">&nbsp;<?=$lang["FAQ"]?></a></li>
								<li><a href="performertechsuportonline.php">&nbsp;<?=$lang["models"]["Online support"]?></a></li>
                                <li><a href="performertechsuport.php">&nbsp;<?=$lang["models"]["Message to support"]?></a></li>
                                </ul>
                            </td>
</tr>
                        <!--<tr><br>
                            <td class="extended"><h3><a class="nonea blacktext" href="index.php">Logout</a></h3>
                            </td>
                        </tr>-->

                    </table>
<?php
}
/*function performer_menu() {
    ?>
    <h3>
        <div>Your: <?= $_SESSION[usertype] ?></div>
    </h3>
    <h3><div>Name: <?= $_SESSION[nickname] ?></div></h3>
    <a href="logout.php">Logout</a>
    <h3>Welcome</h3>
    <div class="menuBox">
        <ul>
            <li><a href="modelAccaunt.php">Start Here</a></li>
            <li><a href="performerphotos.php">Documents</a></li>
            <li><a href="searchuser.php">Search Users</a></li>
            <li><a href="messenger.php">Messenger</a></li>


        </ul>
    </div>
    <h3>My Profile</h3>

    <div class="menuBox">
        <ul>
            <li><a href="performerprofile.php?id=<?= $_SESSION[userid] ?>">View My Profile</a></li>
            <li><a href="performerdetails.php">Edit Personal Details</a></li>
            <li><a href="performerdetails1.php">Edit Public Details</a></li>
            <li><a href="performertags.php">My Model Tags</a></li>
            <li><a href="performerdesires.php">My Desires</a></li>
            <li><a href="performerphotogallery.php">Manage Photos</a></li>
            <li><a href="performervideogallery.php">Manage Video</a></li>
            <li><a href="performerschedule.php">My Schedule</a></li>
            <li><a href="performerstatistic.php">My Stats</a></li>
            <li><a href="history-perf.php">My History</a></li>
        </ul>
    </div>
    <h3>Settings</h3>
    <div class="menuBox">
        <ul>
            <li><a href="performerprofilestyle.php">My page style</a></li>
            <li><a href="performersetings.php">Profile Settings</a></li>
            <li><a href="performerprivacyctrl.php">Privacy Options</a></li>
            <li><a href="wall.php">Wall</a></li>
        </ul>

    </div>
    <?php
}
  */
function phpFriendlistm(){
//print_r($_COOKIE);
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) & $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
{
    global $lang;
    if (isset($_POST['delmess'])) {
    $k=mysql_real_escape_string($_POST['msg']);
    $k1=mysql_real_escape_string($_POST['msg1']);
    $t="";
    if(strlen($k)>0 && preg_match('/([^0-9,]+)/',$k)==0)
    { 	//echo("UPDATE `messages` SET `statusfrom`=1 WHERE `from` = '".mysql_real_escape_string($_SESSION['userid'])."' AND `id` IN(".mysql_real_escape_string($k).")");
        mysql_query("UPDATE `messages` SET `statusto`=1 WHERE `to` = '".mysql_real_escape_string($_SESSION['userid'])."' AND `id` IN(".mysql_real_escape_string($k).")");
      $t.="ok";
    }
    //else
    //{$t.=strlen($k)."-".preg_match('/([^0-9,]+)/',$k);}
    if(strlen($k1)>0 && preg_match('/([^0-9,]+)/',$k1)==0)
    {
      //echo("UPDATE `messages` SET `statusto`=1 WHERE `to` = '".mysql_real_escape_string($_SESSION['userid'])."' AND `id` IN(".mysql_real_escape_string($k1).")");
      mysql_query("UPDATE `messages` SET `statusfrom`=1 WHERE `from` = '".mysql_real_escape_string($_SESSION['userid'])."' AND `id` IN(".mysql_real_escape_string($k1).")");
      $t.="1ok";
    }
    //else{$t.=strlen($k1)."-".preg_match('/([^0-9,]+)/',$k1);}
    echo($t);
    }
	//echo("<br>".$_SERVER['PHP_SELF']."<br>");
	if($_SERVER['PHP_SELF']=='/catalog.php')
	{
		 $_SESSION['performeruserid']=0+$_POST["pideq"];
	}
   else $_SESSION['memberid']=0+$_POST["pideq"];

   if($_SESSION['usertype']==AUTH_USER_TYPE_MODEL && $_POST['msgsend'])
    {
     $subject = safe_string($_POST['sub']);
     $message = safe_string($_POST['messages']);
     $id = safe_string($_POST['msgsend']);
	if(strlen($message)>0 && strlen($id)>0){
            $query = mysql_query("SELECT `id` FROM authorize WHERE id = '$id'")or die(mysql_error());
            //echo(mysql_num_rows($query));
            if (mysql_num_rows($query) == 0) {
                $no = false;
                $res= $lang["Sorry but this user name does not exist"];
                }
            else {
                $now = date("U");
                $res= $lang["models"]["Message successfuly sent!"];
                $message = str_replace($censure, '*', $message);
                $subject = str_replace($censure, '*', $subject);
                mysql_query("INSERT INTO `messages` (`from`, `to`, `date`, `subject`, `content`, `read`, `totype`) VALUES ('".mysql_real_escape_string($_SESSION['userid'])."', '$id', '$now', '$subject', '$message', 'no', 'users')");
                //exit();
		}
		//echo($res);
	}
	else $res="Bad length text!";
        echo $res;
    }
	//print_r($_SESSION);
	//print_r($_POST);
	/*

	*/
	if($_SESSION['usertype']==AUTH_USER_TYPE_MODEL && isset($_POST['chkrd']))
    {
	$t=date("t",time());
    $d=date("d",time());
    $m=date("m",time());
    $y=date("Y",time());
    if($d<16){$d1=01;$d2=15;}
    else{
        $d1=16;$d2=$t;
    }
    $da1=mktime(0,0,0,$m,$d1,$y);
    $da2=mktime(23,59,59,$m,$d2,$y);
    $yt="SELECT SUM(sessions.earnedchips) as chips FROM sessions WHERE sessions.performer = '$_SESSION[userid]' AND sessions.date BETWEEN $da1 AND $da2 AND sessions.video_sess_type!='public' ";
    $tt=mysql_query("SELECT studioid FROM performers WHERE id= '$_SESSION[userid]'");
    if(mysql_num_rows($tt)>0)
    {
        if(mysql_result($tt,0,0)>0)
        $yt="SELECT (SUM(sessions.earnedchips)*((100-studios.percentage)/100))as chips FROM sessions, studios,performers WHERE sessions.performer = performers.id AND performers.id='$_SESSION[userid]' AND performers.studioid=studios.id AND sessions.date BETWEEN $da1 AND $da2 AND sessions.video_sess_type!='public'";
    }
	$q=mysql_query($yt);
    $r=mysql_fetch_row($q);
    $bpc = getconf("buckperchip");
	$r[0]=sprintf("%.2f",$r[0]);
    $r[0]=number_format(($r[0]*$bpc),2);
	echo ($r[0]);
	}
	if($_SESSION['usertype']==AUTH_USER_TYPE_MODEL && isset($_POST['ftype'])&& isset($_POST['ftid']) && isset($_POST['fcid']) && isset($_POST['ffrom']) && isset($_POST['fsub']) && isset($_POST['ftext']))
    {
		 $subject = safe_string($_POST['fsub']);
		 $message = safe_string($_POST['ftext']);
		 $from = safe_string($_POST['ffrom']);
		 $ftype = safe_string($_POST['ftype']);
		 $ftid = safe_string($_POST['ftid']);
		 $fcid = safe_string($_POST['fcid']);
        //write_log($message);
        $message=str_replace("\\n", "<br>", $message);
		 //echo(strlen($subject)."-".$subject."-".$message."-".$from."-".$ftype."-".$ftid."-".$fcid."-");
		 $erft=array("");
		if(strlen($message)==0) $erft[0].=$lang["forum"]["Bad length text"]."!\n";
		if(strlen($subject)==0 && $ftid==0) $erft[0].=$lang["forum"]["Bad length Subject"]."!\n";
		if(strlen($from)==0) $erft[0].=$lang["forum"]["Bad length From"]."!";
		if(strlen($erft[0])==0)
		{
			if($ftid==0){
				$now = date("U");
				//echo("INSERT INTO `forum_thema`(`name`, `date`,`idmember`,`delete`,`closed`,`from`,`subject`,`idtype`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,1,'$from','$subject') ");
				$query = mysql_query("INSERT INTO `forum_thema`(`name`, `date`,`idmember`,`delete`,`closed`,`from`,`subject`,`idtype`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,1,'$from','$subject','$ftype') ")or die(mysql_error());
				$res[0]= $lang["forum"]["Your theme waiting for approval!"];
				$res[1]=array("type"=>"thema","id"=>mysql_insert_id());
			}
			else {
				$now = date("U");
				//echo("INSERT INTO `forum_messages`(`text`, `date`,`id_member`,`delete`,`closed`,`from`,`thema`,`nesting`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,1,'$from','$ftid','$fcid') ");
				$query = mysql_query("INSERT INTO `forum_messages`(`text`, `date`,`id_members`,`deleted`,`closed`,`from`,`thema`,`nesting`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,1,'$from','$ftid','$fcid') ")or die(mysql_error());
				$res[0]= $lang["forum"]["Your message waiting for approval!"];
				$res[1]=array("type"=>"comment","id"=>mysql_insert_id());
			}
			echo json_encode($res);
		}
		else echo json_encode($erft);
    }
	if($_SESSION['usertype']==AUTH_USER_TYPE_MODEL && isset($_POST['ftid']) && isset($_POST['gc']) && $_POST['gc']==1)
    {
		 $ftid = safe_string($_POST['ftid']);
		 //echo($ftid);
		 $a=array();
		if($ftid>0)
		{

				$query = mysql_query("SELECT `id`,`date`,`text`,`nesting`,`from` FROM `forum_messages` WHERE `deleted`=0 AND `closed`=0 AND thema=".$ftid)or die(mysql_error());
				if(mysql_num_rows($query)>0)
				{
					while($ff=mysql_fetch_assoc($query))
					{
						$a[]=array("id"=>$ff['id'],"date"=>date("m.d.Y H:i",$ff['date']),"text"=>$ff['text'],"from"=>$ff['from'],"nesting"=>$ff['nesting']);
					}
				}
		}
		echo json_encode($a);
    }
	//else echo("dawa");
    if($_SESSION['usertype']==AUTH_USER_TYPE_MODEL && $_POST['search'])
    {

	$ter=reg_test_factory($_POST['search'], REG_TEST_STRIP_TAGS);
	//pront_r($ter);

    $all=array();
        //echo("SELECT memberid FROM banned_members WHERE bannedmodels REGEXP '^".mysql_real_escape_string($_SESSION['userid'])."$|-".mysql_real_escape_string($_SESSION['userid'])."$|-".mysql_real_escape_string($_SESSION['userid'])."-|^".mysql_real_escape_string($_SESSION['userid'])."-'");
        $fr1=mysql_query("SELECT memberid FROM banned_members WHERE bannedmodels REGEXP '^".mysql_real_escape_string($_SESSION['userid'])."$|-".mysql_real_escape_string($_SESSION['userid'])."$|-".mysql_real_escape_string($_SESSION['userid'])."-|^".mysql_real_escape_string($_SESSION['userid'])."-'") or die(mysql_error());
        $gty="";
        if(mysql_num_rows($fr1)>0)
        {
            $gty="authorize.id NOT IN(";
            $gh="";
            while($vfr=mysql_fetch_array($fr1))
            {
                $gh.=",".$vfr["memberid"];
            }
            $ptn = "/(,{2,})/";
            $gh=preg_replace($ptn, ",", $gh);
            $ptn1 = "/^,|,$/";
            $gh=preg_replace($ptn1, "", $gh);
            //echo($gh."<br/>");
            $hg=  mysql_query("SELECT favs, my_request FROM performers WHERE id=".mysql_real_escape_string($_SESSION['userid'])) or die(mysql_error());
            if(mysql_num_rows($hg)>0)
            {
                while($vfr1=mysql_fetch_array($hg))
                {
                    $gh.=",".$vfr1["favs"].",".$vfr1["my_request"];
                }
                //echo($gh);
            }
            $ptn12 = "/(-{1,})/";
            $gh=preg_replace($ptn12, ",", $gh);
            $ptn = "/(,{2,})/";
            $gh=preg_replace($ptn, ",", $gh);
            $ptn1 = "/^,|,$/";
            $gh=preg_replace($ptn1, "", $gh);
            $gty.=$gh.") AND ";
        }
        //echo("SELECT authorize.login,users.id,photomember.name FROM users,photomember,authorize WHERE $gty authorize.login LIKE '%".$ter."%' AND authorize.status!=2 AND users.id=authorize.id AND users.avatar=photomember.id ORDER BY authorize.login");
        //$qt="SELECT authorize.login,users.id,photomember.name FROM users,photomember,authorize WHERE $gty authorize.login LIKE '%".$ter."%' AND authorize.status!=2 AND users.id=authorize.id AND users.avatar=photomember.id ORDER BY authorize.login";

        $fr=mysql_query("SELECT authorize.login,users.id,photomember.name FROM users,photomember,authorize WHERE $gty authorize.login LIKE '%".$ter."%' AND authorize.status!=2 AND users.id=authorize.id AND users.avatar=photomember.id ORDER BY authorize.login") or die(mysql_error());



        if(mysql_num_rows($fr)>0)
        {
            while($tt=mysql_fetch_array($fr))
            {
                $all[$tt["login"]]=$tt;
            }
        }
        //print_r($all);
        //natcasesort($all);
        //print_r($all);

        echo(json_encode($all));
    }
	if($_SESSION['usertype']==AUTH_USER_TYPE_MODEL && isset($_POST['flh']) && isset($_POST['top']) && isset($_POST['left']))
    {
     setcookie("pfavll", $_POST['left'], time() + 86400, "/");
	 setcookie("pfavlt", $_POST['top'], time() + 86400, "/");
	 setcookie("pfavls", $_POST['flh'], time() + 86400, "/");
    }
   if($_SESSION['usertype']==AUTH_USER_TYPE_MODEL && $_POST['msg'])
    {

     $_SESSION['memberid']=0+$_POST["msg"];
	 //print_r($_SESSION);
	 //exit();
    }
    elseif($_SESSION['usertype']==AUTH_USER_TYPE_MODEL)
    {
        $_SESSION['memberid']=0+$_POST["pideq"];
    }
	if (isset($_POST['vae1']) && $_POST['vae1']==1) {
      $_SESSION["vae1"]=1;
      }
    if (isset($_POST['vae1'])&& $_POST['vae1']==0) {
      $_SESSION["vae1"]=0;
      }
    exit();
}}
function phpFriendlist(){
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) & $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
{
    global $lang;
    if (isset($_POST['delmess'])) {
    $k=  safe_string($_POST['msg']);
    $k1=safe_string($_POST['msg1']);
    $rt=explode(",",$k1);
	$rt=array_map(function($f){return((int) $f);},$rt);
	$k1=implode(",",$rt);
	$rt1=explode(",",$k);
	$rt1=array_map(function($f){return((int) $f);},$rt1);
	$k=implode(",",$rt1);
    $t="";
    if(strlen($k)>0 && preg_match('/([^0-9,]+)/',$k)==0)
    {
      mysql_query("UPDATE `messages` SET `statusfrom`=1 WHERE `from` = '".mysql_real_escape_string($_SESSION['userid'])."' AND `id` IN($k)");
      $t.="ok";
    }
    //else
    //{$t.=strlen($k)."-".preg_match('/([^0-9,]+)/',$k);}
    if(strlen($k1)>0 && preg_match('/([^0-9,]+)/',$k1)==0)
    {
      mysql_query("UPDATE `messages` SET `statusto`=1 WHERE `to` = '".mysql_real_escape_string($_SESSION['userid'])."' AND `id` IN($k1)");
      $t.="1ok";
    }
    //else{$t.=strlen($k1)."-".preg_match('/([^0-9,]+)/',$k1);}
    echo($t);
    }
   $_SESSION['performeruserid']=0+$_POST["pideq"];
    if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER  && isset($_POST['pideq']) && isset($_POST['catv']))
	{				
				$ght=mysql_query("SELECT onlinestatus FROM performers WHERE id='".mysql_real_escape_string($_POST['pideq'])."'");
				if(mysql_num_rows($ght)>0){
					echo(mysql_result($ght,0,0));
				}	
	}
   if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER && $_POST['pid'])
   {
	$_SESSION['performeruserid']=0+$_POST["pid"];
   }
   if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER && $_POST['pideq'] && $_POST['vvide'])
   {
	$_SESSION['performeruserid']=0+$_POST["pideq"];
	$ght=mysql_query("SELECT id, name, data, cost, views FROM videos WHERE ownerid=".mysql_real_escape_string($_SESSION['performeruserid']." AND cost>0 AND deleted=0 AND hidden=1 ORDER BY data ASC"));
	if(mysql_num_rows($ght)>0){
		$m=array();
		while($rt=mysql_fetch_array($ght))
		{
			$m[]=array($rt[0],$rt[1],floor($rt[2]/1000),$rt[3],$rt[4]);			
		}
		echo(json_encode($m));
	}
	else echo(json_encode(array("Error")));
   }   
   if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER && $_POST['msgsend'])
    {
     $subject = safe_string($_POST['sub']);
     $message = safe_string($_POST['messages']);
     $id = safe_string($_POST['msgsend']);
	if(strlen($message)>0 && strlen($id)>0){
            $query = mysql_query("SELECT authorize.`id` FROM authorize, performers WHERE authorize.id = '$id' AND authorize.id=performers.id AND performers.deleted=0")or die(mysql_error());
            //echo(mysql_num_rows($query));
            if (mysql_num_rows($query) == 0) {
                $no = false;
                $res= $lang["Sorry but this user name does not exist"];
                }
            else {
                $now = date("U");
                $res= $lang["models"]["Message successfuly sent!"];
                $message = str_replace($censure, '*', $message);
                $subject = str_replace($censure, '*', $subject);
                mysql_query("INSERT INTO `messages` (`from`, `to`, `date`, `subject`, `content`, `read`, `totype`) VALUES ('".mysql_real_escape_string($_SESSION['userid'])."', '$id', '$now', '$subject', '$message', 'no', 'performers')");
                //exit();
		}
		//echo($res);
	}
	else $res="Bad length text!";
        echo $res;
    }
	if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER && isset($_POST['ftype'])&& isset($_POST['ftid']) && isset($_POST['fcid']) && isset($_POST['ffrom']) && isset($_POST['fsub']) && isset($_POST['ftext']))
    {
		 $subject = safe_string($_POST['fsub']);
		 $message = safe_string($_POST['ftext']);
		 $from = safe_string($_POST['ffrom']);
		 $ftype = safe_string($_POST['ftype']);
		 $ftid = safe_string($_POST['ftid']);
		 $fcid = safe_string($_POST['fcid']);
        $message=str_replace("\\n", "<br>", $message);
		 //echo(strlen($subject)."-".$subject."-".$message."-".$from."-".$ftype."-".$ftid."-".$fcid."-");
		 $erft=array("");
		if(strlen($message)==0) $erft[0].=$lang["forum"]["Bad length text"]."!\n";
		if(strlen($subject)==0 && $ftid==0) $erft[0].=$lang["forum"]["Bad length Subject"]."!\n";
		if(strlen($from)==0) $erft[0].=$lang["forum"]["Bad length From"]."!";
		if(strlen($erft[0])==0)
		{
			if($ftid==0){
				$now = date("U");
				//echo("INSERT INTO `forum_thema`(`name`, `date`,`idmember`,`delete`,`closed`,`from`,`subject`,`idtype`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,1,'$from','$subject') ");
				$query = mysql_query("INSERT INTO `forum_thema`(`name`, `date`,`idmember`,`delete`,`closed`,`from`,`subject`,`idtype`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,1,'$from','$subject','$ftype') ")or die(mysql_error());
				$res[0]= $lang["forum"]["Your theme waiting for approval!"];
				$res[1]=array("type"=>"thema","id"=>mysql_insert_id());
			}
			else {
				$now = date("U");
				//echo("INSERT INTO `forum_messages`(`text`, `date`,`id_member`,`delete`,`closed`,`from`,`thema`,`nesting`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,1,'$from','$ftid','$fcid') ");
				$query = mysql_query("INSERT INTO `forum_messages`(`text`, `date`,`id_members`,`deleted`,`closed`,`from`,`thema`,`nesting`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,1,'$from','$ftid','$fcid') ")or die(mysql_error());
				$res[0]= $lang["forum"]["Your message waiting for approval!"];
				$res[1]=array("type"=>"thema","id"=>mysql_insert_id());
			}
			echo json_encode($res);
		}
		else echo json_encode($erft);
    }
	if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER && isset($_POST['ftid']) && isset($_POST['gc']) && $_POST['gc']==1)
    {
		 $ftid = safe_string($_POST['ftid']);
		 //echo($ftid);
		 $a=array();
		if($ftid>0)
		{

				$query = mysql_query("SELECT `id`,`date`,`text`,`nesting`,`from` FROM `forum_messages` WHERE `deleted`=0 AND `closed`=0 AND thema=".$ftid)or die(mysql_error());
				if(mysql_num_rows($query)>0)
				{
					while($ff=mysql_fetch_assoc($query))
					{
						$a[]=array("id"=>$ff['id'],"date"=>date("m.d.Y H:i",$ff['date']),"text"=>$ff['text'],"from"=>$ff['from'],"nesting"=>$ff['nesting']);
					}
				}
		}
		echo json_encode($a);
    }
    if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER && $_POST['search'])
    {
	$ter=reg_test_factory($_POST['search'], REG_TEST_STRIP_TAGS);
        $all=array();
        //echo("SELECT performers.nickname,performers.id,photos.name FROM performers,photos,authorize WHERE performers.nickname LIKE '%".$ter."%' AND performers.nickactive=0 AND authorize.status!=2 AND performers.id=authorize.id AND performers.avatar=photos.id");
        $fr1=mysql_query("SELECT bannedmodels FROM banned_members WHERE memberid=".mysql_real_escape_string($_SESSION['userid'])) or die(mysql_error());
        $gty="";
        if(mysql_num_rows($fr1)>0)
        {
            $gty="authorize.id NOT IN(";
            $gh=  mysql_result($fr1, 0,0);
            $ptn12 = "/(-{1,})/";
            $gh=preg_replace($ptn12, ",", $gh);
            $ptn = "/(,{2,})/";
            $gh=preg_replace($ptn, ",", $gh);
            $ptn1 = "/^,|,$/";
            $gh=preg_replace($ptn1, "", $gh);
            //echo($gh."<br/>");
            $hg=  mysql_query("SELECT favs, request_favs FROM users WHERE id=".mysql_real_escape_string($_SESSION['userid'])) or die(mysql_error());
            if(mysql_num_rows($hg)>0)
            {
                while($vfr1=mysql_fetch_array($hg))
                {
                    $gh.=",".$vfr1["favs"].",".$vfr1["my_request"];
                }
                //echo($gh);
            }
            $ptn12 = "/(-{1,})/";
            $gh=preg_replace($ptn12, ",", $gh);
            $ptn = "/(,{2,})/";
            $gh=preg_replace($ptn, ",", $gh);
            $ptn1 = "/^,|,$/";
            $gh=preg_replace($ptn1, "", $gh);
            $gty.=$gh.") AND ";
        }
		include_once "inc.php";
		$gi = geoip_open("./Scripts/GeoIP.dat", GEOIP_STANDARD);
		$ips = explode(',',getRealIpAddr());
		if (count($ips)>1){
			$thiscntry=array();
			for($i=0;$i<count($ips);$i++){		
				$thiscntry[] = geoip_country_code_by_addr($gi, $ips[$i]);
			}
			$thiscntry=implode("','",$thiscntry);
		}else{
		$ip = getRealIpAddr();
		$thiscntry = geoip_country_code_by_addr($gi, $ip);
		}		
		$htyop="AND ((SELECT country_code FROM countries WHERE id=performers.country) NOT IN('$thiscntry') ) ";
		
		
        //echo("SELECT performers.nickname,performers.id,photos.name FROM performers,photos,authorize WHERE $gty performers.nickname LIKE '%".$ter."%' AND performers.nickactive=0 AND authorize.status!=2 AND performers.id=authorize.id AND performers.avatar=photos.id ORDER BY performers.nickname");
//$qt="SELECT performers.nickname,performers.id,photos.name FROM performers,photos,authorize WHERE $gty performers.nickname LIKE '%".$ter."%' AND performers.nickactive=0 AND authorize.status!=2 AND performers.id=authorize.id AND performers.avatar=photos.id ORDER BY performers.nickname";
        $fr=mysql_query("SELECT performers.nickname,performers.id,photos.name FROM performers,photos,authorize WHERE $gty performers.nickname LIKE '%".$ter."%' AND performers.deleted=0 $htyop AND performers.nickactive=0 AND authorize.status!=2 AND performers.id=authorize.id AND performers.avatar=photos.id ORDER BY performers.nickname") or die(mysql_error());


        if(mysql_num_rows($fr)>0)
        {
            while($tt=mysql_fetch_array($fr))
            {
                $all[$tt["nickname"]]=$tt;
            }
        }
        //echo("SELECT performers.nickname1,performers.id,photos.name FROM performers,photos,authorize WHERE $gty performers.nickname1 LIKE '%".$ter."%' AND performers.nickactive=1 AND authorize.status!=2 AND performers.id=authorize.id AND performers.avatar=photos.id ORDER BY performers.nickname1");
        $fr=mysql_query("SELECT performers.nickname1,performers.id,photos.name FROM performers,photos,authorize WHERE $gty performers.nickname1 LIKE '%".$ter."%' AND performers.deleted=0 $htyop AND performers.nickactive=1 AND authorize.status!=2 AND performers.id=authorize.id AND performers.avatar=photos.id ORDER BY performers.nickname1") or die(mysql_error());



        if(mysql_num_rows($fr)>0)
        {
            while($tt=mysql_fetch_array($fr))
            {
                $all[$tt["nickname1"]]=$tt;
            }
        }
        //print_r($all);
        //natcasesort($all);
        //print_r($all);
        echo(json_encode($all));
    }
	if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER && isset($_POST['flh']) && isset($_POST['top']) && isset($_POST['left']))
    {
     setcookie("favll", $_POST['left'], time() + 86400, "/");
	 setcookie("favlt", $_POST['top'], time() + 86400, "/");
	 setcookie("favls", $_POST['flh'] , time() + 86400, "/");
    }
   if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER && $_POST['msg'])
    {

     $_SESSION['performeruserid']=0+$_POST["msg"];
	 //print_r($_SESSION);
	 //exit();
    }
    elseif($_SESSION['usertype']==AUTH_USER_TYPE_MODEL)
    {
        $_SESSION['memberid']=0+$_POST["pideq"];
    }
	exit();
}}
function jsFriendlistuser()
{
global $lang;
echo("jQuery(\".but_logout>a\").live(\"click\",function(){
                if(confirm(\"Do You really want to log out? If yes, click OK\")){
					   if(jQuery.cookie('is_signin')!==undefined){
						    console.log('logout');
            				jQuery.removeCookie('is_signin');
            		   }
					   return true;
				}
                else return false;
                return false;
              });");
//echo("/*");
//print_r($_COOKIE);
//echo("*/");
?>
<?php
if(isset($_COOKIE['favls']) && $_COOKIE['favls']=="false") {echo("jQuery(\".favlist-wrap\").hide();"); }
//else echo("jQuery(\".favlist-wrap\").offset({top:\"".$_COOKIE['favlt']."\",left:\"".$_COOKIE['favll']."\"});");
 ?>
 
jQuery.getScript('http://<?=$_SERVER["HTTP_HOST"]?>:8080/socket.io/socket.io.js', function(){
		//jQuery(".favlist-wrap").draggable({containment: "document", handle:'.favlist-title'});
		jQuery('.favlist-scroll-wrap').tinyscrollbar();
		jQuery('.search-wrap').tinyscrollbar();
		jQuery('ul.users-list li div.favlist-button, .search-user, .closefav, .tip-close, .close-search').tipsy({gravity: jQuery.fn.tipsy.autoNS, fade: true});
			function entryModel(id,username,avatar,request){				
				if(avatar.indexOf("thumb")==-1)
				{					
					avatar=avatar.replace(".jpg","-thumb.jpg");
				}					
				return '<li data-id="'+id+'">\
							<div class="item hover">\
								<img src="profileimages/'+avatar+'" height="44" width="49">\
								<div class="nickname">'+username+'</div>\
								<div class="show-panel"></div>\
								<div class="clear"></div>\
								<div class="panel">'+(request?
								'<div class="favlist-button recycle reqdelete" original-title="<?=$lang["tooltip"]["Remove from friend list"]?>" data-id="'+id+'"></div>\
								<div class="favlist-button block" original-title="<?=$lang["tooltip"]["Blocked model"]?>" data-id="'+id+'"></div>\
								<div class="favlist-button room" original-title="<?=$lang["tooltip"]["Go to "]?> '+username+' <?=$lang["tooltip"]["chat"]?>" data-id="'+id+'"></div>\
								<div class="favlist-button page" original-title="<?=$lang["tooltip"]["Go to "]?> '+username+' <?=$lang["tooltip"]["profile"]?>" data-id="'+id+'"></div>\
								<div class="favlist-button addreq" original-title="<?=$lang["tooltip"]["Add to friend list"]?>" data-id="'+id+'"></div>':
								'<div class="favlist-button recycle frdelete" original-title="<?=$lang["tooltip"]["Remove from friend list"]?>" data-id="'+id+'"></div>\
								<div class="favlist-button tip" original-title="<?=$lang["tooltip"]["Leave a tip offline"]?>" data-id="'+id+'"></div>\
								<div class="favlist-button message" original-title="<?=$lang["tooltip"]["Send message"]?>" data-id="'+id+'"></div>\
								<div class="favlist-button hide" original-title="<?=$lang["tooltip"]["Appear Offline to"]?> '+username+'" data-id="'+id+'"></div>\
								<div class="favlist-button room" original-title="<?=$lang["tooltip"]["Go to "]?> '+username+' <?=$lang["tooltip"]["chat"]?>" data-id="'+id+'"></div>\
								<div class="favlist-button page" original-title="<?=$lang["tooltip"]["Go to "]?> '+username+' <?=$lang["tooltip"]["profile"]?>" data-id="'+id+'"></div>')+
								'</div>\
							</div>\
						</li>';
			}

           jQuery('#show_fl').toggle(function(){
            jQuery('#fl-wrap').animate({left: "0px"}, 200);
           }, function(){
               jQuery('#fl-wrap').animate({left: "-215px"}, 200);
           });
			function addModelToList(list){
				for(var i in list)
					if (list[i]['videostatus']==='online')
						jQuery('.online-wrap .users-list').append(entryModel(list[i]["id"],list[i]["username"], list[i]["name"],false));
					else
						jQuery('.offline-wrap .users-list').append(entryModel(list[i]["id"],list[i]["username"], list[i]["name"], false));
				jQuery('ul.users-list li div.favlist-button, .search-user, .closefav, .tip-close, .close-search').tipsy({gravity: jQuery.fn.tipsy.autoNS, fade: true});
				jQuery('.favlist-scroll-wrap').tinyscrollbar();
			}
            var isConnected=false;
             setTimeout(function(){
                 if(!isConnected)
                   jQuery(".favlist-wrap").remove();
              },10000);
              		console.log("[socket.io]");
                    socket = io.connect('http://<?=$_SERVER["HTTP_HOST"]?>:8080',{'try multiple transports':true});
                jQuery(".linkonpages").live("click", function(){
                var p=jQuery(this).parent().parent().attr("uid");
                jQuery.post("<?= $_SERVER["PHP_SELF"]?>",{'pideq':p},function(){
                window.location.href="<?php if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER) echo("viewperformerpage.php"); elseif($_SESSION['usertype']==AUTH_USER_TYPE_MODEL) echo("performermemberprofile.php"); ?>";
                });
                });
                socket.on("connect",function(){
                  isConnected=true;
                  console.log("[socket.io]","connected!")
				  <?php
					//echo("/*");
					//print_r($_SESSION);
					//print_r($_SERVER);
					//echo("*/");
				  if($_SERVER["PHP_SELF"]=="/catalog.php" && isset($_SESSION["activations"]) && $_SESSION["activations"]=="erttre")
					{
						echo("socket.emit('add_members');");
						unset($_SESSION["activations"]);
					}				
				?>
                socket.emit("user_online");
				<?php 
					if(($_SERVER["PHP_SELF"]=="/memberphotogallery.php" || $_SERVER["PHP_SELF"]=="/memberphotogallery.php")&& (isset($_POST['submitphoto']) && !$error))
					{
					echo("socket.emit('dcontent');");
					}				
				?>
                socket.on("get_favs",function(arr)
                {
                    jQuery('.online-wrap .users-list').empty()
					jQuery('.offline-wrap .users-list').empty()
					addModelToList(arr);
                });
                socket.on("get_favsr",function(arr)
                {
					jQuery('.request-wrap .users-list').empty();
					for(var i in arr){
                        jQuery('.request-wrap .users-list').append(entryModel(arr[i]["id"],arr[i]["username"], arr[i]["name"],true));
                    }
					jQuery('ul.users-list li div.favlist-button, .search-user, .closefav, .tip-close, .close-search').tipsy({gravity: jQuery.fn.tipsy.autoNS, fade: true});
					jQuery('.favlist-scroll-wrap').tinyscrollbar();
                });

                socket.on("friend_online",function(arr)
                {
                    jQuery("div.favlist-wrap>div.favlist-scroll-wrap>div.viewport>div.overview>div#favlist-users-wrap>div.online-wrap>ul.users-list").prepend(jQuery("li[data-id='"+arr+"']"));
                });
                socket.on("friend_offline",function(arr)
                {
					jQuery("div.favlist-wrap>div.favlist-scroll-wrap>div.viewport>div.overview>div#favlist-users-wrap>div.offline-wrap>ul.users-list").prepend(jQuery("li[data-id='"+arr+"']"));
                });
                 socket.on("fav_delete",function(arr)
                {
					jQuery("li[data-id='"+arr+"']").remove();
                });
                socket.on("new_fav",function(arr)
                {					
                    if (arr['videostatus']==='online')
						jQuery('.online-wrap .users-list').append(entryModel(arr["id"],arr["username"], arr["name"],false));
					else
						jQuery('.offline-wrap .users-list').append(entryModel(arr["id"],arr["username"], arr["name"], false));
					jQuery('ul.users-list li div.favlist-button, .search-user, .closefav, .tip-close, .close-search').tipsy({gravity: jQuery.fn.tipsy.autoNS, fade: true});
					jQuery('.favlist-scroll-wrap').tinyscrollbar();
                });
                socket.on("request_delete",function(arr)
                {
					jQuery("li[data-id='"+arr+"']").remove();
                });

                socket.on("add_request",function(arr)
                {
                    for(var i in arr){
                        jQuery('.request-wrap .users-list').append(entryModel(arr[i]["id"],arr[i]["username"], arr[i]["name"],true));
                    }
					jQuery('ul.users-list li div.favlist-button, .search-user, .closefav, .tip-close, .close-search').tipsy({gravity: $.fn.tipsy.autoNS, fade: true});
					jQuery('.favlist-scroll-wrap').tinyscrollbar();
                });
				socket.on("change_gredits", function(arr)
				{
					//console.log(arr['cr']+" "+arr['b']);
					jQuery("#creditsb>table>tbody>tr>td>div>i").text(arr['b'].toFixed(2));
					jQuery("#credits").text(arr['cr'].toFixed(2));

				});
				socket.on("favlistsendtipsansver", function(dat){
					alert(dat);
				});
                socket.on("retriveMess",function()
                {
					var myArray = jQuery("td.messagesin").find("em").text().replace(/[<?=$lang["tooltip"]["You have"] ?> | <?=$lang["tooltip"]["new messages"] ?>]/g,"")
					jQuery("td.messagesin").find("em").text("<?=$lang["tooltip"]["You have"] ?> "+(myArray*1+1)+" <?=$lang["tooltip"]["new messages"] ?>");
                    jQuery("td.messagesin").find("img").attr("src","images/sms-red.png");
                });
				/*socket.on('message', function (msg) {
                    // Добавляем в лог сообщение, заменив время, имя и текст на полученные
                    //document.querySelector('#log').innerHTML += strings[msg.event].replace(/\[([a-z]+)\]/g, '<span class="$1">').replace(/\[\/[a-z]+\]/g, '</span>').replace(/\%time\%/, msg.time).replace(/\%name\%/, msg.name).replace(/\%text\%/, unescape(msg.text).replace('<', '&lt;').replace('>', '&gt;')) + '<br>';
                    // Прокручиваем лог в конец
                    //document.querySelector('#log').scrollTop = document.querySelector('#log').scrollHeight;
                });*/
				<?php if($_SERVER["PHP_SELF"]=="/usertechsuportonline.php" )
				{
				?>
				socket.emit("enter_support");
                /*socket.on('message', function (msg) {                    
					var time = (new Date).toLocaleTimeString();
                    jQuery('.chats>div').html(jQuery('.chats>div').html() +"["+time+"] "+"Admin: " +msg.text+ '</br>');        			
        			jQuery('.chats>div').scrollTop(jQuery('.chats>div').scrollTop()+30);
        		});*/
        		// При нажатии <Enter> или кнопки отправляем текст
                       /* jQuery('#input').keypress(function(e) {
                        //console.log(e);
                        try{
                        e = e || window.event;
                        if(e.which == '13' && e.ctrlKey)
                            {
                                jQuery(this).val(jQuery(this).val()+"\n");
                            }
                        else if (e.which == '13') {
                            var time = (new Date).toLocaleTimeString();
                            // Отправляем содержимое input'а, закодированное в escape-последовательность
                            socket.json.send({'text':jQuery('#input').val(),'room':''});
                            jQuery('.chats>div').html(jQuery('.chats>div').html() +"["+time+"] "+"<?= $_SESSION['first'] ?>: " +jQuery('#input').val()+ '</br>');
                            jQuery(".chats>div").scrollTop(jQuery(".chats>div").scrollTop()+30);
							// Очищаем input
                            jQuery('#input').val("");
                        }
                        }
                        catch(e){
                            console.log(e);
                        }
                    });
                    jQuery('#send').click(function() {
                        try{
                        var time = (new Date).toLocaleTimeString();
                        // Отправляем содержимое input'а, закодированное в escape-последовательность
                        socket.json.send({'text':jQuery('#input').val(),'room':''});
                        jQuery('.chats>div').html(jQuery('.chats>div').html() +"["+time+"] "+"<?= $_SESSION['first'] ?>: " +jQuery('#input').val()+ '</br>');
                        jQuery(".chats>div").scrollTop(jQuery(".chats>div").scrollTop()+30);
						jQuery('#input').val("");                        
						}
                        catch(e){
                            console.log(e);
                        }
                    });*/
				<?php
				}
				?>
                jQuery(".favlist-button.hide").live("click",function(){
                    jQuery(this).removeClass("hide");
                    jQuery(this).addClass("show");
					jQuery(this).attr("original-title","<?=$lang["tooltip"]["Appear Online to"]?> "+jQuery(this).parents("div.panel").parent().children("div.nickname").text().trim());
                    socket.emit("user_hide",{msg:jQuery(this).attr("data-id")});
                });
                jQuery(".favlist-button.show").live("click",function(){
                    jQuery(this).removeClass("show");
                    jQuery(this).addClass("hide");
					jQuery(this).attr("original-title","<?=$lang["tooltip"]["Appear Offline to"]?> "+jQuery(this).parents("div.panel").parent().children("div.nickname").text().trim());
                    socket.emit("user_show",{msg:jQuery(this).attr("data-id")});
                });
                jQuery(".favlist-button.tip").live("click",function(){
                    jQuery("div.tip-popup-dialog>div.btn-ok").attr("pid",jQuery(this).attr("data-id"));
                    jQuery("div.tip-popup-dialog>input.tip-input").val("");
                    jQuery(".tipsfav").show();
                });
                jQuery(".favtipsnum").live("keypress",function(e)
                {
                    e=e||window.event;
                    if(e.which>47 && e.which<58 || e.which==8)
                    {
                    }
                    else return false;
                });
                jQuery("div.tip-popup-dialog>div.btn-ok").click(function(){
                    var myArray = jQuery("div.tip-popup-dialog>input.tip-input").val().match(/^[0-9]*\.?[0-9]*$/g);
                    if ( myArray != null) {                       
						console.log(parseFloat(jQuery("div.tip-popup-dialog>input.tip-input").val()));
						if(parseFloat(jQuery("div.tip-popup-dialog>input.tip-input").val())>0){
								jQuery.post('functionsmembers.php',{
									'functions':'sendTips',
									'uid':<?= $_SESSION['userid']?>,
									'pid':jQuery("div.tip-popup-dialog>div.btn-ok").attr("pid"),
									'tips':parseFloat(jQuery("div.tip-popup-dialog>input.tip-input").val()),
									'msg':'',
									'subj':"Tips",
									'type':'page'
								},function(data){
									if(data.indexOf("numberincorrect")!=-1) {alert("Incorrectly entered a number tip");jQuery("div.tip-popup-dialog").hide();}
									if(data.indexOf("credittooless")!=-1) {alert("You have insufficient funds in the account");jQuery("div.tip-popup-dialog").hide();}
									if(data.indexOf("servererror")!=-1) {alert("Sorry there was an error on the server");jQuery("div.tip-popup-dialog").hide();}
									if(data.indexOf("success")!=-1) {alert("Tipping sent");jQuery("div.tip-popup-dialog").hide();}										
								},"text"
							);}
							else {alert("Incorrectly entered a number tip");jQuery("div.tip-popup-dialog").hide();}						
                    }
                });
                jQuery(".favtipsclose").click(function(){
                    jQuery(".tipsfav").hide();
                });
		jQuery(".favlist-button.message").live("click",function(){
                    jQuery(".messagespod").find(".favmessname").text(jQuery(this).parents("div.panel").parent().children("div.nickname").text().trim());
                    jQuery(".messagespod").find(".favmessname").attr("pid",jQuery(this).attr("data-id"));
                    jQuery(".messagespod").find(".favmesssub").val("");
                    jQuery(".messagespod").find(".favmessmessages").val("");
					<?php if($_SERVER["PHP_SELF"]=="/onlinemember.php" )
					{
					?>
					jQuery("#flashcontents").hide();
					<?php } ?>
			jQuery(".messagespod").show();
                });
                jQuery(".favlist-button.recycle.frdelete").live("click",function(){
                    socket.emit("fav_remove",{msg:jQuery(this).attr("data-id")});
                    jQuery("div.tipsy").hide();
					jQuery(this).parents("li[data-id='"+jQuery(this).attr("data-id")+"']").remove();
                });

		jQuery(".favlist-button.page").live("click",function(){
					jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"msg":jQuery(this).attr("data-id")},function(dat){
					//var win=window.open("http://www.livecamdream.com/viewperformerpage.php");
					//win.focus();
					window.location.href="viewperformerpage.php";
					},"html");
                });

                jQuery(".sendmess").live("click",function(){
				var t=jQuery(".messagespod").find(".favmessname").attr("pid");
                    jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"msgsend":jQuery(".messagespod").find(".favmessname").attr("pid"),sub:jQuery(".messagespod").find(".favmesssub").val().trim(),messages:jQuery(".messagespod").find(".favmessmessages").val().trim()},function(dat){
                                        if(dat.indexOf("successfuly")!=-1)
                                        {
										socket.emit("getMess",{msg:t});
										<?php if($_SERVER["PHP_SELF"]=="/onlinemember.php" )
										{
										?>
										jQuery("#flashcontents").show();
										<?php } ?>
										jQuery(".messagespod").hide();
                                        }
                                        else {alert(dat);
										<?php if($_SERVER["PHP_SELF"]=="/onlinemember.php" )
										{
										?>
										jQuery("#flashcontents").show();
										<?php } ?>
										jQuery(".messagespod").hide();}
					});
                },"html");
                jQuery(".favlist-button.room").live("click",function(){
					jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"msg":jQuery(this).attr("data-id")},function(dat){
					window.location.href="onlinemember.php";
					});
                });
                jQuery(".favlist-button.recycle.reqdelete").live("click",function(){
                    socket.emit("req_remove",{msg:jQuery(this).attr("data-id")});
					jQuery("div.tipsy").hide();
					jQuery(this).parents("li[data-id='"+jQuery(this).attr("data-id")+"']").remove();
                });
                jQuery(".favlist-button.addreq").live("click",function(){
                    socket.emit("add_fav",{msg:jQuery(this).attr("data-id")});
					jQuery("div.tipsy").hide();
                    jQuery(this).parents("li[data-id='"+jQuery(this).attr("data-id")+"']").remove();
                });
                jQuery(".favlist-button.block").live("click",function(){
                    socket.emit("fav_req_block",{msg:jQuery(this).attr("data-id")});
					jQuery("div.tipsy").hide();
                   jQuery(this).parents("li[data-id='"+jQuery(this).attr("data-id")+"']").remove();
                });
               });
                jQuery('div.search-wrap>.btn-ok').live('click',function(){
					var r="";
                    jQuery("div.viewport>div.overview > ul.searched-users>li.active").each(function(){
                        r+="-"+jQuery(this).attr("data-id");
                    });
                    socket.emit("addFriend",r);
                    jQuery('.search-wrap').hide('fast');
					jQuery('.favlist-scroll-wrap>.viewport').show();
					jQuery('.favlist-scroll-wrap>.scrollbar').show();
					jQuery('.favlist-search').show();
					jQuery('.search-user').show();
					jQuery('.favlist-scroll-wrap').tinyscrollbar_update('relative');
				});
                jQuery("input[name='search_nickname']").keyup(function(){
                    jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"search":jQuery(this).val()},function(dat){
                                        console.log(dat);
                                        jQuery("ul.searched-users").empty();
                                        for(var t in dat)
                                        {
                                            if(dat[t][2].indexOf("thumb")==-1)
											dat[t][2]=dat[t][2].replace(".jpg","-thumb.jpg");
											jQuery("ul.searched-users").append("<li data-id=\""+dat[t][1]+"\"><img src=\"profileimages/"+dat[t][2]+"\" width=\"30\" height=\"30\"><div class=\"nickname\">"+dat[t][0]+"</div></li>");
                                        }

					},"json").complete(function() {jQuery('.search-wrap').tinyscrollbar();});
                });
                jQuery(".messfavclose").click(function(){
                <?php if($_SERVER["PHP_SELF"]=="/onlinemember.php" )
					{
					?>
					jQuery("#flashcontents").show();
					<?php } ?>
				jQuery(".messagespod").hide();
                });
                jQuery(".closefav").live("click",function(){
				jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"flh":"false","top":jQuery(this).parent().offset().top,"left":jQuery(this).parent().offset().left},function(dat){
					jQuery(".favlist-wrap").hide();
					});
                   //console.log(jQuery(this).parent().offset().top+"-"+jQuery(this).parent().offset().left);

                });
                jQuery(".favlist").live("click",function(){
				   jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"flh":"true","top":0,"left":0},function(dat){
					jQuery(".favlist-wrap").show();
				   <?php if(isset($_COOKIE['favlt']) && isset($_COOKIE['favll'])) echo("jQuery(\".favlist-wrap\").offset({top:\"".$_COOKIE['favlt']."\",left:\"".$_COOKIE['favll']."\"});"); ?>
					});
                });
				}).fail(function(){/*jQuery("#favlist").remove();*/});
				/*var s=jQuery("#favlist").offset().top+jQuery("#favlist").height();
				//jQuery("#favlist").offset().top
				if(jQuery("table#main").height()<s)
				{
					jQuery("#favlist").offset({top:jQuery("#favlist").offset().top-(s-jQuery("table#main").height())});
				}*/
				<?
}
function htmlFriendlistuser()
{    global $lang;
     ?>
	 <script type="text/javascript" src="js/jquery.tinyscrollbar.min.js"></script>
     <script type="text/javascript" src="js/jquery.tipsy.js"></script>
     <script type="text/javascript" src="js/mainf.js"></script>
     <script type="text/javascript" src="../js/jquery.cookie.js"></script>

    <div id="fl-wrap" style="position: fixed; left: -215px; top: 160px; z-index: 1500; width: 238px;">
	 <div class="favlist-wrap">
            <div class="favlist-title">
                <div class="search-user" original-title="<?=$lang["tooltip"]["Add to friend list"]?>"></div>
                <?= $lang["models"]["Friends"]; ?>
                <div class="closefav" original-title="Close fav.list"></div>
            </div>
            <div class="favlist-search">
                <input name="search" type="text" placeholder="<?= $lang["models"]["Search"]; ?>">
            </div>
            <div class="tip-popup-dialog">
                <div class="tip-title">Enter amount
                    <div class="tip-close" original-title="Close"></div>
                </div>
                <input name="amount" class="tip-input" type="text" value="">
                <span>$</span>

                <div class="btn-ok">Ok</div>
            </div>
			<div class="clear"></div>
            <div class="search-wrap">
                <div class="close-search" original-title="Close search"></div>
                <input name="search_nickname" type="text" placeholder="Enter nickname">
                <div class="btn-ok">Ok</div>
                <div class="scrolled-wrap">
                    <div class="viewport">
                        <div class="overview">
                            <ul class="searched-users">
                               <!-- <li>
                                    <img src="images/ava.png" width="30" height="30">
                                    <div class="nickname">Sample</div>
                                </li>-->
                            </ul>
                        </div>
                    </div>
                    <div class="scrollbar">
                        <div class="track">
                            <div class="thumb">
                                <div class="end"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="favlist-scroll-wrap">
                <div class="viewport">
                    <div class="overview">
                        <div id="favlist-users-wrap">
                            <div class="online-wrap">
                                <div class="online-title">Online</div>
                                <ul class="users-list">
                                    <!--<li data-id="1">
                                        <div class="item hover">
                                            <img src="images/ava.png" height="30" width="30">
                                            <div class="nickname">Sample</div>
                                            <div class="show-panel"></div>
                                            <div class="clear"></div>
                                            <div class="panel">
                                                <div class="favlist-button recycle" original-title="Remove from friend list"></div>
                                                <div class="favlist-button tip" original-title="Leave a tip offline"></div>
                                                <div class="favlist-button message" original-title="Send message"></div>
                                                <div class="favlist-button hide" original-title="Appear Offline to dawsd12"></div>
                                                <div class="favlist-button room" original-title="Go to dawsd12 chat"></div>
                                                <div class="favlist-button page" original-title="Go to dawsdddddd12 profile"></div>
                                            </div>
                                        </div>
                                    </li>-->
                                </ul>
                            </div>

                            <div class="offline-wrap">
                                <div class="offline-title">Offline</div>
                                <ul class="users-list">
                                    <!--<li data-id="1">
                                        <div class="item hover">
                                            <img src="images/ava.png" height="30" width="30">
                                            <div class="nickname">Sample</div>
                                            <div class="show-panel"></div>
                                            <div class="clear"></div>
                                            <div class="panel">
                                                <div class="favlist-button recycle" original-title="Remove from friend list"></div>
                                                <div class="favlist-button tip" original-title="Leave a tip offline"></div>
                                                <div class="favlist-button message" original-title="Send message"></div>
                                                <div class="favlist-button hide" original-title="Appear Offline to dawsd12"></div>
                                                <div class="favlist-button room" original-title="Go to dawsd12 chat"></div>
                                                <div class="favlist-button page" original-title="Go to dawsdddddd12 profile"></div>
                                            </div>
                                        </div>
                                    </li>-->
                                </ul>
                            </div>

                            <div class="request-wrap">
                                <div class="request-title">Request</div>
                                <ul class="users-list">
                                    <!--<li data-id="100">
                                        <div class="item hover">
                                            <img src="images/ava.png" height="30" width="30">
                                            <div class="nickname">Melody</div>
                                            <div class="show-panel"></div>
                                            <div class="clear"></div>
                                            <div class="panel">
                                                <div class="favlist-button recycle" data-id="100" original-title="Remove from friend list"></div>
                                                <div class="favlist-button block" data-id="100" original-title="Blocked model"></div>
                                                <div class="favlist-button room" data-id="100" original-title="Go to Melody chat"></div>
                                                <div class="favlist-button page" data-id="100" original-title="Go to Melody profile"></div>
                                            </div>
                                        </div>
                                    </li> -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="scrollbar">
                    <div class="track">
                        <div class="thumb">
                            <div class="end"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
<div class="messagespod" style="display:none;width:100%; height:100%; position:absolute; top:0px;left:0px; z-index: 1999;background: url(images/favlist/message/fon.png);">
  <div class="messagesFav" style="width:439px;border:1px solid #ffffff; height:297px; position:absolute; top:50%;left:50%; margin-left: -221px;margin-top: -149px;z-index: 2000;border-radius: 5px;background: url(images/favlist/message/podlojka.png);">
    <div class="mess_header" style="width:439px;height:32px;border-radius: 5px 5px 0px 0px;background: url(images/favlist/message/pod_nadpis.png);">
    <div style="text-align:center; width:415px; height:100%;float:left;color:#ffffff;font-weight: bold;padding-top: 4px;"><?=$lang["New Message"]?></div><div class="messfavclose"></div>
    </div>
    <div class="mess_content">
        <p style="height: 32px; padding: 2px;margin: 10px 0px 10px 0px;"><span style="width:80px;display:inline-block;margin-left: 20px;"><?=$lang["models"]["To"]?>:</span><span class="favmessname" style="display:inline-block;border-radius: 5px; background-color: #ffffff; border:1px solid #626262; width:315px; height:22px;padding: 0px 2px;text-align: center;">fesfes</span></p>
        <p style="height: 32px; padding: 2px;margin: 10px 0px 10px 0px;"><span style="width:80px;display:inline-block;margin-left: 20px;"><?=$lang["models"]["Subject"]?>:</span><input class="favmesssub" type="text" style="display:inline-block;border-radius: 5px; background-color: #ffffff; border:1px solid #626262; width:315px; height:22px;padding: 0px 2px;"></p>
        <p style="height: 94px; padding: 2px;margin: 10px 0px 10px 0px;"><span style="width:80px;display:inline-block;margin-left: 20px;position:absolute;">Text:</span><textarea class="favmessmessages" style="display:inline-block;border-radius: 5px; background-color: #ffffff; border:1px solid #626262; width:315px; height:94px;padding: 0px 2px;margin-left: 100px;"></textarea></p>
        <div class="sendmess"><?=$lang["models"]["Send"]?></div>
    </div>
</div>
</div>
        <div id="show_fl" style="background: url(images/friendlistnew/fl_label.png) no-repeat; background-position: 0px 22px; width: 23px; height: 118px; float: right;">
        <div style="width: 22px; height: 22px; background: url(images/friendlistnew/fl_icon.png) no-repeat;"></div>
    </div>
 </div>


     <?php
}
//френдлист модели
function jsFriendlist()
{
global $lang;
//echo("/*");
//print_r($_COOKIE);
//echo("*/");
?>
<?php if(isset($_COOKIE['pfavls']) && $_COOKIE['pfavls']=="false") {echo("jQuery(\".favlist-wrap\").hide();"); }
//else echo("jQuery(\".favlist-wrap\").offset({top:\"".$_COOKIE['pfavlt']."\",left:\"".$_COOKIE['pfavll']."\"});");
?>

jQuery.getScript('http://<?=$_SERVER["HTTP_HOST"]?>:8080/socket.io/socket.io.js', function(){
		//jQuery(".favlist-wrap").draggable({containment: "document", handle:'.favlist-title'});
		jQuery('.favlist-scroll-wrap').tinyscrollbar();
		jQuery('.search-wrap').tinyscrollbar();
		jQuery('ul.users-list li div.favlist-button, .search-user, .closefav, .tip-close, .close-search').tipsy({gravity: jQuery.fn.tipsy.autoNS, fade: true});
			function entryModel(id,username,avatar,request){
			if(avatar.indexOf("thumb")==-1)
					avatar=avatar.replace(".jpg","-thumb.jpg");
				return request?'<li data-id="'+id+'">\
									<div class="item hover pending">\
										<img src="profileimages/'+avatar+'" height="44" width="49">\
										<div class="nickname">'+username+'</div>\
										<div class="status">pending</div>\
										<div class="clear"></div>\
									</div>\
								</li>':'<li data-id="'+id+'">\
									<div class="item hover">\
										<img src="profileimages/'+avatar+'" height="30" width="30">\
										<div class="nickname">'+username+'</div>\
										<div class="show-panel"></div>\
										<div class="clear"></div>\
										<div class="panel">\
											<div class="favlist-button recycle" original-title="<?=$lang["tooltip"]["Remove from friend list"]?>" data-id="'+id+'"></div>\
											<div class="favlist-button message" original-title="<?=$lang["tooltip"]["Send message"]?>" data-id="'+id+'"></div>\
											<div class="favlist-button hide" original-title="<?=$lang["tooltip"]["Appear Offline to"]?> '+username+' <?=$lang["tooltip"]["chat"]?>" data-id="'+id+'"></div>\
											<div class="favlist-button page" original-title="<?=$lang["tooltip"]["Go to "]?> '+username+' <?=$lang["tooltip"]["profile"]?>" data-id="'+id+'"></div>\
										</div>\
									</div>\
								</li>';
			}

			function addModelToList(list){
				for(var i in list)
					if (list[i]['videostatus']==='online')
						jQuery('.online-wrap .users-list').append(entryModel(list[i]["id"],list[i]["username"], list[i]["name"],false));
					else
						jQuery('.offline-wrap .users-list').append(entryModel(list[i]["id"],list[i]["username"], list[i]["name"], false));
				jQuery('ul.users-list li div.favlist-button, .search-user, .closefav, .tip-close, .close-search').tipsy({gravity: jQuery.fn.tipsy.autoNS, fade: true});
				jQuery('.favlist-scroll-wrap').tinyscrollbar();
			}
            var isConnected=false;
             setTimeout(function(){
                 if(!isConnected)
                   jQuery(".favlist-wrap").remove();
              },10000);
                    socket = io.connect('http://<?=$_SERVER["HTTP_HOST"]?>:8080',{'try multiple transports':true});
                jQuery(".linkonpages").live("click", function(){
                var p=jQuery(this).parents("tr.ikonks").attr("uid");
                jQuery.post("<?= $_SERVER["PHP_SELF"]?>",{'pideq':p},function(){
                window.location.href="<?php if($_SESSION['usertype']==AUTH_USER_TYPE_MEMBER) echo("viewperformerpage.php"); elseif($_SESSION['usertype']==AUTH_USER_TYPE_MODEL) echo("performermemberprofile.php"); ?>";
                });
                });
                socket.on("connect",function(){
                  isConnected=true;
				  <?php					
				  if($_SERVER["PHP_SELF"]=="/modelAccaunt.php" && isset($_SESSION["activations"]) && $_SESSION["activations"]=="erttre")
					{
						echo("socket.emit('add_members');");
						unset($_SESSION["activations"]);
					}				
				?>
                socket.emit("user_online");
				<?php if($_SERVER["PHP_SELF"]=="/performerphotos.php" && ((isset($_POST['frondevoId1']) && !$error) || (isset($_POST['frondevoId2']) && !$error1) || (isset($_POST['frondevoId3']) && !$error2) ||(isset($_POST['frondevoId4']) && !$error3)) || (isset($_POST['frondevoId5']) && !$error4))
				{
					echo("socket.emit('didphoto');");
				}
				else if($_SERVER["PHP_SELF"]=="/performerdetails.php" && (isset($_POST['submit']) && !$error))
				{
					echo("socket.emit('didetails');");
				}
				else if(($_SERVER["PHP_SELF"]=="/performerphotogallery.php" || $_SERVER["PHP_SELF"]=="/performervideogallery.php")&& (isset($_POST['submitphoto']) && !$error))
				{
					echo("socket.emit('dcontent');");
				}
				?>

				socket.on("get_favs",function(arr)
                {
					jQuery('.online-wrap .users-list').empty()
					jQuery('.offline-wrap .users-list').empty()
					addModelToList(arr);
                });
                socket.on("get_favsrm",function(arr)
                {
					jQuery('.request-wrap .users-list').empty();
					for(var i in arr){
                        jQuery('.request-wrap .users-list').append(entryModel(arr[i]["id"],arr[i]["username"], arr[i]["name"],true));
                    }
					jQuery('ul.users-list li div.favlist-button, .search-user, .closefav, .tip-close, .close-search').tipsy({gravity: jQuery.fn.tipsy.autoNS, fade: true});
					jQuery('.favlist-scroll-wrap').tinyscrollbar();
                });
                socket.on("add_my_request",function(arr)
                {
					for(var i in arr){
                        jQuery('.request-wrap .users-list').append(entryModel(arr[i]["id"],arr[i]["username"], arr[i]["name"],true));
                    }
					jQuery('ul.users-list li div.favlist-button, .search-user, .closefav, .tip-close, .close-search').tipsy({gravity: $.fn.tipsy.autoNS, fade: true});
					jQuery('.favlist-scroll-wrap').tinyscrollbar();
                });
                socket.on("my_request_delete",function(arr)
                {
					//console.log("my_request_delete");

					if(window.getFlexApp)
					{
						//console.log(arr);
						//console.log(getFlexApp("Performer").removeRequest(arr));
						getFlexApp("Performer").removeRequest(arr);
					}
                    jQuery("li[data-id='"+arr+"']").remove();
                });
                socket.on("friend_online",function(arr)
                {
                    jQuery("div.favlist-wrap>div.favlist-scroll-wrap>div.viewport>div.overview>div#favlist-users-wrap>div.online-wrap>ul.users-list").prepend(jQuery("li[data-id='"+arr+"']"));
                });
                socket.on("friend_offline",function(arr)
                {
					//console.log(arr);
                    jQuery("div.favlist-wrap>div.favlist-scroll-wrap>div.viewport>div.overview>div#favlist-users-wrap>div.offline-wrap>ul.users-list").prepend(jQuery("li[data-id='"+arr+"']"));
                });
                 socket.on("fav_delete",function(arr)
                {
					if(window.getFlexApp)
					{
						//console.log(arr);
						//console.log(getFlexApp("Performer").removeRequest(arr));
						getFlexApp("Performer").removeFriend(arr);
					}
                    jQuery("li[data-id='"+arr+"']").remove();
                });
                socket.on("new_fav",function(arr)
                {
					if(window.getFlexApp)
					{
						getFlexApp("Performer").approveFriend(arr["id"]);
					}
                    if (arr['videostatus']==='online')
						jQuery('.online-wrap .users-list').append(entryModel(arr["id"],arr["username"], arr["name"],false));
					else
						jQuery('.offline-wrap .users-list').append(entryModel(arr["id"],arr["username"], arr["name"], false));
					jQuery('ul.users-list li div.favlist-button, .search-user, .closefav, .tip-close, .close-search').tipsy({gravity: jQuery.fn.tipsy.autoNS, fade: true});
					jQuery('.favlist-scroll-wrap').tinyscrollbar();
                });
                socket.on("request_delete",function(arr)
                {
                    jQuery("li[data-id='"+arr+"']").remove();
                });
				socket.on("retriveMess",function()
                {
                    var myArray = jQuery("td.messagesin").find("em").text().replace(/[<?=$lang["tooltip"]["You have"] ?> | <?=$lang["tooltip"]["new messages"] ?>]/g,"")
					jQuery("td.messagesin").find("em").text("<?=$lang["tooltip"]["You have"] ?> "+(myArray*1+1)+" <?=$lang["tooltip"]["new messages"] ?>");
                    jQuery("td.messagesin").find("img").attr("src","images/sms-red.png");
                });
                /*socket.on('message', function (msg) {
                    // Добавляем в лог сообщение, заменив время, имя и текст на полученные
                    //document.querySelector('#log').innerHTML += strings[msg.event].replace(/\[([a-z]+)\]/g, '<span class="$1">').replace(/\[\/[a-z]+\]/g, '</span>').replace(/\%time\%/, msg.time).replace(/\%name\%/, msg.name).replace(/\%text\%/, unescape(msg.text).replace('<', '&lt;').replace('>', '&gt;')) + '<br>';
                    // Прокручиваем лог в конец
                    //document.querySelector('#log').scrollTop = document.querySelector('#log').scrollHeight;
                });*/
                jQuery(".favlist-button.hide").live("click",function(){
                    jQuery(this).removeClass("hide");
                    jQuery(this).addClass("show");
					jQuery(this).attr("original-title","<?=$lang["tooltip"]["Appear Online to"]?> "+jQuery(this).parents("div.panel").parent().children("div.nickname").text().trim());
                    socket.emit("user_hide",{msg:jQuery(this).attr("data-id")});
                });
                jQuery(".favlist-button.show").live("click",function(){
                    jQuery(this).removeClass("show");
                    jQuery(this).addClass("hide");
					jQuery(this).attr("original-title","<?=$lang["tooltip"]["Appear Offline to"]?> "+jQuery(this).parents("div.panel").parent().children("div.nickname").text().trim());
                    socket.emit("user_show",{msg:jQuery(this).attr("data-id")});
                });
				jQuery(".favlist-button.message").live("click",function(){
                    jQuery(".messagespod").find(".favmessname").text(jQuery(this).parents("div.panel").parent().children("div.nickname").text().trim());
                    jQuery(".messagespod").find(".favmessname").attr("pid",jQuery(this).attr("data-id"));
                    jQuery(".messagespod").find(".favmesssub").val("");
                    jQuery(".messagespod").find(".favmessmessages").val("");
					<?php if($_SERVER["PHP_SELF"]=="/onlinePerformer.php" )
					{
					?>
					jQuery("#flashcontents").hide();
					<?php } ?>
					jQuery(".messagespod").show();
                });
                jQuery(".favlist-button.recycle").live("click",function(){
                    socket.emit("fav_remove",{msg:jQuery(this).attr("data-id")});
                    jQuery("div.tipsy").hide();
					jQuery(this).parents("li[data-id='"+jQuery(this).attr("data-id")+"']").remove();
                });
				jQuery(".favlist-button.page").live("click",function(){
					jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"msg":jQuery(this).attr("data-id")},function(dat){
					var win=window.open("http://videochat/performermemberprofile.php", '_blank');
					win.focus();
					//window.location.href="performermemberprofile.php";
					},"html");
                });
                jQuery('#show_fl').toggle(function(){
                    jQuery('#fl-wrap').animate({left: "0px"}, 200);
                }, function(){
                    jQuery('#fl-wrap').animate({left: "-215px"}, 200);
                });
				<?php if($_SERVER["PHP_SELF"]=="/performertechsuportonline.php" )
				{
				?>
				socket.emit("enter_support");
                /*socket.on('message', function (msg) {
                    var time = (new Date).toLocaleTimeString();
                    jQuery('.chats>div').html(jQuery('.chats>div').html() +"["+time+"] "+"Admin: " +msg.text+ '</br>');
        			// Прокручиваем лог в конец
        			jQuery('.chats>div').scrollTop(jQuery('.chats>div').scrollTop()+30);
        		});
        		// При нажатии <Enter> или кнопки отправляем текст
                        jQuery('#input').keypress(function(e) {
                        //console.log(e);
                        try{
                        e = e || window.event;
                        if(e.which == '13' && e.ctrlKey)
                            {
                                jQuery(this).val(jQuery(this).val()+"\n");
                            }
                        else if (e.which == '13') {
                            var time = (new Date).toLocaleTimeString();
                            // Отправляем содержимое input'а, закодированное в escape-последовательность
                            socket.json.send({'text':jQuery('#input').val(),'room':''});
                            jQuery('.chats>div').html(jQuery('.chats>div').html() +"["+time+"] "+"<?= $_SESSION['first'] ?>: " +jQuery('#input').val()+ '</br>');
                            jQuery(".chats>div").scrollTop(jQuery(".chats>div").scrollTop()+30);
							// Очищаем input
                            jQuery('#input').val("");
                        }
                        }
                        catch(e){
                            console.log(e);
                        }
                    });
                    jQuery('#send').click(function() {
                        try{
                        var time = (new Date).toLocaleTimeString();
                        // Отправляем содержимое input'а, закодированное в escape-последовательность
                        socket.json.send({'text':jQuery('#input').val(),'room':''});
                        jQuery('.chats>div').html(jQuery('.chats>div').html() +"["+time+"] "+"<?= $_SESSION['first'] ?>: " +jQuery('#input').val()+ '</br>');
                        jQuery(".chats>div").scrollTop(jQuery(".chats>div").scrollTop()+30);
						jQuery('#input').val("");
                        }
                        catch(e){
                            console.log(e);
                        }
                    });*/
				<?php
				}
				?>
                jQuery(".sendmess").live("click",function(){
				var t=jQuery(".messagespod").find(".favmessname").attr("pid");
                    jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"msgsend":jQuery(".messagespod").find(".favmessname").attr("pid"),sub:jQuery(".messagespod").find(".favmesssub").val().trim(),messages:jQuery(".messagespod").find(".favmessmessages").val().trim()},function(dat){
										//alert(dat.indexOf("successfuly"));
										if(dat.indexOf("successfuly")!=-1)
                                        {
										//alert(t);
										socket.emit("getMess",{msg:t});
					<?php if($_SERVER["PHP_SELF"]=="/onlinePerformer.php" )
					{
					?>
					jQuery("#flashcontents").show();
					<?php } ?>jQuery(".messagespod").hide();
                                        }
                                        else alert(dat);
					});
                },"html");

               });
                jQuery("input[name='search_nickname']").keyup(function(){
                    jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"search":jQuery(this).val()},function(dat){
                                        //console.log(dat);
                                        jQuery("ul.searched-users").empty();
                                        for(var t in dat)
                                        {
											if(dat[t][2].indexOf("thumb")==-1)
												dat[t][2]=dat[t][2].replace(".jpg","-thumb.jpg");
                                            jQuery("ul.searched-users").append("<li data-id=\""+dat[t][1]+"\"><img src=\"profileimages/"+dat[t][2]+"\" width=\"30\" height=\"30\"><div class=\"nickname\">"+dat[t][0]+"</div></li>");
                                        }

					},"json").complete(function() {jQuery('.search-wrap').tinyscrollbar();});
                });
				jQuery('div.search-wrap>.btn-ok').live('click',function(){
					var r="";
                    jQuery("div.viewport>div.overview > ul.searched-users>li.active").each(function(){
                        r+="-"+jQuery(this).attr("data-id");
                    });
                    socket.emit("add_req",r);
                    jQuery('.search-wrap').hide('fast');
					jQuery('.favlist-scroll-wrap>.viewport').show();
					jQuery('.favlist-scroll-wrap>.scrollbar').show();
					jQuery('.favlist-search').show();
					jQuery('.search-user').show();
					jQuery('.favlist-scroll-wrap').tinyscrollbar_update('relative');
				});
                jQuery(".messfavclose").click(function(){
                <?php if($_SERVER["PHP_SELF"]=="/onlinePerformer.php" )
					{
					?>
					jQuery("#flashcontents").show();
					<?php } ?>jQuery(".messagespod").hide();
                });
                jQuery(".closefav").live("click",function(){
                   jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"flh":"false","top":jQuery(this).parent().offset().top,"left":jQuery(this).parent().offset().left},function(dat){

					jQuery(".favlist-wrap").hide();
					});
                   //console.log(jQuery(this).parent().offset().top+"-"+jQuery(this).parent().offset().left);
                });
                jQuery(".favlist").live("click",function(){
                   jQuery.post("<?=$_SERVER["PHP_SELF"]?>",{"flh":"true","top":jQuery(this).parent().offset().top,"left":jQuery(this).parent().offset().left},function(dat){
					jQuery(".favlist-wrap").show();
				   <?php if(isset($_COOKIE['pfavlt']) && isset($_COOKIE['pfavll'])) echo("jQuery(\".favlist-wrap\").offset({top:\"".$_COOKIE['pfavlt']."\",left:\"".$_COOKIE['pfavll']."\"});"); ?>
					});
                });}).fail(function(){jQuery("#favlist").remove();});
				/*var s=jQuery("#favlist").offset().top+jQuery("#favlist").height();
				jQuery("#favlist").offset().top
				if(jQuery("table#main").height()<s)
				{
					jQuery("#favlist").offset({top:jQuery("#favlist").offset().top-(s-jQuery("table#main").height())});
				}*/<?
}
function htmlFriendlist()
{    global $lang;
     ?>
     <script type="text/javascript" src="js/jquery.tinyscrollbar.min.js"></script>
        <script type="text/javascript" src="js/jquery.tipsy.js"></script>
        <script type="text/javascript" src="js/mainf.js"></script>
    <div id="fl-wrap" style="position: fixed; left: -215px; top: 160px; z-index: 1500; width: 238px;">
	 <div class="favlist-wrap">
            <div class="favlist-title">
                <div class="search-user" original-title="<?=$lang["tooltip"]["Add to friend list"]?>"></div>
                <?= $lang["models"]["Friends"]; ?>
                <div class="closefav" original-title="Close fav.list"></div>
            </div>
            <div class="favlist-search">
                <input name="search" type="text" placeholder="<?= $lang["models"]["Search"]; ?>">
            </div>
            <div class="tip-popup-dialog">
                <div class="tip-title">Enter amount
                    <div class="tip-close" original-title="Close"></div>
                </div>
                <input name="amount" class="tip-input" type="text" value="">
                <span>$</span>

                <div class="btn-ok">Ok</div>
            </div>
            <div class="search-wrap">
                <div class="close-search" original-title="Close search"></div>
                <input name="search_nickname" type="text" placeholder="Enter nickname">
                <div class="btn-ok">Ok</div>
                <div class="scrolled-wrap">
                    <div class="viewport">
                        <div class="overview">
                            <ul class="searched-users">
                            </ul>
                        </div>
                    </div>
                    <div class="scrollbar">
                        <div class="track">
                            <div class="thumb">
                                <div class="end"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="favlist-scroll-wrap">
                <div class="viewport">
                    <div class="overview">
                        <div id="favlist-users-wrap">
                            <div class="online-wrap">
                                <div class="online-title">Online</div>
                                <ul class="users-list">
                                </ul>
                            </div>

                            <div class="offline-wrap">
                                <div class="offline-title">Offline</div>
                                <ul class="users-list">
                                </ul>
                            </div>

                            <div class="request-wrap">
                                <div class="request-title">Request</div>
                                <ul class="users-list">
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="scrollbar">
                    <div class="track">
                        <div class="thumb">
                            <div class="end"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<div class="messagespod" style="display:none;width:100%; height:100%; position:absolute; top:0px;left:0px; z-index: 1999;background: url(images/favlist/message/fon.png);">
  <div class="messagesFav" style="width:439px;border:1px solid #ffffff; height:297px; position:absolute; top:50%;left:50%; margin-left: -221px;margin-top: -149px;z-index: 2000;border-radius: 5px;background: url(images/favlist/message/podlojka.png);">
    <div class="mess_header" style="width:439px;height:32px;border-radius: 5px 5px 0px 0px;background: url(images/favlist/message/pod_nadpis.png);">
    <div style="text-align:center; width:415px; height:100%;float:left;color:#ffffff;font-weight: bold;padding-top: 4px;"><?=$lang["New Message"]?></div><div class="messfavclose"></div>
    </div>
    <div class="mess_content">
        <p style="height: 32px; padding: 2px;margin: 10px 0px 10px 0px;"><span style="width:80px;display:inline-block;margin-left: 20px;"><?=$lang["models"]["To"]?>:</span><span class="favmessname" style="display:inline-block;border-radius: 5px; background-color: #ffffff; border:1px solid #626262; width:315px; height:22px;padding: 0px 2px;text-align: center;">fesfes</span></p>
        <p style="height: 32px; padding: 2px;margin: 10px 0px 10px 0px;"><span style="width:80px;display:inline-block;margin-left: 20px;"><?=$lang["models"]["Subject"]?>:</span><input class="favmesssub" type="text" style="display:inline-block;border-radius: 5px; background-color: #ffffff; border:1px solid #626262; width:315px; height:22px;padding: 0px 2px;"></p>
        <p style="height: 94px; padding: 2px;margin: 10px 0px 10px 0px;"><span style="width:80px;display:inline-block;margin-left: 20px;position:absolute;">Text:</span><textarea class="favmessmessages" style="display:inline-block;border-radius: 5px; background-color: #ffffff; border:1px solid #626262; width:315px; height:94px;padding: 0px 2px;margin-left: 100px;"></textarea></p>
        <div class="sendmess"><?=$lang["models"]["Send"]?></div>
    </div>
</div>
</div>
        <div id="show_fl" style="background: url(images/friendlistnew/fl_label.png) no-repeat; background-position: 0px 22px; width: 23px; height: 118px; float: right;">
        <div style="width: 22px; height: 22px; background: url(images/friendlistnew/fl_icon.png) no-repeat; writing-"></div>
    </div>
     </div>
     <?php
}
function phpAdminpan(){
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) & $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
{
   //print_r($_SESSION);
   global $lang;
   if (isset($_POST['id']) && isset($_POST['publish'])){
       $publish = isset($_POST['publish'])?(int)$_POST['publish']:0;
       $id = isset($_POST['id'])?(int)$_POST['id']:0;
       $sql = "UPDATE `news` SET `publish`= ".mysql_real_escape_string($publish)
           ." WHERE  `id`=".mysql_real_escape_string($id);
       mysql_query($sql) or die(mysql_error());
       if (mysql_query($sql))
           exit('ok');
       else
           exit('err');
   }
   elseif($_POST['type'] && $_POST['pideq'] && $_POST['type']=="performers")
    {
     $_SESSION['checkperformerid']=0+$_POST["pideq"];
    }
   elseif($_POST['type'] && $_POST['pideq'] && $_POST['type']=="users")
    {
        $_SESSION['checkuserid']=0+$_POST["pideq"];
    }
   elseif($_POST['type'] && $_POST['pideq'] && $_POST['type']=="studio")
   {
       $_SESSION['checkstudioid']=0+$_POST["pideq"];
   }
   if($_POST['vpch']==1)
    {
	   echo("tre");	   
    }
   if(isset($_POST['type']) && $_POST['pideq'] && $_POST['type']=="supports")
   {
     $_SESSION['checksupportrid']=0+$_POST["pideq"];
   }
   if( $_POST['pideq'] && $_POST['vvide'])
   {
	$_SESSION['performeruserid']=0+$_POST["pideq"];
	$ght=mysql_query("SELECT id, name, data, cost, views FROM videos WHERE ownerid=".mysql_real_escape_string($_SESSION['performeruserid']." AND cost>0 AND deleted=0 AND hidden=1 ORDER BY data ASC"));
	if(mysql_num_rows($ght)>0){
		$m=array();
		while($rt=mysql_fetch_array($ght))
		{
			$m[]=array($rt[0],$rt[1],floor($rt[2]/1000),$rt[3],$rt[4]);
		}
		echo(json_encode($m));
	}
	else echo(json_encode(array("Error")));	
   }  
   if($_POST['type']=="performer")
    {
       $_SESSION['checkperformerid']=0+$_POST["pideq"];
    }
   elseif($_POST['type']=="newuser")
    {
       $_SESSION['checkuserid']=0+$_POST["pideq"];
    }
   elseif($_POST['type']=="user")
    {
       $_SESSION['checkuserid']=0+$_POST["pideq"];
    }
   if(isset($_POST['ftype'])&& isset($_POST['ftid']) && isset($_POST['fcid']) && isset($_POST['ffrom']) && isset($_POST['fsub']) && isset($_POST['ftext']))
    {
		 $subject = safe_string($_POST['fsub']);
		 $message = safe_string($_POST['ftext']);
        $message=str_replace("\\n", "<br>", $message);
		 $from = safe_string($_POST['ffrom']);
		 $ftype = safe_string($_POST['ftype']);
		 $ftid = safe_string($_POST['ftid']);
		 $fcid = safe_string($_POST['fcid']);
		 //echo(strlen($subject)."-".$subject."-".$message."-".$from."-".$ftype."-".$ftid."-".$fcid."-");
		 $erft="";
		if(strlen($message)==0) $erft.=$lang["forum"]["Bad length text"]."!\n";
		if(strlen($subject)==0 && $ftid==0) $erft.=$lang["forum"]["Bad length Subject"]."!\n";
		if(strlen($from)==0) $erft.=$lang["forum"]["Bad length From"]."!";
		if(strlen($erft)==0)
		{
			if($ftid==0){
				$now = date("U");
				//echo("INSERT INTO `forum_thema`(`name`, `date`,`idmember`,`delete`,`closed`,`from`,`subject`,`idtype`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,1,'$from','$subject') ");
				$query = mysql_query("INSERT INTO `forum_thema`(`name`, `date`,`idmember`,`delete`,`closed`,`from`,`subject`,`idtype`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,0,'$from','$subject','$ftype') ")or die(mysql_error());
				//$res= $lang["forum"]["Your theme waiting for approval!"];
				$res=mysql_insert_id();
			}
			else {
				$now = date("U");
				//echo("INSERT INTO `forum_messages`(`text`, `date`,`id_member`,`delete`,`closed`,`from`,`thema`,`nesting`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,1,'$from','$ftid','$fcid') ");
				$query = mysql_query("INSERT INTO `forum_messages`(`text`, `date`,`id_members`,`deleted`,`closed`,`from`,`thema`,`nesting`) VALUES('$message','$now','".mysql_real_escape_string($_SESSION['userid'])."',0,0,'$from','$ftid','$fcid') ")or die(mysql_error());
				//$res= $lang["forum"]["Your message waiting for approval!"];
				$res=mysql_insert_id();
			}
			echo $res;
		}
		else echo $erft;
    }
	if(isset($_POST['ftid']) && isset($_POST['gc']) && $_POST['gc']==1)
    {
		 $ftid = safe_string($_POST['ftid']);
		 //echo($ftid);
		 $a=array();
		if($ftid>0)
		{

				$query = mysql_query("SELECT `id`,`date`,`text`,`nesting`,`from`, `closed` FROM `forum_messages` WHERE `deleted`=0 AND thema=".$ftid)or die(mysql_error());
				if(mysql_num_rows($query)>0)
				{
					while($ff=mysql_fetch_assoc($query))
					{
						$a[]=array("id"=>$ff['id'],"date"=>date("m.d.Y H:i",$ff['date']),"text"=>$ff['text'],"from"=>$ff['from'],"nesting"=>$ff['nesting'],"closed"=>$ff['closed']);
					}
				}
		}
		echo json_encode($a);
    }
	if((isset($_POST['fd']) && $_POST['fd']=="111") && isset($_POST['id']) && (isset($_POST['ft']) && ($_POST['ft']=="c" ||$_POST['ft']=="t")))
    {
		 $ft = safe_string($_POST['ft']);
		 $id = safe_string($_POST['id']);
		 //echo($ftid);
		 $a=array();
		if($ft=="c")
		{
			$query = mysql_query("UPDATE `forum_messages` SET `deleted`=1 WHERE id=".$id)or die(mysql_error());
		}
		else
		{
			$query = mysql_query("UPDATE `forum_thema` SET `delete`=1 WHERE id=".$id)or die(mysql_error());
			$query1 = mysql_query("UPDATE `forum_messages` SET `deleted`=1 WHERE thema=".$id)or die(mysql_error());
		}
    }
	if((isset($_POST['fc']) && $_POST['fc']=="111") && isset($_POST['id']) && (isset($_POST['ft']) && ($_POST['ft']=="c" ||$_POST['ft']=="t")))
    {
		 $ft = safe_string($_POST['ft']);
		 $id = safe_string($_POST['id']);
		 //echo($ftid);
		 $a=array();
		if($ft=="c")
		{
			$query = mysql_query("UPDATE `forum_messages` SET `closed`=0 WHERE id=".$id)or die(mysql_error());
		}
		else
		{
			$query = mysql_query("UPDATE `forum_thema` SET `closed`=0 WHERE id=".$id)or die(mysql_error());
			$query1 = mysql_query("UPDATE `forum_messages` SET `closed`=0 WHERE thema=".$id)or die(mysql_error());
		}
    }
	if((isset($_POST["forum_type"]) && ($_POST["forum_type"]=="c"||$_POST["forum_type"]=="t")) && (isset($_POST["forum_id"]) && $_POST["forum_id"]>0))
	{
		$_SESSION['forumid']=0+$_POST["forum_id"];
		$_SESSION['forumtype']=$_POST["forum_type"];
		//print_r($_SESSION);
		echo("forum");
	}
    if(isset($_POST["pae"]) && ($_POST["pae"]=="o" || $_POST["pae"]=="p"))
    {
        $tdn = date("U");
        if ($_POST['pae']=="o") {
            $id = safe_string($_POST['pid']);
            mysql_query("UPDATE `performers` SET `onlinestatus` = 'offline', `lastonline` = '$tdn' WHERE `id` = '$id'") or die("Error3");
        }
        if ($_POST['pae']=="p") {
            $id = safe_string($_POST['pid']);
            mysql_query("UPDATE `performers` SET `onlinestatus` = 'online', `lastonline` = '$tdn' WHERE `id` = '$id'") or die("Error4");
        }
    }
    else if(isset($_POST["spy"]) && $_POST["pae"]==1)
    {
        $_SESSION['checkperformerid']=0+$_POST["pid"];
    }
	else if(isset($_POST["spy"]) && $_POST["spy"]==1 && isset($_POST["pid"]) && (int)$_POST["pid"]>0)
    {
        $_SESSION['performeruserid']=0+$_POST["pid"];
    }
    else if(isset($_POST["pideq"]) && $_POST["type"]==0)
    {
        $_SESSION['checkperformerid']=0+$_POST["pideq"];
    }
    else if(isset($_POST["pideq"]) && $_POST["type"]==1)
    {
        $_SESSION['checkuserid']=0+$_POST["pideq"];
    }
    if (isset($_POST['delmess']) && $_POST['type']=="performers") {
    $k=mysql_real_escape_string($_POST['msg']);
    $k1=mysql_real_escape_string($_POST['msg1']);
    $t="";
    if(strlen($k)>0 && preg_match('/([^0-9,]+)/',$k)==0)
    {
        mysql_query("UPDATE `messages` SET `statusto`=1 WHERE `to` = '$id' AND `id` IN($k)");
      $t.="ok";
    }
    //else
    //{$t.=strlen($k)."-".preg_match('/([^0-9,]+)/',$k);}
    if(strlen($k1)>0 && preg_match('/([^0-9,]+)/',$k1)==0)
    {
      mysql_query("UPDATE `messages` SET `statusfrom`=1 WHERE `from` = '$id' AND `id` IN($k1)");
      $t.="1ok";
    }
    //else{$t.=strlen($k1)."-".preg_match('/([^0-9,]+)/',$k1);}
    echo($t);
    }
    if($_POST['type'] && $_POST['pideq'] && $_POST['type']=="performerss")
    {
     $_SESSION['performeruserid']=0+$_POST["pideq"];
    }
    if(isset($_POST['profile']))
    {$_SESSION['performeruserid']=0+$_POST["pideq"];}
    if(isset($_POST['edit']))
    {$_SESSION['checkperformerid']=0+$_POST["pideq"];}	
	$_SESSION['performeruserid']=0+$_POST["pideq"];
if(isset($_POST['mega']))
    {$_SESSION['megastudiaid']=0+$_POST["pideq"];}
    if (isset($_POST['delmess']) && $_POST['type']=="userss") {
    $k=mysql_real_escape_string($_POST['msg']);
    $k1=mysql_real_escape_string($_POST['msg1']);
    $t="";
    if(strlen($k)>0 && preg_match('/([^0-9,]+)/',$k)==0)
    {
      mysql_query("UPDATE `messages` SET `statusfrom`=1 WHERE `from` = '$id' AND `id` IN($k)");
      $t.="1ok";
    }
    //else
    //{$t.=strlen($k)."-".preg_match('/([^0-9,]+)/',$k);}
    if(strlen($k1)>0 && preg_match('/([^0-9,]+)/',$k1)==0)
    {
    mysql_query("UPDATE `messages` SET `statusto`=1 WHERE `to` = '$id' AND `id` IN($k1)");
      $t.="ok";
    }
    //else{$t.=strlen($k1)."-".preg_match('/([^0-9,]+)/',$k1);}
    echo($t);
    }
    exit();
}}
function jsAdminpan()
{
//print_r($_SESSION);
?>



jQuery.getScript('http://<?=$_SERVER["HTTP_HOST"]?>:8080/socket.io/socket.io.js', function()
	{
	jQuery("#adminpan").draggable({containment: "document",  handle:'.adminpanheader',stop:function(e,ui){
                //$("div#imagefone").height($("table#main").outerHeight(true));
                //$("div#imagefone").width($("table#main").outerWidth(true));
                if(jQuery("body").width()<(jQuery(this).offset().left+200)) jQuery(this).offset({left:(jQuery("body").width()-200)});
                if(jQuery("body").height()<(jQuery(this).offset().top+323)) jQuery(this).offset({top:(jQuery("body").height()-323)});

                //alert(jQuery("body").height()+" "+(jQuery(this).offset().top+323));
                }});
            var isConnected=false;
             setTimeout(function(){
                 //alert(isConnected);
				 if(!isConnected)
                   { //alert("sss");
				   jQuery("#adminpan").remove();}
              },5000);
                    socket = io.connect('http://<?=$_SERVER["HTTP_HOST"]?>:8080',{'try multiple transports':true});

                /*socket.on('disconnect',function(){
					console.log('Disconnected from server');
					});
				socket.on('error', function (reason){
					console.error('Can\'t connect to the server. Reason: ', reason);
					});

					socket.on( 'reconnect', function() {
					console.log('my connection has been restored!');
					} );*/
                socket.on("connect",function(){
				//console.log('connect');
                  isConnected=true;
                socket.emit("user_online");
				<?php
				//print_r($_SESSION);
				
				if($_SERVER["PHP_SELF"]=="/admin/infserver.php")
				{
					echo("socket.emit('info_server'); 
					jQuery.post(\"".$_SERVER["PHP_SELF"]."\",{'tru':1},function(dat){
						jQuery(\"div.fmsstat\").html(dat);
					});	
					setInterval(function(){socket.emit('info_server');
					jQuery.post(\"".$_SERVER["PHP_SELF"]."\",{'tru':1},function(dat){
						jQuery(\"div.fmsstat\").html(dat);
					});					
					},60000);
					setInterval(function(){
					jQuery.post(\"".$_SERVER["PHP_SELF"]."\",{'tru':1},function(dat){
						jQuery(\"div.fmsstat\").html(dat);
					});					
					},10000);");
					echo("socket.on(\"get_info_server\", function(arr){
						//console.log(arr);
						jQuery(\"p#userscount>span\").empty();
						jQuery(\"p#performerscount>span\").empty();
						jQuery(\"p#operformerscount>span\").empty();
						jQuery(\"p#adminscount>span\").empty();
						var c=0;
						for(var i in arr[\"users\"])
						{
							c++;
							jQuery(\"p#userscount>span\").append(\"<b>\"+i+\"</b>, \");
						}
						jQuery(\"p#userscount>i\").text(c);
						c=0;
						for(var i in arr[\"performers\"])
						{
							c++;
							jQuery(\"p#performerscount>span\").append(\"<b>\"+i+\"</b>, \");
						}
						jQuery(\"p#performerscount>i\").text(c);
						c=0;
						for(var i in arr[\"operformers\"])
						{
							c++;
							jQuery(\"p#operformerscount>span\").append(\"<b class='typechat\"+arr[\"operformers\"][i][\"chat\"]+\"'>(\"+i+\")\"+arr[\"operformers\"][i][\"nick\"]+\"</b>, \");
						}
						jQuery(\"p#operformerscount>i\").text(c);
						c=0;
						for(var i in arr[\"admins\"])
						{
							c++;
							jQuery(\"p#adminscount>span\").append(\"<b>\"+i+\"</b>, \");
						}
						jQuery(\"p#adminscount>i\").text(c);
						
					});");
				}
				
				if($_SERVER["PHP_SELF"]=="/admin/edit-studio.php" && isset($_SESSION['checkstudioid']))
				{
					echo("socket.emit('connect_admin',{r:'in',id:".$_SESSION['checkstudioid']."});");
				}
				else if($_SERVER["PHP_SELF"]=="/admin/edit-performer.php" && isset($_SESSION['checkperformerid']))
				{
					echo("socket.emit('connect_admin',{r:'in',id:".$_SESSION['checkperformerid']."});");
				}
				?>
				socket.on("conn_admin", function(arr){
				//alert("OOOOO");
					if(jQuery("tr.friend[uid='"+arr["id"]+"'][t='members']"))
					{
						if(arr["r"]==="in")
						{
							jQuery("tr.friend[uid='"+arr["id"]+"'][t='members']>td>div.adminm").addClass("adminma");
							jQuery("tr.friend[uid='"+arr["id"]+"'][t='members']>td>div.adminma").removeClass("adminm");
						}
						else
						{
							jQuery("tr.friend[uid='"+arr["id"]+"'][t='members']>td>div.adminma").addClass("adminm");
							jQuery("tr.friend[uid='"+arr["id"]+"'][t='members']>td>div.adminma").removeClass("adminma");
						}
					}
				});
                socket.on("get_favs",function(arr)
                { 
					//console.log('arr');
					//console.log(arr);
                    jQuery("div#adminpan>table>tbody>tr>td>div>table.nm>tbody").empty();
					jQuery("div#adminpan>table>tbody>tr>td>div>table.pm>tbody").empty();
					jQuery("div#adminpan>table>tbody>tr>td>div>table.dm>tbody").empty();
                    for(var i in arr){
					//console.log(arr[i]["login"]+"-"+arr[i]["idphoto"]+"-"+arr[i]["count"]);
					
										
					if(arr[i]["type"].indexOf("studio")!=-1){
						var d=new Date(arr[i]["signupdate"]*1000);
						var ber=d.getFullYear()+"."+(d.getMonth()+1)+"."+d.getDate()+" "+d.getHours()+":"+d.getMinutes();
						if(arr[i]["idphotos"]==="")
						{
							//console.log(arr[i]["login"]+"-"+arr[i]["idphoto"]+"-"+arr[i]["count"]);
							jQuery("<tr uid=\""+arr[i]["id"]+"\" class=\"friend\" type=\"studio\" t=\"idphoto\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typemems\">&nbsp;</div></td><td><a style=\"font-size:11px;  display: block; overflow-x: hidden;\">"+arr[i]["login"]+"</a></td><td width=\"38px\" align=\"center\" valign=\"middle\"  class=\"titleheader\" style=\"z-index:auto;\"><div style=\"position:relative;\"><em style=\"right:3px;\">Number<i class=\"tip\"></i></em></div><div class=\"colm\">"+arr[i]["count"]+"</div></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.pm>tbody");
						}
						if(arr[i]["details"]==="")
						{							
							//console.log(arr[i]["login"]+"-"+arr[i]["idphoto"]+"-"+arr[i]["count"]);
							jQuery("<tr uid=\""+arr[i]["id"]+"\" class=\"friend\" type=\"studio\" t=\"details\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typemems\">&nbsp;</div></td><td><a style=\"font-size:11px;  display: block; overflow-x: hidden;\">"+arr[i]["login"]+"</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.dm>tbody");
						}
                        jQuery("<tr uid=\""+arr[i]["id"]+"\" class=\"friend\" type=\"studio\" t=\"members\"><td width=\"30px\" align=\"center\" valign=\"middle\"><div class=\"typemems\">&nbsp;</div></td><td><a style=\"font-size:11px; width: 89px; display: block; overflow-x: hidden;\">"+arr[i]["login"]+"</a></td><td width=\"30px\" align=\"center\" valign=\"middle\"  class=\"titleheader\" style=\"z-index:auto;\"><div style=\"position:relative;\"><em>Documents<i></i></em></div><div class=\"photomem"+arr[i]["idphoto"]+"\">&nbsp;</div></td><td width=\"30px\" align=\"center\" valign=\"middle\"  class=\"titleheader\" style=\"z-index:auto;\"><div class=\"detailsm"+arr[i]["details"]+"\" style=\"position:relative;\">&nbsp;<em>Details<i></i></em></div></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\" style=\"z-index:auto;\"><div style=\"position:relative;\"><em>Admins<i></i></em></div><div class=\"adminm\">&nbsp;</div></td><td width=\"22px\" align=\"center\" valign=\"middle\"><div class=\"datem\">"+ber+"</div></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.nm>tbody");
						}
						else {
						var d=new Date(arr[i]["signupdate"]*1000);
						var ber=d.getFullYear()+"."+(d.getMonth()+1)+"."+d.getDate()+" "+d.getHours()+":"+d.getMinutes();
						
						if(arr[i]["idphotos"]==="")
						{
							//console.log(arr[i]["login"]+"-"+arr[i]["idphoto"]+"-"+arr[i]["count"]);
							jQuery("<tr uid=\""+arr[i]["id"]+"\" class=\"friend\" type=\"performers\" t=\"idphoto\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememm\">&nbsp;</div></td><td><a style=\"font-size:11px;  display: block; overflow-x: hidden;\">"+arr[i]["login"]+"</a></td><td width=\"38px\" align=\"center\" valign=\"middle\" class=\"titleheader\" style=\"z-index:auto;\"><div style=\"position:relative;\"><em style=\"right:3px;\">Number<i class=\"tip\"></i></em></div><div class=\"colm\">"+arr[i]["count"]+"</div></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.pm>tbody");
						}
						if(arr[i]["details"]==="")
						{
							//console.log(arr[i]["login"]+"-"+arr[i]["idphoto"]+"-"+arr[i]["count"]);
							jQuery("<tr uid=\""+arr[i]["id"]+"\" class=\"friend\" type=\"performers\" t=\"details\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememm\">&nbsp;</div></td><td><a style=\"font-size:11px;  display: block; overflow-x: hidden;\">"+arr[i]["login"]+"</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.dm>tbody");
						}
						jQuery("<tr uid=\""+arr[i]["id"]+"\" class=\"friend \" type=\"performers\" t=\"members\"><td width=\"30px\" align=\"center\" valign=\"middle\"><div class=\"typememm\">&nbsp;</div></td><td><a style=\"font-size:11px; width: 89px; display: block; overflow-x: hidden;\">"+arr[i]["login"]+"</a></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\" style=\"z-index:auto;\"><div style=\"position:relative;\"><em>Documents<i></i></em></div><div class=\"photomem"+arr[i]["idphoto"]+"\">&nbsp;</div></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\" style=\"z-index:auto;\"><div style=\"position:relative;\"><em>Details<i></i></em></div><div class=\"detailsm"+arr[i]["details"]+"\">&nbsp;</div></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\" style=\"z-index:auto;\"><div style=\"position:relative;\"><em>Admins<i></i></em></div><div class=\"adminm\">&nbsp;</div></td><td width=\"22px\" align=\"center\" valign=\"middle\"><div class=\"datem\">"+ber+"</div></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.nm>tbody");
						}
                    }
                });
                socket.on("get_content",function(arr)
                { 
					//console.log('Get content', arr);
					jQuery("div#adminpan>table>tbody>tr>td>div>table.cm>tbody").empty();
                    for(var i in arr){
					//console.log(arr[i]["login"]+"-"+arr[i]["idphoto"]+"-"+arr[i]["count"]);
						if(arr[i]["type"].indexOf("user")!=-1){
							jQuery("<tr uid=\""+arr[i]["id"]+"\" class=\"friend\" type=\"users\" t=\"content\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememu\">&nbsp;</div></td><td><a style=\"font-size:11px; display: block; overflow-x: hidden;\">"+arr[i]["login"]+"</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.cm>tbody");
						}
						else {
							jQuery("<tr uid=\""+arr[i]["id"]+"\" class=\"friend\" type=\"performers\" t=\"content\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememm\">&nbsp;</div></td><td><a style=\"font-size:11px; display: block; overflow-x: hidden;\">"+arr[i]["login"]+"</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.cm>tbody");
						}
                    }
                });
				socket.on("get_forum",function(arr)
                { //console.log(arr);
					jQuery("div#adminpan>table>tbody>tr>td>div>table.fm>tbody").empty();
                    for(var i in arr){
					//console.log(arr[i]["login"]+"-"+arr[i]["idphoto"]+"-"+arr[i]["count"]);
						if(arr[i]["type"].indexOf("c")!=-1){
							jQuery("<tr cid=\""+arr[i]["id"]+"\" class=\"friend\" type=\"c\" ><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememfc\">&nbsp;</div></td><td class=\"forum_link\" style=\"cursor:pointer;\"><a style=\"font-size:11px; display: block; overflow-x: hidden;\">"+arr[i]["text"]+"</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.fm>tbody");
						}
						else {
							jQuery("<tr cid=\""+arr[i]["id"]+"\" class=\"friend\" type=\"t\" ><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememft\">&nbsp;</div></td><td class=\"forum_link\" style=\"cursor:pointer;\"><a style=\"font-size:11px; display: block; overflow-x: hidden;\">"+arr[i]["text"]+"</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.fm>tbody");
						}
                    }
                });
                socket.on("rem_member",function(arr)
                {
                    jQuery("tr.friend[uid='"+arr["id"]+"'][t='"+arr["type"]+"']").remove();
                });
				socket.on("rem_forum",function(arr)
                {
                    jQuery("tr.friend[cid='"+arr["id"]+"'][t='"+arr["type"]+"']").remove();
                });
				socket.on("new_forum",function(arr)
                {
				console.log(arr);
					if(arr["type"]==="comment" && jQuery("tr.friend[cid='"+arr["id"]+"'][type='c']").length==0)
					{
						jQuery("<tr cid=\""+arr["id"]+"\" class=\"friend\" type=\"c\" ><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememfc\">&nbsp;</div></td><td class=\"forum_link\" style=\"cursor:pointer;\"><a style=\"font-size:11px; display: block; overflow-x: hidden;\">New comments</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.fm>tbody");
					}
					else if(arr["type"]==="thema" && jQuery("tr.friend[cid='"+arr["id"]+"'][type='t']").length==0)
					{
						jQuery("<tr cid=\""+arr["id"]+"\" class=\"friend\" type=\"t\" ><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememft\">&nbsp;</div></td><td class=\"forum_link\" style=\"cursor:pointer;\"><a style=\"font-size:11px; display: block; overflow-x: hidden;\">New thema</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.fm>tbody");
					}
                });
                socket.on("new_details",function(arr)
                {
					//console.log(arr);
					if(arr["type"]==="studios" && jQuery("tr.friend[uid='"+arr["id"]+"'][t='details'][type='studio']").length==0)
					{
					jQuery("<tr uid=\""+arr["id"]+"\" class=\"friend\" type=\"studio\" t=\"details\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typemems\">&nbsp;</div></td><td><a style=\"font-size:11px;  display: block; overflow-x: hidden;\">"+arr["name"]+"</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.dm>tbody");
					}
					else if(arr["type"]==="performers" && jQuery("tr.friend[uid='"+arr["id"]+"'][t='details'][type='performers']").length==0)
					{
						jQuery("<tr uid=\""+arr["id"]+"\" class=\"friend\" type=\"performers\" t=\"details\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememm\">&nbsp;</div></td><td><a style=\"font-size:11px;  display: block; overflow-x: hidden;\">"+arr["name"]+"</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.dm>tbody");
					}
                    //jQuery("#favlist>table>tbody>tr>td>table.fro>tbody").prepend(jQuery("tr[uid='"+arr+"']"));
                });
				socket.on("new_didphoto",function(arr)
                {
					//console.log(arr);
					if(arr["type"]==="studios" && jQuery("tr.friend[uid='"+arr["id"]+"'][t='idphoto'][type='studio']").length==0)
					{
					jQuery("<tr uid=\""+arr["id"]+"\" class=\"friend\" type=\"studio\" t=\"idphoto\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typemems\">&nbsp;</div></td><td><a style=\"font-size:11px;  display: block; overflow-x: hidden;\">"+arr["name"]+"</a></td><td width=\"38px\" align=\"center\" valign=\"middle\"><div class=\"colm\"  class=\"titleheader\" style=\"z-index:auto;\"><div style=\"position:relative;\"><em style=\"right:3px;\">Number<i class=\"tip\"></i></em></div>"+arr["count"]+"</div></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.pm>tbody");
					}
					else if(arr["type"]==="performers" && jQuery("tr.friend[uid='"+arr["id"]+"'][t='idphoto'][type='performers']").length==0)
					{
						jQuery("<tr uid=\""+arr["id"]+"\" class=\"friend\" type=\"performers\" t=\"idphoto\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememm\">&nbsp;</div></td><td><a style=\"font-size:11px;  display: block; overflow-x: hidden;\">"+arr["name"]+"</a></td><td width=\"38px\" align=\"center\" valign=\"middle\"><div class=\"colm\"  class=\"titleheader\" style=\"z-index:auto;\"><div style=\"position:relative;\"><em style=\"right:3px;\">Number<i class=\"tip\"></i></em></div>"+arr["count"]+"</div></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.pm>tbody");
					}
                });
				socket.on("new_content",function(arr)
                {
					//console.log(arr);
					getFlexApp("playsound").play("new_photos_video");
					if(arr["type"]==="users" && jQuery("tr.friend[uid='"+arr["id"]+"'][t='content'][type='users']").length==0)
					{
						jQuery("<tr uid=\""+arr["id"]+"\" class=\"friend\" type=\"users\" t=\"content\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememu\">&nbsp;</div></td><td><a style=\"font-size:11px; display: block; overflow-x: hidden;\">"+arr["name"]+"</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.cm>tbody");
					}
					else if(arr["type"]==="performers" && jQuery("tr.friend[uid='"+arr["id"]+"'][t='content'][type='performers']").length==0)
					{
						jQuery("<tr uid=\""+arr["id"]+"\" class=\"friend\" type=\"performers\" t=\"content\"><td width=\"34px\" align=\"center\" valign=\"middle\"><div class=\"typememm\">&nbsp;</div></td><td><a style=\"font-size:11px; display: block; overflow-x: hidden;\">"+arr["name"]+"</a></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.cm>tbody");
					}
                });
                socket.on("new_member",function(arr)
                {
					console.log(arr);
					getFlexApp("playsound").play("new_user");
                    if(arr["type"].indexOf("studio")!=-1){
						if(arr["type"]==="studio" && jQuery("tr.friend[uid='"+arr["id"]+"'][t='members'][type='studio']").length==0)
                        jQuery("<tr uid=\""+arr["id"]+"\" class=\"friend\" type=\"studio\" t=\"members\"><td width=\"30px\" align=\"center\" valign=\"middle\"><div class=\"typemems\">&nbsp;</div></td><td><a style=\"font-size:11px; width: 89px; display: block; overflow-x: hidden;\">"+arr["name"]+"</a></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\"><div style=\"position:relative;\"><em>Documents<i></i></em></div><div class=\"photomem\">&nbsp;</div></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\"><div style=\"position:relative;\"><em>Details<i></i></em></div><div class=\"detailsm\">&nbsp;</div></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\"><div style=\"position:relative;\"><em>Admins<i></i></em></div><div class=\"adminm\">&nbsp;</div></td><td width=\"22px\" align=\"center\" valign=\"middle\"><div class=\"datem\">"+arr["date"]+"</div></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.nm>tbody");
						}
						else if(arr["type"].indexOf("users")!=-1) {
						if(arr["type"]==="users" && jQuery("tr.friend[uid='"+arr["id"]+"'][t='members'][type='users']").length==0)
						jQuery("<tr uid=\""+arr["id"]+"\" class=\"friend\" type=\"users\" t=\"members\"><td width=\"30px\" align=\"center\" valign=\"middle\"><div class=\"typememu\">&nbsp;</div></td><td><a style=\"font-size:11px; width: 89px; display: block; overflow-x: hidden;\">"+arr["name"]+"</a></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\"><div style=\"position:relative;\"><em>Documents<i></i></em></div><div class=\"photomem\">&nbsp;</div></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\"><div style=\"position:relative;\"><em>Details<i></i></em></div><div class=\"detailsm\">&nbsp;</div></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\"><div style=\"position:relative;\"><em>Admins<i></i></em></div><div class=\"adminm\">&nbsp;</div></td><td width=\"22px\" align=\"center\" valign=\"middle\"><div class=\"datem\">"+arr["date"]+"</div></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.nm>tbody");
						}
						else{
						if(arr["type"]==="performers" && jQuery("tr.friend[uid='"+arr["id"]+"'][t='members'][type='performers']").length==0)
						jQuery("<tr uid=\""+arr["id"]+"\" class=\"friend\" type=\"performers\" t=\"members\"><td width=\"30px\" align=\"center\" valign=\"middle\"><div class=\"typememm\">&nbsp;</div></td><td><a style=\"font-size:11px; width: 89px; display: block; overflow-x: hidden;\">"+arr["name"]+"</a></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\"><div style=\"position:relative;\"><em>Documents<i></i></em></div><div class=\"photomem\">&nbsp;</div></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\"><div style=\"position:relative;\"><em>Details<i></i></em></div><div class=\"detailsm\">&nbsp;</div></td><td width=\"30px\" align=\"center\" valign=\"middle\" class=\"titleheader\"><div style=\"position:relative;\"><em>Admins<i></i></em></div><div class=\"adminm\">&nbsp;</div></td><td width=\"22px\" align=\"center\" valign=\"middle\"><div class=\"datem\">"+arr["date"]+"</div></td></tr>").appendTo("div#adminpan>table>tbody>tr>td>div>table.nm>tbody");
						}
                });
		jQuery("tr.friend>td>a").live("click",function(){
                    //alert(jQuery(this).parent().parent().attr("uid")+" - "+jQuery(this).parent().parent().attr("type"));
					var g=jQuery(this).parent().parent().attr("type");
                    var h=jQuery(this);
					jQuery.post("<?= $_SERVER["PHP_SELF"]?>",{'pideq':jQuery(this).parent().parent().attr("uid"),'type':jQuery(this).parent().parent().attr("type")},
					function(){
                    socket.emit('remove_member',{'type':h.closest("tr.friend").attr("t"),'id':h.closest("tr.friend").attr("uid")});
					h.closest("tr.friend").remove();					
					setTimeout(function(){
					if(g==="studio")
					{
						window.location.href="/admin/edit-studio.php";
					}
					else if(g==="performers")
					{
						window.location.href="/admin/edit-performer.php";
					}
					else if(g==="users")
					{
						window.location.href="/admin/edit-user.php";
					}
					},1000);
					
                });
               });
			   jQuery("tr.friend>td.forum_link").live("click",function(){
					var t=jQuery(this).parent().attr("type");
                    var id=jQuery(this).parent().attr("cid");

					jQuery.post("<?= $_SERVER["PHP_SELF"]?>",{'forum_type':t,'forum_id':id},function(dat){
                    //socket.emit('remove_member',{'type':h.closest("tr.friend").attr("t"),'id':h.closest("tr.friend").attr("uid")});
					//h.closest("tr.friend").remove();
					console.log(dat);
					if(dat.indexOf("forum")!=-1)
					{
						window.location.href="/admin/adminforum.php";
					}
                });
               });
                jQuery(".closefav").live("click",function(){
                   jQuery(this).parent().hide();
                });
				jQuery("p.adminpanheader").live("click",function(){
                   jQuery("div.adminpancont").each(function(){
						jQuery(this).hide();
				   });
				   jQuery(this).next("div.adminpancont").show();
				   //console.log("s");
                });
                jQuery("td.adminpan").live("click",function(){
                   jQuery("#adminpan").show();
                });
				});
				}).fail(function(){console.log("sss");
				jQuery("#adminpan").remove();});

				<?
}

function htmlAdminpan()
{    global $lang;
     ?>
     
    
<script type="text/javascript" src="../js/swfobject.js"></script>
<script type="text/javascript">
	 var xiSwfUrlStr = "playerProductInstall.swf";
var swfVersionStr = "11.1.0";
var flashvars = {};
var params = {};
params.quality = "high";
params.bgcolor = "#ffffff";
params.allowscriptaccess = "sameDomain";
params.allowfullscreen = "true";
var attributes = {};
attributes.id = "playsound";
attributes.name = "playsound";
attributes.align = "middle";
swfobject.embedSWF(
"../frontend/playsound.swf", "flashContent",
"0", "0",
swfVersionStr, xiSwfUrlStr,
flashvars, params, attributes);
// JavaScript enabled so display the flashContent div in case it is not replaced with a swf object.
swfobject.createCSS("#flashContent", "display:block;text-align:left;");

function getFlexApp (appName){
if(navigator.appName.indexOf("Microsoft") >= 0){
return document[appName];
}else if(navigator.appName.indexOf("Netscape") >= 0){
return document[appName];
} else {
return window[appName];
}
}
var timeoutId;
function js_initiate_callback(){
try{
getFlexApp("playsound").init_callbacks();
//getFlexApp("playsound").play("new_user");
clearTimeout ( timeoutId );
}catch(e){
console.log(e);
}
}
function isAvaliable(){
timeoutId = setTimeout(js_initiate_callback, 0);
}
	 </script>
	 <div id="flashContent"></div>
     <div id="adminpan">
	<div style="float:right; position: absolute; right: 0px;" class="closefav"></div>
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr valign="top"><td >
	<p class="adminpanheader" style="background-image: url(../images/adminpan/verh.png);cursor:pointer; height:27px;border-radius: 5px 5px 0px 0px; color:#ffffff;text-align:center;font-size: 11pt; font-weight: bold; padding:10px 2px 2px 2px;">New member</p>
	<div style="overflow-y: auto; height:369px;" class="adminpancont">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="nm">
	<tbody>
		<!--<tr uid="1" class="friend">
			<td width="34px" align="center" valign="middle"><div class="typemems">&nbsp;</div></td>
			<td ><a style="font-size:11px;  display: block; overflow-x: hidden;">dwadawdawdawdwa</a></td>
			<td width="38px" align="center" valign="middle"><div class="colm">5</div></td>
		</tr>-->
	</tbody>
	</table>
	</div>
</td></tr>
<tr valign="top"><td>
	<p class="adminpanheader" style="background-image: url(../images/adminpan/verh.png);cursor:pointer;  height:27px; color:#ffffff;text-align:center;font-size: 11pt; font-weight: bold; padding:10px 2px 2px 2px;">Id Photo</p>
	<div style="overflow-y: auto;  height:369px; display:none;" class="adminpancont">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="pm">
	<tbody>
		<!--<tr uid="1" class="friend">
			<td width="34px" align="center" valign="middle"><div class="typemems">&nbsp;</div></td>
			<td ><a style="font-size:11px;  display: block; overflow-x: hidden;">dwadawdawdawdwa</a></td>
			<td width="38px" align="center" valign="middle"><div class="colm">5</div></td>
		</tr>-->
	</tbody>
	</table>
	</div>
</td></tr>
<tr valign="top"><td>
	<p class="adminpanheader" style="background-image: url(../images/adminpan/verh.png);cursor:pointer;  height:27px; color:#ffffff;text-align:center;font-size: 11pt; font-weight: bold; padding:10px 2px 2px 2px;">Details</p>
	<div style="overflow-y: auto;  height:369px;  display:none;" class="adminpancont">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="dm">
	<tbody>
		<!--<tr uid="1" class="friend">
			<td width="34px" align="center" valign="middle"><div class="typemems">&nbsp;</div></td>
			<td><a style="font-size:11px;  display: block; overflow-x: hidden;">dwadawdawdawdwa</a></td>
		</tr>-->
	</tbody>
	</table>
	</div>
</td></tr ><tr valign="top"><td>
	<p class="adminpanheader" style="background-image: url(../images/adminpan/verh.png);cursor:pointer;  height:27px; color:#ffffff;text-align:center;font-size: 11pt; font-weight: bold; padding:10px 2px 2px 2px;">Photo/Video</p>
	<div style="overflow-y: auto;  height:369px; display:none;" class="adminpancont">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="cm">
	<tbody>
	<!--<tr uid="1" class="friend">
			<td width="34px" align="center" valign="middle"><div class="typemems">&nbsp;</div></td>
			<td><a style="font-size:11px; display: block; overflow-x: hidden;">dwadawdawdawdwa</a></td>
		</tr>
		<tr uid="1" class="friend">
			<td width="34px" align="center" valign="middle"><div class="typemems">&nbsp;</div></td>
			<td><a style="font-size:11px; display: block; overflow-x: hidden;">dwadawdawdawdwa</a></td>
		</tr>
		<tr uid="1" class="friend">
			<td width="34px" align="center" valign="middle"><div class="typemems">&nbsp;</div></td>
			<td><a style="font-size:11px; display: block; overflow-x: hidden;">dwadawdawdawdwa</a></td>
		</tr>-->
	</tbody>
	</table>
	</div>
</td></tr>
<tr valign="top"><td>
	<p class="adminpanheader" style="background-image: url(../images/adminpan/verh.png);cursor:pointer; border-radius:  0px 0px 5px 5px; height:27px; color:#ffffff;text-align:center;font-size: 11pt; font-weight: bold; padding:10px 2px 2px 2px;">Forum</p>
	<div style="overflow-y: auto;  height:369px; display:none;border-radius:  0px 0px 5px 5px;" class="adminpancont">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="fm">
	<tbody>
	<tr cid="17" type="c" class="friend">
			<td width="34px" align="center" valign="middle"><div class="typemems">&nbsp;</div></td>
			<td class="forum_link" style="cursor:pointer;"><a style="font-size:11px; display: block; overflow-x: hidden;">dwadawdawdawdwa</a></td>
		</tr>
	<tr cid="8" type="t" class="friend">
			<td width="34px" align="center" valign="middle"><div class="typemems">&nbsp;</div></td>
			<td class="forum_link" style="cursor:pointer;"><a style="font-size:11px; display: block; overflow-x: hidden;">thema</a></td>
		</tr>
	</tbody>
	</table>
	</div>
</td></tr>
</tbody>
</table>
</div>

     <?php
}
function htmlFriendlist_old()
{    global $lang;
     ?>
     <div id="favlist">
	<div style="float:right;" class="closefav"><img src="images/cloze.png" /></div>
	<div style="padding:3px;background-image: url(images/favlist/verh.png); height:32px;"><?= $lang["models"]["Friends"]; ?></div>
    <p style=" padding:4px; text-align:center;"><input name="search" type="text" style="background-image: url(images/favlist/sesrch.png);border-radius:7px;border:none; width:218px; height:27px; margin: 0px 0px 5px 0px;" placeholder="<?= $lang["models"]["Search"]; ?>"/></p>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr><td><p style="background-image: url(images/favlist/online.png); height:19px; color:#747373;text-align:center;font-size: 10pt; font-weight: bold; padding:2px;">Online</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="fro">
<tbody>
</tbody>
</table>
<p style="background-image: url(images/favlist/offline.png); height:19px; color:#747373;text-align:center;font-size: 10pt; font-weight: bold; padding:2px;">Offline</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="frf">
<tbody>
</tbody>
</table>
</td></tr>
<tr><td>
<p style="background-image: url(images/favlist/regust.png); height:19px; color:#747373;text-align:center;font-size: 10pt; font-weight: bold; padding:2px;">My request</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="mr" style="color: #000000;">
<tbody>
</tbody>
</table>
</td></tr>
<tr><td>
<p style="background-image: url(images/favlist/regust.png); height:19px; color:#747373;text-align:center;font-size: 10pt; font-weight: bold; padding:2px;">Request</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="re" style="color: #000000;">
<tbody>
</tbody>
</table>
</td></tr>
</tbody>
</table>
    </div>
     <?php
}
function footer()
  {
      global $lang;
      /*echo("<div id=\"footer\">
                    	<a href=\"index.php\" class=\"footer\" id=\"performerlogins\">".$lang['Performer login']."</a>
                        <a href=\"index.php\" class=\"footer\" id=\"studioslogins\">".$lang['Studio login']."</a>
                        <a href=\"index.php\" class=\"footer\" id=\"affiliateslogins\">".$lang['Affiliate login']."</a>
                        <a href=\"performersignup.php\" class=\"footer\">".$lang['Performer wanted']."</a>
                        <a href=\"studiosignup.php\" class=\"footer\">".$lang['Studio wonted']."</a>
                       <a href=\"affiliatesignup.php\" class=\"footer\">".$lang['Affiliate wonted']."</a>
                       <p>".$lang["LiveCam Worldt Webcam Community offers premium quality live webcams with online feeds and 2 way sound. View for free with our hotties or join an uncensored live show with any of our cameras. LiveCam.com is the hottest live cam website featuring amateurs from all over the world. You must be 12 or older to view this website! 2011 LiveCam.com All Rights Reserved. LiveCam.com is owned and operated by AbraCadabra Ltd., New Stret 7, 1010 Town, Country."]."</p>
                    </div>");*/
      echo("<div id=\"footer\">
                    	<a href=\"index.php\" class=\"footer\" id=\"performerlogins\">".$lang['Performer login']."</a>
                        <a href=\"index.php\" class=\"footer\" id=\"studioslogins\">".$lang['Studio login']."</a>
						<a href=\"index.php\" class=\"footer\" id=\"affiliateslogins\">".$lang['Affiliate login']."</a>
						<a href=\"index.php\" class=\"footer\" id=\"affiliatesslogins\">".$lang['Affiliates login']."</a>
                        <a href=\"affiliatesignup.php\" class=\"footer\">".$lang['Affiliate signup']."</a>
                        <a href=\"performersignup.php\" class=\"footer\">".$lang['Performer wanted']."</a>						
                        <a href=\"studiosignup.php\" class=\"footer\">".$lang['Studio wonted']."</a>
                       <p>".$lang["LiveCam Worldt Webcam Community offers premium quality live webcams with online feeds and 2 way sound. View for free with our hotties or join an uncensored live show with any of our cameras. LiveCam.com is the hottest live cam website featuring amateurs from all over the world. You must be 12 or older to view this website! 2011 LiveCam.com All Rights Reserved. LiveCam.com is owned and operated by AbraCadabra Ltd., New Stret 7, 1010 Town, Country."]."</p>
                    </div>");
  }
function header_affiliates()
{ global $lang;
  echo("<div id=\"main_menu\">
                <form method=\"post\" action=\"index.php\">
                  <table style=\"width:96%\">
                  <tr>
                  <td style=\"width:100px; background-image: url(images/logo.png); background-repeat: no-repeat; background-position: 50px -2px;padding: 0px 7px 0px 102px; height: 31px; color:#ffffff\"><a class=\"logolink\" href=\"index.php\" style=\"font-size: 14pt;font-family: 'Gabriola';\">".$lang["LiveCamTouch.com"]."</a></td>
                  <td style=\"width:100%; text-align: right;color: #ff0000; padding-right: 7px\">"); if (isset($err)){echo $err; $err="";}echo("</td>
                  <td style=\"padding-right:50px; word-break: normal;color:#ffffff;font-size:9pt\" nowrap=\"nowrap\">");
                 $t=date("t",time());
                  $d=date("d",time());
                  $m=date("m",time());
                  $y=date("Y",time());
                  if($d<16){$d1=01;$d2=15;}
                  else{
                      $d1=16;$d2=$t;
                  }
                  $da1=mktime(0,0,0,$m,$d1,$y);
                  $da2=mktime(23,59,59,$m,$d2,$y);
                  $yt="SELECT (SELECT SUM(msreferned) FROM sessions WHERE sessions.performer=authorize.id AND date BETWEEN ".mysql_real_escape_string($da1)." AND ".mysql_real_escape_string($da2).") AS chips FROM authorize, performers WHERE authorize.msref=".$_SESSION['userid']." AND authorize.id=performers.id AND authorize.user_type='performers'";
                  $q=mysql_query($yt);
                  $r=mysql_fetch_row($q);
                 $bpc = getconf("buckperchip");
				 //echo($r[0]."-".$bpc."-");
				 $r[0]=sprintf("%.2f",$r[0]);
                $r[0]=number_format(($r[0]*$bpc),2);

            	echo($lang["models"]["WELCOME,"]."
                <a class=\"main\" href=\"affiliatesaccount.php\">".$_SESSION['first']."</a>!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$lang["models"]["Your Earning In This Period:"]." ".$r[0]." ".$lang["chips"]."
                </td>				
				<td style=\"padding-left:20px;\"><div class=\"but_logout\"><a href=\"logout.php\" >Logout</a></div></td></tr></table>



                </form> </div>");
}
function header_affiliatess()
{ global $lang;
  echo("<div id=\"main_menu\">
                <form method=\"post\" action=\"index.php\">
                  <table style=\"width:96%\">
                  <tr>
                  <td style=\"width:100px; background-image: url(images/logo.png); background-repeat: no-repeat; background-position: 50px -2px;padding: 0px 7px 0px 102px; height: 31px; color:#ffffff\"><a href=\"../index.php\" class=\"nonea\" id=\"logo_text\"></a></td>
                  <td style=\"width:100%; text-align: right;color: #ff0000; padding-right: 7px\">"); if (isset($err)){echo $err; $err="";}echo("</td>
                  <td style=\"padding-right:50px; word-break: normal;color:#ffffff;font-size:9pt\" nowrap=\"nowrap\">");
                 $t=date("t",time());
                  $d=date("d",time());
                  $m=date("m",time());
                  $y=date("Y",time());
                  if($d<16){$d1=01;$d2=15;}
                  else{
                      $d1=16;$d2=$t;
                  }
                  $da1=mktime(0,0,0,$m,$d1,$y);
                  $da2=mktime(23,59,59,$m,$d2,$y);
                  $yt="SELECT (SELECT SUM(msreferned) FROM sessions WHERE sessions.performer=authorize.id AND date BETWEEN ".mysql_real_escape_string($da1)." AND ".mysql_real_escape_string($da2).") AS chips FROM authorize, performers WHERE authorize.msref=".$_SESSION['userid']." AND authorize.id=performers.id AND authorize.user_type='performers'";
                  $q=mysql_query($yt);
                  $r=mysql_fetch_row($q);
                 $bpc = getconf("buckperchip");
				 //echo($r[0]."-".$bpc."-");
				 $r[0]=sprintf("%.2f",$r[0]);
                $r[0]=number_format(($r[0]*$bpc),2);

            	echo($lang["models"]["WELCOME,"]."
                <a class=\"main\" href=\"affiliatessaccount.php\">".$_SESSION['first']."</a>!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$lang["models"]["Your Earning In This Period:"]." ".$r[0]." ".$lang["chips"]."
                </td>				
				<td style=\"padding-left:20px;\"><div class=\"but_logout\"><a href=\"logout.php\" >Logout</a></div></td></tr></table>



                </form> </div>");
}
function header_performers()
{ global $lang;
  echo("<div id=\"main_menu\">
                <form method=\"post\" action=\"index.php\">
                  <table style=\"width:96%\">
                  <tr>
                  <td  style=\"min-width:200px;background-repeat: no-repeat; background-position: 50px -2px;padding: 0px 7px 0px 102px; height: 45px; color:#ffffff; overflow: hidden;\"><a href=\"../index.php\" class=\"nonea\" id=\"logo_text\"></a></td>
                  <td  style=\"width:100%; text-align: right;color: #ff0000; padding-right: 7px\">"); if (isset($err)){echo $err; $err="";}echo("</td>
                  <td  style=\"padding-right:10px; word-break: normal;color:#ffffff;font-size:9pt\" nowrap=\"nowrap\">");
				  $t=date("t",time());
                  $d=date("d",time());
                  $m=date("m",time());
                  $y=date("Y",time());
                  if($d<16){$d1=01;$d2=15;}
                  else{
                      $d1=16;$d2=$t;
                  }
                  $da1=mktime(0,0,0,$m,$d1,$y);
                  $da2=mktime(23,59,59,$m,$d2,$y);
                  $yt="SELECT SUM(sessions.earnedchips) as chips FROM sessions WHERE sessions.performer = '$_SESSION[userid]' AND sessions.date BETWEEN $da1 AND $da2 AND sessions.video_sess_type!='public' ";
                  //echo($yt);				  
				  //echo($yt);
                  //echo("SELECT SUM(sessions.earnedchips) as chips FROM sessions WHERE sessions.performer = '$_SESSION[userid]' AND sessions.date BETWEEN $da1 AND $da2 AND sessions.video_sess_type!='public' ");
                  $q=mysql_query($yt);
                  //$q=mysql_query("SELECT chips FROM performers WHERE id=$_SESSION[userid]");
                    $r=mysql_fetch_row($q);
                 $bpc = getconf("buckperchip");
				 //echo($r[0]."-".$bpc."-");
				 $r[0]=sprintf("%.2f",$r[0]);
                $r[0]=number_format(($r[0]*$bpc),2);
				//echo($r[0]);
				$sms="";
				$smsq=mysql_query("SELECT COUNT(id) FROM messages WHERE (totype='performers' AND `to`=".$_SESSION['userid']." AND `read`='no' AND statusto=0)");
				if(mysql_num_rows($smsq)>0)
				{
					if(mysql_result($smsq,0,0)>0)
					$sms=mysql_result($smsq,0,0);
				}
            	echo($lang["models"]["WELCOME,"]."
                <a class=\"main\" href=\"modelAccaunt.php\">".$_SESSION['first']."</a>!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$lang["models"]["Your Earning In This Period:"]." <span id=credits>".$r[0]."</span>
                   </td><td class=\"messagesin titleheader\" style=\"padding-right:5px; color:#fff;\"><div style=\"position:relative;\"><em style=\"width:73px;\">".$lang["tooltip"]["You have"]." ".mysql_result($smsq,0,0)." ".$lang["tooltip"]["new messages"]."<i></i></em><a href=\"messenger-up.php\" target=\"_blank\"><b>".$sms."</b></a></div></td>
                  </td>
				<td style=\"padding-left:20px;\"><div class=\"but_logout\" style=\"top: 0px;\"><a href=\"logout.php\" >".$lang["models"]["Logout"]."</a></div></td>
                  </tr>
                  </table>
                </form> </div>");
}
function header_studio()
{ global $lang;
    restricted_area(AUTH_USER_TYPE_STUDIO);
    echo("<div id=\"main_menu\">
                <form method=\"post\" action=\"index.php\">
                  <table style=\"width:96%\">
                  <tr>
                  <td style=\"width:100px; padding: 0px 7px 0px 102px; height: 45px; color:#ffffff\"><a href=\"../index.php\" id=\"logo_text\" class=\"nonea\">"."</a></td>
                  <td style=\"width:100%; text-align: right;color: #ff0000; padding-right: 7px\">"); if (isset($err)){echo $err; $err="";}echo("</td>
                  <td style=\"padding-right:50px; word-break: normal;color:#ffffff;font-size:9pt\" nowrap=\"nowrap\">");
                  $t=date("t",time());
                  $d=date("d",time());
                  $m=date("m",time());
                  $y=date("Y",time());
                  if($d<16){$d1=01;$d2=15;}
                  else{
                      $d1=16;$d2=$t;
                  }
                  $da1=mktime(0,0,0,$m,$d1,$y);
                  $da2=mktime(23,59,59,$m,$d2,$y);
                  //echo("SELECT (SUM(sessions.earnedchips*(performers.procentage/100)))as chips FROM sessions, studios,performers WHERE sessions.performer = performers.id AND performers.studioid='$_SESSION[userid]' AND performers.studioid=studios.id AND sessions.date BETWEEN $da1 AND $da2 AND sessions.video_sess_type!='public' ");
                  //print_r($_SESSION);
				  //echo("SELECT (SUM(sessions.earnedchips+sessions.studioerned))as chips FROM sessions, studios,performers WHERE sessions.performer = performers.id AND performers.studioid='$_SESSION[userid]' AND performers.studioid=studios.id AND sessions.date BETWEEN $da1 AND $da2 AND sessions.video_sess_type!='public' ");
				  //echo($_SESSION['userid']);
				  $q=mysql_query("SELECT (SUM(sessions.earnedchips+sessions.studioerned))as chips FROM sessions, studios,performers WHERE sessions.performer = performers.id AND performers.studioid='$_SESSION[userid]' AND performers.studioid=studios.id AND sessions.date BETWEEN $da1 AND $da2 AND sessions.video_sess_type!='public' ");
                  //$q=mysql_query("SELECT chips FROM performers WHERE id=$_SESSION[userid]");
                    $r=mysql_fetch_row($q);
                 $bpc = getconf("buckperchip");
                //echo($r[0]."-".$bpc."-");
				 $r[0]=sprintf("%.2f",$r[0]);
                $r[0]=number_format(($r[0]*$bpc),2);
				//echo($r[0]);
            	echo("WELCOME,
                <a class=\"main\" href=\"studioaccount.php\">".$_SESSION['first']."</a>!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;General Earnings In This Period: ".$r[0]."
                   </td><td class=\"favlist\" style=\"padding-right:5px\"><!--<img src=\"images/ico-friends.png\" width=\"16\" height=\"16\">--></td><td class=\"messagesin\" style=\"padding-right:5px; color:#fff;\"><a href=\"messenger-s.php\"><img src=\"images/ico-mail.png\" width=\"16\" height=\"16\"></a></td>
                  </td>
				<td style=\"padding-left:20px;\"><div class=\"but_logout\"><a href=\"logout.php\" >Logout</a></div></td>
                  </tr>
                  </table>
                </form> </div>");
}
function header_lim_studio()
{ global $lang;
//print_r($_SESSION);
    //@restricted_area5();
    echo("<div id=\"main_menu\">
                <form method=\"post\" action=\"index.php\">
                  <table style=\"width:96%\">
                  <tr>
                  <td style=\"width:100px; padding: 0px 7px 0px 102px; height: 45px; color:#ffffff\"><a href=\"../index.php\" id=\"logo_text\" class=\"nonea\">"."</a></td>
                  <td style=\"width:100%; text-align: right;color: #ff0000; padding-right: 7px\">"); if (isset($err)){echo $err; $err="";}echo("</td>
                  <td style=\"padding-right:50px; word-break: normal;color:#ffffff;font-size:9pt\" nowrap=\"nowrap\">");
                  $q=mysql_query("SELECT chips FROM studios WHERE id=$_SESSION[userid]");
                    $r=mysql_fetch_row($q);

            	echo("WELCOME,
                <a class=\"main\" href=\"manageraccount.php\">".$_SESSION['username']."</a>!
                </td>
				<td style=\"padding-left:20px;\"><div class=\"but_logout\"><a href=\"logout.php\" >Logout</a></div></td></tr></table>



                </form> </div>");
}
function header_users()
{ global $lang;
//print_r($_SESSION);

  echo("<div id=\"main_menu\">
                <form method=\"post\" action=\"index.php\">
                  <table style=\"width:96%; position:relative;\">
                  <tr>
                  <td  style=\"min-width:200px;;  background-repeat: no-repeat; background-position: 50px -2px;padding: 0px 7px 0px 102px; height: 45px; color:#ffffff; overflow: hidden;\"><a href=\"../index.php\" class=\"nonea\" id=\"logo_text\"></a></td>
                  <td  style=\"width:100%; text-align: right;color: #ff0000; padding-right: 7px\">"); if (isset($err)){echo $err; $err="";}echo("</td>
                  <td \"titleheader\" style=\"padding-right:30px; word-break: normal;color:#ffffff;font-size:9pt\" nowrap=\"nowrap\">");
                     $q=mysql_query("SELECT chips, bonuscredits FROM users WHERE id=$_SESSION[userid]");
                    $r=mysql_fetch_row($q);
                    $query = mysql_query("SELECT `login` FROM `authorize` WHERE `id` = '$_SESSION[userid]'");
        $rsdfz = mysql_fetch_row($query);
				$sms="";
				$smsq=mysql_query("SELECT COUNT(id) FROM messages WHERE (totype='users' AND `to`=".$_SESSION['userid']." AND `read`='no' AND statusto=0)");
				if(mysql_num_rows($smsq)>0)
				{
					//echo(mysql_result($smsq,0,0));
					if(mysql_result($smsq,0,0)>0)
					$sms=mysql_result($smsq,0,0);
				}
            	echo("<a class=\"main\" href=\"catalog.php\" style=\"text-decoration:none;\">WELCOME,
                ".$rsdfz['0']."</a>!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Current balance: <span id=credits>".sprintf("%.2f",$r[0])."</span><b> (<span id=creditsb><table style=\"display: inline-block; position: relative; top: 5px;\"><tr><td  class=\"titleheader\"><div style=\"position:relative;cursor:pointer;color: green;font-size: 9pt; font-weight: bold;\"><em style=\"top: 25px; height: 100px; text-align: center; white-space: pre-wrap;white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; width: 111px; word-wrap: break-word;\">".$lang["tooltip"]["These are FREE bonus credits earned after purchase of credits. YOU can only use these credits on a new model."]."<i style=\"left:50px;\"></i></em><i>".sprintf("%.2f",$r[1])."</i></div></td></tr></table></span>)</b>".$lang["chips"]."
                  </td><td class=\"messagesin titleheader\" style=\"padding-right:5px; color:#fff;\"><div style=\"position:relative; margin-top: 7px;\"><em style=\"width:73px; right:-60px; z-index: 3;\">".$lang["tooltip"]["You have"]." ".mysql_result($smsq,0,0)." ".$lang["tooltip"]["new messages"]."<i></i></em><a href=\"messenger-up.php\" target=\"_blank\"><b>".$sms."</b></a></div></td>
                  <td class=\"titleheader\" style=\"padding-left:10px;\"><!--<button class=\"add_btn\" style=\"width:116px;margin-left:0px;\">BUY MORE CREDITS</button>--><a href=\"buy-credits.php\" target=\"_blank\"><div style=\"position:relative; margin-top: 7px;\"><em>".$lang["tooltip"]["Buy credits"]."<i></i></em><img src=\"images/$-green.png\" width=\"23\" height=\"18\"></div></a></td>
				<td style=\"padding-left:20px;\"><div class=\"but_logout\"><a href=\"logout.php\" >Logout</a></div></td>
                  </tr>
                  </table>
                </form> </div>");
}
function header_admin()
{ global $lang;
 echo("<div id=\"main_menu\">
                <form method=\"post\" action=\"index.php\">
                  <table style=\"width:96%\">
                  <tr>
                  <td  style=\"min-width:200px;; background-image: url(images/logo.png); background-repeat: no-repeat; background-position: 50px -2px;padding: 0px 7px 0px 102px; height: 45px; color:#ffffff; overflow: hidden;\"><a href=\"../index.php\" class=\"nonea\" id=\"logo_text\">".$lang["LiveCamTouch.com"]."</a></td>
                  <td style=\"width:100%; text-align: right;color: #ff0000; padding-right: 7px\">"); if (isset($err)){echo $err; $err="";}echo("</td>
                  <td style=\"padding-right:50px; word-break: normal;color:#ffffff;font-size:9pt\" nowrap=\"nowrap\">");
                  //$q=mysql_query("SELECT chips FROM affiliates WHERE id=$_SESSION[userid]");
                    //$r=mysql_fetch_row($q);

            	echo("Hello
                <a class=\"main\" href=\"/admin/admin.php\">".$_SESSION['first']."</a>!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </td><td class=\"adminpan titleheader\" style=\"padding-right:5px\"><div style=\"position:relative;\"><em>Panel<i></i></em><img src=\"../images/adminpan/shit.png\" width=\"20\" height=\"20\"></div></td>
				<td style=\"padding-left:20px;\"><div class=\"but_logout\"><a href=\"logout.php\" >Logout</a></div></td></tr></table>



                </form> </div>");
}
function header_admin_studio()
{ global $lang;
 echo("<div id=\"main_menu\">
                <form method=\"post\" action=\"index.php\">
                  <table style=\"width:96%\">
                  <tr>
                 <td  style=\"min-width:200px;; background-image: url(images/logo.png); background-repeat: no-repeat; background-position: 50px -2px;padding: 0px 7px 0px 102px; height: 45px; color:#ffffff; overflow: hidden;\"><a href=\"../index.php\" class=\"nonea\" id=\"logo_text\">".$lang["LiveCamTouch.com"]."</a></td>
                  <td style=\"width:100%; text-align: right;color: #ff0000; padding-right: 7px\">"); if (isset($err)){echo $err; $err="";}echo("</td>
                  <td style=\"padding-right:50px; word-break: normal;color:#ffffff;font-size:9pt\" nowrap=\"nowrap\">");
                  $t=date("t",time());
                  $d=date("d",time());
                  $m=date("m",time());
                  $y=date("Y",time());
                  if($d<16){$d1=01;$d2=15;}
                  else{
                      $d1=16;$d2=$t;
                  }
                  $da1=mktime(0,0,0,$m,$d1,$y);
                  $da2=mktime(23,59,59,$m,$d2,$y);
                  $q=mysql_query("SELECT (SUM(sessions.megastuderned+sessions.msreferned))as chips FROM sessions, authorize WHERE sessions.performer = authorize.id AND authorize.studio='$_SESSION[userid]' AND sessions.date BETWEEN $da1 AND $da2 AND sessions.video_sess_type!='public' ");
                  $r=mysql_fetch_row($q);
                 $bpc = getconf("buckperchip");
                //echo($r[0]."-".$bpc."-");
				 $r[0]=sprintf("%.2f",$r[0]);
                $r[0]=number_format(($r[0]*$bpc),2);

            	echo("Hello
                <a class=\"main\" href=\"/admin/megastudio.php\">".$_SESSION['first']."</a>!&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$lang["models"]["Your Earning In This Period:"]." ".$r[0]." ".$lang["chips"]."
                </td>
				<!--<td style=\"padding-left:20px;\"><button class=\"add_btn\">Add credits</button></td>-->
				<td style=\"padding-left:20px;\"><div class=\"but_logout\"><a href=\"logout.php\" >Logout</a></div></td></tr></table>



                </form> </div>");
}
function header_admin_studio_help()
{ global $lang;
 echo("<div id=\"main_menu\">
                <form method=\"post\" action=\"index.php\">
                  <table style=\"width:96%\">
                  <tr>
                  <td style=\"width:100px; background-image: url(images/logo.png); background-repeat: no-repeat; background-position: 50px -2px;padding: 0px 7px 0px 102px; height: 31px; color:#ffffff\"><a href=\"../index.php\" class=\"nonea\" style=\"font-size: 14pt;font-family: 'Gabriola';\">".$lang["LiveCamTouch.com"]."</a></td>
                  <td style=\"width:100%; text-align: right;color: #ff0000; padding-right: 7px\">"); if (isset($err)){echo $err; $err="";}echo("</td>
                  <td style=\"padding-right:50px; word-break: normal;color:#ffffff;font-size:9pt\" nowrap=\"nowrap\">");                  

            	echo("Hello
                <a class=\"main\" href=\"/admin/indexmm-studio.php\">".$_SESSION['first']."</a>!
                </td>				
				<td style=\"padding-left:20px;\"><div class=\"but_logout\"><a href=\"logout.php\" >Logout</a></div></td></tr></table>



                </form> </div>");
}
function studio_menu() {
    global $lang;

      ?>
    <script language="JavaScript" type="text/javascript" src="Scripts/show-submenu.js"></script>
    <table   cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="extended" style="border-radius:5px 0px 0px 0px;"><a class="nonea blacktext" href="studioaccount.php"><?=$lang["studio"]["Home page"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studiophotos.php"><?=$lang["models"]["Documents"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studiodetails.php"><?=$lang["studio"]["Account information"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="messenger-up.php"><?=$lang["studio"]["Messages"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studiopayments.php"><?=$lang["models"]["My statistic"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studiostatisticpayments.php"><?=$lang["models"]["Previous payments"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studioperformersignup.php"><?=$lang["studio"]["Add models"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studioperformers.php"><?=$lang["studio"]["All models"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studiopaymentmethode.php"><?=$lang["studio"]["Payment method"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studiopupdate.php"><?=$lang["models"]["Change password"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studioupdateemail.php"><?=$lang["models"]["Change email"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studiopupdatemanpass.php"><?=$lang["studio"]["Studio administrator"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studionews.php"><?=$lang["studio"]["News"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="studiorules.php"><?=$lang["studio"]["Rules"]?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="performerhints.php" style="display: block; "><?=$lang["models"]["Hints"]?></a>
                            </td>

                            <td class="extended"><a href="studiofaq.php"><?=$lang["models"]["Tech support"]?></a>
                                <ul>
								<li class="global_p1"><a class="nonea " href="studiofaq.php" style="display: block; width: 150px;">&nbsp;<?=$lang["FAQ"]?></a></li>
								<li class="global_p1"><a class="nonea " href="studiotechsuportonline.php">&nbsp;<?=$lang["models"]["Online support"]?></a></li>
                                <!--<p class="global_p1"><a class="nonea " href="studiotechsuport.php">&nbsp;<?=$lang["models"]["Message to support"]?></a></p>-->
                                </ul>
                            </td>
                        </tr>
                        <!--<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="index.php">Logout</a></h3>
                            </td>
                        </tr>-->

                    </table>
<?php }

//заполнение футера

function left() {
    ?>
    <a href="#">Кто в онлайне</a>&nbsp;<a href="#">Поиск</a>

    <?php
}

function right() {
    if (!isset($_SESSION['usertype'])) {
        ?>
        Логин&nbsp;<input type="text" size="20">&nbsp;Праоль<input type="text" size="20">&nbsp;<input type="button" value="Войти">
        <?php
    } else {
        ?>
        <form name="logout" action="logout.php">
            <input type="submit" value="Выйти"/>
        </form>
        <?php
    }
}

// ########################################################################
function setDesireItem($pid, $link, $sign) {
    $link = reg_test_factory($link,REG_TEST_STRIP_TAGS,"<iframe><a><img><b><i><video>");
    $sign = reg_test_factory($sign,REG_TEST_STRIP_TAGS,"<a><b><i><font>");
    $date = date("Y-m-d h:i:s");
    $res = mysql_query("INSERT INTO desires(pid, link, sign, date) VALUES('$pid', '$link', '$sign', '$date')");
}

function getDesires($pid) {
    $desires = array();
    $i = 0;
    $res = mysql_query("SELECT * FROM desires WHERE pid='".mysql_real_escape_string($pid)."'");
    while ($row = mysql_fetch_assoc($res)) {
        $desires[$i]['id'] = $row['id'];
        $desires[$i]['link'] = $row['link'];
        $desires[$i]['sign'] = $row['sign'];
        $desires[$i]['date'] = $row['date'];
        $i++;
    }
    return $desires;
    //echo json_encode(json_fix_cyr($desires));
}

// ###############==- Language modification -==##############################
function stripslashes_deep($value) {
    $value = is_array($value) ?
            array_map('stripslashes_deep', $value) :
            stripslashes($value);
    return $value;
}

if (get_magic_quotes_gpc()) {
    $_POST = stripslashes_deep($_POST);
    $_GET = stripslashes_deep($_GET);
}

$languages_abbreviation = array("en","ru"); // use here the abbreviation of the language that will be used for images as described in image_list.txt; Eg: French => fr
$languages = array("English");
// do not change anything below
$l = $_POST['language'];
if ((in_array($l, $languages_abbreviation)) && (isset($_POST['language']))) {
    setcookie("langs", $l, time() + 3333333, "/");

    if ($_COOKIE['langs'] != $l) {
        $_COOKIE['langs'] = $l;
    }

}

if (in_array($_COOKIE['langs'], $languages_abbreviation)) {
    include "lang." . $_COOKIE['langs'] . ".php";
} else {
    include "lang.en.php";
}
// ##########################################################################
$whiteip = getconf('whiteip');
$dsfaasdf = explode('-', $whiteip); #echo ($_SERVER['REMOTE_ADDR']==$dsfaasdf[0]); echo "'".$_SERVER['REMOTE_ADDR']."' '".$dsfaasdf[0]."'"; exit;
if (!in_array($_SERVER['REMOTE_ADDR'], $dsfaasdf)) { // kkt
    if (!preg_match('/admin|subadm|assist/', $_SERVER['REQUEST_URI'])) { // check
        $tari = getconf('bannedc');
        $kkt = explode('-', $tari);
        if (in_array(wherefrom($_SERVER['REMOTE_ADDR']), $kkt)) {
            header("HTTP/1.1 301");
            header("Location: " . getconf('bannedurl'));
            exit();
        }
    } //check
    $bannedip = getconf('bannedip');
    $listt = explode('-', $bannedip);
    if (in_array($_SERVER['REMOTE_ADDR'], $listt)) {
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>500 Internal Server Error</title>
</head><body>
<h1>Internal Server Error</h1>
<p>The server encountered an internal error or
misconfiguration and was unable to complete
your request.</p>
<p>Please contact the server administrator,
 webmaster@localhost and inform them of the time the error occurred,
and anything you might have done that may have
caused the error.</p>
<p>More information about this error may be available
in the server error log.</p>
<p>Additionally, a 404 Not Found
error was encountered while trying to use an ErrorDocument to handle the request.</p>
</body></html>
';
        exit();
    }
} // kkt
$title = " - " . $lang["LiveCamTouch online adult chat"];
$width = 400;

$bottom1 = "<div align=\"center\" class=\"whitet3\"><a href=\"performersaccount.php\" class=\"whitet3\">" . $lang["Performer's area"] . " </a> | <a href=\"performersignup.php\" class=\"whitet3\">\$\$" . $lang["Performers wanted"] . "\$\$</a> | <a href=\"privacy.php\" class=\"whitet3\">" . $lang["Privacy policy"] . "</a> | <a href=\"terms.php\" class=\"whitet3\">" . $lang["Terms &amp; conditions"] . "</a> | <a href=\"myaccount.php\" class=\"whitet3\">" . $lang["Log in"] . "</a> | <a href=\"studios.php\" class=\"whitet3\">" . $lang["Studios"] . "</a> | <a href=\"affiliates.php\" class=\"whitet3\">" . $lang["Affiliates"] . "</a> | <a href=\"contactus.php\" class=\"whitet3\">" . $lang["Contact us"] . "</a></div>";


$bottom1 = "<a href=\"managers.php\">Limited Login</a> &nbsp;|&nbsp; <a href=\"performerlogin.php\">" . $lang["Performer's area"] . "</a> &nbsp;|&nbsp; <a href=\"performersignup.php\">\$\$" . $lang["Performers wanted"] . "\$\$</a> &nbsp;|&nbsp; <a href=\"privacy.php\">" . $lang["Privacy policy"] . "</a> &nbsp;|&nbsp; <a href=\"terms.php\">" . $lang["Terms &amp; conditions"] . "</a> &nbsp;|&nbsp; <a href=\"login.php\">" . $lang["Log in"] . "</a> &nbsp;|&nbsp; <a href=\"studios.php\">" . $lang["Studios"] . "</a> &nbsp;|&nbsp; <a href=\"affiliates.php\">" . $lang["Affiliates"] . "</a> &nbsp;|&nbsp; <a href=\"contactus.php\">" . $lang["Contact us"] . "</a><br />
<br />
";


$meta_desc = 'Free live adult Web Cams and chat. Nude sexy camgirls, teens, lesbians, voyeur, couples, amateur video and chat.';
$meta_keywords = 'webcam, webcams, cam, cams, live, girls, free, video, women, lesbians, porn, porno, teen, teens, men, guys, nude, private, pussy, sexy, webcam, camgirls, home cams, video chat, free video, teen, sex, xxx, adult, naked';

function getconf($description) {
    $query = mysql_query("SELECT `content` FROM `config` WHERE `description` = '$description'");
    $row = mysql_fetch_row($query);
    return $row[0];
}

function updateconf($what, $with) {
    mysql_query("UPDATE `config` SET `content` = '$with' WHERE `description` = '$what'");
}

function admin_mail() {
    return getconf('admin');
}

function restricted_access() {
    session_start();
		$_SESSION['this_i'] = true;
    if (isset($_SESSION['HTTP_USER_AGENT'])) {
        if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'] . 'fsdjghwkj54')) {
            @session_destroy();
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT'] . 'fsdjghwkj54');
    }

    if (($_SESSION['logged'] != 1) || ($_SESSION['type'] != 'admin')) {
        session_destroy();
        header("Location: index.php");
    } else {
        session_regenerate_id();
    }
}

function fver($name) {
    $users = array("root", "admin", "administrator", "mysql", "php", "mail", "email", "var", "usr");
    if (in_array($name, $users)) {
        return true;
    } else {
        return false;
    }
}

function good_number($val) {
    if (preg_match("/^[0-9]{1,5}$/", $val))
        return true;
    else
        return false;
}

function good_big_number($val) {
    if (preg_match("/^[0-9]{1,9}$/", $val))
        return true;
    else
        return false;
}

function goodusername($usr) {
    if (preg_match("/^[A-Z0-9]{4,24}$/i", $usr)) {
        return true;
    } else
        return false;
}

function allowed_cntry($ip) {
    include_once "inc.php";
    //include("geo/geoipcity.inc");
	$gi = geoip_open("./Scripts/GeoIP.dat", GEOIP_STANDARD);
    $thiscntry = geoip_country_code_by_addr($gi, $ip);

    $query = mysql_query("SELECT `content` FROM `config` WHERE `description` = 'bannedc'");
    $row = mysql_fetch_row($query);
    $blist = explode("-", $row[0]);
    foreach ($blist as $k) {
        if ($k == $thiscntry) {
            return false;
        }
    }
    geoip_close($gi);
    return true;
}

function allowed_perf($ip, $id) {
    include_once "inc.php";
    $gi = geoip_open("Scripts/GeoIP.dat", GEOIP_STANDARD);
	//include("geo/geoipcity.inc");
	//$gi = geoip_open("geo/GeoLiteCity.dat",GEOIP_STANDARD);
    $thiscntry = geoip_country_code_by_addr($gi, $ip);
    $query = mysql_query("SELECT `bans` FROM `performers` WHERE `id` = '$id'");
    $row = mysql_fetch_row($query);
    $blist = explode("-", $row[0]);
    foreach ($blist as $k) {
        if ($k == $thiscntry) {
            return false;
        }
    }
    geoip_close($gi);
    return true;
}

function wherefrom($ip) {
    include_once "inc.php";
    $gi = geoip_open("./Scripts/GeoIP.dat", GEOIP_STANDARD);
	//include("geo/geoipcity.inc");
	//$gi = geoip_open("geo/GeoLiteCity.dat",GEOIP_STANDARD);
    $thiscntry = geoip_country_name_by_addr($gi, $ip);
    $thiscntry1 = geoip_region_by_addr($gi, $ip);
    //http://www.geoiptool.com/en/?IP=46.119.93.254
    //$thiscntry1 = _get_region($gi, $ip);
    geoip_close($gi);
    if ($thiscntry == '')
        return 'NA';
    return $thiscntry.$thiscntry1;
}

function isvalidmail($email) {
    if (preg_match("/^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,6}$/i", $email)) {
        return true;
    } else
        return false;
}

function cy() {
    $number = getconf("currency");
    if ($number == 1) {
        return "$";
    }
    if ($number == 2) {
        return "&euro;";
    }
    if ($number == 3) {
        return "&pound;";
    }
}
function restricted_area5() {
    @session_start();
    if (isset($_SESSION['HTTP_USER_AGENT'])) {
        if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta')) {
            @session_destroy();
            header("Location: manageraccount.php");
            exit;
        }
    } else {
        $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta');
    }

    if ($_SESSION['usertype'] != 'studiomanager') {
        header("Location: index.php");
        exit;
    } else {
        //session_regenerate_id();
    }
}

/*function restricted_area1() {
    @session_start();
    if (isset($_SESSION['HTTP_USER_AGENT'])) {
        if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta')) {
            @session_destroy();
            header("Location: myaccount.php");
            exit;
        }
    } else {
        $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta');
    }

    if ($_SESSION['usertype'] != 'users') {
        setcookie("Gowhere2", $_SERVER['REQUEST_URI'], time() + 600);
        header("Location: index.php");
        exit;
    } else {
        //session_regenerate_id();
        $query = mysql_query("SELECT `chips` FROM `users` WHERE `id` = '$_SESSION[userid]'");
        $rsdfz = mysql_fetch_row($query);
        global $availablechips;
        $availablechips = $rsdfz[0];
    }
}
*/
function restricted_area2() {
    @session_start();
    if (isset($_SESSION['HTTP_USER_AGENT'])) {
        if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta')) {
            @session_destroy();
            header("Location: performersaccount.php");
            exit;
        }
    } else {
        $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta');
    }

    if ($_SESSION['usertype'] != 'performers') {
    header("Location: index.php");
        exit;
    } else {
        //@session_regenerate_id();
    }
}

function restricted_admin() {
    @session_start();
    if (isset($_SESSION['HTTP_USER_AGENT'])) {
        if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta')) {
            @session_destroy();
            header("Location: admin.php");
            exit;
        }
    } else {
        $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta');
    }

    if ($_SESSION['usertype'] != 'admin') {
        header("Location: index.php");
        exit;
    } else {
        //session_regenerate_id();
    }
}

function restricted_area3() {
    @session_start();
    if (isset($_SESSION['HTTP_USER_AGENT'])) {
        if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta')) {
            @session_destroy();
            header("Location: studioaccount.php");
            exit;
        }
    } else {
        $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta');
    }

    if ($_SESSION['usertype'] != 'studios') {
        header("Location: index.php");
        exit;
    } else {
        //session_regenerate_id();
    }
}

function restricted_area4() {
    @session_start();
    if (isset($_SESSION['HTTP_USER_AGENT'])) {
        if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta')) {
            @session_destroy();
            header("Location: affiliatesaccount.php");
            exit;
        }
    } else {
        $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT'] . 'w36tgsdfyg235eratgfdfhyhae45ta');
    }

    if ($_SESSION['usertype'] != 'affiliates') {
        header("Location: index.php");
        exit;
    } else {
        //session_regenerate_id();
    }
}

function showerror() {
    global $error;
    if ($error) {
        echo '<div class="text" align="center">';
        foreach ($error as $k) {
            echo "<span class=\"error\">" . $lang["Error"] . ":</span> $k<br>";
        }
        echo '</div>
<a href="javascript:history.go(-1)" class="text">' . $lang["Go back"] . '</a><br>';
    }
}

function safe_string($go) {
    //return mysql_real_escape_string(((($go))));
    return mysql_real_escape_string(htmlentities(trim(stripslashes($go)), ENT_QUOTES, 'UTF-8'));
}

function checkData($mydate) {
    list($yy, $mm, $dd) = explode("-", $mydate);
    if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd)) {
        return checkdate($mm, $dd, $yy);
    }
    return false;
}

function vmail($mailadd) {
    if (!preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", $mailadd)) {
        $ret = false;
    } else {
        $ret = true;
    }
    return $ret;
}

function howmany($no) {
    if (($no >= 1) && ($no <= 8)) {
        return 1;
    }
    if (($no >= 9) && ($no <= 15)) {
        return 2;
    }
    if (($no >= 16) && ($no <= 22)) {
        return 3;
    }
    if (($no >= 23) && ($no <= 29)) {
        return 4;
    }
    if (($no >= 30) && ($no <= 33)) {
        return 1;
    }
    if (($no >= 34) && ($no <= 37)) {
        return 2;
    }
    if (($no >= 38) && ($no <= 41)) {
        return 3;
    }
    if (($no >= 42) && ($no <= 45)) {
        return 4;
    }
}

function makeThumb($max_width, $max_height, $upfile, $dstfile) {
    $size = GetImageSize($upfile);
    $width = $size[0];
    $height = $size[1];

    $x_ratio = $max_width / $width;
    $y_ratio = $max_height / $height;

    if (($width <= $max_width) && ($height <= $max_height)) {
        $tn_width = $width;
        $tn_height = $height;
    } elseif (($x_ratio * $height) < $max_height) {
        $tn_height = ceil($x_ratio * $height);
        $tn_width = $max_width;
    } else {
        $tn_width = ceil($y_ratio * $width);
        $tn_height = $max_height;
    }

    $src = @ImageCreateFromJpeg($upfile);
    $dst = @ImageCreateTrueColor($tn_width, $tn_height);
    @imagecopyresampled($dst, $src, 0, 0, 0, 0, $tn_width, $tn_height, $width, $height);
    @ImageJpeg($dst, $dstfile, 80);
}

function privatecosts($performer) {
    $customcost = getconf('customcost');

    if ($customcost == 'yes') {
        $tutz = mysql_query("SELECT `privatechips` FROM `performers` WHERE `id` = '$performer'");
        $rw = mysql_fetch_row($tutz);
        return $rw[0];
    } //end if custom cost
    else {
        return getconf('chipperminute');
    }
}

// end privatecosts

function publiccosts($performer) {
    $customcost = getconf('customcost');

    if ($customcost == 'yes') {
        $tutz = mysql_query("SELECT `multiplechips` FROM `performers` WHERE `id` = '$performer'");
        $rw = mysql_fetch_row($tutz);
        if (good_number($rw[0])) {
            return $rw[0];
        } else {
            return getconf('chipperminutepublic_private');
        }
    } //end if custom cost
    else {
        return getconf('chipperminutepublic_private');
    }
}

// end publiccosts

function videoc($perf) {
    $query = mysql_query("SELECT `pvc` FROM `performers` WHERE `id` = '$perf'");
    $row = mysql_fetch_row($query);
    return $row[0];
}

function videoc1($id) {
    if (getconf('customcost') == 'yes') {
        $query = mysql_query("SELECT `cost` FROM `videos` WHERE `id` = '$id'");
        $row = mysql_fetch_row($query);
        return $row[0];
    } else {
        return getconf('pvc');
    }
}

function studiochips($studioid) {
$perioddates=get_now_period()[0];
$perioddatee=get_now_period()[1];
    $query = mysql_query("SELECT SUM(sessions.earnedchips+sessions.studioerned) FROM sessions, performers WHERE sessions.performer=performers.id AND performers.studioid=".mysql_real_escape_string($studioid)." AND (date BETWEEN $perioddates AND $perioddatee)");
    $sregdf = mysql_fetch_row($query);
    return $sregdf[0];
}

function studiopercentage($studioid) {
    $query = mysql_query("SELECT `percentage` FROM `studios` WHERE `id` = '$studioid'");
    $row = mysql_fetch_row($query);
    return $row[0];
}

function orlist($studioid) {
    $query = mysql_query("SELECT `id` FROM `performers` WHERE `studioid` = '$studioid'");
    while ($row = mysql_fetch_assoc($query)) {
        $wtf .= "`id` = '$row[id]' OR ";
    }
    return substr($wtf, 0, -4);
}

function orlist2($studioid) {
    $query = mysql_query("SELECT `id` FROM `performers` WHERE `studioid` = '$studioid'");
    while ($row = mysql_fetch_assoc($query)) {
        $wtf .= "`performer` = '$row[id]' OR ";
    }
    if (mysql_num_rows($query) == 0) {
        return "1=2";
    } else {
        return substr($wtf, 0, -4);
    }
}

function perchips($perfid) {
    $query = mysql_query("SELECT `studioid`,`chips` FROM `performers` WHERE `id` = '$perfid'");
    $row = mysql_fetch_row($query);
    if ($row[0] == 0) {
        return $row[1];
    } else {
        return ceil(($row[1] / 100) * (100 - studiopercentage($row[0])));
    }
}

function addtoaff($usedtime, $ppm, $sessid, $userid) {
    // ################### AFFILIATES ####################
    $ergfds = mysql_query("SELECT `referrer`,`aff2` FROM `users` WHERE `id` = '$userid' AND `aff3` = '0'");
    echo mysql_error();
    $sedf = mysql_fetch_row($ergfds);

    $dfrsg = mysql_query("SELECT `commission` FROM `affiliates` WHERE `id` = '$sedf[0]' AND `status` != 'suspended'");
    $sgfd = mysql_fetch_row($dfrsg);
    // if ($sedf[1] != $sgfd[0]) {return FALSE;}
    if (mysql_num_rows($dfrsg) != 1) {
        return false;
    }

    if ($sedf[1] == 1) { // start comm type
        if ($usedtime == 0) {
            return false;
        }

        function badmail2($where) {
            $adminmail = getconf("admin");
            global $code, $status, $id;
            $headers = 'MIME-Version: 1.0' . "\n";
            $headers .= 'Content-type: text/html;' . "\n";
            $headers .= "From: \"LiveCamTouch Auto-mail\" <$adminmail>\n";
            $rtseg = date("r");
            $content = "
	URL: include.php ( http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI] ) <br>\n
	IP: $_SERVER[REMOTE_ADDR] <br>\n
	Date: $rtseg <br>\n
	Where: $where";
            email($adminmail, "Error/HackAttempt", $content, $headers);
        }

        $today = date("U");
        $plm = $ppm;
        $affpercentage = getconf('affperc');
        $buckperchip = getconf('buckperchip');
        $ppm = $ppm * $buckperchip;

        $priceperminute = round((($ppm / 100) * $affpercentage), 2);

        $earnednow = round(($priceperminute * $usedtime / 60), 2);

        mysql_query("UPDATE `affiliates` SET `chips` = `chips` + '$earnednow', `totalchips` = `totalchips` + '$earnednow' WHERE `id` = '$sedf[0]'");

        mysql_query("INSERT INTO `affearnings` (`affid`, `date`, `earnedmoney`, `seconds`, `sessid`, `cpm`, `userid`) VALUES ('$sedf[0]', '$today', '$earnednow', '$usedtime', '$sessid', '$plm', '$userid')");
        echo mysql_error();
    } // end comm type
    else { // start comm type
        if ($usedtime == 0) {
            $today = date("U");
            $plm = $ppm;
            $affpercentage = getconf('affperc2');
            $buckperchip = getconf('buckperchip');
            $ppm = $ppm * $buckperchip;

            $priceperminute = round((($ppm / 100) * $affpercentage), 2);

            $earnednow = $priceperminute;

            mysql_query("UPDATE `affiliates` SET `chips` = `chips` + '$earnednow', `totalchips` = `totalchips` + '$earnednow' WHERE `id` = '$sedf[0]'");

            mysql_query("INSERT INTO `affearnings` (`affid`, `date`, `earnedmoney`, `seconds`, `sessid`, `cpm`, `userid`) VALUES ('$sedf[0]', '$today', '$earnednow', 'n/a', 'n/a', '$plm', '$userid')");
            if (mysql_error()) {
                badmail2(mysql_error());
            }

            mysql_query("UPDATE `users` SET `aff3` = '1' WHERE `id` = '$userid'");
        }
    } // end comm type
    // ################### AFFILIATES ####################
}

//end addtoaff

function affcom($affid) {
    $query = mysql_query("SELECT `commission` FROM `affiliates` WHERE `id` = '$affid'");
    $row = mysql_fetch_row($query);
    return $row[0];
}

function cy2() {
    $number = getconf("currency");
    if ($number == 1) {
        return "USD";
    }
    if ($number == 2) {
        return "EUR";
    }
    if ($number == 3) {
        return "GBP";
    }
}

function addstat($perfid, $what, $value, $value2 = 0) { // start function
    $wh = array('rating', 'online', 'video', 'paidvideo', 'nude');

    $check1 = mysql_query("SELECT `first` FROM `performers` WHERE `id` = '$perfid'");
    if (mysql_num_rows($check1) == 1) {
        $ch1 = true;
    }

    if ((in_array($what, $wh)) && ($ch1) && (good_number($value)) && (good_number($value2))) { // start
        $year = date("Y");
        $month = date("n");
        $today = date("d");
        $now = strtotime("$year-$month-$today");

        $query = mysql_query("SELECT `id` FROM `statistics` WHERE `day` = '$now' AND `type` = '$what' AND `userid` = '$perfid'");
        if (mysql_num_rows($query) == 0) {
            mysql_query("INSERT INTO `statistics` (`day`, `userid`, `type`, `value`, `value2`) VALUES ('$now', '$perfid', '$what', '$value', '$value2')");
        } else {
            mysql_query("UPDATE `statistics` SET `value` = `value` + '$value', `value2` = `value2` + '$value2' WHERE `day` = '$now' AND `type` = '$what' AND `userid` = '$perfid'");
        }
    } //end
}

//end function

function check_ip_address($checkip, $jolly_char = '') {
    if ($jolly_char == '.') // dot ins't allowed as jolly char
        $jolly_char = '';

    if ($jolly_char != '') {
        $checkip = str_replace($jolly_char, '*', $checkip); // replace the jolly char with an asterisc
        $my_reg_expr = "/^[0-9\*]{1,3}\.[0-9\*]{1,3}\.[0-9\*]{1,3}\.[0-9\*]{1,3}$/";
        $jolly_char = '*';
    } else
        $my_reg_expr = "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/";

    if (preg_match($my_reg_expr, $checkip)) {
        for ($i = 1; $i <= 3; $i++) {
            if (!(substr($checkip, 0, strpos($checkip, ".")) >= "0" && substr($checkip, 0, strpos($checkip, ".")) <= "255")) {
                if ($jolly_char != '') { // if exists, check for the jolly char
                    if (substr($checkip, 0, strpos($checkip, ".")) != $jolly_char)
                        return false;
                } else
                    return false;
            }

            $checkip = substr($checkip, strpos($checkip, ".") + 1);
        }

        if (!($checkip >= "0" && $checkip <= "255")) { // class D
            if ($jolly_char != '') { // if exists, check for the jolly char
                if ($checkip != $jolly_char)
                    return false;
            } else
                return false;
        }
    } else
        return false;

    return true;
}

function deundepanaunde($deunde, $panaunde, $unde) {
    $dsafsd = explode($deunde, $unde);
    $wertwe = explode($panaunde, $dsafsd[1]);
    return $wertwe[0];
}

if (!function_exists('stream_get_contents')) {

    function stream_get_contents($handle) {
        $b = '';
        if ($handle) {
            while (!feof($handle)) {
                $buffer = fgets($handle, 4096);
                $b .= $buffer;
            }
            return($b);
        }
    }

}

function getbadwords1() {
    $handle = fopen("censure.xml", "r");
    $contents = stream_get_contents($handle);
    fclose($handle);

    $list = explode(' type="censure"/>', $contents);
    unset($list[count($list) - 1]);
    $ret = array();

    foreach ($list as $k) {
        $ret[] = deundepanaunde('<word str="', '"', $k);
    }
    return $ret;
}

function getbadwords2() {
    $handle = fopen("censure.xml", "r");
    $contents = stream_get_contents($handle);
    fclose($handle);

    $sdf = explode('type="censure"', $contents);
    $contents = $sdf[count($sdf) - 1];

    $list = explode(' type="ban"/>', $contents);
    unset($list[count($list) - 1]);
    $ret = array();

    foreach ($list as $k) {
        $ret[] = deundepanaunde('<word str="', '"', $k);
    }
    return $ret;
}

function substr_count_array($haystack, $needle) {
    $count = 0;
    foreach ($needle as $substring) {
        $count += substr_count(strtolower($haystack), strtolower($substring));
    }
    return $count;
}

function makeIcons_MergeCenter($src, $dst, $dstx, $dsty) {
    // $src = original image location
    // $dst = destination image location
    // $dstx = user defined width of image
    // $dsty = user defined height of image
    $allowedExtensions = 'jpg jpeg gif png';

    $name = explode(".", $src);
    $currentExtensions = $name[count($name) - 1];
    $extensions = explode(" ", $allowedExtensions);

    for ($i = 0; count($extensions) > $i; $i = $i + 1) {
        if ($extensions[$i] == $currentExtensions) {
            $extensionOK = 1;
            $fileExtension = $extensions[$i];
            break;
        }
    }

    if ($extensionOK) {
        $size = getImageSize($src);
        $width = $size[0];
        $height = $size[1];

        if ($width >= $dstx AND $height >= $dsty) {
            $proportion_X = $width / $dstx;
            $proportion_Y = $height / $dsty;

            if ($proportion_X > $proportion_Y) {
                $proportion = $proportion_Y;
            } else {
                $proportion = $proportion_X;
            }
            $target['width'] = $dstx * $proportion;
            $target['height'] = $dsty * $proportion;

            $original['diagonal_center'] =
                    round(sqrt(($width * $width) + ($height * $height)) / 2);
            $target['diagonal_center'] =
                    round(sqrt(($target['width'] * $target['width']) +
                            ($target['height'] * $target['height'])) / 2);

            $crop = round($original['diagonal_center'] - $target['diagonal_center']);

            if ($proportion_X < $proportion_Y) {
                $target['x'] = 0;
                $target['y'] = round((($height / 2) * $crop) / $target['diagonal_center']);
            } else {
                $target['x'] = round((($width / 2) * $crop) / $target['diagonal_center']);
                $target['y'] = 0;
            }

            if ($fileExtension == "jpg" OR $fileExtension == 'jpeg') {
                $from = ImageCreateFromJpeg($src);
            } elseif ($fileExtension == "gif") {
                $from = ImageCreateFromGIF($src);
            } elseif ($fileExtension == 'png') {
                $from = imageCreateFromPNG($src);
            }

            $new = ImageCreateTrueColor($dstx, $dsty);

            imagecopyresampled($new, $from, 0, 0, $target['x'], $target['y'], $dstx, $dsty, $target['width'], $target['height']);

            if ($fileExtension == "jpg" OR $fileExtension == 'jpeg') {
                imagejpeg($new, $dst, 70);
            } elseif ($fileExtension == "gif") {
                imagegif($new, $dst);
            } elseif ($fileExtension == 'png') {
                imagepng($new, $dst);
            }
        }
    }
}
function email($to, $subj, $content, $headers)
{
//echo($to." ".$subj." ". $content." ". $headers);
//error_reporting(0);
   /*$to      = 'mas_bk@mail.ru';
$subject = 'the subject';
$message = 'hello test';
$headers = 'From: livecamtouch@mail.ru' . "\r\n" .
    'Reply-To: livecamtouch@mail.ru' . "\r\n" .
    'X-Mailer: PHP/';*/
require_once('PEAR.php');
require_once('Mail.php');
require_once('Mail/mime.php');
//$mail = &Mail::factory('smtp', array('host'=>'smtp.gmail.com', 'port'=>"465", 'auth'=>true, 'username'=>'alx.kas87@gmail.com','password'=>'AK987!@#$%'));
$mail = &Mail::factory("mail");

$_mail = $mail->send($to, $headers, $content);

//echo($to."-".$headers."-".$content);
//echo ($_mail->getMessage());
logoperation(777,0,"send email","email: ".$to." headers:".$headers['From']."-".$headers['To']." content: ".$content,$_SERVER['REMOTE_ADDR']);
}


/*function email($to, $subj, $content, $headers)
{
   /*$to      = 'mas_bk@mail.ru';
$subject = 'the subject';
$message = 'hello test';
$headers = 'From: livecamtouch@mail.ru' . "\r\n" .
    'Reply-To: livecamtouch@mail.ru' . "\r\n" .
    'X-Mailer: PHP/';*/

/*ini_set ( "SMTP", "127.0.0.1" );
date_default_timezone_set('America/New_York');

mail($to, $subj, $content, $headers);
//echo($to." ".$subject." ". $message." ". $headers);
}*/
/*function email12($to, $subj, $content, $headers)
{
     // start smtp
        include 'libmail.php';

        $smtp_server = getconf('smtp_server');
        $smtp_user = getconf('smtp_user');
        $smtp_password = getconf('smtp_password');
        $smtp_port = getconf('smtp_port');
        $from1 = getconf('admin');

        $from_name = deundepanaunde('"', '"', $headers);
        $from_mail = deundepanaunde("<", '>', $headers);
        $from = "$from_name <$from_mail>";

        $host = "mail.example.com";
        $username = "smtp_username";
        $password = "smtp_password";

        $headers = array ('From' => $from,
            'To' => $to,
            'Subject' => $subj);

        $mail=new Mail("utf8");
$mail->Subject($subj);
$mail->From($from1);
$mail->ReplyTo($from1);
$mail->To($to);
$mail->Body($content, "html");
$mail->BuildMail();
$mail->smtp_on($smtp_server, $smtp_user, $smtp_password);
echo($smtp_server. $smtp_user. $smtp_password);
$mail->Send();

} //mail

function email1($to, $subj, $content, $headers)
{
    $type = getconf('enable_smtp');
    if ($type != 1) {
        mail($to, $subj, $content, $headers);
    } else { // start smtp
        $kkt = (explode('/', $_SERVER["SCRIPT_FILENAME"], -1));
        $new = implode('/', $kkt) . '/Scripts/pear';
        ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $new);
        require_once "Scripts/pear/Mail.php";
        include('Scripts/pear/Mail/mime.php');

        $smtp_server = getconf('smtp_server');
        $smtp_user = getconf('smtp_user');
        $smtp_password = getconf('smtp_password');
        $smtp_port = getconf('smtp_port');

        $from_name = deundepanaunde('"', '"', $headers);
        $from_mail = deundepanaunde("<", '>', $headers);
        $from = "$from_name <$from_mail>";

        $host = "mail.example.com";
        $username = "smtp_username";
        $password = "smtp_password";

        $headers = array ('From' => $from,
            'To' => $to,
            'Subject' => $subj);

        $mime = new Mail_mime();

        $mime->setTXTBody(strip_tags($content));
        $mime->setHTMLBody($content);

        $body = $mime->get();
        $headers = $mime->headers($headers);

        $smtp = &Mail::factory('smtp',
            array ('host' => $smtp_server,
                'auth' => true,
                'port' => $smtp_port,
                'username' => $smtp_user,
                'password' => $smtp_password));
        //print_r($smtp);
        if (PEAR::isError($smtp)) {
            echo("<p>" . $smtp->getMessage() . "</p>");
        }
        //echo($to. $headers. $body);
        echo $mail = $smtp->send($to, $headers, $body);

        if (PEAR::isError($mail)) {
            echo("<p>" . $mail->getMessage() . "</p>");
        }
    } //end smt
} //mail*/
function rand_bg() {
    return mt_rand(1, 5);
}

function limg($img) {
    return $img;
}

function yearDiff($date) {
    // получает количество секунд между двумя датами
    $curDate = time();
    $tempDate = strtotime($date);

    $timedifference = $curDate - $tempDate;
    //$retval=0;
	$retval = bcdiv($timedifference, 31536000);
    return $retval;
}

function himg() {
    if ($_SESSION['usertype'] != '') {
        return '<img src="images/theader' . mt_rand(1, 2) . '.jpg" alt="" />';
    } else {
        return '<a href="usersignup.php"><img src="images/theader10.jpg" alt="Join Now" border="0" /></a>';
    }
}

function show_left() {
    $tri = wherefrom($_SERVER['REMOTE_ADDR']);
    $query = mysql_query("SELECT `id`, `nickname` FROM `performers` WHERE `S1` != '' AND `bans` NOT LIKE '%$tri%' ORDER BY RAND() LIMIT 8");
    while ($row = mysql_fetch_assoc($query)) {
        if (file_exists("profileimages/main-$row[id].jpg")) {
            $img = "profileimages/main-$row[id].jpg";
        } else {
            $img = "images/newp.jpg";
        }
        echo '
            <div style="float: left; width: 125px; padding-top: 10px;">
              <table border="0" cellpadding="0" cellspacing="0" style="border-bottom:  1px solid #444444;">
                <tr>
                  <td style=" background-image: url(images/thumb_01.jpg); width: 109px; height: 6px;"></td>

                </tr>
                <tr>
                  <td height="75" valign="middle" style="background-image: url(images/thumb_02.jpg); text-align: center;" ><a href="profile.php?id=' . $row['id'] . '"><img src="' . $img . '" alt="" border="0"></a></td>
                </tr>
                <tr>
                  <td style=" background-image:url(images/thumb_03.jpg); width: 109px; " valign="top"><div align="center" style="padding-top: 6px; height: 16px;"><a href="profile.php?id=' . $row['id'] . '" class="yelb">' . $row['nickname'] . '</a></div>
                    <!-- <div class="chatlinks" style="text-align: center; padding-top: 12px; height: 35px;"><a href="#" class="chatlinks">Free chat</a><br />Private chat</div> -->
                  </td>
                </tr>
              </table>
            </div>';
    }
}
function affiliatess_menu() {
  global $lang;
  
?><table id="top_menu" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="extended"><a class="nonea blacktext" href="affiliatessaccount.php"><?= $lang["My account"] ?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="affssstatss.php"><?= $lang["My payments &amp; earnings"] ?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="affiliatesspaymentmethode.php"><?=$lang["studio"]["Payment method"]?></a>
                            </td>

							<td class="extended"><a class="nonea blacktext" href="affiliatessdetails.php"><?=$lang["member"]["Edit my profile"]?></a>
							</td>

							<td class="extended"><a class="nonea blacktext" href="affiliatesspupdate.php"><?=$lang["member"]["Change password"]?></a>
							</td>

							<td class="extended"><a class="nonea blacktext" href="affiliatesschangeemail.php"><?=$lang["member"]["Change email"]?></a>
							</td>

							<td class="extended"><a class="nonea blacktext" href="affssreklama.php"><?=$lang["member"]["Banner"]?></a>
							</td>

							<td class="extended"><a class="nonea blacktext" href="affsspromo.php"><?=$lang["member"]["Promo tools"]?></a>
							</td>

							<td class="extended"><a class="nonea blacktext" href="affssicq.php">icq</a>
							</td>

							<td class="extended"><a class="nonea blacktext" href="afftraffic.php">traffic</a>
							</td>
						</tr>
						
                    </table><?php

}

function affiliates_menu() {
  global $lang;
  
?><table  cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="extended"><a class="nonea blacktext" href="affiliatesaccount.php"><?= $lang["My account"] ?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="affstats.php"><?= $lang["My payments &amp; earnings"] ?></a>
                            </td>

                            <td class="extended"><a class="nonea blacktext" href="affiliatespaymentmethode.php"><?=$lang["studio"]["Payment method"]?></a>
                            </td>
                        </tr>
						
                    </table><?php

}
function admin_menu($type=1) {
@session_start();
$type=$_SESSION['admintype'];
  global $lang;
  switch($type)
  {
    case 1:
?><table  style="width: 200px;"  cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="extended" style="border-radius:5px 0px 0px 0px;"><h3><a class="nonea blacktext" href="/admin/admin.php"><?=$lang["Statistics"]?></a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended1"><h3><?= $lang["Personal"] ?></h3>
                                <div>
                                <p class="global_p1"><a class="nonea " href="/admin/performers.php">&nbsp;<?=$lang["Performers"]?></a></p>
                                <p class="global_p1"><a class="nonea " href="/admin/users.php">&nbsp;<?=$lang["Users"]?></a></p>
                                <p class="global_p1"><a class="nonea " href="/admin/studios.php">&nbsp;<?=$lang["Studios"]?></a></p>
								<p class="global_p1"><a class="nonea " href="/admin/megastudios.php">&nbsp;<?=$lang["Megastudios"]?></a></p>
								<p class="global_p1"><a class="nonea " href="/admin/allsupports.php">&nbsp;<?=$lang["Supports"]?></a></p>
								<p class="global_p1"><a class="nonea " href="/admin/deleted-account.php">&nbsp;<?=$lang["studio"]["Deleted"]?></a></p>
                                <!--<p class="global_p1"><a class="nonea " href="affiliates.php">&nbsp;<?=$lang["Affiliates"]?></a></p>-->
                                </div>
                            </td>
                        </tr>
						<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/infserver.php">Server information</a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/configuration.php"><?= $lang["Configuration"] ?></a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/getpays.php"> <?=$lang["Payment types"]?></a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/changeindexmodels.php"><?= $lang["Change index model"] ?></a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/newsletter.php"><?= $lang["Newsletter"] ?></a></h3>
                            </td>
                        </tr>
                         <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/changect.php"><?= $lang["Categories"] ?></a></h3>
                            </td>
                        </tr>
						<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/adminforum.php"><?= $lang["models"]["Forum"] ?></a></h3>
                            </td>
                        </tr>
                         <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/changestyle.php"><?= $lang["Change style"] ?></a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/online-models.php"><?= $lang["Online models"] ?></a></h3>
                            </td>
                        </tr>
						<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/penalties.php"><?= $lang["Penalties"] ?></a></h3>
                            </td>
                        </tr>
						<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/reklama.php"><?= $lang["Reklama"] ?></a></h3>
                            </td>
                        </tr>
                         <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/support-videos.php">Support videos</a></h3>
                            </td>
                        </tr>
						<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/changelang.php"><?= $lang["Change language"] ?></a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended1"><h3>News managment</h3>
                                <div><p class="global_p1"><a class="nonea " href="/admin/newnews.php">&nbsp;New news</a></p>
                                    <p class="global_p1"><a class="nonea " href="/admin/news.php">&nbsp;All news</a></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended1"><h3>Tech support</h3>
                                <div><p class="global_p1"><a class="nonea " href="/admin/indexm.php">&nbsp;Online support</a></p>
								<p class="global_p1"><a class="nonea " href="/admin/adminfaq.php" style="display: block; width: 150px;">&nbsp;<?=$lang["FAQ"]?></a></p>
                                <p class="global_p1"><a class="nonea " href="/admin/indexmsuport.php">&nbsp;Message to support</a></p>
                                </div>
                            </td>
                        </tr>
                    </table><?php
                    break;
         case 2:
?><table  style="width: 200px;"  cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="extended1" style="border-radius:5px 0px 0px 0px;"><h3><?= $lang["Personal"] ?></h3>
                                <div>
                                <p class="global_p1"><a class="nonea " href="/admin/performers.php">&nbsp;<?=$lang["Performers"]?></a></p>
                                <p class="global_p1"><a class="nonea " href="/admin/users.php">&nbsp;<?=$lang["Users"]?></a></p>
                                <p class="global_p1"><a class="nonea " href="/admin/studios.php">&nbsp;<?=$lang["Studios"]?></a></p>
                                <!--<p class="global_p1"><a class="nonea " href="affiliates.php">&nbsp;<?=$lang["Affiliates"]?></a></p>-->
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/changeindexmodels.php"><?= $lang["Change index model"] ?></a></h3>
                            </td>
                        </tr>
                    </table><?php
                    break;
     case 3:
?><table  style="width: 200px;"  cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="extended" style="border-radius:5px 0px 0px 0px;"><h3><a class="nonea blacktext" href="/admin/performers.php"><?= $lang["Performers"] ?></a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/users.php"> <?=$lang["Users"]?></a></h3>
                            </td>
                        </tr>
                    </table><?php
                    break;
      case 4:
?><table  style="width: 200px;"  cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="extended1" style="border-radius:5px 0px 0px 0px;"><h3><?= $lang["Personal"] ?></h3>
                                <div>
                                <p class="global_p1"><a class="nonea " href="/admin/performers.php">&nbsp;<?=$lang["Performers"]?></a></p>
                                <p class="global_p1"><a class="nonea " href="/admin/users.php">&nbsp;<?=$lang["Users"]?></a></p>
                                <p class="global_p1"><a class="nonea " href="/admin/studios.php">&nbsp;<?=$lang["Studios"]?></a></p>
								<p class="global_p1"><a class="nonea " href="/admin/deleted-account.php">&nbsp;<?=$lang["studio"]["Deleted"]?></a></p>
                                <!--<p class="global_p1"><a class="nonea " href="affiliates.php">&nbsp;<?=$lang["Affiliates"]?></a></p>-->
                                </div>
                            </td>
                        </tr>
						<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/newsletter.php"><?= $lang["Newsletter"] ?></a></h3>
                            </td>
                        </tr>
						<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/adminforum.php"><?= $lang["models"]["Forum"] ?></a></h3>
                            </td>
                        </tr>
						<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/online-models.php"><?= $lang["Online models"] ?></a></h3>
                            </td>
                        </tr>
						 <tr>
                            <td class="extended1"><h3>News managment</h3>
                                <div><p class="global_p1"><a class="nonea " href="/admin/newnews.php">&nbsp;New news</a></p>
                                    <p class="global_p1"><a class="nonea " href="/admin/news.php">&nbsp;All news</a></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended1"><h3>Tech support</h3>
                                <div><p class="global_p1"><a class="nonea " href="/admin/adminfaq.php" style="display: block; width: 150px;">&nbsp;<?=$lang["FAQ"]?></a></p>
								<p class="global_p1"><a class="nonea " href="/admin/indexm.php"><?= $lang["admin"]["Support"] ?></a></p>
                                <p class="global_p1"><a class="nonea " href="/admin/indexmsuport.php">&nbsp;Message to support</a></p>
                                </div>
                            </td>
                        </tr>
						
                    </table><?php
                    break;
    case 5:
?><table  style="width: 200px;"  cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="extended1" style="border-radius:5px 0px 0px 0px;"><h3><?= $lang["Personal"] ?></h3>
                                <div>
                                <p class="global_p1"><a class="nonea " href="/admin/performers.php">&nbsp;<?=$lang["Performers"]?></a></p>
                                <p class="global_p1"><a class="nonea " href="/admin/users.php">&nbsp;<?=$lang["Users"]?></a></p>
                                <p class="global_p1"><a class="nonea " href="/admin/studios.php">&nbsp;<?=$lang["Studios"]?></a></p>
                                <!--<p class="global_p1"><a class="nonea " href="affiliates.php">&nbsp;<?=$lang["Affiliates"]?></a></p>-->
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/online-models.php"><?= $lang["admin"]["Online room"] ?></a></h3>
                            </td>
                        </tr>
                    </table><?php
                    break;
                    }

}
function admin_studio_menu() {
  global $lang;
  ?>
    <script language="JavaScript" type="text/javascript" src="Scripts/show-submenu.js"></script>
    <table id="top_menu"  style="width: 200px;"  cellpadding="0" cellspacing="0">
                        
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/megastudio-news.php"><?= $lang["studio"]["News"] ?></a></h3>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended1" style="border-radius:5px 0px 0px 0px;"><h3><?= $lang["Personal"] ?></h3>
                                <ul>
                                <li class="global_p1"><a class="nonea " href="/admin/performers-studio.php">&nbsp;<?=$lang["Performers"]?></a></li>
                                <li class="global_p1"><a class="nonea " href="/admin/accounts-studio.php">&nbsp;<?=$lang["Accuont"]?></a></li>
                                <li class="global_p1"><a class="nonea " href="/admin/studios-studio.php">&nbsp;<?=$lang["Studios"]?></a></li>
								<li class="global_p1"><a class="nonea " href="/admin/referer-studio.php">&nbsp;<?=$lang["Referrer"]?></a></li>
                                </ul>
                            </td>
                        </tr>
						<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/megastudiopaymentmethode.php"><?= $lang["studio"]["Payment method"] ?></a></h3>
                            </td>
                        </tr>
						<tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/megastudiostatistic.php"><?=$lang["models"]["Statistic"]?></a></h3>
                            </td>
                        </tr>						
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/indexmm-studio.php"><?= $lang["models"]["Online support"] ?></a></h3>
                            </td>
                        </tr>
                      
                    </table><?php

}
function admin_studio_help_menu() {
  global $lang;
  ?><table  style="width: 200px;"  cellpadding="0" cellspacing="0">                                                
                        <tr>
                            <td class="extended1" style="border-radius:5px 0px 0px 0px;"><h3><?= $lang["Personal"] ?></h3>
                                <div>
                                <p class="global_p1"><a class="nonea " href="/admin/performers-studio.php">&nbsp;<?=$lang["Performers"]?></a></p>                                
                                <p class="global_p1"><a class="nonea " href="/admin/studios-studio.php">&nbsp;<?=$lang["Studios"]?></a></p>
								<p class="global_p1"><a class="nonea " href="/admin/referer-studio.php">&nbsp;<?=$lang["Referrer"]?></a></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="extended"><h3><a class="nonea blacktext" href="/admin/indexmm-studio.php"><?= $lang["models"]["Online support"] ?></a></h3>
                            </td>
                        </tr>
                      
                    </table><?php

}
function checkadminprivilegies($type)
{
    @session_start();
    //echo("SELECT pages FROM privilegies WHERE id=".$_SESSION['admintype']);
    $rt=mysql_query("SELECT pages FROM privilegies WHERE id=".$_SESSION['admintype']) or die("Err");
    if(mysql_num_rows($rt)>0)
    {
    $fr=mysql_result($rt,0,0);
    if($fr=="")
        return true;
    else
    {
      $er=basename($_SERVER["PHP_SELF"]);
    if(strpos($fr,$er.",")>-1)
    return true;
    else return false;
    }

    }
    else return false;
}
function show_top_left() {
    global $lang;
    if ($_SESSION['usertype'] == 'users') {
        echo '<table align="center" >
            <tr>
              <td style="text-align: center;"><img src="images/i1.jpg" width="20" height="24" /></td>
              <td><a href="myaccount.php" class="whiteb2">' . $lang["My account"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i2.jpg" /></td>
              <td><a href="pupdate.php" class="whiteb2">' . $lang["Password update"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i3.jpg" /></td>
              <td><a href="statement.php" class="whiteb2">' . $lang["My statement"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i4.jpg" /></td>
              <td><a href="orderchips.php" class="whiteb2">' . $lang["Order chips"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i5.jpg" /></td>
              <td><a href="messenger-u.php" class="whiteb2">' . $lang["Messenger"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i6.jpg" /></td>
              <td><a href="logout.php" class="whiteb2">' . $lang["Logout"] . '</a></td>
            </tr>
          </table>';
    } elseif ($_SESSION['usertype'] == 'performers') {
        $wout = "<a href=\"performeronline.php\" class=\"whiteb2\">" . $lang["Go online!"] . "</a>";
        echo '<table align="center" >
            <tr>
              <td style="text-align: center;"><img src="images/i1.jpg" width="20" height="24" /></td>
              <td><a href="performersaccount.php" class="whiteb2">' . $lang["My account"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i3.jpg" /></td>
              <td class="whiteb2">' . $wout . '</td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i2.jpg" /></td>
              <td><a href="performersettings.php" class="whiteb2">' . $lang["My settings"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i4.jpg" /></td>
              <td><a href="payments.php" class="whiteb2">' . $lang["My payments"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i5.jpg" /></td>
              <td><a href="messenger.php" class="whiteb2">' . $lang["Messenger"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i6.jpg" /></td>
              <td><a href="logout.php" class="whiteb2">' . $lang["Logout"] . '</a></td>
            </tr>
          </table>';
    } elseif ($_SESSION['usertype'] == 'studios') {
        echo '<table align="center" >
            <tr>
              <td style="text-align:center;"><img src="images/i1.jpg" width="20" height="24" /></td>
              <td><a href="studioaccount.php" class="whiteb2">' . $lang["My account"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align:center;"><img src="images/i2.jpg" /></td>
              <td><a href="studiosettings.php" class="whiteb2">' . $lang["My settings"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align:center;"><img src="images/i9.jpg" /></td>
              <td><a href="performersignup.php?studio=' . $_SESSION['userid'] . '" class="whiteb2">' . $lang["Add performer"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align:center;"><img src="images/i8.jpg" /></td>
              <td><a href="studioperformers.php" class="whiteb2">' . $lang["My performers"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align:center;"><img src="images/i4.jpg" /></td>
              <td><a href="studiopayments.php" class="whiteb2">' . $lang["My payments"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align:center;"><img src="images/i6.jpg" /></td>
              <td><a href="logout.php" class="whiteb2">' . $lang["Logout"] . '</a></td>
            </tr>
          </table>';
    } elseif ($_SESSION['usertype'] == 'affiliates') {
        echo '<table align="center" >
            <tr>
              <td style="text-align: center;"><img src="images/i1.jpg" width="20" height="24" /></td>
              <td><a href="affiliatesaccount.php" class="whiteb2">' . $lang["My account"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i2.jpg" /></td>
              <td><a href="affiliatesettings.php" class="whiteb2">' . $lang["My settings"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i3.jpg" /></td>
              <td><a href="affpromo.php" class="whiteb2">' . $lang["Promo tools"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i4.jpg" /></td>
              <td><a href="affstats.php" class="whiteb2">' . $lang["My payments &amp; earnings"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i7.jpg" /></td>
              <td><a href="afftraffic.php" class="whiteb2">' . $lang["Traffic"] . '</a></td>
            </tr>
            <tr>
              <td style="text-align: center;"><img src="images/i6.jpg" /></td>
              <td><a href="logout.php" class="whiteb2">' . $lang["Logout"] . '</a></td>
            </tr>
          </table>';
    } else {
        echo '<form method="POST" action="login.php?v">
            <div style="padding-left: 15px; padding-top: 15px;"><img src="images/your_name.png" alt="your name" width="101" height="20" /><br />
              <div class="inputdiv"><input name="username" type="text" value="Username" onfocus="this.value = \'\';" size="31" /></div>
              <br />
              <img src="images/password.png" alt="Password" width="86" height="21" /><br />
              <div class="inputdiv"><input name="password" type="password" value="00000000000" onfocus="this.value = \'\';" size="31" /></div>
              <a href="usersignup.php"><img src="images/register.png" alt="Register" width="114" height="34" border="0" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <input type="image" value="Submit" src="images/login.png" alt="Submit" class="cd">
            </div>
            </form>';
    }
}
function chekpurchasedgal($id,$galid,$pid){
  $query=mysql_query("SELECT id FROM purchasedgal WHERE galid=".mysql_real_escape_string($galid)." AND mid=".mysql_real_escape_string($id)." AND pid=".mysql_real_escape_string($pid)." AND deleted=0");
  if(mysql_num_rows($query)>0)
  return true;
  else return false;

}
//displaypurchasedava("bc4ce9326270d91066cee020397dcc7f-1.jpg",150,150);

function displaypurchasedava($name1, $w=0, $h=0) {
    $name2 = "zamok.png";
//$name1="3dbb4ebab32fc66d44e18617ab13d8a8-1.jpg";
    if (!$image = @imagecreatefromjpeg("profileimages/$name1")) {
        echo("Bad name file");
    }
    if (!$image1 = @imagecreatefrompng("images/$name2")) {
        echo("Bad name file2");
    }
    if ($w != 0 && $h != 0) {
        header("Content-type: image/png");
        $res = imagecreatetruecolor($w, $h);
        imagecopyresized($image, $image1, 0, 0, 0, 0, imagesx($image), imagesy($image), imagesx($image1), imagesy($image1));
        imagecopyresampled($res, $image, 0, 0, 0, 0, imagesx($res), imagesy($res), imagesx($image), imagesy($image));
        imagepng($res);
    }

}
/*
	0- guest
	1-member
	2-performer
	3-studio
	4-
	5-
	6- admin
*/
 function logoperation($type=0,$uid=0,$op='',$com='',$ip='')
{
    $date=date('Y-m-d H:i:s');
    //echo("INSERT INTO log VALUE('','$type','$uid','$op','$com','$date','$ip')");

    mysql_query("INSERT INTO log VALUE('','$type','".mysql_real_escape_string($uid)."','".mysql_real_escape_string($op)."','".mysql_real_escape_string($com)."','$date','$ip')") or die("Error");
}

function write_log($var)
{
    $fp=fopen("log.txt", "a");
    fputs($fp, print_r($var,true));
    fclose($fp);
}

$cntry[] = "US";
$cntry[] = "United States";
$cntry[] = "AF";
$cntry[] = "Afghanistan";
$cntry[] = "AL";
$cntry[] = "Albania";
$cntry[] = "DZ";
$cntry[] = "Algeria";
$cntry[] = "AS";
$cntry[] = "American Samoa";
$cntry[] = "AO";
$cntry[] = "Angola";
$cntry[] = "AI";
$cntry[] = "Anguilla";
$cntry[] = "AD";
$cntry[] = "Andorra";
$cntry[] = "AQ";
$cntry[] = "Antarctica";
$cntry[] = "AG";
$cntry[] = "Antigua and Barbuda";
$cntry[] = "AR";
$cntry[] = "Argentina";
$cntry[] = "AM";
$cntry[] = "Armenia";
$cntry[] = "AT";
$cntry[] = "Austria";
$cntry[] = "AU";
$cntry[] = "Australia";

$cntry[] = "AW";
$cntry[] = "Aruba";
$cntry[] = "AZ";
$cntry[] = "Azerbaijan";
$cntry[] = "BS";
$cntry[] = "Bahamas";
$cntry[] = "BH";
$cntry[] = "Bahrain";
$cntry[] = "BD";
$cntry[] = "Bangladesh";
$cntry[] = "BB";
$cntry[] = "Barbados";
$cntry[] = "BY";
$cntry[] = "Belarus";
$cntry[] = "BE";
$cntry[] = "Belgium";
$cntry[] = "BZ";
$cntry[] = "Belize";
$cntry[] = "BJ";
$cntry[] = "Benin";
$cntry[] = "BM";
$cntry[] = "Bermuda";
$cntry[] = "BT";
$cntry[] = "Bhutan";
$cntry[] = "BO";
$cntry[] = "Bolivia";
$cntry[] = "BA";
$cntry[] = "Bosnia and Herzegovina";
$cntry[] = "BW";
$cntry[] = "Botswana";
$cntry[] = "BV";
$cntry[] = "Bouvet Island";
$cntry[] = "BG";
$cntry[] = "Bulgaria";
$cntry[] = "BF";
$cntry[] = "Burkina Faso";
$cntry[] = "BI";
$cntry[] = "Burundi";
$cntry[] = "BR";
$cntry[] = "Brazil";
$cntry[] = "IO";
$cntry[] = "British Indian Ocean Ter.";
$cntry[] = "BN";
$cntry[] = "Brunei Darussalam";
$cntry[] = "CM";
$cntry[] = "Cameroon";
$cntry[] = "KH";
$cntry[] = "Cambodia";
$cntry[] = "CA";
$cntry[] = "Canada";
$cntry[] = "KY";
$cntry[] = "Cayman Islands";
$cntry[] = "CF";
$cntry[] = "Central African Rep.";
$cntry[] = "TD";
$cntry[] = "Chad";
$cntry[] = "CC";
$cntry[] = "Cocos (Keeling) Isl.";
$cntry[] = "CG";
$cntry[] = "Congo";
$cntry[] = "CI";
$cntry[] = "Cote D'Ivoire";
$cntry[] = "CK";
$cntry[] = "Cook Islands";
$cntry[] = "CL";
$cntry[] = "Chile";
$cntry[] = "CN";
$cntry[] = "China";
$cntry[] = "CO";
$cntry[] = "Colombia";
$cntry[] = "KM";
$cntry[] = "Comoros";
$cntry[] = "CR";
$cntry[] = "Costa Rica";
$cntry[] = "CS";
$cntry[] = "Czechoslovakia (former)";
$cntry[] = "CU";
$cntry[] = "Cuba";
$cntry[] = "CV";
$cntry[] = "Cape Verde";
$cntry[] = "CX";
$cntry[] = "Christmas Island";
$cntry[] = "HR";
$cntry[] = "Croatia (Hrvatska)";
$cntry[] = "CY";
$cntry[] = "Cyprus";
$cntry[] = "CZ";
$cntry[] = "Czech Republic";
$cntry[] = "DJ";
$cntry[] = "Djibouti";
$cntry[] = "DK";
$cntry[] = "Denmark";
$cntry[] = "DM";
$cntry[] = "Dominica";
$cntry[] = "DO";
$cntry[] = "Dominican Republic";
$cntry[] = "TP";
$cntry[] = "East Timor";
$cntry[] = "EC";
$cntry[] = "Ecuador";
$cntry[] = "EG";
$cntry[] = "Egypt";
$cntry[] = "SV";
$cntry[] = "El Salvador";
$cntry[] = "GQ";
$cntry[] = "Equatorial Guinea";
$cntry[] = "ER";
$cntry[] = "Eritrea";
$cntry[] = "EE";
$cntry[] = "Estonia";
$cntry[] = "ET";
$cntry[] = "Ethiopia";
$cntry[] = "FK";
$cntry[] = "Falkland Islands";
$cntry[] = "FO";
$cntry[] = "Faroe Islands";
$cntry[] = "FI";
$cntry[] = "Finland";
$cntry[] = "FJ";
$cntry[] = "Fiji";
$cntry[] = "FR";
$cntry[] = "France";
$cntry[] = "FX";
$cntry[] = "France, Metropolitan";
$cntry[] = "GF";
$cntry[] = "French Guiana";
$cntry[] = "TF";
$cntry[] = "French Southern Ter.";
$cntry[] = "GA";
$cntry[] = "Gabon";
$cntry[] = "GM";
$cntry[] = "Gambia";
$cntry[] = "GE";
$cntry[] = "Georgia";
$cntry[] = "DE";
$cntry[] = "Germany";
$cntry[] = "GH";
$cntry[] = "Ghana";
$cntry[] = "GI";
$cntry[] = "Gibraltar";
$cntry[] = "GL";
$cntry[] = "Greenland";
$cntry[] = "GR";
$cntry[] = "Greece";
$cntry[] = "GB";
$cntry[] = "Great Britain (UK)";
$cntry[] = "GD";
$cntry[] = "Grenada";
$cntry[] = "GP";
$cntry[] = "Guadeloupe";
$cntry[] = "GU";
$cntry[] = "Guam";
$cntry[] = "GT";
$cntry[] = "Guatemala";
$cntry[] = "GN";
$cntry[] = "Guinea";
$cntry[] = "GW";
$cntry[] = "Guinea-Bissau";
$cntry[] = "GY";
$cntry[] = "Guyana";
$cntry[] = "HT";
$cntry[] = "Haiti";
$cntry[] = "HM";
$cntry[] = "Heard and McDonald Isl.";
$cntry[] = "HN";
$cntry[] = "Honduras";
$cntry[] = "HK";
$cntry[] = "Hong Kong";
$cntry[] = "HU";
$cntry[] = "Hungary";
$cntry[] = "IS";
$cntry[] = "Iceland";
$cntry[] = "IN";
$cntry[] = "India";
$cntry[] = "ID";
$cntry[] = "Indonesia";
$cntry[] = "IE";
$cntry[] = "Ireland";
$cntry[] = "IQ";
$cntry[] = "Iraq";
$cntry[] = "IR";
$cntry[] = "Iran";
$cntry[] = "IL";
$cntry[] = "Israel";
$cntry[] = "IT";
$cntry[] = "Italy";
$cntry[] = "JM";
$cntry[] = "Jamaica";
$cntry[] = "JP";
$cntry[] = "Japan";
$cntry[] = "JO";
$cntry[] = "Jordan";
$cntry[] = "KZ";
$cntry[] = "Kazakhstan";
$cntry[] = "KE";
$cntry[] = "Kenya";
$cntry[] = "KI";
$cntry[] = "Kiribati";
$cntry[] = "KP";
$cntry[] = "Korea (North)";
$cntry[] = "KR";
$cntry[] = "Korea (South)";
$cntry[] = "KW";
$cntry[] = "Kuwait";
$cntry[] = "KG";
$cntry[] = "Kyrgyzstan";
$cntry[] = "LA";
$cntry[] = "Laos";
$cntry[] = "LV";
$cntry[] = "Latvia";
$cntry[] = "LB";
$cntry[] = "Lebanon";
$cntry[] = "LS";
$cntry[] = "Lesotho";
$cntry[] = "LR";
$cntry[] = "Liberia";
$cntry[] = "LY";
$cntry[] = "Libya";
$cntry[] = "LI";
$cntry[] = "Liechtenstein";
$cntry[] = "LT";
$cntry[] = "Lithuania";
$cntry[] = "LU";
$cntry[] = "Luxembourg";
$cntry[] = "MO";
$cntry[] = "Macau";
$cntry[] = "MK";
$cntry[] = "Macedonia";
$cntry[] = "MG";
$cntry[] = "Madagascar";
$cntry[] = "MW";
$cntry[] = "Malawi";
$cntry[] = "MY";
$cntry[] = "Malaysia";
$cntry[] = "MV";
$cntry[] = "Maldives";
$cntry[] = "MT";
$cntry[] = "Malta";
$cntry[] = "ML";
$cntry[] = "Mali";
$cntry[] = "MH";
$cntry[] = "Marshall Islands";
$cntry[] = "MQ";
$cntry[] = "Martinique";
$cntry[] = "MR";
$cntry[] = "Mauritania";
$cntry[] = "MU";
$cntry[] = "Mauritius";
$cntry[] = "YT";
$cntry[] = "Mayotte";
$cntry[] = "MX";
$cntry[] = "Mexico";
$cntry[] = "FM";
$cntry[] = "Micronesia";
$cntry[] = "MD";
$cntry[] = "Moldova";
$cntry[] = "MC";
$cntry[] = "Monaco";
$cntry[] = "MN";
$cntry[] = "Mongolia";
$cntry[] = "MS";
$cntry[] = "Montserrat";
$cntry[] = "MA";
$cntry[] = "Morocco";
$cntry[] = "MZ";
$cntry[] = "Mozambique";
$cntry[] = "MM";
$cntry[] = "Myanmar";
$cntry[] = "NA";
$cntry[] = "Namibia";
$cntry[] = "NR";
$cntry[] = "Nauru";
$cntry[] = "NP";
$cntry[] = "Nepal";
$cntry[] = "NL";
$cntry[] = "Netherlands";
$cntry[] = "AN";
$cntry[] = "Netherlands Antilles";
$cntry[] = "NT";
$cntry[] = "Neutral Zone";
$cntry[] = "NC";
$cntry[] = "New Caledonia";
$cntry[] = "NZ";
$cntry[] = "New Zealand (Aotearoa)";
$cntry[] = "NI";
$cntry[] = "Nicaragua";
$cntry[] = "NE";
$cntry[] = "Niger";
$cntry[] = "NG";
$cntry[] = "Nigeria";
$cntry[] = "NU";
$cntry[] = "Niue";
$cntry[] = "NF";
$cntry[] = "Norfolk Island";
$cntry[] = "MP";
$cntry[] = "Northern Mariana Islands";
$cntry[] = "NO";
$cntry[] = "Norway";
$cntry[] = "OM";
$cntry[] = "Oman";
$cntry[] = "PA";
$cntry[] = "Panama";
$cntry[] = "PE";
$cntry[] = "Peru";
$cntry[] = "PF";
$cntry[] = "French Polynesia";
$cntry[] = "PG";
$cntry[] = "Papua New Guinea";
$cntry[] = "PH";
$cntry[] = "Philippines";
$cntry[] = "PK";
$cntry[] = "Pakistan";
$cntry[] = "PL";
$cntry[] = "Poland";
$cntry[] = "PN";
$cntry[] = "Pitcairn";
$cntry[] = "PR";
$cntry[] = "Puerto Rico";
$cntry[] = "PT";
$cntry[] = "Portugal";
$cntry[] = "PW";
$cntry[] = "Palau";
$cntry[] = "PY";
$cntry[] = "Paraguay";
$cntry[] = "QA";
$cntry[] = "Qatar";
$cntry[] = "RE";
$cntry[] = "Reunion";
$cntry[] = "RO";
$cntry[] = "Romania";
$cntry[] = "RU";
$cntry[] = "Russian Federation";
$cntry[] = "RW";
$cntry[] = "Rwanda";
$cntry[] = "WS";
$cntry[] = "Samoa";
$cntry[] = "VC";
$cntry[] = "Saint Vincent";
$cntry[] = "PM";
$cntry[] = "St. Pierre";
$cntry[] = "GS";
$cntry[] = "S. Georgia";
$cntry[] = "LC";
$cntry[] = "Saint Lucia";
$cntry[] = "LK";
$cntry[] = "Sri Lanka";
$cntry[] = "KN";
$cntry[] = "Saint Kitts and Nevis";
$cntry[] = "SA";
$cntry[] = "Saudi Arabia";
$cntry[] = "Sb";
$cntry[] = "Solomon Islands";
$cntry[] = "SC";
$cntry[] = "Seychelles";
$cntry[] = "SD";
$cntry[] = "Sudan";
$cntry[] = "SE";
$cntry[] = "Sweden";
$cntry[] = "SG";
$cntry[] = "Singapore";
$cntry[] = "SH";
$cntry[] = "St. Helena";
$cntry[] = "SI";
$cntry[] = "Slovenia";
$cntry[] = "SJ";
$cntry[] = "Svalbard & Jan Mayen Isl.";
$cntry[] = "SK";
$cntry[] = "Slovak Republic";
$cntry[] = "SL";
$cntry[] = "Sierra Leone";
$cntry[] = "SM";
$cntry[] = "San Marino";
$cntry[] = "SN";
$cntry[] = "Senegal";
$cntry[] = "ES";
$cntry[] = "Spain";
$cntry[] = "SO";
$cntry[] = "Somalia";
$cntry[] = "SR";
$cntry[] = "Suriname";
$cntry[] = "ST";
$cntry[] = "Sao Tome and Principe";
$cntry[] = "CH";
$cntry[] = "Switzerland";
$cntry[] = "ZA";
$cntry[] = "South Africa";
$cntry[] = "SY";
$cntry[] = "Syria";
$cntry[] = "SZ";
$cntry[] = "Swaziland";
$cntry[] = "TC";
$cntry[] = "Turks and Caicos Isl.";
$cntry[] = "TG";
$cntry[] = "Togo";
$cntry[] = "TH";
$cntry[] = "Thailand";
$cntry[] = "TJ";
$cntry[] = "Tajikistan";
$cntry[] = "TK";
$cntry[] = "Tokelau";
$cntry[] = "TM";
$cntry[] = "Turkmenistan";
$cntry[] = "TN";
$cntry[] = "Tunisia";
$cntry[] = "TO";
$cntry[] = "Tonga";
$cntry[] = "TR";
$cntry[] = "Turkey";
$cntry[] = "TT";
$cntry[] = "Trinidad and Tobago";
$cntry[] = "TV";
$cntry[] = "Tuvalu";
$cntry[] = "TW";
$cntry[] = "Taiwan";
$cntry[] = "TZ";
$cntry[] = "Tanzania";
$cntry[] = "SU";
$cntry[] = "USSR (former)";
$cntry[] = "UA";
$cntry[] = "Ukraine";
$cntry[] = "UG";
$cntry[] = "Uganda";
$cntry[] = "UK";
$cntry[] = "United Kingdom";
$cntry[] = "UM";
$cntry[] = "US Minor Outlying Isl.";
$cntry[] = "AE";
$cntry[] = "United Arab Emirates";
$cntry[] = "UY";
$cntry[] = "Uruguay";
$cntry[] = "UZ";
$cntry[] = "Uzbekistan";
$cntry[] = "VA";
$cntry[] = "Vatican City State";
$cntry[] = "VE";
$cntry[] = "Venezuela";
$cntry[] = "VG";
$cntry[] = "Virgin Islands (British)";
$cntry[] = "VI";
$cntry[] = "Virgin Islands (U.S.)";
$cntry[] = "VN";
$cntry[] = "Viet Nam";
$cntry[] = "VU";
$cntry[] = "Vanuatu";
$cntry[] = "YE";
$cntry[] = "Yemen";
$cntry[] = "YU";
$cntry[] = "Yugoslavia";
$cntry[] = "WF";
$cntry[] = "Wallis and Futuna Isl.";
$cntry[] = "EH";
$cntry[] = "Western Sahara";
$cntry[] = "ZM";
$cntry[] = "Zambia";
$cntry[] = "ZR";
$cntry[] = "Zaire";
$cntry[] = "ZW";
$cntry[] = "Zimbabwe";

$type[] = "";
$type[] = $lang["Girl"];
$type[] = $lang["Boy"];
$type[] = $lang["Gay"];
$type[] = $lang["Mature woman"];
$type[] = $lang["Fetish woman"];
$type[] = $lang["Transgendered"];
$type[] = $lang["Hermaphrodite"];
$type[] = $lang["Shemale"];
$type[] = $lang["Couple"];
$type[] = $lang["Lesbian"];
$type[] = $lang["Gay couple"];
$type[] = $lang["Fetish couple"];
$type[] = $lang["Transgendered couple"];
$type[] = $lang["Hermaphrodite couple"];
$type[] = $lang["Shemale couple"];
$type[] = $lang["Threesome"];
$type[] = $lang["Lesbian threesome"];
$type[] = $lang["Gay threesome"];
$type[] = $lang["Fetish threesome"];
$type[] = $lang["Transgendered threesome"];
$type[] = $lang["Hermaphrodite threesome"];
$type[] = $lang["Shemale threesome"];
$type[] = $lang["Group"];
$type[] = $lang["Lesbian group"];
$type[] = $lang["Gay group"];
$type[] = $lang["Fetish group"];
$type[] = $lang["Transgendered group"];
$type[] = $lang["Hermaphrodite group"];
$type[] = $lang["Shemale group"];
$type[] = $lang["Dating"];
$type[] = $lang["Making friends"];
$type[] = $lang["Nasty words"];
$type[] = $lang["Get married"];
$type[] = $lang["Dating"];
$type[] = $lang["Making friends"];
$type[] = $lang["Nasty words"];
$type[] = $lang["Get married"];
$type[] = $lang["Dating"];
$type[] = $lang["Making friends"];
$type[] = $lang["Nasty words"];
$type[] = $lang["Get married"];
$type[] = $lang["Dating"];
$type[] = $lang["Making friends"];
$type[] = $lang["Nasty words"];
$type[] = $lang["Get married"];
?>
