
<?
require_once("./include/my_func.inc.php");
require_once("./include/db_info.inc.php");
if(isset($OJ_LANG)){
		require_once("./lang/$OJ_LANG.php");
	}
require_once("./include/const.inc.php");

?>


<meta http-equiv='refresh' content='60'>
<title>Submission Status</title>
<center>

<?
$str2="";
/*
if($OJ_SIM){
	$sql="SELECT * FROM `solution` left join `sim` on solution.solution_id=sim.s_id WHERE 1 ";
	if(isset($_GET['showsim'])){
		$showsim=intval($_GET['showsim']);
		$sql="SELECT * FROM `solution` right join `sim` on solution.solution_id=sim.s_id left join(select solution_id old_s_id,user_id old_user_id from solution) old on old.old_s_id=sim.sim_s_id WHERE  old_user_id!=user_id and sim_s_id!=solution_id and sim.sim>= $showsim ";	
		$str2="showsim=$showsim";
	}
}else{
	$sql="SELECT * FROM `solution` WHERE 1 ";
}
*/
$seatmark=0;
$contestsql="";
$sql="SELECT * FROM `solution` WHERE 1 ";
if (isset($_GET['cid'])){
          $cid=intval($_GET['cid']);
          $sql1="SELECT `start_time`,`end_time`  FROM `contest` WHERE `contest_id`='$cid'";
          $result=mysql_query($sql1) or die(mysql_error());
          $rows_cnt=mysql_num_rows($result);
          $start_time=0;
          if ($rows_cnt>0){
                  $row=mysql_fetch_array($result);
                  $start_time=strtotime($row[0]);
                  $end_time=strtotime($row[1]);
          }
         mysql_free_result($result);
         $start_timeC=strftime("%Y-%m-%d %X",($start_time));
         $end_timeC=strftime("%Y-%m-%d %X",($end_time)); 

         if(!isset($OJ_RANK_LOCK_PERCENT)) $OJ_RANK_LOCK_PERCENT=0;
         $lock_time=$end_time-($end_time-$start_time)*$OJ_RANK_LOCK_PERCENT;
         $lock_timeC=strftime("%Y-%m-%d %X",($lock_time));
         if($end_time>time())
	{
	     if(isset($_SESSION['administrator'])||isset($_SESSION['contest_creator']))
                         $timetoend=$end_timeC;
             else
                        $timetoend=$lock_timeC;
        }else
                       $timetoend=$end_timeC;
  	
        $sql=$sql." AND `contest_id`='$cid' and in_date>'$start_timeC' and (in_date<'$timetoend' ";
        if(isset($_SESSION['user_id'])){
                   $user_id=$_SESSION['user_id'];
               $sql=$sql." or (in_date<'$end_timeC' and user_id='$user_id' ) )";
                     
        }else
               $sql=$sql." ) ";
	$str2=$str2."&cid=$cid";
	require_once("contest-header.php");
}else{
	if(!isset($_SESSION['administrator'])){
	   $nowtime=strftime("%Y-%m-%d %X",time());
	   $contestsql=$contestsql."AND (`contest_id` NOT IN(SELECT `contest_id` FROM `contest` WHERE `start_time`<'$nowtime' AND `end_time`>'$nowtime') or `contest_id` is NULL)";
           $sql=$sql.$contestsql;
	}
	require_once("oj-header.php");
}
?>
<table width=90%>
<tr><td>
<?
$order_str=" ORDER BY `solution_id` DESC ";
$start_first=1;
// check the top arg
if (isset($_GET['top'])){
	$top=strval(intval($_GET['top']));
	if ($top!=-1) $sql=$sql."AND `solution_id`<='".$top."' ";
}

