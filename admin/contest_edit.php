<?php require("admin-header.php");
include_once("kindeditor.php") ;

if(isset($_POST['cid']))
      $cid=intval($_POST['cid']);
if(isset($_GET['cid']))
      $cid=intval($_GET['cid']);
//if(!(isset($_SESSION["m$cid"])||isset($_SESSION['administrator'])))
if(!(isset($_SESSION["m$cid"])))
    {
      echo "You don't have the privilage";
      exit();
    }

if (isset($_POST['syear']))
{
	require_once("../include/check_post_key.php");

	$starttime=intval($_POST['syear'])."-".intval($_POST['smonth'])."-".intval($_POST['sday'])." ".intval($_POST['shour']).":".intval($_POST['sminute']).":00";
	$endtime=intval($_POST['eyear'])."-".intval($_POST['emonth'])."-".intval($_POST['eday'])." ".intval($_POST['ehour']).":".intval($_POST['eminute']).":00";

	$running_time = strtotime($endtime) - strtotime($starttime);
	if ($running_time > 60 * 60 * 24 * 15 || $running_time < 0) {
		echo "<script> alert('The contest time is too long or too short');";
		print "history.go(-1);</script>";	
		exit();
	}

//	echo $starttime;
//	echo $endtime;
	$title=mysql_real_escape_string($_POST['title']);
	$description=mysql_real_escape_string($_POST['description']);
	$private=mysql_real_escape_string($_POST['private']);
	if($private=='2'){
		$regstarttime=intval($_POST['rsyear'])."-".intval($_POST['rsmonth'])."-".intval($_POST['rsday'])." ".intval($_POST['rshour']).":".intval($_POST['rsminute']).":00";
         	$regendtime=intval($_POST['reyear'])."-".intval($_POST['remonth'])."-".intval($_POST['reday'])." ".intval($_POST['rehour']).":".intval($_POST['reminute']).":00";
		$a=strtotime($regendtime);
		$b=strtotime($regstarttime);
		if($a<$b)
		{
			print "<script language='javascript'>\n";
			print "alert('register time is wrong!\\n');\n";
			print "history.go(-1);\n</script>";
			exit(0);
		}
	}
        if (get_magic_quotes_gpc ()) {
             $title = stripslashes ( $title);
              $description = stripslashes ( $description);
           }


   $lang=$_POST['lang'];
   $langmask=0;
   foreach($lang as $t){
			$langmask+=1<<$t;
	}
	$langmask=127&(~$langmask);
	// echo $langmask;

	$cid=intval($_POST['cid']);
            //echo $cid;
	//if(!(isset($_SESSION["m$cid"])||isset($_SESSION['administrator'])))
	if(!(isset($_SESSION["m$cid"])))
         {
         echo "You don't have the privilage";
          exit();
		 }
	$ipList = json_encode(explode("\r\n", $_POST['ip_list']));
	
	$sql="UPDATE `contest` set `title`='$title',description='$description',`start_time`='$starttime',`reg_start_time`='$regstarttime',`reg_end_time`='$regendtime',`end_time`='$endtime',`private`='$private',`langmask`=$langmask, `allow_ips` = '$ipList' WHERE `contest_id`=$cid";
	//echo $sql;
	mysql_query($sql) or die(mysql_error());

	// 验证是否可以选择该题目
	$plist = trim($_POST['cproblem']);
	$_problemIds = empty($plist) ? array() : explode(',', $plist);
	$pieces = array();
	if (count($_problemIds) > 0) {
		$sql = "select defunct, author, problem_id from problem where problem_id in ($plist)";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_object($result)) {
			if ($row->defunct == 'N') {
				$pieces[] = $row->problem_id;
			} else {
			//	if (!strcmp($row->author, $_SESSION['user_id']) || isset($_SESSION['administrator'])) {
				if (!strcmp($row->author, $_SESSION['user_id']) ) {
					$pieces[] = $row->problem_id;
				}
			}
		}
	}
	if (count($_problemIds) != count($pieces)) {
		print "<script language='javascript'>\n";
		print "alert('选择的题目列表中有隐藏题目, 不能保存!\\n');\n";
		print "history.go(-1);\n</script>";
		exit(0);
	}

	$sql="DELETE FROM `contest_problem` WHERE `contest_id`=$cid";
	mysql_query($sql);

	if (count($pieces)>0 && strlen($pieces[0])>0){
		$sql_1="INSERT INTO `contest_problem`(`contest_id`,`problem_id`,`num`)
			VALUES ('$cid','$pieces[0]',0)";
		for ($i=1;$i<count($pieces);$i++)
			$sql_1=$sql_1.",('$cid','$pieces[$i]',$i)";
		mysql_query("update solution set num=-1 where contest_id=$cid");
		for ($i=0;$i<count($pieces);$i++){
			$sql_2="update solution set num='$i' where contest_id='$cid' and problem_id='$pieces[$i]';";
			mysql_query($sql_2);
		}
		//echo $sql_1;

		mysql_query($sql_1) or die(mysql_error());
		$sql="update `problem` set defunct='N' where `problem_id` in ($plist)";
		mysql_query($sql) or die(mysql_error());

	}

	$sql="DELETE FROM `privilege` WHERE `rightstr`='c$cid'";
	mysql_query($sql);
	$pieces = explode("\n", trim($_POST['ulist']));
	if (count($pieces)>0 && strlen($pieces[0])>0){
		$sql_1="INSERT INTO `privilege`(`user_id`,`rightstr`)
			VALUES ('".trim($pieces[0])."','c$cid')";
		for ($i=1;$i<count($pieces);$i++)
			$sql_1=$sql_1.",('".trim($pieces[$i])."','c$cid')";
		//echo $sql_1;
		mysql_query($sql_1) or die(mysql_error());
	}

        echo "<script>window.location.href=\"contest_list.php\";</script>";
	exit();
}else{
	$cid=intval($_GET['cid']);
	$sql="SELECT * FROM `contest` WHERE `contest_id`=$cid";
	$result=mysql_query($sql);
	if (mysql_num_rows($result)!=1){
		mysql_free_result($result);
		echo "No such Contest!";
		exit(0);
	}
	$row=mysql_fetch_assoc($result);
	$starttime=$row['start_time'];
	$endtime=$row['end_time'];
	$private=$row['private'];
	$langmask=$row['langmask'];
	$description=$row['description'];
	$title=htmlspecialchars($row['title']);
	$regstarttime=$row['reg_start_time'];
	$regendtime=$row['reg_end_time'];
	$ipList = implode("\r\n", json_decode($row['allow_ips'], true));
	mysql_free_result($result);
	$plist="";
	$sql="SELECT `problem_id` FROM `contest_problem` WHERE `contest_id`=$cid ORDER BY `num`";
	$result=mysql_query($sql) or die(mysql_error());
	for ($i=mysql_num_rows($result);$i>0;$i--){
		$row=mysql_fetch_row($result);
		$plist=$plist.$row[0];
		if ($i>1) $plist=$plist.',';
	}
	$ulist="";
	$sql="SELECT `user_id` FROM `privilege` WHERE `rightstr`='c$cid' order by user_id";
	$result=mysql_query($sql) or die(mysql_error());
	for ($i=mysql_num_rows($result);$i>0;$i--){
		$row=mysql_fetch_row($result);
		$ulist=$ulist.$row[0];
		if ($i>1) $ulist=$ulist."\n";
	}


}
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Contest Editor</title>
</head>

