<?require_once("../include/db_info.inc.php");?>
<?require_once("admin-header.php");
if (!(isset($_SESSION['administrator']))){
	echo "<a href='../loginpage.php'>Please Login First!</a>";
	exit(1);
}
?>
<?
  require_once("../include/simple_html_dom.php");
  //$url='http://acm.pku.edu.cn/JudgeOnline/problem?id=1000';
  $url=$_POST ['url'];
  if (get_magic_quotes_gpc ()) {
	$url = stripslashes ( $url);
  }
  $baseurl=substr($url,0,strrpos($url,"/")+1);
//  echo $baseurl;
  $html = file_get_html($url);
  foreach($html->find('img') as $element)
        $element->src=$baseurl.$element->src;
        
  $element=$html->find('div[class=ptt]',0);
  $title=$element->plaintext;
  
  $element=$html->find('div[class=plm]',0);
  $tlimit=$element->find('td',0);//->next_sibling();
  $tlimit=substr($tlimit->plaintext,11);
  $tlimit=substr($tlimit,0,strlen($tlimit)-2);
  $mlimit=$element->find('td',2);//->nextSibling();
  $mlimit=substr($mlimit->plaintext,13);
  $mlimit=substr($mlimit,0,strlen($mlimit)-1);
  $tlimit/=1000;
  $mlimit/=1000;
  
  $element=$html->find('div[class=ptx]',0);
  $descriptionHTML=$element->outertext;
  $element=$html->find('div[class=ptx]',1);
  $inputHTML=$element->outertext;
  $element=$html->find('div[class=ptx]',2);
  $outputHTML=$element->outertext;
  
  $element=$html->find('pre[class=sio]',0);
  $sample_input=$element->innertext;
  $element=$html->find('pre[class=sio]',1);
  $sample_output=$element->innertext;
?>
<html>
<head>
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Content-Language" content="zh-cn">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>New Problem</title>
    <script src="../ckeditor/ckeditor.js"></script>
</head>
<body leftmargin="30">
<center>
<form method=POST action=problem_add.php>
<p align=center><font size=4 color=#333399>Add a Problem</font></p>
<input type=hidden name=problem_id value=New Problem>
<p align=left>Problem Id:&nbsp;&nbsp;New Problem</p>
<p align=left>Title:<input type=text name=title size=71 value="<?=$title?>"></p>
<p align=left>Time Limit:<input type=text name=time_limit size=20 value="<?=$tlimit?>">S</p>
<p align=left>Memory Limit:<input type=text name=memory_limit size=20 value="<?=$mlimit?>">MByte</p>
    <p align=left>Description:<br>
        <textarea name="description"></textarea>
    </p>
</p>
    <p align=left>Input:<br>
        <textarea name="input"></textarea>
    </p>

    <p align=left>Output:<br>
        <textarea name="output"></textarea>
    </p>

<p align=left>Sample Input:<br><textarea rows=13 name=sample_input cols=80><?=$sample_input?></textarea></p>

<p align=left>Sample Output:<br><textarea rows=13 name=sample_output cols=80><?=$sample_output?></textarea></p>

<p align=left>Test Input:<br><textarea rows=13 name=test_input cols=80></textarea></p>

<p align=left>Test Output:<br><textarea rows=13 name=test_output cols=80></textarea></p>

    <p align=left>Hint:<br><!--<textarea rows=13 name=input cols=80></textarea>-->
        <textarea name="hint"></textarea>
    </p>
<p>SpecialJudge: N<input type=radio name=spj value='0' checked>Y<input type=radio name=spj value='1'></p>

<p align=left>Source:<br><textarea name=source rows=1 cols=70></textarea></p>

<p align=left>contest:
	<select  name=contest_id>
<?
$sql="SELECT `contest_id`,`title` FROM `contest` WHERE `start_time`>NOW() order by `contest_id`";
$result=mysql_query($sql);
echo "<option value=''>none</option>";
if (mysql_num_rows($result)==0){
}else{
	for (;$row=mysql_fetch_object($result);)
		echo "<option value='$row->contest_id'>$row->contest_id $row->title</option>";
}
?>
	</select>
</p>
<div align=center>
<?require_once("../include/set_post_key.php");?>
<input type=submit value=Submit name=submit>
</div></form>
    <script>
        CKEDITOR.replace('input');
        CKEDITOR.replace('output');
        CKEDITOR.replace('description');
        CKEDITOR.replace('hint');
    </script>
<p>
<?require_once("../oj-footer.php");?>
</body></html>
