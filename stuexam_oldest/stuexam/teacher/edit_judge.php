<?php
	require_once("./teacher-header.php");
?>
<?php
	if(isset($_POST['judge_des']))
	{
		require_once("../../include/check_post_key.php");
		$arr['question']=test_input($_POST['judge_des']);
		$arr['answer']=$_POST['answer'];
		$arr['point']=test_input($_POST['point']);
		$judgeid=intval($_POST['judgeid']);
		$arr['easycount']=intval($_POST['easycount']);
		$arr['isprivate']=intval($_POST['isprivate']);

		$prisql="SELECT `creator`,`isprivate` FROM `ex_judge` WHERE `judge_id`='$judgeid'";
		$prirow=fetchOne($prisql);
		$creator=$prirow['creator'];
		$private2=$prirow['isprivate'];
		if(checkAdmin(4,$creator))
		{
			alertmsg("You have no privilege to modify it!","edit_judge.php?id={$judgeid}",0);
		}
		else if($private2==2&&!checkAdmin(1))
		{
			alertmsg("You have no privilege to modify it!","admin_judge.php",0);
		}
		else
		{
			Update('ex_judge',$arr,"judge_id={$judgeid}");
			unset($arr);
			alertmsg("修改成功","admin_judge.php",0);
		}
	}
	else
	{
		if(isset($_GET['id']))
		{
			$id=intval($_GET['id']);
			if(filter_var($id, FILTER_VALIDATE_INT))
			{
				$query="SELECT `question`,`answer`,`creator`,`point`,`easycount`,`isprivate` FROM `ex_judge` 
				WHERE `judge_id`='$id'";
				$row=fetchOne($query);
				if($row)
				{
					$question=$row['question'];
					$answer=$row['answer'];
					$creator=$row['creator'];
					$point=$row['point'];
					$easycount=$row['easycount'];
					$isprivate=$row['isprivate'];
					if($isprivate==2&&!checkAdmin(1))
					{
						alertmsg("You have no privilege!","admin_judge.php",0);
					}
					if(!checkAdmin(1))
					{
						if($isprivate==1&&$creator!=$_SESSION['user_id'])
						{
							alertmsg("You have no privilege!","admin_judge.php",0);
						}
					}
				}
				else{
					alertmsg("No such problem");
				}
			}
			else{
				alertmsg("Invaild data");
			}
		}
		else{
			alertmsg("Invaild path");
		}
?>
<script language="javascript">
function chkinput(form){
	if(form.judge_des.value==""){
	alert("请输入题目描述");
	form.judge_des.focus();
	return(false); 
	}
	if(form.point.value==""){
	alert("请输入题目描述");
	form.point.focus();
	return(false); 
	}
	if(form.answer.value==""){
	alert("请输入答案!");
	return(false); 
	}
	return(true);
}
</script>
<div>
<div class="leftmenu pull-left" id="left">
<ul class="nav nav-pills bs-docs-sidenav">
	<li class=""><a href="./">考试管理</a></li>
	<li class=""><a href="admin_choose.php">选择题管理</a></li>
	<li class="active"><a href="admin_judge.php">判断题管理</a></li>
	<li class=""><a href="admin_fill.php">填空题管理</a></li>
	<?php
		if(checkAdmin(1)){
			echo "<li><a href=\"admin_point.php\">知识点管理</a></li>";
		}
	?>
	<li><a href="../">退出管理页面</a></li>
</ul>
</div>
<div class="container" style="margin-left:23%" id="right">
	<h2 style="text-align:center">查看修改判断题</h2>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>" onSubmit="return chkinput(this)">
		<div class="pull-left span8">
			<label>题目描述:</label>
			<textarea style="width:250px;height:150px;overflow-x:visible;overflow-y:visible;" 
			name="judge_des"><?=$question?></textarea>
		</div>
		<?require_once("../../include/set_post_key.php");?>
		<div class="span8">
			<strong>答案:</strong>
			对<input type="radio" name="answer" value="Y" <?=($answer=='Y')?"checked":""?> >&nbsp;
			错<input type="radio" name="answer" value="N" <?=($answer=='N')?"checked":""?> >&nbsp;
		</div>
		<div class="span8">
			<input type="hidden" value="<?=$id?>" name="judgeid">
			<label for="point">知识点:</label>
			<select name="point" id="point">
			<?php
				$sql="SELECT * FROM `ex_point`";
				$result=mysql_query($sql) or die(mysql_error());
				while($row=mysql_fetch_object($result))
				{
					if($row->point==$point)
						echo "<option value=\"$row->point\" selected>$row->point</option>";
					else
						echo "<option value=\"$row->point\">$row->point</option>";
				}
				mysql_free_result($result);
			?>
			</select>
			<label for="easycount">难度系数:</label>
			<select name="easycount" id="easycount">
				<option value="0" <?echo $easycount==0?"selected":""?> >0</option>
				<option value="1" <?echo $easycount==1?"selected":""?> >1</option>
				<option value="2" <?echo $easycount==2?"selected":""?> >2</option>
				<option value="3" <?echo $easycount==3?"selected":""?> >3</option>
				<option value="4" <?echo $easycount==4?"selected":""?> >4</option>
				<option value="5" <?echo $easycount==5?"selected":""?> >5</option>
				<option value="6" <?echo $easycount==6?"selected":""?> >6</option>
				<option value="7" <?echo $easycount==7?"selected":""?> >7</option>
				<option value="8" <?echo $easycount==8?"selected":""?> >8</option>
				<option value="9" <?echo $easycount==9?"selected":""?> >9</option>
				<option value="10" <?echo $easycount==10?"selected":""?> >10</option>
			</select>
			<label for="isprivate">请选题库类型:</label>
			<select name="isprivate" id="isprivate" onchange="showmsg()">
				<option value="0" <?echo $isprivate==0?"selected":""?> >公共题库</option>
				<option value="1" <?echo $isprivate==1?"selected":""?> >私人题库</option>
				<option value="2" <?echo $isprivate==2?"selected":""?> >系统隐藏</option>
			</select><strong><font color=red id="msg"></font></strong><br/>
			<input type="submit" value="提交" class="mybutton">
			<input type="button" value="返回" onclick="javascript:history.go(-1);" class="mybutton">
		</div>
	</form>
</div>
</div>
<script type="text/javascript">
function showmsg()
{
	if($('#isprivate').val()==0)
		$('#msg').html('(*公共题库所有人都可见)');
	else if($('#isprivate').val()==1)
		$('#msg').html('(*私人题库仅限本人和最高管理员可见)');
	else if($('#isprivate').val()==2)
		$('#msg').html('(*系统隐藏选择确认后,仅限最高管理员可以查看和修改，请谨慎选择和查看)');
}
$(function(){
	if($("#left").height()<700)
		$("#left").css("height",700)
	if($("#left").height()>$("#right").height()){
		$("#right").css("height",$("#left").height())
	}
	else{
		$("#left").css("height",$("#right").height())
	}
});
</script>
<?php
}
	require_once("./teacher-footer.php");
?>