<?php
	require_once("oj-header.php");
	echo "<title>SDIBT Online Judge WebBoard</title>";
	$sql="SELECT `title`, `cid`, `pid`, `status`, `top_level` FROM `topic` WHERE `tid` = '".mysql_escape_string($_REQUEST['tid'])."' AND `status` <= 1";
	$result=mysql_query($sql) or die("Error! ".mysql_error());
	$rows_cnt = mysql_num_rows($result) or die("Error! ".mysql_error());
	$row= mysql_fetch_object($result);
	$isadmin = isset($_SESSION['administrator']);
        $sz=20;
        $tid=strval(intval($_GET['tid']));
       if (isset($_GET['page']))
             $page=strval(intval($_GET['page']));
        else $page=0;
            $start=$page*$sz;

?>

<center>
<div style="width:90%; margin:0 auto; text-align:left;"> 
<div style="text-align:left;font-size:80%;float:left;">[ <a href="newpost.php">New Thread</a> ]</div>
<?
if ($isadmin){
	?><div style="font-size:80%; float:right"> Change sticky level to<?
	$adminurl = "threadadmin.php?target=thread&tid={$_REQUEST['tid']}&action=";
	if ($row->top_level == 0) echo "[ <a href=\"{$adminurl}sticky&level=3\">Level Top</a> ] [ <a href=\"{$adminurl}sticky&level=2\">Level Mid</a> ] [ <a href=\"{$adminurl}sticky&level=1\">Level Low</a> ]"; else echo "[ <a href=\"{$adminurl}sticky&level=0\">Standard</a> ]";
	?> | <?
	if ($row->status != 1) echo (" [ <a  href=\"{$adminurl}lock\">Lock</a> ]"); else echo(" [ <a href=\"{$adminurl}resume\">Resume</a> ]");
	?> | <?
	echo (" [ <a href=\"{$adminurl}delete\">Delete</a> ]");
	?></div><?
}
?>
<table style="width:100%; clear:both">
<tr align=center class='toprow'>
	<td style="text-align:left">
	<a href="discuss.php<?if ($row->pid!=0 && $row->cid!=null) echo "?pid=".$row->pid."&cid=".$row->cid;
	else if ($row->pid!=0) echo"?pid=".$row->pid; else if ($row->cid!=null) echo"?cid=".$row->cid;?>">
	<?php if ($row->pid!=0) echo "Problem ".$row->pid; else echo "MainBoard";?></a> >> <?php echo nl2br(htmlspecialchars($row->title));?></td>
</tr>

<?php
	$sql="SELECT `rid`, `author_id`, `time`, `content`, `status` FROM `reply` WHERE `topic_id` = '".mysql_escape_string($_REQUEST['tid'])."' AND `status` <=1 ORDER BY `rid` LIMIT $start,$sz";
	$result=mysql_query($sql) or die("Error! ".mysql_error());
	$rows_cnt = mysql_num_rows($result);
	$cnt=0;

	for ($i=0;$i<$rows_cnt;$i++){
		mysql_data_seek($result,$i);
		$row=mysql_fetch_object($result);
		$url = "threadadmin.php?target=reply&rid={$row->rid}&tid={$_REQUEST['tid']}&action=";
		$isuser = strtolower($row->author_id)==strtolower($_SESSION['user_id']);
?>
<tr align=center class='<?php echo ($cnt=!$cnt)?'even':'odd';?>row'>
	<td>
		
		<a name=post<?php echo $row->rid;?>></a>
		<div style="display:inline;text-align:left; float:left; margin:0 10px"><a href="userinfo.php?user=<?echo $row->author_id?>"><?php echo $row->author_id; ?> </a> @ <?php echo $row->time; ?></div>
		<div class="mon" style="display:inline;text-align:right; float:right">
			<?if (isset($_SESSION['administrator'])) {?>  
			<span>[ <a href="
				<? 
				if ($row->status==0) echo $url."disable\">Disable";
				else echo $url."resume\">Resume";
				?> </a> ]</span>
			<span>[ <a href="#">Reply</a> ]</span> 
			<? } ?>
			<span>[ <a href="#">Quote</a> ]</span>
			<span>[ <a href="#">Edit</a> ]</span>
			<span>[ <a 
			<?
				if ($isuser || $isadmin) echo "href=".$url."delete";
			?>
			>Delete</a> ]</span>
			<span style="width:5em;text-align:right;display:inline-block;font-weight:bold;margin:0 10px">
			<?php echo $start+$i+1;?>#</span>
		</div>
		<div style="text-align:left; clear:both; margin:10px 30px;white-space:normal;">
			<?php	if ($row->status == 0) echo nl2br(htmlspecialchars($row->content));
					else {
						if (!$isuser || $isadmin)echo "<div style=\"border-left:10px solid gray\"><font color=red><i>Notice : <br>This reply is blocked by administrator.</i></font></div>";
						if ($isuser || $isadmin) echo nl2br(htmlspecialchars($row->content));
					}
			?>
		</div>
		<div style="text-align:left; clear:both; margin:10px 30px; font-weight:bold; color:red"></div>
	</td>
</tr>
<?php
	}
?>
</table>
<div style="font-size:90%; width:100%; text-align:center">
<?
echo  "<a href='thread.php?tid=$tid'>[Top]</a>";
if($page>0){
    $page--;
    echo "&nbsp;&nbsp;<a href='thread.php?tid=$tid&page=$page'>[Previous]</a>";
    $page++;
}
else
     echo "&nbsp;&nbsp;<a href='thread.php?tid=$tid&page=$page'>[Previous]</a>";

if($rows_cnt==$sz){
     $page++;
    echo "&nbsp;&nbsp;<a href='thread.php?tid=$tid&page=$page'>[Next]</a>";
    $page--;
}
else
    echo "&nbsp;&nbsp;<a href='thread.php?tid=$tid&page=$page'>[Next]</a>";
//echo [<a href="thread.php?">Previous Page</a>]  [<a href="#">Next Page</a>] </div>
echo "</div>"
?>

<?
if (isset($_SESSION['user_id'])){?>
<div style="font-size:80%;"><div style="margin:0 10px">New Reply:</div></div>
<form action="post.php?action=reply" method=post>
<input type=hidden name=tid value=<?php echo $_REQUEST['tid'];?>>
<div><textarea name=content style="border:1px dashed #8080FF; width:700px; height:200px; font-size:75%;margin:0 10px; padding:10px"></textarea></div>
<?php if($OJ_VCODE){?>
<div style="font-size:80%; margin:0px 10px">Verification code:</div>
<div><input name="vcode" size=4 type=text style="border:1px dashed #8080FF; width:100px; height:20px; font-size:75%;margin:0 10px; padding:2px 10px"> <img src=vcode.php align="absmiddle">*</div>
<?php }?>
<div><input type="submit" style="margin:5px 10px" value="Submit"></input></div>
</form>
<?
}
?>

</center>
</div>

<?require_once("./oj-footer.php")?>
