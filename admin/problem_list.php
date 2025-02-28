<?
require("admin-header.php");
require_once("../include/set_get_key.php");
if (!(isset($_SESSION['administrator'])||isset($_SESSION['contest_creator'])||isset($_SESSION['problem_editor']))){
	echo "<a href='../loginpage.php'>Please Login First!</a>";
	exit(1);
}

$sql="SELECT max(`problem_id`) as upid FROM `problem`";
$page_cnt=100;
$result=mysql_query($sql);
echo mysql_error();
$row=mysql_fetch_object($result);
$cnt=intval($row->upid)-1000;
$cnt=intval($cnt/$page_cnt);
if (isset($_GET['page'])){
	$page=intval($_GET['page']);
}else $page=$cnt+1;
$pstart=900+$page_cnt*intval($page);
$pend=$pstart+$page_cnt-1;

echo "<title>Problem List</title>";
echo "<center><h2>Problem List</h2>";

for ($i=1;$i<=$cnt+1;$i++){
	if ($i>1) echo '&nbsp;';
	if ($i==$page) echo "<span class=red>$i</span>";
	else echo "<a href='problem_list.php?page=".$i."'>".$i."</a>";
}
echo "</center>";
if(isset($_GET['search'])){
   if(trim($_GET['search'])=="")
      $sql="select  `problem_id`,`title`,`in_date`,`defunct`,`author` FROM `problem` where `defunct`='Y'  order by `problem_id` desc  ";
   else
     {
      $search=mysql_real_escape_string($_GET['search']);
      $sql="select  `problem_id`,`title`,`in_date`,`defunct`,`author` FROM `problem` where (author like '%$search%' or title like '%$search%' or source like '%$search%') order by `problem_id` desc  ";
      }
}
else

{
    $sql="select `problem_id`,`title`,`in_date`,`defunct`,`author` FROM `problem` where problem_id>=$pstart and problem_id<=$pend order by `problem_id` desc";

 }
//echo $sql;
$result=mysql_query($sql) or die(mysql_error());
echo "<center><table width=90% border=1>";
//echo "<form method=post action=contest_add.php>";
if (isset($_SESSION['administrator']))
  {     
         echo "<form method=post action=contest_add.php>";
         echo "<tr><td colspan=8><input type=submit name='problem2contest' value='CheckToNewContest'>";
}
echo "<tr><td>PID<td>Title<td>author<td>Date";
//if(isset($_SESSION['administrator'])){
    echo "<td>Defunct<td>Edit<td>TestData<td>Action</tr>";
//}
for (;$row=mysql_fetch_object($result);){
     if($row->defunct=="N" ||($row->author==$_SESSION['problem_editor']&&$row->author==$_SESSION['user_id']))
     {
	echo "<tr>";
	echo "<td>".$row->problem_id;
         if (isset($_SESSION['administrator']))
           echo "<input type=checkbox name='pid[]' value='$row->problem_id'>";
        echo "<td><a href='../problem.php?id=$row->problem_id'>".$row->title."</a>";
	echo "<td>".$row->author;
        echo "<td>".$row->in_date;
	if(isset($_SESSION['administrator'])||$row->author==$_SESSION['user_id']){
        echo "<td><a href=problem_df_change.php?id=$row->problem_id&getkey=".$_SESSION['getkey'].">".($row->defunct=="N"?
                "<span title='click to reserve it' class=green>Available</span>":
                "<span class=red title='click to be available'>Reserved</span>")."</a>";
        echo "<td><a href=problem_edit.php?id=$row->problem_id&getkey=".$_SESSION['getkey'].">Edit</a>";
	    echo "<td><a href=quixplorer/index.php?action=list&dir=$row->problem_id&order=name&srt=yes>TestData</a>";
	} else {
	    echo "<td></td><td></td><td></td>";
    }
         if (isset($_SESSION['administrator'])) {
             echo "<td><a href='#' onclick='exchangeProblem($row->problem_id)'>Exchange</a>";
         } else {
             echo "<td>";
         }
	echo "</tr>";
    }
 }
echo "</form>";
echo "<tr><td width='90%' colspan='8'><form>$MSG_SEARCH<input type='text' name='search'><input type='submit' value='$MSG_SEARCH' ></form></td></tr>";
echo "</table></center>";
?>

<script src="../mergely/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
    function exchangeProblem(from) {
        var res = prompt("请输入要转移到的题目编号", "");
        if (res == null) {
            return;
        }
        var to = parseInt(res);
        if (to <= 0) {
            return;
        }
        $.ajax({
            type: 'GET',
            dataType: 'text',
            url: "problem_move.php?moveFrom="+from +"&moveTo=" + to,
            success: function (response) {
                if (response === "success") {
                    window.location.reload();
                } else {
                    alert(response);
                }
            },
            error: function() {
                alert("something error");
            }
        });
    }
</script>

<?php
require("../oj-footer.php");
?>