<body onload="JudgeUp()">
<form method=POST action='<?php echo $_SERVER['PHP_SELF']?>'>
<?php require_once("../include/set_post_key.php");?>
<p align=center><font size=4 color=#333399>Edit a Contest</font></p>
<input type=hidden name='cid' value=<?php echo $cid?>>
<p align=left>Title:<input type=text name=title size=71 value='<?php echo $title?>'></p>
<p align=left>Start Time:<br>&nbsp;&nbsp;&nbsp;
Year:<input type=text name=syear value=<?php echo substr($starttime,0,4)?> size=7 >
Month:<input type=text name=smonth value='<?php echo substr($starttime,5,2)?>' size=7 >
Day:<input type=text name=sday size=7 value='<?php echo substr($starttime,8,2)?>'>
Hour:<input type=text name=shour size=7 value='<?php echo substr($starttime,11,2)?>'>
Minute:<input type=text name=sminute size=7 value=<?php echo substr($starttime,14,2)?>></p>
<p align=left>End Time:<br>&nbsp;&nbsp;&nbsp;

Year:<input type=text name=eyear value=<?php echo substr($endtime,0,4)?> size=7 >
Month:<input type=text name=emonth value=<?php echo substr($endtime,5,2)?> size=7 >
Day:<input type=text name=eday size=7 value=<?php echo substr($endtime,8,2)?>>&nbsp;
Hour:<input type=text name=ehour size=7 value=<?php echo substr($endtime,11,2)?>> &nbsp;
Minute:<input type=text name=eminute size=7 value=<?php echo substr($endtime,14,2)?>></p>