// check the problem arg
$problem_id="";
if (isset($_GET['problem_id'])){
	if(isset($_GET['cid']))
	{
		$cid=intval($_GET['cid']);
		$numid=ord($_GET['problem_id'])-65;
		$sql2="SELECT `problem_id` FROM `contest_problem` WHERE `contest_id`='$cid' AND `num`='$numid'";
		$cnt=0;
		$result2=mysql_query($sql2);
		if($result2)
			$cnt=mysql_num_rows($result2);
		else
			$cnt=0;
		if($cnt)
		{
			$row2=mysql_fetch_array($result2);
			mysql_free_result($result2);
			if($row2['problem_id']!=0)
			{
				$problem_id=chr($numid+65);
				//echo $problem_id;
				$sql=$sql."AND `problem_id`='".$row2['problem_id']."' ";
				$str2=$str2."&problem_id=".$problem_id;
			}
			else
				$problem_id="";
		}
		else
			$problem_id="";
		//echo $problem_id;
	}
	else
	{
		$problem_id=strval(intval($_GET['problem_id']));
		if ($problem_id!='0'){
			$sql=$sql."AND `problem_id`='".$problem_id."' ";
			$str2=$str2."&problem_id=".$problem_id;
		}
		else $problem_id="";
	}
}
// check the user_id arg
$user_id="";
if (isset($_GET['user_id'])){
	$user_id=trim($_GET['user_id']);
	if (is_valid_user_name($user_id) && $user_id!=""){
           if(isset($_SESSION['administrator'])||isset($_SESSION['contest_creator'])||isset($_SESSION['source_browser']))
                   $sql=$sql."AND user_id like '%$user_id%' ";
               else
		   $sql=$sql."AND `user_id`='".$user_id."' ";
		if ($str2!="") $str2=$str2."&";
		$str2=$str2."user_id=".$user_id;
	}else $user_id="";
}
if (isset($_GET['language'])) $language=intval($_GET['language']);
else $language=-1;

if ($language>6 || $language<0) $language=-1;
if ($language!=-1){
	$sql=$sql."AND `language`='".strval($language)."' ";
	$str2=$str2."&language=".$language;
}
?>
<form id=simform action="status.php" method="get">
<? if(isset($_GET['cid'])){
                $cid=intval($_GET['cid']);
                $sql22="SELECT count('problem_id') FROM `contest_problem` WHERE `contest_id`='$cid'";
                $cnt=0;
                $result22=mysql_query($sql22);
                $row22=mysql_fetch_array($result22);
                mysql_free_result($result22);
                if($row22[0])
                {
                        echo "$MSG_PROBLEM_ID<select size=1 name=problem_id>";
                       $pro_selectid=-1;
                       if(isset($_GET['problem_id']))
                            $pro_selectid=$_GET['problem_id'];
                       if($pro_selectid==-1) echo "<option value='' selected>All</option>";
                                else echo "<option value=''>All</option>"; 
                       for($i=0;$i<$row22[0];$i++)
                        {
                             $pro_id=chr($i+65);
                             if($pro_id==$pro_selectid)
                              echo "<option value=$pro_id selected>$pro_id</option>";
                             else
                              echo "<option value=$pro_id>$pro_id</option>";
                        }
                       echo "</select>";
                }

}
else
    echo "<br>".$MSG_PROBLEM_ID.":<input type=text size=4 name=problem_id value='".$problem_id."'>";

?>
<?=$MSG_USER?>:<input type=text size=6 name=user_id value='<?=$user_id?>'>
<?if (isset($cid)) echo "<input type='hidden' name='cid' value='$cid'>";?>
<?=$MSG_LANG?>:<select size="1" name="language">
<?
if (isset($_GET['language'])) $language=$_GET['language'];
else $language=-1;
if ($language<0||$language>10) $language=-1;
if ($language==-1) echo "<option value='-1' selected>All</option>";
else echo "<option value='-1'>All</option>";
for ($i=0;$i<7;$i++){
if ($i == 5) continue;
	if ($i==$language) echo "<option value=$i selected>$language_name[$i]</option>";
	else echo "<option value=$i>$language_name[$i]</option>";
}
?>
</select>
<?=$MSG_RESULT?>:<select size="1" name="jresult">
<?
if (isset($_GET['jresult'])) $jresult=intval($_GET['jresult']);
else $jresult=-1;
if ($jresult>=12||$jresult<0) $jresult=-1;
if ($jresult!=-1){
	$sql=$sql."AND `result`='".strval($jresult)."' ";
	$str2=$str2."&jresult=".strval($jresult);
}
if ($jresult==-1) echo "<option value='-1' selected>All</option>";
else echo "<option value='-1'>All</option>";
for ($j=0;$j<12;$j++){
	$i=($j+4)%12;
	if ($i==$jresult) echo "<option value='".strval($jresult)."' selected>".$judge_result[$i]."</option>";
	else echo "<option value='".strval($i)."'>".$judge_result[$i]."</option>"; 
}
echo "</select>";
?>
</select>

