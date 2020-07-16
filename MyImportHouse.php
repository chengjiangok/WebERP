

<?php
/* $Id: MyImportHouse.php  $*/
/*房地产公司专用导入
* @Author: ChengJiang 
* @Date: 2019-09-28  
 * @Last Modified by: mikZ_ey.zhaopeng
 * @Last Modified time: 2019-08-15 08:30:27
*/
require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;		
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
include ('includes/session.php');

$Title ='导入商品房屋资料';// Screen identificator.
$ViewTopic = 'MyTools';// Filename's id in ManualContents.php's TOC.
$BookMark = 'ImportBankTrans';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/bank.png" title="' .$Title. '" /> ' .	$Title. '</p>';// Page title.
include('includes/SQL_CommonFunctions.inc');
include('includes/CurrenciesArray.php');

   
    echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<div class="page_help_text">
			功能简介：WebERP上传文件，文件保存在companies/数据库/temp，Update上传文件生成表</br>		
				</div>';	
	$move_to_path='companies/'.$_SESSION['DatabaseName']."/Houses/";
	$dir='./companies/'.$_SESSION['DatabaseName'].'/Houses/';
	echo'<table class="selection">';			
	echo '<tr>
				<td>上传文件(csv,Excel)</td>';
				//if (isset( $BankTransArr)){
    				echo'<td><input type="file" id="ImportFile"    title="' . _('Select the file that contains the bank transactions in MT940 format') . '" name="ImportFile"> </td>';
				//}else{
				///	echo'<td><input type="file" id="ImportFile"  	autofocus="autofocus"  required="required"  title="' . _('Select the file that contains the bank transactions in MT940 format') . '" name="ImportFile"> </td>';
	echo'</tr>
		</table><br>';
	echo '<table class="selection">';
	$header='<tr>
		<th colspan="8" height="2">'.$_POST['dirname'].'</th>
		</tr>
		<tr>
			<th>序号</th>		
			<th>文件名</th>
			<th>文件大小</th>
			<th>日期时间</th>
			<th></th>
			<th></th>							
		</tr>';			
		$f=0;		
	if (is_dir($dir)) {		
			if ($dh = opendir($dir)) {
				$i = 0;
				unset($file);
				while (($file = readdir($dh)) !== false) {
			
					if ($file != "." && $file != "..") {
						$files[$i]["name"] = $file;//获取文件名称
						$files[$i]["size"] = round((filesize('./'.$dir.$file)/1024),2);//获取文件大小
						$files[$i]["time"] = date("Y-m-d H:i:s",filemtime('./'.$dir.$file));//获取文件最近修改日期
						$i++;
					}
				}
			}
			closedir($dh);
			foreach($files as $k=>$v){
				$size[$k] = $v['size'];
				$time[$k] = $v['time'];
				$name[$k] = $v['name'];
			}
			array_multisort($time,SORT_DESC,SORT_STRING, $files);//按时间排序
			//array_multisort($name,SORT_DESC,SORT_STRING, $files);//按名字排序
			//array_multisort($size,SORT_DESC,SORT_NUMERIC, $files);//按大小排序
			//print_r($files);

		$d=0;
		echo $header;
		foreach($files as $key=>$row){		
						
			if (!isset($filesname[$row['name']])||(isset($filesname[$row['name']])&&(strtotime($filesname[$row['name']][0])>strtotime($row['time'])))){
				
				if (file_exists($dir.$row['name'])){

					if ($k==1){
						echo '<tr class="EvenTableRows">';
						$k=0;
					} else {
						echo '<tr class="OddTableRows">';
						$k=1;
					}
						echo '
						<td>'.$f.'</td>	
						
						<td>'. $row['name']. '</td>
						<td>'. $row['size']. '</td>
						<td>'. $row['time']. "</td>
						<td></td>
						<td>
						<a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?Read=".$move_to_path.$row['name']."\" >读入</a>&nbsp&nbsp
						<a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?Del=".$move_to_path.$row['name']."\" onclick=\"return confirm('你确认要删除该文件吗!');\" >" . _('Delete') . "</a></td>
					
						</tr>";
					
					$f++;
				}
			}
			$d++;
		}//end for		
		if ($f>0){
			prnMsg("$dir 目录下共计".($f)." 目录,".$f."文件",'info');
		}
	}
	echo'</div></table>';		
    echo'<div class="centre">		  
			<input type="submit" name="Import" value="上传/读取缓存">';
			if (isset($_GET['Read'])){
				echo'<input type="submit" name="Updateing" value="更新保存">';
			}
		//	if (isset($_POST['Import'])){
				echo'<input type="submit" name="CreatTable" value="生成数据表">';
		//	}
	echo'</div>';	   
		$file_name='';	
		$readtype=0;	
			//检查是否有数组未更新,检验是否有上传未更新文件