Public:<select name=private id=private onchange="JudgePrivate(this.value)">
	<option value=0 <?php echo $private=='0'?'selected=selected':''?>>Public</option>
	<option value=1 <?php echo $private=='1'?'selected=selected':''?>>Private</option>
	<option value=2 <?php echo $private=='2'?'selected=selected':''?>>Register</option>
</select>
<?php $lang=(~((int)$langmask))&1023; 
 $C_select=($lang&1)>0?"selected":"";
 $CPP_select=($lang&2)>0?"selected":"";
 $P_select=($lang&4)>0?"selected":"";
 $J_select=($lang&8)>0?"selected":"";
 $Rb_select=($lang & 16 ) > 0 ? "selected" : "";
 $Py_select=($lang & 64 ) > 0 ? "selected" : "";
// echo $lang;
?>
Language:<select name="lang[]" multiple>
		<option value=0 <?php echo $C_select?>>C</option>
		<option value=1 <?php echo $CPP_select?>>C++</option>
		<option value=2 <?php echo $P_select?>>Pascal</option>
		<option value=3 <?php echo $J_select?>>Java</option>
		<option value=4 <?php echo $Rb_select?>>Ruby</option>
		<option value=6 <?php echo $Py_select?>>Python</option>
	</select>
<br>Problems:<input type=text size=60 name=cproblem value='<?php echo $plist?>'>
	<div id="registertime" style="display:none">
	<p align=left>Register Start:<br>&nbsp;&nbsp;&nbsp;
	Year:<input type=text name=rsyear value=<?php echo substr($regstarttime,0,4)?> size=7 >
	Month:<input type=text name=rsmonth value='<?php echo substr($regstarttime,5,2)?>' size=7 >
	Day:<input type=text name=rsday size=7 value='<?php echo substr($regstarttime,8,2)?>'>
	Hour:<input type=text name=rshour size=7 value='<?php echo substr($regstarttime,11,2)?>'>
	Minute:<input type=text name=rsminute size=7 value=<?php echo substr($regstarttime,14,2)?>></p>
	<p align=left>Register End:<br>&nbsp;&nbsp;&nbsp;
	Year:<input type=text name=reyear value=<?php echo substr($regendtime,0,4)?> size=7 >
	Month:<input type=text name=remonth value=<?php echo substr($regendtime,5,2)?> size=7 >
	Day:<input type=text name=reday size=7 value=<?php echo substr($regendtime,8,2)?>>&nbsp;
	Hour:<input type=text name=rehour size=7 value=<?php echo substr($regendtime,11,2)?>> &nbsp;
	Minute:<input type=text name=reminute size=7 value=<?php echo substr($regendtime,14,2)?>></p>
	</div>

<p align=left>Description:<br> <textarea class="kindeditor" rows=13 name=description cols=120><?php echo htmlspecialchars($description)?></textarea></p>
    </p>
<br><br>
Users:<textarea name="ulist" rows="20" cols="20"><?php if (isset($ulist)) { echo $ulist; } ?></textarea>
Allow Login IP:<textarea name="ip_list" rows="20" cols="20"><?php if (isset($ipList)) { echo $ipList; } ?></textarea>
<p><input type=submit value=Submit name=submit><input type=reset value=Reset name=reset></p>

</form>

<script language='javascript'>
    function JudgePrivate(temp)
    {
        var divn=document.getElementById('registertime');
        if(temp==2)
            divn.style.display='';
        else
            divn.style.display='none';
    }
    function JudgeUp()
    {
        var ispublic=document.getElementById('private');
        var divn=document.getElementById('registertime');
        var value=ispublic.value;
        if(value==2)
            divn.style.display='';
        else
            divn.style.display='none';
    }
</script>

</body>
<?php require_once("../oj-footer.php");?>