<?
if(isset($_SESSION['administrator'])||isset($_SESSION['contest_creator'])){
	if(isset($_GET['showsim']))
		$showsim=intval($_GET['showsim']);
	else
		$showsim=0;
	echo "SIM:
			<select name=showsim onchange=\"document.getElementById('simform').submit();\">
			<option value=0 ".($showsim==0?'selected':'').">All</option>
			<option value=50 ".($showsim==50?'selected':'').">50</option>
			<option value=60 ".($showsim==60?'selected':'').">60</option>
			<option value=70 ".($showsim==70?'selected':'').">70</option>
			<option value=80 ".($showsim==80?'selected':'').">80</option>
			<option value=90 ".($showsim==90?'selected':'').">90</option>
			<option value=100 ".($showsim==100?'selected':'').">100</option>
		  </select>";
/*	if (isset($_GET['cid'])) 
		echo "<input type=hidden name=cid value='".$_GET['cid']."'>";
	if (isset($_GET['language'])) 
		echo "<input type=hidden name=language value='".$_GET['language']."'>";
	if (isset($_GET['user_id'])) 
		echo "<input type=hidden name=user_id value='".$_GET['user_id']."'>";
	if (isset($_GET['problem_id'])) 
		echo "<input type=hidden name=problem_id value='".$_GET['problem_id']."'>";
	//echo "<input type=submit>";
*/
	
	
	
}
echo "<input type=submit value='$MSG_SEARCH'>";
echo "</form>";

?>
</td></tr>
</table>
<table align=center width=95%>
<tr align=center  class='toprow'>
<td width="8%"><?=$MSG_RUNID?>
<td width="10%"><?=$MSG_USER?>
<?if(isset($cid)){
	$sql_cid="SELECT `private` FROM `contest` WHERE `contest`.`contest_id` ='$cid'";
	$result_cid=mysql_query($sql_cid);
	$row_cid=mysql_fetch_array($result_cid);
	mysql_free_result($result_cid);
	if($row_cid[0]=='2'){
	echo "<td width=\"5%\">seat";
	$seatmark=1;	
	}
}?>
<td width="6%"><?=$MSG_PROBLEM?>
<td width="17%"><?=$MSG_RESULT?>
<td width="10%"><?=$MSG_MEMORY?>
<td width="8%"><?=$MSG_TIME?>
<td width="6%"><?=$MSG_LANG?>
<td width="10%"><?=$MSG_CODE_LENGTH?>
<td width="17%"><?=$MSG_SUBMIT_TIME?>
</tr>
<?

if($OJ_SIM){
$sql="select * from ($sql order by solution_id desc limit 20) solution left join `sim` on solution.solution_id=sim.s_id WHERE 1 ";
	if(isset($_GET['showsim'])&&intval($_GET['showsim'])>0){
		$showsim=intval($_GET['showsim']);
		$sql=$sql." and result=4 ";
		$sql="SELECT * FROM ($sql order by solution_id desc limit 20) `solution` 
			left join(select solution_id old_s_id,user_id old_user_id from solution) old 
				on old.old_s_id=sim_s_id WHERE  old_user_id!=user_id and sim_s_id!=solution_id and sim>= $showsim  ";	
	
		$str2.="&showsim=$showsim";
	}
	//$sql=$sql.$order_str." LIMIT 20";

}
$sql=$sql.$order_str." LIMIT 20";
//echo $sql;
$result = mysql_query($sql) or die("Error! ".mysql_error());
$rows_cnt=mysql_num_rows($result);
$top=$bottom=-1;
$cnt=0;
if ($start_first){
	$row_start=0;
	$row_add=1;
}else{
	$row_start=$rows_cnt-1;
	$row_add=-1;
}