if ($_GET['Read']){
	//$tmp_file='companies/gjw_hld/temp/渔具19年1月到11月科目汇总表.xls';//
	$tmp_file=$_GET['Read'];//$_SESSION['UploadedFile'];
	//调试语句
	prnMsg($tmp_file);
$spreadsheet = IOFactory::load($tmp_file);//"./file.xlsx"); // 载入Excel表格

# 这里和上面代码的效果都一样,好像就是只读区别吧 不是特别清楚,官网上也给出了很多读写的写法,应该用会少消耗资源# 官网地址 https://phpspreadsheet.readthedocs.io/en/develop/topics/reading-and-writing-to-file/
//$reader = IOFactory::createReader('Xlsx');
//$reader->setReadDataOnly(TRUE);
//$spreadsheet = $reader->load('./file.xlsx'); //载入excel表格

$worksheet = $spreadsheet->getActiveSheet();
$highestRow = $worksheet->getHighestRow(); // 总行数
$highestColumn = $worksheet->getHighestColumn(); // 总列数
# 把列的索引字母转为数字 从1开始 这里返回的是最大列的索引# 我尝试了下不用这块代码用以前直接循环字母的方式,拿不到数据# 测试了下超过26个字母也是没有问题的
$highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

echo '<table cellpadding="2" class="selection">
			<tr>
				<th >楼层</th>							
				<th >楼号</th>
				<th >房间号</th>
				<th >房屋编码</th>	
				<th >房屋面积</th>
				<th >共摊面积</th>
				<th >小计</th>
				<th >实际面积</th>					
				<th style="word-wrap:break-word;word-break:break-all;">实际共摊面积</th>			
				<th ></th>
				<th ></th>
			</tr>';

/*getFormattedValue(); // 获得日期的格式化数值
如果ABCDEFG也要使用程序的变量来代替，
最好是用getCellByColumnAndRow($columnIndex, $row, createIfNotExists=true)
代替getCell(createIfNotExists = true)
代替getCell(createIfNotExists=true)
代替getCell(pCoordinate, createIfNotExists=true)，
并且createIfNotExists = true)，
并且createIfNotExists=true)，
并且columnIndex是从1开始的列数，而$row是从0开始的行数。
*/
$data=[];
for ($row = 2; $row <= $highestRow; ++$row) { // 从第二行开始
	$row_data = [];

	for ($column = 1; $column <= $highestColumnIndex; $column++) {
		if ($column==4){
			
		
			$row_data[] = $worksheet->getCellByColumnAndRow($column, $row)->getCalculatedValue(); // 获得公式计算值
		}else{
			$row_data[] = $worksheet->getCellByColumnAndRow($column, $row)->getValue();
		
		}
	}
	$data[] = $row_data;
}
if (count($data)>0){
	$_SESSION['ReadData']=$data;
}
//var_export($data);
foreach ($data as  $key=>$row){
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	 foreach($row as $ky=>$val){
		 echo '<td>'.$val.'</td>';
	 }
	 echo'</tr>';
}
echo'</table>';
		

		
}//endif 276
if (isset($_POST['Import'])||isset($_POST['ImportUpdate'])){		
     
	$file_size=$_FILES['ImportFile']['size'];  

	if ($_FILES['ImportFile']['error']==0 ){//选择上传文件正常=0,未选择文件=4
		
		if($file_size>2*1024*1024) {  
				prnMsg("文件过大，不能上传大于2M的文件",'info');  
					include('includes/footer.php');
					exit;
					$ReadTheFile ='No';
			}  
			$file_type=$_FILES['ImportFile']['type'];  
			if($file_type!="application/octet-stream" && $file_type!="application/vnd.ms-excel" && $file_type!='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {  
				prnMsg("文件类型只能为csv Excel格式",'info');   
					include('includes/footer.php');
					exit;
					$ReadTheFile ='No';
				}  
		   //判断是否上传成功（是否使用post方式上传）  
			if(is_uploaded_file($_FILES['ImportFile']['tmp_name'])) {  
				$uploaded_file=$_FILES['ImportFile']['tmp_name'];  
				
				// mb_substr($_SESSION['EDI_MsgSent'],0, strrpos($_SESSION['EDI_MsgSent'],'/'))."/BankCopy/";  
				//判断该用户文件夹是否已经有这个文件夹 			
				$file_name=$_FILES['ImportFile']['name'];  
		
			//	$file_name=date('Ymd',time()).substr($file_true_name,strrpos($file_true_name,"."));  
				if(move_uploaded_file($uploaded_file,$move_to_path.$file_name)) { 
					
					$_SESSION['UploadedFile']=$move_to_path.$file_name;
					
					$msg.= $_FILES['ImportFile']['name']."上传成功!<br>";  
				} else {  
					prnMsg("上传失败",'info');  
				}  
			} else {  
				prnMsg("上传失败!",'info');  
			} 
	
		if(!file_exists($move_to_path.$file_name) && $file_name=='12345') {  
		
			prnMsg($file_name.'文件不存在,系统自动删除!','warn');
			include ('includes/footer.php');
			exit;
		}
	}
}

