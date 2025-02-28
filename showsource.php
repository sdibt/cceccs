<?require_once("oj-header.php")?>
<title>Source Code</title>

<link href='highlight/styles/shCore.css' rel='stylesheet' type='text/css'/> 
<link href='highlight/styles/shThemeDefault.css' rel='stylesheet' type='text/css'/> 
<script src='highlight/scripts/shCore.js' type='text/javascript'></script> 
<script src='highlight/scripts/shBrushCpp.js' type='text/javascript'></script> 
<script src='highlight/scripts/shBrushCSharp.js' type='text/javascript'></script> 
<script src='highlight/scripts/shBrushCss.js' type='text/javascript'></script> 
<script src='highlight/scripts/shBrushJava.js' type='text/javascript'></script> 
<script src='highlight/scripts/shBrushDelphi.js' type='text/javascript'></script> 
<script src='highlight/scripts/shBrushRuby.js' type='text/javascript'></script> 
<script src='highlight/scripts/shBrushBash.js' type='text/javascript'></script>
<script src='highlight/scripts/shBrushPython.js' type='text/javascript'></script> 
<script language='javascript'> 
SyntaxHighlighter.config.bloggerMode = false;
SyntaxHighlighter.config.clipboardSwf = 'highlight/scripts/clipboard.swf';
SyntaxHighlighter.all();
</script>

<?
require_once("./include/db_info.inc.php");
require_once("./include/const.inc.php");
if (!isset($_GET['id'])){
	echo "No such code!\n";
	require_once("oj-footer.php");
	exit(0);
}
$ok=0;
$id=strval(intval($_GET['id']));
$sql="SELECT * FROM `solution` WHERE `solution_id`='".$id."'";
$result=mysql_query($sql);
$row=mysql_fetch_object($result);
$slanguage=$row->language;
$sresult=$row->result;
$stime=$row->time;
$smemory=$row->memory;
$sproblem_id=$row->problem_id;
$suser_id=$row->user_id;

$sql="SELECT `contest_id` FROM `contest` WHERE (`end_time`>NOW() and `start_time`<NOW()) and `defunct`='N'";
$result= mysql_query($sql);
$row=mysql_num_rows($result);
//暂时注释
if($row)
   $OJ_AUTO_SHARE=false;


if (isset($OJ_AUTO_SHARE)&&$OJ_AUTO_SHARE&&isset($_SESSION['user_id'])){
	$sql="SELECT 1 FROM solution where 
			result=4 and problem_id=$sproblem_id and user_id='".$_SESSION['user_id']."'";
	$rrs=mysql_query($sql);
	$sok=(mysql_num_rows($rrs)>0);
	mysql_free_result($rrs);
}

if ((isset($_SESSION['user_id']) && $row && $sok)||(isset($_SESSION['user_id'])&&$suser_id==$_SESSION['user_id'])) $ok=true;
#if ((isset($_SESSION['user_id'])&&$row && $row->user_id==$_SESSION['user_id'] && $sok)||(isset($_SESSION['user_id'])&&$suser_id==$_SESSION['user_id'])) $ok=true;
if(isset($OJ_VIP_CONTEST)&&$OJ_VIP_CONTEST)
{
       /* $sql="SELECT 1 FROM `contest_problem` WHERE `problem_id`=$sproblem_id AND `contest_id` IN (
        SELECT `contest_id` FROM `contest` WHERE `start_time`<NOW() AND `end_time`>NOW())";
         $rrs=mysql_query($sql);
         $flag=!(mysql_num_rows($rrs)>0);
         if($flag)
              $ok=false;
         else 
               $ok=true; 
          mysql_free_result($rrs);*/ 
	  $ok=false;
}


if (isset($_SESSION['source_browser']))
{
   $sql="SELECT author FROM `problem` WHERE `defunct`='Y' and `problem_id`=$sproblem_id";//题目不可见，不能看代码
   $rrs=mysql_query($sql);
   $rrow=mysql_fetch_object($rrs);
   $flag=!(mysql_num_rows($rrs)>0);
   if($flag||$rrow->author==$_SESSION['user_id'])
     $ok=true;
  else 
     $ok=false;
     mysql_free_result($rrs);
}
if (isset($_SESSION['administrator']))$ok=true;
if ($ok==true){
	$brush=strtolower($language_name[$slanguage]);
	if ($brush=='pascal') $brush='delphi';
	echo "<pre class=\"brush:".$brush.";\">";
	ob_start();
	echo "/**************************************************************\n";
	echo "\tProblem: $sproblem_id\n\tUser: $suser_id\n";
	echo "\tLanguage: ".$language_name[$slanguage]."\n\tResult: ".$judge_result[$sresult]."\n";
	if ($sresult==4){
		echo "\tTime:".$stime." ms\n";
		echo "\tMemory:".$smemory." kb\n";
	}
	echo "****************************************************************/\n\n";
	$auth=ob_get_contents();
	ob_end_clean();
	mysql_free_result($result);
	$sql="SELECT `source` FROM `source_code` WHERE `solution_id`='".$id."'";
	$result=mysql_query($sql);
	$row=mysql_fetch_object($result);
	echo htmlspecialchars(str_replace("\n\r","\n",$row->source))."\n".$auth."</pre>";
	mysql_free_result($result);
}else{
	mysql_free_result($result);
	echo "I am sorry, You could not view this code!";
}
?>
<?require_once("oj-footer.php")?>