//for ($i=0;$i<$rows_cnt;$i++){
//	mysql_data_seek($result,$row_start+$row_add*$i);
while(	$row=mysql_fetch_object($result)){
	if ($top==-1) $top=$row->solution_id;
	$bottom=$row->solution_id;
	if ($cnt) echo "<tr align=center class='oddrow'>";
	else echo "<tr align=center class='evenrow'>";
	$flag=(!is_running(intval($row->contest_id))) ||
			isset($_SESSION['source_browser']) ||
			isset($_SESSION['administrator']) || 
			(isset($_SESSION['user_id'])&&!strcmp($row->user_id,$_SESSION['user_id']));
	$cnt=1-$cnt;
	echo "<td>".$row->solution_id;
	echo "<td><a href='userinfo.php?user=".$row->user_id."'>".$row->user_id."</a>";
	if($seatmark)
	{
$sql_seat="SELECT `seatnum` FROM `contestreg` WHERE `contestreg`.`contest_id` ='$cid' AND `contestreg`.`user_id`='".$row->user_id."'";
		$result_seat=mysql_query($sql_seat);
		$row_seat=mysql_fetch_array($result_seat);
		mysql_free_result($result_seat);
		echo "<td>$row_seat[0]";
	}
	if (isset($cid)) 
		echo "<td><a href='problem.php?cid=$cid&pid=$row->num'>".$PID[$row->num]."</a>";
	else 
		echo "<td><a href='problem.php?id=".$row->problem_id."'>".$row->problem_id."</a>";
	if (intval($row->result)==11 && ((isset($_SESSION['user_id'])&&$row->user_id==$_SESSION['user_id']) || isset($_SESSION['source_browser']))){
		echo "<td><a href='ceinfo.php?sid=$row->solution_id'><font color=".$judge_color[$row->result].">".$judge_result[$row->result]."</font></a>";
	}
        else if (intval($row->result)==10 && ((isset($_SESSION['user_id'])&&$row->user_id==$_SESSION['user_id']) || isset($_SESSION['source_browser']))){
  
         //  echo "<td><a href='reinfo.php?sid=$row->solution_id' class=".$judge_color[$row->result].">".$judge_result[$row->result]."</a>";
             echo "<td><a href='reinfo.php?sid=$row->solution_id'><font color=".$judge_color[$row->result].">".$judge_result[$row->result]."</font></a>";


        }

        else{
		if($OJ_SIM&&$row->sim&&$row->sim_s_id!=$row->s_id) {
			echo "<td>*<font color=".$judge_color[$row->result].">".$judge_result[$row->result]."</font>-<font color=red>";
			if( isset($_SESSION['source_browser'])){
					//echo "<a href=showsource.php?id=".$row->sim_s_id." target=original>".$row->sim_s_id."(".$row->sim."%)</a>";
                                        echo "<a href=comparesource.php?left=".$row->solution_id."&right=".$row->sim_s_id."  class='btn btn-info'  target=original>".$row->sim_s_id."(".$row->sim."%)</a>";
			}else{
					echo $row->sim_s_id;
			}
			if(isset($_GET['showsim'])&&isset($row->old_user_id)){
					echo "<a href=userinfo.php?user=".$row->old_user_id."><font color=red>".$row->old_user_id."</font></a>";
				
			}
			echo	 "</font>";
		}else{
			echo "<td><font color=".$judge_color[$row->result].">".$judge_result[$row->result]."</font>";
		}
		
	}

            if (isset($row->pass_rate)&&$row->pass_rate>0&&$row->pass_rate<0.98) 
            { 
    	       if( isset($_SESSION['source_browser']))
         	echo "--".($row->pass_rate*100)."%"; 
              else      
    		echo "--**%"; 
           }
if ($flag){

		if ($row->result>=4){
			echo "<td>".$row->memory." <font color=red>kb</font>";
			echo "<td>".$row->time." <font color=red>ms</font>";
		}else{
			echo "<td>------<td>------";
		}
		if (!(isset($_SESSION['user_id'])&&strtolower($row->user_id)==strtolower($_SESSION['user_id']) || isset($_SESSION['source_browser']))){
			echo "<td>".$language_name[$row->language];
		}else{
                      if(!$OJ_VIP_CONTEST || isset($_SESSION['source_browser'])){
			echo "<td><a target=_blank href=showsource.php?id=".$row->solution_id.">".$language_name[$row->language]."</a>/";
			if (isset($cid)) {
				echo "<a target=_self href=\"submitpage.php?cid=".$cid."&pid=$row->num&sid=".$row->solution_id."\">Edit</a>";
			}else{
				echo "<a target=_self href=\"submitpage.php?id=".$row->problem_id."&sid=".$row->solution_id."\">Edit</a>";
			}
                      }
                     else
                        {if($row->contest_id!=''&&is_running(intval($row->contest_id))){
                                      echo "<td><a target=_blank href=showsource.php?id=".$row->solution_id.">".$language_name[$row->language]."</a>/";

                                     echo "<a target=_self href=\"submitpage.php?cid=".$cid."&pid=$row->num&sid=".$row->solution_id."\">Edit</a>";
                       }   
                       else
                                   echo "<td>".$language_name[$row->language];
                        }
		}
		echo "<td>".$row->code_length." B";
		
	}else echo "<td>------<td>------<td>".$language_name[$row->language]."<td>------";
	echo "<td>".$row->in_date;
	echo "</tr>";
}
mysql_free_result($result);
?>
</table>
<?
echo "[<a href=status.php?".$str2.">Top</a>]&nbsp;&nbsp;";
if (isset($_GET['prevtop']))
	echo "[<a href=status.php?".$str2."&top=".intval($_GET['prevtop']).">Previous Page</a>]&nbsp;&nbsp;";
else
	echo "[<a href=status.php?".$str2."&top=".($top+20).">Previous Page</a>]&nbsp;&nbsp;";
echo "[<a href=status.php?".$str2."&top=".$bottom."&prevtop=$top>Next Page</a>]";
?>
</center>
<?require_once("oj-footer.php");
?>