if (isset($_SESSION['ReadData']) && isset($_POST['Updateing'])){
	$_SESSION['UploadedFile']=$_GET['Read'];
     $SQL="INSERT INTO `goodshouse`(	`houseno`,
										`housetype`,
										`room`,
										`units`,
										`inarea`,
										`sharearea`,
										`actualinarea`,
										`actualsharearea`,
										`flag`,
										`notes`
									)
									VALUES(";
	//var_dump($_SESSION['ReadData']);
	/*
	foreach($data as $key=>$cell){
       if ($col==0){
		   $col=count($cell);
	   }
	  
	 
		for ($i=0;$i<count($cell);$i++){
			if ($cell[$i]==""|| $cell[$i]==null){
				//空
				$isemp++;
			}elseif(is_numeric($cell[$i])){
				//是数字
				$isnum++;
			}elseif (preg_match("/([\x81-\xfe][\x40-\xfe])/", $cell[$i], $match)) { 		
				//汉字
				$ischar++;
			}
	   }
	   $row++;
	
	
         
	}*/


	
	
	
	
	
}
if($_POST['CreatTable']){
	//prnMsg('CreatTabel');
	//var_dump($_POST);
	foreach ($_POST as $key => $value) {
		prnMsg($key.'='.$value.';');
		if (mb_strpos($key,'Select')) {
	        
			$LineID = mb_substr($key,0, mb_strpos($key,'Select'));		
			$Qty =str_replace(',','', $_POST[$LineID.'Select']);
			// prnMsg($LineID.'='.$Qty);		
		}//if end
		if (mb_strpos($key,'Field')>0) {
			$cl = mb_substr($key,0, mb_strpos($key,'Field'));		
			//prnMsg($LineID.'='.$_POST[$cl.'Field']);
		}
		 $sql="CREATE TABLE Persons
						(
						PersonID int,
						LastName varchar(255),
						FirstName varchar(255),
						Address varchar(255),
						City varchar(255)
						)";
	}//foreach end
}

/*
			//prnMsg($BankFlagArr[2]) ;
if(isset($BankTransArr)){
		echo '<table cellpadding="2" class="selection">
				<tr>
					<th >序号</th>							
					<th >' . _('Date') . '</th>';			
				echo'<th >收入金额</th>
					<th >支出金额</th>	
					<th >余额<br></th>';					
				echo'<th >对方账号</th>
					<th >对方名称</th>					
					<th style="word-wrap:break-word;word-break:break-all;">摘要</th>				
					<th >备注</th>
				</tr>';
		$nowbalance=$BankFlagArr[0];
		foreach($BankTransArr as $row){
			$nowbalance+=$row[2]-$row[11];
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
		
				$amountcurr=0;
				echo'<td>'.($RowIndex+1).'</td>';
				echo'<td>'.$row[1].'</td>';							
				echo'<td class="number">'.locale_number_format($row[2],2).'</td>
						<td class="number">'.locale_number_format($row[11],2).'</td>
						<td class="number">'.locale_number_format($nowbalance,2).'</td>';			
				echo'<td >'.$row[4].'</td>				
						<td >'.$row[5]. '</td>
						<td >'.$row[7]. '</td>
						<td >'.$row[8]. '</td>';
			
			echo'</tr>';
					$RowIndex = $RowIndex + 1;
		}	//end of while loop
		$BankFlagArr[7] =round($nowbalance,2);
		echo '</table>';
		
}//if 
*/	//end isset($_POST['Import']

echo '</form>';
include ('includes/footer.php');


function read_csv($file)
{
    setlocale(LC_ALL,'zh_CN');//linux系统下生效
    $data = null;//返回的文件数据行
    if(!is_file($file)&&!file_exists($file))
    {
        die('文件错误');
    }
    $cvs_file = fopen($file,'r'); //开始读取csv文件数据
    $i = 0;//记录cvs的行
    while ($file_data = fgetcsv($cvs_file,200,"~"))
    {
		$i++;
		/*
        if($i==1)
        {
            continue;//过滤表头
        }*/
        if($file_data[0]!='')
        {
            $data[$i] = $file_data;
        }
 
    }
    fclose($cvs_file);
    return $data;
}

?>
