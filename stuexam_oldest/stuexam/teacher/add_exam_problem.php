<?php
	require_once("./teacher-header.php");
	if(isset($_GET['type'])&&isset($_GET['eid']))
	{
		$type=intval($_GET['type']);
		$eid=intval($_GET['eid']);
		$prisql="SELECT `creator` FROM `exam` WHERE `exam_id`='$eid'";
		$prirow=fetchOne($prisql);
		$creator=$prirow['creator'];
		if(checkAdmin(4,$creator))
		{
			alertmsg("You have no privilege of this exam","./",0);
		}
		else
		{
			if(isset($_GET['search']))
			{
				$search=mysql_real_escape_string($_GET['search']);
				if($search!='')
					$searchsql=" WHERE (`creator` like '%$search%' or `point` like '%$search%')";
				else
					$searchsql="";
			}
			else
			{
				$search="";
				$searchsql="";
			}
			if(isset($_GET['problem']))
			{
				$problem = intval($_GET['problem']);
				$prosql = problemshow($problem,$searchsql);
			}
			else
			{
				$problem=0;
				if($searchsql=="")
					$prosql=" WHERE `isprivate`='$problem'";
				else
					$prosql=" AND `isprivate`='$problem'";
			}
			if(filter_var($type, FILTER_VALIDATE_INT)&&filter_var($eid, FILTER_VALIDATE_INT)&&$eid>0)
			{
				if($type==1||$type==2||$type==3)
				{
					if($type==1)
						$pageinfo = splitpage('ex_choose',$searchsql,$prosql);
					else if($type==2)
						$pageinfo = splitpage('ex_judge',$searchsql,$prosql);
					else if($type==3)
						$pageinfo = splitpage('ex_fill',$searchsql,$prosql);
					$page = $pageinfo['page'];
					$prepage=$pageinfo['prepage'];
					$startpage=$pageinfo['startpage'];
					$endpage=$pageinfo['endpage'];
					$nextpage=$pageinfo['nextpage'];
					$lastpage=$pageinfo['lastpage'];
					$eachpage=$pageinfo['eachpage'];
					$sqladd=$pageinfo['sqladd'];
				}
				if($type==9)// main about the exam
				{
					?>
					<div>
					<div class="pull-left leftmenu" id="left">
					<ul class="nav nav-pills bs-docs-sidenav">
					<li class="active"><a href="./">考试管理</a></li>
					<li><a href="admin_choose.php">选择题管理</a></li>
					<li><a href="admin_judge.php">判断题管理</a></li>
					<li><a href="admin_fill.php">填空题管理</a></li>
					<?php
						if(checkAdmin(1)){
							echo "<li><a href=\"admin_point.php\">知识点管理</a></li>";
						}
					?>
					<li><a href="../">退出管理页面</a></li>
					</ul>
					</div>
					<div class="container" style="margin-left:23%" id="right">
					<h2 style="text-align:center">编辑考试</h2>
					<div style="height:40px">
					<ul class="nav nav-pills pull-left">
			  <li class="active"><a href="add_exam_problem.php?eid=<?=$eid?>&type=9">考试基本信息</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=5">试卷一览</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=1">选择题</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=2">判断题</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=3">填空题</a></li>
					<li><a href="add_program.php?eid=<?=$eid?>&type=4">程序题</a></li>
					<li><a href="add_exam_user.php?eid=<?=$eid?>">添加考生</a></li>
					<li><a href="exam_user_score.php?eid=<?=$eid?>">考生成绩</a></li>
					<li><a href="exam_analysis.php?eid=<?=$eid?>">考试分析</a></li>
					<?php
						if(checkAdmin(1)){
							echo "<li><a href=\"rejudge.php?eid=$eid\">Rejudge</a></li>";
						}
					?>
					</ul>
					</div>
					<?php
					$query="SELECT * FROM `exam` WHERE `exam_id`='$eid'";
					$result=mysql_query($query) or die(mysql_error());
					$row_cnt=mysql_num_rows($result);
					if($row_cnt){
						$row=mysql_fetch_assoc($result);
						$title=htmlspecialchars($row['title']);
						$starttime=$row['start_time'];
						$endtime=$row['end_time'];
						$xzfs=$row['choosescore'];
						$pdfs=$row['judgescore'];
						$tkfs=$row['fillscore'];
						$yxjgfs=$row['prgans'];
						$cxtkfs=$row['prgfill'];
						$cxfs=$row['programscore'];
						$isvip=$row['isvip'];
						mysql_free_result($result);
					?>
						<div style="margin-top:10px">
						<form action="update_exam_ok.php" method="post" onSubmit="return chkinput(this)">
						<br/>
						考试名称:<input type="text" maxlength="100" name="examname" value="<?php echo $title;?>" /><br/>
						考试开始时间:<p></p>
						<input type="text" name="syear"   value="<?php echo substr($starttime,0,4)?>" style="width:40px" />年-
						<input type="text" name="smonth"  value="<?php echo substr($starttime,5,2)?>" style="width:40px" />月-
						<input type="text" name="sday"    value="<?php echo substr($starttime,8,2)?>" style="width:40px" />日-
						<input type="text" name="shour"   value="<?php echo substr($starttime,11,2)?>" style="width:40px" />时-
						<input type="text" name="sminute" value="<?php echo substr($starttime,14,2)?>" style="width:40px" />分</p>
						考试结束时间:<p></p>
						<input type="text" name="eyear"   value="<?php echo substr($endtime,0,4)?>" style="width:40px" />年-
						<input type="text" name="emonth"  value="<?php echo substr($endtime,5,2)?>" style="width:40px" style="width:40px" />月-
					    <input type="text" name="eday"    value="<?php echo substr($endtime,8,2)?>" style="width:40px" />日-
					    <input type="text" name="ehour"   value="<?php echo substr($endtime,11,2)?>" style="width:40px" />时-
						<input type="text" name="eminute" value="<?php echo substr($endtime,14,2)?>" style="width:40px">分</p>
						<font color=red>*以下数值只支持整数</font><br/>
						(1)选择题每题分值:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="xzfs" style="width:50px" value="<?php echo $xzfs;?>" />分;<br/>
						(2)判断题每题分值:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="pdfs" style="width:50px" value="<?php echo $pdfs;?>" />分;<br/>
						(3)基础填空题每空分值:&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="tkfs" style="width:50px" value="<?php echo $tkfs;?>" />分;<br/>
						(4)写运行结果题每题分值:<input type="text" name="yxjgfs" style="width:50px" value="<?php echo $yxjgfs?>" />分;<br/>
						(5)程序填空题每题分值:&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="cxtkfs" style="width:50px" value="<?php echo $cxtkfs?>" />分;<br/>
						(6)程序设计题每题分值:&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="cxfs" style="width:50px" value="<?php echo $cxfs;?>" />分;<br/>
						是否限定一个账号只能在一台机器登陆:<select name="isvip" class="span1">
						<option value="Y" <?echo $isvip=='Y'?"selected":"" ?> >Yes</option>
						<option value="N" <?echo $isvip=='N'?"selected":"" ?> >No</option>
						</select><br/>
						<?require_once("../../include/set_post_key.php");?>
						<input type="hidden" value="<?=$eid?>" name="eid">
						<input type="submit" value="提交" class="mybutton">
						<input type="reset" value="重置" class="mybutton">
						</form>
						</div>
						</div>
						</div>
					<?php
					}
					else
					{
						echo "No such Exam";
						exit(0);
					}
				}
				else if($type==1)//add choose
				{
					?>
					<div>
					<div class="leftmenu pull-left" id="left">
					<ul class="nav nav-pills bs-docs-sidenav">
					<li class="active"><a href="./">考试管理</a></li>
					<li><a href="admin_choose.php">选择题管理</a></li>
					<li><a href="admin_judge.php">判断题管理</a></li>
					<li><a href="admin_fill.php">填空题管理</a></li>
					<?
						if(checkAdmin(1)){
							echo "<li><a href=\"admin_point.php\">知识点管理</a></li>";
						}
					?>
					<li><a href="../">退出管理页面</a></li>
					</ul>
					</div>
					<div class="container" style="margin-left:23%" id="right">
					<h2 style="text-align:center">添加选择题</h2>
					<div style="height:40px">
					<ul class="nav nav-pills pull-left">
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=9">考试基本信息</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=5">试卷一览</a></li>
			  <li class="active"><a href="add_exam_problem.php?eid=<?=$eid?>&type=1">选择题</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=2">判断题</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=3">填空题</a></li>
					<li><a href="add_program.php?eid=<?=$eid?>&type=4">程序题</a></li>
					<li><a href="add_exam_user.php?eid=<?=$eid?>">添加考生</a></li>
					<li><a href="exam_user_score.php?eid=<?=$eid?>">考生成绩</a></li>
					<li><a href="exam_analysis.php?eid=<?=$eid?>">考试分析</a></li>
					<?
						if(checkAdmin(1)){
							echo "<li><a href=\"rejudge.php?eid=$eid\">Rejudge</a></li>";
						}
					?>
					</ul>
					</div>
					<div class="pull-left" style="width:400px">
					<input type="button" value="查看公共题库" class="mybutton" onclick="window.location.href='?eid=<?=$eid?>&type=<?=$type?>&problem=0'">
					<input type="button" value="查看私人题库" class="mybutton" onclick="window.location.href='?eid=<?=$eid?>&type=<?=$type?>&problem=1'">
					<?
						if(checkAdmin(1)){
							echo "<input type=\"button\" value=\"查看隐藏题库\" class=\"mybutton\" onclick=\"window.location.href='?eid=$eid&type=$type&problem=2'\">";
						}
					?>
					</div>
					<form class="form-search pull-right">
  					<div class="input-append">
  					<input type="hidden" name="eid" value="<?=$eid?>" />
  					<input type="hidden" name="type" value="<?=$type?>" />
  					<input type="hidden" name="page" value="<?=$page?>" />
  					<input type="hidden" name="problem" value="<?=$problem?>">
   	 				<input type="text" class="span3 search-query" value="<?=$search?>" name="search" placeholder="查询创建者或知识点">
    				<input type="submit" class="btn" value="Search">
    		 		</div>
					</form>
					<table class="table table-hover table-bordered table-striped table-condensed jiadian">
					<thread>
					<th width=5%>序号</th>
					<th width=32%>题目描述</th>
					<th width=10%>创建时间</th>
					<th width=8%>创建者</th>
					<th width=8%>知识点</th>
					<th width=4%>难度</th>
					<th width=8%>操作</th>
					</thread>
					<tbody>
					<?php
						$cntchoose=1+($page-1)*$eachpage;
						$sql="SELECT `choose_id`,`question`,`addtime`,`creator`,`point`,`easycount` FROM `ex_choose` $searchsql $prosql ORDER BY `choose_id` ASC $sqladd";
						$result = mysql_query($sql) or die(mysql_error());
						while($row=mysql_fetch_object($result)){
						echo "<tr>";
						echo "<td>$cntchoose</td>";
						$question=$row->question;
						$cntchoose++;
						echo "<td><a href='edit_choose.php?id=$row->choose_id'>$question</a></td>";
						echo "<td style=\"font-size:9px\">$row->addtime</td>";
						echo "<td>$row->creator</td>";
						echo "<td>$row->point</td>";
						echo "<td>$row->easycount</td>";
						$query="SELECT COUNT(*) FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='1' AND `question_id`='$row->choose_id'";
						$myresult=mysql_query($query) or die(mysql_error());
						$myrow=mysql_fetch_array($myresult);
						$num=$myrow[0];
						if($num==1)
							echo "<td>已添加</td>";
						else
							echo "<td><span id=\"choose$row->choose_id\"><a href=\"javascript:void(0);\" 
						onclick=\"addquestion('choose$row->choose_id','$eid','$row->choose_id','$type')\">添加到试卷</a></span></td>";
						mysql_free_result($myresult);
						echo "</tr>";
						}
						mysql_free_result($result);
						echo "</tbody></table>";
						$url = "add_exam_problem.php?eid=$eid&type=1";
						$urllast = "&search={$search}&problem={$problem}";
						showpagelast($url,$pageinfo,$urllast);
						echo"</div></div>";
				}
				else if($type==2)//add judge
				{
					?>
					<div>
					<div class="leftmenu pull-left" id="left">
					<ul class="nav nav-pills bs-docs-sidenav">
					<li class="active"><a href="./">考试管理</a></li>
					<li><a href="admin_choose.php">选择题管理</a></li>
					<li><a href="admin_judge.php">判断题管理</a></li>
					<li><a href="admin_fill.php">填空题管理</a></li>
					<?php
						if(checkAdmin(1)){
							echo "<li><a href=\"admin_point.php\">知识点管理</a></li>";
						}
					?>
					<li><a href="../">退出管理页面</a></li>
					</ul>
					</div>
					<div class="container" style="margin-left:23%" id="right">
					<h2 style="text-align:center">添加判断题</h2>
					<div style="height:40px">
					<ul class="nav nav-pills pull-left">
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=9">考试基本信息</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=5">试卷一览</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=1">选择题</a></li>
			  <li class="active"><a href="add_exam_problem.php?eid=<?=$eid?>&type=2">判断题</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=3">填空题</a></li>
					<li><a href="add_program.php?eid=<?=$eid?>&type=4">程序题</a></li>
					<li><a href="add_exam_user.php?eid=<?=$eid?>">添加考生</a></li>
					<li><a href="exam_user_score.php?eid=<?=$eid?>">考生成绩</a></li>
					<li><a href="exam_analysis.php?eid=<?=$eid?>">考试分析</a></li>
					<?
						if(checkAdmin(1)){
							echo "<li><a href=\"rejudge.php?eid=$eid\">Rejudge</a></li>";
						}
					?>
					</ul>
					</div>
					<div class="pull-left" style="width:400px">
					<input type="button" value="查看公共题库" class="mybutton" onclick="window.location.href='?eid=<?=$eid?>&type=<?=$type?>&problem=0'">
					<input type="button" value="查看私人题库" class="mybutton" onclick="window.location.href='?eid=<?=$eid?>&type=<?=$type?>&problem=1'">
					<?
						if(checkAdmin(1)){
							echo "<input type=\"button\" value=\"查看隐藏题库\" class=\"mybutton\" onclick=\"window.location.href='?eid=$eid&type=$type&problem=2'\">";
						}
					?>
					</div>
					<form class="form-search pull-right">
  					<div class="input-append">
  					<input type="hidden" name="eid" value="<?=$eid?>" >
  					<input type="hidden" name="type" value="<?=$type?>" >
  					<input type="hidden" name="page" value="<?=$page?>" >
  					<input type="hidden" name="problem" value="<?=$problem?>" >
   	 				<input type="text" class="span3 search-query" value="<?=$search?>" name="search" placeholder="查询创建者或知识点">
    				<input type="submit" class="btn" value="Search">
    		 		</div>
					</form>
					<table class="table table-hover table-bordered table-striped table-condensed jiadian">
					<thread>
					<th width=5%>序号</th>
					<th width=32%>题目描述</th>
					<th width=10%>创建时间</th>
					<th width=8%>创建者</th>
					<th width=8%>知识点</th>
					<th width=4%>难度</th>
					<th width=8%>操作</th>
					</thread>
					<tbody>
					<?
					$cntjudge=1+($page-1)*$eachpage;
					$sql="SELECT `judge_id`,`question`,`addtime`,`creator`,`point`,`easycount` FROM `ex_judge` $searchsql $prosql ORDER BY `judge_id` ASC $sqladd";
					$result = mysql_query($sql) or die(mysql_error());
					while($row=mysql_fetch_object($result)){
					echo "<tr>";
					echo "<td>$cntjudge</td>";
					$question=$row->question;
					$cntjudge++;
					echo "<td><a href='edit_judge.php?id=$row->judge_id'>$question</a></td>";
					echo "<td style=\"font-size:9px\">$row->addtime</td>";
					echo "<td>$row->creator</td>";
					echo "<td>$row->point</td>";
					echo "<td>$row->easycount</td>";
					//$query="SELECT COUNT(*) FROM `exp_judge` WHERE `exam_id`='$eid' and `judge_id`='$row->judge_id'";
					$query="SELECT COUNT(*) FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='2' AND `question_id`='$row->judge_id'";
					$myresult=mysql_query($query) or die(mysql_error());
					$myrow=mysql_fetch_array($myresult);
					$num=$myrow[0];
					if($num==1)
						echo "<td>已添加</td>";
					else
						echo "<td><span id=\"judge$row->judge_id\"><a href=\"javascript:void(0);\" 
					onclick=\"addquestion('judge$row->judge_id','$eid','$row->judge_id','$type')\">添加到试卷</a></span></td>";
					mysql_free_result($myresult);
					echo "</tr>";
					}
					mysql_free_result($result);
					echo "</tbody></table>";
					$url = "add_exam_problem.php?eid=$eid&type=2";
					$urllast = "&search={$search}&problem={$problem}";
					showpagelast($url,$pageinfo,$urllast);
					echo "</div></div>";
				}
				else if($type==3)//add fill
				{
					?>
					<div>
					<div class="leftmenu pull-left" id="left">
					<ul class="nav nav-pills bs-docs-sidenav">
					<li class="active"><a href="./">考试管理</a></li>
					<li><a href="admin_choose.php">选择题管理</a></li>
					<li><a href="admin_judge.php">判断题管理</a></li>
					<li><a href="admin_fill.php">填空题管理</a></li>
					<?
						if(checkAdmin(1))
						{
							echo "<li><a href=\"admin_point.php\">知识点管理</a></li>";
						}
					?>
					<li><a href="../">退出管理页面</a></li>
					</ul>
					</div>
					<div class="container" style="margin-left:23%" id="right">
					<h2 style="text-align:center">添加填空题</h2>
					<div style="height:40px">
					<ul class="nav nav-pills pull-left">
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=9">考试基本信息</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=5">试卷一览</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=1">选择题</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=2">判断题</a></li>
			  <li class="active"><a href="add_exam_problem.php?eid=<?=$eid?>&type=3">填空题</a></li>
					<li><a href="add_program.php?eid=<?=$eid?>&type=4">程序题</a></li>
					<li><a href="add_exam_user.php?eid=<?=$eid?>">添加考生</a></li>
					<li><a href="exam_user_score.php?eid=<?=$eid?>">考生成绩</a></li>
					<li><a href="exam_analysis.php?eid=<?=$eid?>">考试分析</a></li>
					<?
					if(checkAdmin(1))
					{
						echo "<li><a href=\"rejudge.php?eid=$eid\">Rejudge</a></li>";
					}
					?>
					</ul>
					</div>
					<div class="pull-left" style="width:400px">
					<input type="button" value="查看公共题库" class="mybutton" onclick="window.location.href='?eid=<?=$eid?>&type=<?=$type?>&problem=0'">
					<input type="button" value="查看私人题库" class="mybutton" onclick="window.location.href='?eid=<?=$eid?>&type=<?=$type?>&problem=1'">
					<?
						if(checkAdmin(1))
						{
							echo "<input type=\"button\" value=\"查看隐藏题库\" class=\"mybutton\" onclick=\"window.location.href='?eid=$eid&type=$type&problem=2'\">";
						}
					?>
					</div>
					<form class="form-search pull-right">
  					<div class="input-append">
  					<input type="hidden" name="eid" value="<?=$eid?>" >
  					<input type="hidden" name="type" value="<?=$type?>" >
  					<input type="hidden" name="page" value="<?=$page?>" >
  					<input type="hidden" name="problem" value="<?=$problem?>">
   	 				<input type="text" class="span3 search-query" value="<?=$search?>" name="search" placeholder="查询创建者或知识点">
    				<input type="submit" class="btn" value="Search">
    		 		</div>
					</form>
					<table class="table table-hover table-bordered table-striped table-condensed jiadian">
					<thread>
					<th width=4%>序号</th>
					<th width=30%>题目描述</th>
					<th width=8%>创建时间</th>
					<th width=8%>创建者</th>
					<th width=8%>知识点</th>
					<th width=8%>题型</th>
					<th width=4%>难度</th>
					<th width=8%>操作</th>
					</thread>
					<tbody>
					<?
					$cntfill=1+($page-1)*$eachpage;
					$sql="SELECT `fill_id`,`question`,`addtime`,`creator`,`point`,`easycount`,`kind` FROM `ex_fill` $searchsql $prosql ORDER BY `fill_id` ASC $sqladd";
					$result = mysql_query($sql) or die(mysql_error());
					while($row=mysql_fetch_object($result)){
					echo "<tr>";
					echo "<td>$cntfill</td>";
					$question=$row->question;
					$cntfill++;
					echo "<td><a href='edit_fill.php?id=$row->fill_id'>$question</a></td>";
					echo "<td style=\"font-size:9px\">$row->addtime</td>";
					echo "<td>$row->creator</td>";
					echo "<td>$row->point</td>";
					if($row->kind==1)
						echo "<td>基础填空题</td>";
					else if($row->kind==2)
						echo "<td>写运行结果</td>";
					else
						echo "<td>程序填空题</td>";
					echo "<td>$row->easycount</td>";
					//$query="SELECT COUNT(*) FROM `exp_fill` WHERE `exam_id`='$eid' and `fill_id`='$row->fill_id'";
					$query="SELECT COUNT(*) FROM `exp_question` WHERE `exam_id`='$eid' AND `type`='3' AND `question_id`='$row->fill_id'";
					$myresult=mysql_query($query) or die(mysql_error());
					$myrow=mysql_fetch_array($myresult);
					$num=$myrow[0];
					if($num==1)
						echo "<td>已添加</td>";
					else
						echo "<td><span id=\"fill$row->fill_id\"><a href=\"javascript:void(0);\" 
					onclick=\"addquestion('fill$row->fill_id','$eid','$row->fill_id','$type')\">添加到试卷</a></span></td>";
					mysql_free_result($myresult);
					echo "</tr>";
					}
					mysql_free_result($result);
					echo "</tbody></table>";
					$pageinfo['problem']=$problem;
					$pageinfo['search']=$search;
					$url = "add_exam_problem.php?eid=$eid&type=3";
					$urllast = "&search={$search}&problem={$problem}";
					showpagelast($url,$pageinfo,$urllast);
					echo "</div></div>";
				}
				else if($type==5)//the main exam question
				{
					?>
					<div class="myfont">
					<div class="leftmenu pull-left" id="left">
					<ul class="nav nav-pills bs-docs-sidenav">
					<li class="active"><a href="./">考试管理</a></li>
					<li><a href="admin_choose.php">选择题管理</a></li>
					<li><a href="admin_judge.php">判断题管理</a></li>
					<li><a href="admin_fill.php">填空题管理</a></li>
					<?
						if(checkAdmin(1)){
							echo "<li><a href=\"admin_point.php\">知识点管理</a></li>";
						}
					?>
					<li><a href="../">退出管理页面</a></li>
					</ul>
					</div>
					<style>
						.container pre{
							font-size: 16px;
						}
					</style>
					<div class="container" style="margin-left:23%" id="right">
					<h2 style="text-align:center">试卷一览</h2>
					<div style="height:40px">
					<ul class="nav nav-pills pull-left">
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=9">考试基本信息</a></li>
			  <li class="active"><a href="add_exam_problem.php?eid=<?=$eid?>&type=5">试卷一览</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=1">选择题</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=2">判断题</a></li>
					<li><a href="add_exam_problem.php?eid=<?=$eid?>&type=3">填空题</a></li>
					<li><a href="add_program.php?eid=<?=$eid?>&type=4">程序题</a></li>
					<li><a href="add_exam_user.php?eid=<?=$eid?>">添加考生</a></li>
					<li><a href="exam_user_score.php?eid=<?=$eid?>">考生成绩</a></li>
					<li><a href="exam_analysis.php?eid=<?=$eid?>">考试分析</a></li>
					<?
					if(checkAdmin(1)){
						echo "<li><a href=\"rejudge.php?eid=$eid\">Rejudge</a></li>";
					}
					?>
					</ul>
					</div>
					<br>
					<table class="pull-left huanhang">
					<tr><td><h5>一.选择题</h5></td></tr>
					<?
						$numofchoose=0;
						$query1="SELECT `ex_choose`.`choose_id`,`question`,`ams`,`bms`,`cms`,`dms`,`answer` FROM `ex_choose`,`exp_question` 
						WHERE `exam_id`='$eid' AND `type`='1' AND `ex_choose`.`choose_id`=`exp_question`.`question_id` ORDER BY `choose_id`";
						$result1=mysql_query($query1) or die(mysql_error());
						while($row1=mysql_fetch_object($result1)){
							$numofchoose++;
							$question="<pre>".$row1->question."</pre>";
							echo "<tr><td>$numofchoose.$question";
							echo "<a href=\"del_exam_problem.php?eid=$eid&type=1&qid=$row1->choose_id\">[去除该题]</a>
							<font color=red>答案:$row1->answer</font></td></tr>";
							echo "<tr><td>(A) $row1->ams</td></tr>";
							echo "<tr><td>(B) $row1->bms</td></tr>";
							echo "<tr><td>(C) $row1->cms</td></tr>";
							echo "<tr><td>(D) $row1->dms</td></tr>";
						}
						mysql_free_result($result1);
					?>
					<tr><td><h5>二.判断题</h5></td></tr>
					<?php
						$numofjudge=0;
						$query2="SELECT `ex_judge`.`judge_id`,`question`,`answer` FROM `ex_judge`,`exp_question` 
						WHERE `exam_id`='$eid' AND `type`='2' AND `ex_judge`.`judge_id`=`exp_question`.`question_id` ORDER BY `judge_id`";
						$result2=mysql_query($query2) or die(mysql_error());
						while($row2=mysql_fetch_object($result2)){
							$numofjudge++;
							echo "<tr>";
							$question="<pre>".$row2->question."</pre>";
							echo "<tr><td>$numofjudge.$question";
							echo "<a href=\"del_exam_problem.php?eid=$eid&type=2&qid=$row2->judge_id\">[去除该题]</a>
							<font color=red>答案:$row2->answer</font></td>";
							echo "</tr>";
						}
						mysql_free_result($result2);
					?>
					<tr><td><h5>三.填空题</h5></td></tr>
					<?php
						$numoffill=0;
						$numofprgans=0;
						$numofprgfill=0;
						$fillnum=0;
						$query3="SELECT `ex_fill`.`fill_id`,`question`,`answernum`,`kind` FROM `ex_fill`,`exp_question` 
						WHERE `exam_id`='$eid' AND `type`='3' AND `ex_fill`.`fill_id`=`exp_question`.`question_id` ORDER BY `fill_id`";
						$result3=mysql_query($query3) or die(mysql_error());
						while($row3=mysql_fetch_object($result3)){
							$fillnum++;
							echo "<tr>";
							$question="<pre>".$row3->question."</pre>";
							echo "<tr><td>$fillnum.$question";
							echo "<a href=\"del_exam_problem.php?eid=$eid&type=3&qid=$row3->fill_id\">[去除该题]</a></td>";
							echo "</tr>";
							if($row3->kind==1)
								$numoffill+=$row3->answernum;
							else if($row3->kind==2)
								$numofprgans++;
							else
								$numofprgfill++;
							$fillanswer="SELECT `answer_id`,`answer` FROM `fill_answer` WHERE `fill_id`='$row3->fill_id' ORDER BY `answer_id`";
							$fillresult=mysql_query($fillanswer) or die(mysql_error());
							while($fillrow=mysql_fetch_object($fillresult)){
								echo "<tr><td><font color=red>答案$fillrow->answer_id:$fillrow->answer</font></td></tr>";
							}
						}
						mysql_free_result($result3);
					?>
					<tr><td><h5>四.程序设计题</h5></td></tr>
					<?php
						$numofprogram=0;
						/*$query4="SELECT `program_id`,`title`,`description` FROM `exp_program`,`problem` WHERE `exam_id`='$eid' AND `program_id`=`problem_id`";*/
						$query4="SELECT `question_id` as `program_id`,`title`,`description` FROM `exp_question`,`problem` 
						WHERE `exam_id`='$eid' AND `type`='4' AND `question_id`=`problem_id`";
						$result4=mysql_query($query4) or die(mysql_error());
						while($row4=mysql_fetch_object($result4)){
							$numofprogram++;
							echo "<tr>";
							echo "<td><pre><span id=\"whethershow\">$numofprogram.</span><a href='../../problem.php?id=$row4->program_id'>$row4->title</a>";
							echo "<h4>Description</h4>";
							echo "<p>$row4->description</p>";
							echo "</pre></td>";
							echo "</tr>";
						}
					?>
					</table>
					<?php
						$querymain="SELECT `choosescore`,`judgescore`,`fillscore`,`prgans`,`prgfill`,`programscore` 
						FROM `exam` WHERE `exam_id`='$eid'";
						$resultmain=mysql_query($querymain) or die(mysql_error());
						$rowmain=mysql_fetch_array($resultmain);
						$choosescore=$rowmain[0];
						$judgescore=$rowmain[1];
						$fillscore=$rowmain[2];
						$prgans=$rowmain[3];
						$prgfill=$rowmain[4];
						$programscore=$rowmain[5];
						mysql_free_result($resultmain);
						$sumchoose=$choosescore*$numofchoose;
						$sumjudge=$judgescore*$numofjudge;
						$sumfill=$fillscore*$numoffill+$prgans*$numofprgans+$prgfill*$numofprgfill;
						$sumprogram=$programscore*$numofprogram;
						$sumquestion=$sumchoose+$sumjudge+$sumfill+$sumprogram;
					?>
					<table class="pull-left"  border=1 style="text-align:center">
					<thread>
						<th>题型</th>
						<th>每题(空)分数</th>
						<th>题(空)数</th>
						<th>总分</th>
					</thread>
					<tbody>
						<tr>
							<td>选择题</td>
							<td><?=$choosescore?></td>
							<td><?=$numofchoose?>道</td>
							<td><?=$sumchoose?>分</td>
						</tr>
						<tr>
							<td>判断题</td>
							<td><?=$judgescore?></td>
							<td><?=$numofjudge?>道</td>
							<td><?=$sumjudge?>分</td>
						</tr>
						<tr>
							<td>基础填空题</td>
							<td><?=$fillscore?></td>
							<td><?=$numoffill?>空</td>
							<td><?=$fillscore*$numoffill?>分</td>
						</tr>
						<tr>
							<td>写运行结果</td>
							<td><?=$prgans?></td>
							<td><?=$numofprgans?>道</td>
							<td><?=$prgans*$numofprgans?>分</td>
						</tr>
						<tr>
							<td>程序填空题</td>
							<td><?=$prgfill?></td>
							<td><?=$numofprgfill?>道</td>
							<td><?=$prgfill*$numofprgfill?>分</td>
						</tr>
						<tr>
							<td>程序设计题</td>
							<td><?=$programscore?></td>
							<td><?=$numofprogram?>道</td>
							<td><?=$sumprogram?>分</td>
						</tr>
						<tr>
							<td>总计</td>
							<td>-----</td>
							<td>-----</td>
							<td><?=$sumquestion?>分</td>
						</tr>
					</tbody>
					</table>
					</div>
					</div>
					<?
				}
				else{
					alertmsg("Invaild type");
				}
			}
			else{
				alertmsg("Invaild data");
			}
		}
	}
	else{
		alertmsg("Invaild path");
	}
?>
<script src="../css/questionadd.js"></script>
<script type="text/javascript">
function chkinput(form)
{
	for(var i=0;i<form.length;i++)
	{
		if(form.elements[i].value=="")
		{
			alert("信息不能为空");
			form.elements[i].focus();
			return false;
		}
	}
	return(true);
}
// $(function(){
// 		var x=<?if(isset($numofprogram)) echo "1";else echo "0";?>;
// 		if(x==1)
// 		$('#whethershow').html("");
// });
$(function(){
	if($("#left").height()<700)
		$("#left").css("height",700)
	if($("#left").height()>$("#right").height()){
		$("#right").css("height",$("#left").height())
	}
	else{
		$("#left").css("height",$("#right").height())
	}
	var x=<?if(isset($numofprogram)) echo "1";else echo "0";?>;
		if(x==1)
		$('#whethershow').html("");
});
</script>
<?php
	require_once("./teacher-footer.php");
?>