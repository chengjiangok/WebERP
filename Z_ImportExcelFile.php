

<?php
/* $Id: Z_ImportExcelFile.php  $*/
/*导入任意表生成临时表
* @Author: ChengJiang 
* @Date: 2019-09-28  
 * @Last Modified by: mikZ_ey.zhaopeng
 * @Last Modified time: 2019-08-15 08:30:27
*/

require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';
use \PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;		

include ('includes/session.php');
//include ('includes/FunctionsAccount.php');

$Title ='导入Excel生成Table';// Screen identificator.
$ViewTopic = 'MyTools';// Filename's id in ManualContents.php's TOC.
$BookMark = 'ImportBankTrans';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/bank.png" title="' .$Title. '" /> ' .	$Title. '</p>';// Page title.
include('includes/SQL_CommonFunctions.inc');

		  
if (!isset($_POST['Dbase'])){
	$_POST['Dbase']=$_SESSION['DatabaseName'] ;	 		
}
$db0 = mysqli_connect($host , $DBUser, $DBPassword, 'erp_gjw', $mysqlport);
	mysqli_set_charset($db0, 'utf8');
if (!isset($_POST['SelectTable'])){
	$_POST['SelectTable']='aa_gltrans';
}
   
    echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="SelectTable" value="' . $_POST['SelectTable'] . '" />';
	echo '<div class="page_help_text">
			功能简介：WebERP上传文件，文件保存在companies/数据库/temp，Update上传文件生成表</br>		
				</div>';	
	$move_to_path='companies/'.$_SESSION['DatabaseName']."/temp/";
	$dir='./companies/'.$_SESSION['DatabaseName'].'/temp/';
	echo'<table class="selection">';			
	echo '<tr>
			<td>上传文件(csv,Excel)</td>';
			
    echo'<td><input type="file" id="ImportFile"    title="' . _('Select the file that contains the bank transactions in MT940 format') . '" name="ImportFile"> </td>';
			
	echo'</tr>
		</table><br>';
echo '<table class="selection">';
$header='<tr>
	<th colspan="8" height="2">'.$_POST['dirname'].'</th>
	</tr>
	<tr>
		<th>序号</th>			
		<th>文件</th>
		<th>文件大小</th>
		<th>日期时间</th>
		<th></th>
		<th ></th>								
	</tr>';			
				
if (is_dir($dir)) {
	
		if ($dh = opendir($dir)) {
			$i = 0;
			unset($file);
			while (($file = readdir($dh)) !== false) {
			//	prnMsg(filemtime('./'.$dir.$file).'='.filectime('./'.$dir.$file).'-'.fileatime($file));
				if ($file != "." && $file != "..") {
					$files[$i]["name"] = $file;//获取文件名称
					$files[$i]["size"] = round((filesize('./'.$dir.$file)/1024),2);//获取文件大小
					$files[$i]["time"] = date("Y-m-d H:i:s",filemtime('./'.$dir.$file));//获取文件最近修改���期
					$i++;
				}
			}
		}
		closedir($dh);
		//var_dump($files);
		foreach($files as $k=>$v){
			$size[$k] = $v['size'];
			$time[$k] = $v['time'];
			$name[$k] = $v['name'];
		}
		array_multisort($time,SORT_DESC,SORT_STRING, $files);//按时间����序
		//array_multisort($name,SORT_DESC,SORT_STRING, $files);//按名字排序
		//array_multisort($size,SORT_DESC,SORT_NUMERIC, $files);//按大小排序
		//print_r($files);

	$d=0;
	echo $header;
	foreach($files as $key=>$row){		
					
		//if (!isset($filesname[$row['name']])||(isset($filesname[$row['name']])&&(strtotime($filesname[$row['name']][0])>strtotime($row['time'])))){
			
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
					<a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?Read=".$row['name']."\" >读入</a>&nbsp&nbsp
					<a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?Del=".$row['name']."\" onclick=\"return confirm('请确认要删除该文件吗!');\" >" . _('Delete') . "</a></td>
				
					</tr>";
				
				$f++;
			}
		//}
		$d++;
	}//end for
	
	if ($f>0){
		prnMsg("$dir 目录下共计".($f)." 目录,".$f."文件",'info');
	}
}
echo'</table>';	
echo'<table class="selection">';	

	echo '<tr>
			<td>操作选择:</td>
			<td colspan="2"><input type="checkbox" name="SelectType" value="1" '.($_POST['SelectType']==1? "checked":"").'></td>
			</tr>';
echo '<tr>
	<td>数据表选择:</td>
	<td colspan="2"><input type="text" size="20" name="SelectTable" value="'.$_POST['SelectTable'].'"   />';
	$y=date('Y');


	echo '</td>
		</tr>';	
/*
if($_POST['SetFields']||$_POST['FieldsSave']){
		$sql="SELECT `COLUMN_NAME`, `COLUMN_TYPE`, `COLUMN_KEY`, `EXTRA`, `excelcol`, `tofield`, `flag`
		        FROM `columntofield` WHERE TABLE_NAME='".$_POST['SelectTable']."'";
		$result=mysqli_query($db0,$sql);
		$TableCol=array();
		while($row=mysqli_fetch_array($result)){
			$TableFields[$row['COLUMN_NAME']]=array($row['excelcol'],$row['tofield'],$row['COLUMN_TYPE'],0 );
		}
		$R=1;
    foreach($TableFields as $key=>$row){
		
		if (!isset($_POST['InChar'.$key])){
		  $_POST['InChar'.$key]=$row[0];
		}
		echo '<tr>
		        <td>'.$R.'</td>
				<td>'.$key.'</td>
				<td ><input type="text" size="5" name="InChar'.$key.'" value="'.$_POST['InChar'.$key].'"   /></td>
					</tr>';	
		$R++;
		if ($row[0]!=''){
			$_SESSION['ExcelTable'][$row[0]]=$key;
		}
	}
	$_SESSION['ExcelTable']['Table']=$_POST['SelectTable'];

}*/
	echo'</table>';	
	
echo'<div class="centre">		  
		<input type="submit" name="Import" value="上传">
		<input type="submit" name="Update" value="导入表">
	';
	//	if (isset($_POST['Import'])){	<input type="submit" name="SetFields" value="设置字段">
		//	echo'<input type="submit" name="UpdateTable" value="更新数据">';
	//	}
	echo'<input type="submit" name="Clear" value="清除缓存">';
echo'</div>';
if (isset($_GET['Read']))	{
	$_SESSION['UploadedFile']=$_GET['Read'];
}   
    $file_name='';	
    $readtype=0;
	//检查是否有数组未更新,检验是否有上���未更新文件	
if (isset($_POST['Import'])||isset($_POST['ImportUpdate'])){		
     
	$file_size=$_FILES['ImportFile']['size'];  

	if ($_FILES['ImportFile']['error']==0 ){//选择上传文件正常=0,未选择文件=4
		
		if($file_size>5*1024*1024) {  
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
				//if(!file_exists($user_path)) {  
				//	mkdir($user_path);  
				//}      
				$file_name=$_FILES['ImportFile']['name'];  
			//prnMsg(substr($file_true_name,strrpos($file_true_name,".")));
			//	$file_name=date('Ymd',time()).substr($file_true_name,strrpos($file_true_name,"."));  
				if(move_uploaded_file($uploaded_file,$move_to_path.$file_name)) { 
					
					$_SESSION['UploadedFile']=$file_name;
					
					$msg.= $_FILES['ImportFile']['name']."上传功!<br>";  
				} else {  
					prnMsg("上传失败",'info');  
				}  
			} else {  
				prnMsg("上传失败!",'info');  
			} 
	
		if(!file_exists($move_to_path.$file_name) && $file_name=='12345') {  
		
			prnMsg($file_name.'文件不存在,系统自动清除!','warn');
			include ('includes/footer.php');
			exit;
		}
	}
}
if (isset($_POST['Update'])||isset($_GET['Read'])){	
    
	//if (isset($_SESSION['UploadedFile'])){
		//添加验证表是否存在	
		prnMsg("读取数据表".$move_to_path.$_SESSION['UploadedFile'],"info");
		$filedstr=array("B"=>"account","D"=>"name","F"=>"currcode","G"=>"curramount","H"=>"amount");
		$options =[];
		//$_SESSION['UploadedFile']=$_GET['Read'];
		if (!isset($excelarray))
		$excelarray=ExcelToArray($move_to_path.$_SESSION['UploadedFile'], 0, 0, $options);
		//print_r($excelarray);
		prnMsg("选择模式=".$_POST['SelectType']);
		$sql="show tables like '".$_POST['SelectTable']."'";
		$result=DB_query($sql);
		$tablerow=DB_fetch_assoc($result);
			
		if (!isset($tablerow)){
			$SQL="CREATE TABLE ".$_POST['SelectTable']."  ( ";		
			foreach ($excelarray[1] as $key=>$val){			
						
				$SQL.=$key.' varchar(60)  DEFAULT "",';					
				
							
					
				}
			
			$SQL =substr($SQL,0,-1);
			$SQL.=' ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
			$result=DB_query($SQL);	
		}		
        //foreach  ($excelarray as $key=>$val){
			$sql="SELECT COUNT(* ) FROM  ".$_POST['SelectTable']." WHERE 1";
			$result=DB_query($sql);
			$glrow=DB_fetch_assoc($result);

			if ($glrow[0]==0){
				foreach($excelarray as $key=>$cell){
						if ($key>1){
							$SQL="INSERT INTO ".$_POST['SelectTable']." VALUES ( ";
							$chk='';
						
							foreach($cell  as $cl=>$val){
								//prnMsg($cl.'-'.$val);
								$chk.=$val;
								$valstr='';
								if ($val==''){
									if( $field[$cl][0]!=2) {								
										$valstr=0;							
									}								
								}else{								
									$valstr=$val;								
								}
								$SQL.="'".$valstr."',";
							}
							if ($chk!=''){
								$SQL =substr($SQL,0,-1)." )";
								$result=DB_query($SQL);
							}
					}
				}
			}
		
		

		exit;
		if ($_POST['SelectType']){
               prnMsg("选择模式".$_POST['SelectType']);
		}else{
			if (is_array($excelarray)){
				foreach($excelarray[1]  as $key=>$val){
					$field[$key]=array(0,1);
				}		
				foreach($excelarray as $key=>$cell){
					$strlen=0;
					//prnMsg($cell['B']);
					if ($key>1){
						foreach($cell  as $cl=>$val){
							//var_dump($val);
							// prnMsg($cl.'['.$val);
							if ($val!=''){
									$strlen=strlen($val);
								if ($field[$cl][1]<$strlen)
									$field[$cl][1]=$strlen;
							}
							if (is_numeric($val)){
								if (!strpos($val,'.')===false){
									$field[$cl][0]=1;
								}else{

									if ($field[$cl][0]!=1){
										$field[$cl][0]=0;
									}
								}
								
							}else{
								if ($field[$cl][0]!=1){
									$field[$cl][0]=2;
								}
							}
						}
					}

				}
				$sql="show tables like '".$_POST['SelectTable']."'";
				$result=DB_query($sql);
				$tablerow=DB_fetch_assoc($result);
					
			if (!isset($tablerow)){
			
					//创建表
					$SQL="CREATE TABLE ".$_POST['SelectTable']."  ( ";			
						foreach($field as $key =>$val){
							if(isset($filedstr[$key])){
								$fld=$filedstr[$key];
							}else{
								$fld=$key;
							}
							if ($val[0]==0){
								$SQL.=$fld .' int('.$val[1].')  DEFAULT 0,';
							}elseif ($val[0]==1){
								$SQL.=$fld .' double('.$val[1].',2)  DEFAULT 0,';
							}elseif ($val[0]==2){
								$SQL.=$fld .' varchar('.$val[1].')  DEFAULT "",';
							}
			
						}
						$SQL =substr($SQL,0,-1);
						$SQL.=' ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
				
						//var_dump($field);
						
						//DROP TABLE IF EXISTS `accountgroups`;
						$result=DB_query($SQL);				
				
			}
			$sql="SELECT COUNT(* ) FROM  ".$_POST['SelectTable']." WHERE 1";
			$result=DB_query($sql);
			$glrow=DB_fetch_assoc($result);

			if ($glrow[0]==0){
				foreach($excelarray as $key=>$cell){
						if ($key>1){
							$SQL="INSERT INTO ".$_POST['SelectTable']." VALUES ( ";
							$chk='';
						
							foreach($cell  as $cl=>$val){
								//prnMsg($cl.'-'.$val);
								$chk.=$val;
								$valstr='';
								if ($val==''){
									if( $field[$cl][0]!=2) {								
										$valstr=0;							
									}								
								}else{								
									$valstr=$val;								
								}
								$SQL.="'".$valstr."',";
							}
							if ($chk!=''){
								$SQL =substr($SQL,0,-1)." )";
								$result=DB_query($SQL);
							}
					}
				}
			}
		}
	}
		//prnMsg("temp_gltrans表已经存在！","info");
	
	
}
if($_POST['FieldsSave']){
	$SQL='';
	foreach ($_POST as $FormVariableName =>$Qty) {
		//prnMsg(mb_substr($FormVariableName,7));
		
		if (mb_substr($FormVariableName, 0, 6)=='InChar') {
			
			$FN=mb_substr($FormVariableName,6);
						
			$Char =$_POST['InChar' .$FN ];
			if ($Char!=''  && preg_match('/^[a-zA-Z]+$/u',$Char)){
				$SQL="UPDATE `columntofield` SET excelcol='" .strtoupper($Char). "' WHERE TABLE_NAME='".$_POST['SelectTable']."' AND COLUMN_NAME='".$FN."'";
				
			
			}else{
				$SQL="UPDATE `columntofield` SET excelcol='' WHERE TABLE_NAME='".$_POST['SelectTable']."' AND COLUMN_NAME='".$FN."'";
			}
			//prnMsg($SQL);
			$Result=mysqli_query($db0,$SQL);
		
		}
	}
	 //$SQL="UPDATE `columntofield` SET " .substr($SQL,0,-1). " WHERE TABLE_NAME='".$_POST['SelectTable']."'";
	 
	 //$Result=mysqli_query($db0,$SQL);
	 // prnMsg($SQL);
	 /*
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
	*/
}
/*
if($_POST['UpdateTable']){
    //prnMsg(count($_SESSION['ReadExcel']));
	$FieldName=array('accountold',	'trandate',	'periodno',	'transno','gltype',	'account',	'narrative','actname',	'name',	'currcode',	
						'exrate','examount','debit','credit',	'amount','endamount','chequeno',	'userid','tag',	'flg');
	$SQL="INSERT INTO `temp_gltrans`(	`accountold`,
										`trandate`,
										`periodno`,
										`transno`,
										`gltype`,
										`account`,
										`narrative`,
										`actname`,
										`name`,
										`currcode`,
										`exrate`,
										`examount`,
										`debit`,
										`credit`,
										`amount`,
										`endamount`,
										`chequeno`,
										`userid`,
										`tag`,
										`flg`
									)
									VALUES( ";
			 $keyarr=array_keys($_SESSION['ReadExcel'][0]) ;
			 //$SQ=''; 
					$R=0;
			 foreach($_SESSION['ReadExcel'] as $row){
				$SQ=''; 
				foreach($FieldName as $fdn){
				//	prnMsg($fdn);
					$flag=0;
					foreach ($keyarr as $key=>$val){
						if($_SESSION['ExcelTable'][$val]==$fdn){
							if ($row[$val]==''){
								$SQ.="'',";

							}else{
								$SQ.='"'.$row[$val].'",';
							}
							$flag=1;
							break;
						}							
					}
					if ($flag==0){
						$SQ.='"0",';
					}		
				}
				$SQ=$SQL.substr($SQ,0,-1)." )";
				$R++;
				$Result=DB_query($SQ);
			//	if ($R>10){
			//	break;
			//	}					
			}	//end of while loop
		//	var_dump($FieldName);
		 
		

}else*/
if($_POST['Clear']){
	prnMsg('清���缓存!','info');
	unset($_SESSION['ExcelTable']);
	unset($_SESSION['ReadExcel']);

}
	
/*
if ($_GET['Read']){

	$tmp_file=$_GET['Read'];//$_SESSION['UploadedFile'];
	
	$spreadsheet = IOFactory::load($tmp_file);//"./file.xlsx"); // 载入Excel表格
	$worksheet = $spreadsheet->getActiveSheet();
	$excelRow = $worksheet->getHighestRow(); // 总行数
	$excelColumn = $worksheet->getHighestColumn(); // 总列数
	//prnMsg( $worksheet->getCellByColumnAndRow(17,4)->getValue());
	//$CharColumn=	array_flip(tochar($excelColumn));
	prnMsg($_SESSION['ExcelTable']['Table']);
	
	# 把列的索引字母转为数字 从1开始 这里返回的是最大列的索引# 我尝试了下���用这块代码用以前直接循环字母的方��,拿不到数据# 测试了下超过26个字母也是没有问题的
	$highestColumnIndex = Coordinate::columnIndexFromString($excelColumn);
     
		
		$_SESSION['ReadExcel']=[];//暂存上传内容
		for ($row = 2; $row <= $excelRow; ++$row) { // 从第二行开始
			$row_data = [];
			$column = '';
			$kk=-1;
			for($c='A',$k=0; $c<='Z'; $c++,$k++){
				if ($c==$excelColumn) {			
					$kk=$k;
				}			
				//for ($column = 'A'; $column <= $excelColumn; $column++) {
				$cr=$c.$row;
				$column=$k;
				//prnMsg($c.'='.$_POST['SelectTable'].'-'.$_SESSION[$_POST['SelectTable']][$c]);
			    if (isset($_SESSION['ExcelTable'][$c])){
				
					if ($c=='D'){				
						$row_data[$c] = $worksheet->getCell("{$cr}")->getCalculatedValue(); // 获得公式计算值
					}else{
						
							$row_data[$c] = $worksheet->getCell("{$cr}")->getValue();
						}
				}
				if($k ==$kk){			
					
					break;
				} 
			}
			$_SESSION['ReadExcel'][] = $row_data;
			//if ($row>1000){
			//	break;
			//}
			
		}
	
	//var_dump($_SESSION['ReadExcel']);
	//var_dump($_SESSION['ExcelTable']);
				
			
}//endif 276
		*/
if(!isset($_SESSION['ReadExcel'])){
		 $keyarr=array_keys($_SESSION['ReadExcel'][0]) ;
	    echo '<table cellpadding="2" class="selection">
				<tr>
				   <th >序号</th>';
			foreach ($keyarr as $key=>$val){
					echo '<th >' .$val . '</th>';
			}			
			echo'</tr>';		
		foreach($_SESSION['ReadExcel'] as $row){
		
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}				
				echo'<td>'.($RowIndex+1).'</td>';
				foreach ($keyarr as $key=>$val){
					echo'<td>'.$row[$val].'</td>';
				}				
			echo'</tr>';
					$RowIndex = $RowIndex + 1;
		}	//end of while loop
	
		echo '</table>';
		
}//if 
	

echo '</form>';
include ('includes/footer.php');

function ExcelToArray(string $file = '', int $sheet = 0, int $columnCnt = 0, &$options = []){
    try {
        /* 转码 */
        $file = iconv("utf-8", "gb2312", $file);

        if (empty($file) OR !file_exists($file)) {
            throw new \Exception('文件不存在!');
        }

        /** @var Xlsx $objRead */
        $objRead = IOFactory::createReader('Xlsx');

        if (!$objRead->canRead($file)) {
            /** @var Xls $objRead */
            $objRead = IOFactory::createReader('Xls');

            if (!$objRead->canRead($file)) {
                throw new \Exception('只支持导入Excel文件！');
            }
        }

        /* 如果不需要获取特殊操作，则只读内容，可以大幅度提升读取Excel效率 */
        empty($options) && $objRead->setReadDataOnly(true);
        /* 建立excel对象 */
        $obj = $objRead->load($file);
        /* 获���指定的sheet表 */
        $currSheet = $obj->getSheet($sheet);

        if (isset($options['mergeCells'])) {
            /* 读取合并行列 */
            $options['mergeCells'] = $currSheet->getMergeCells();
        }

        if (0 == $columnCnt) {
            /* 取得最大的列号 */
            $columnH = $currSheet->getHighestColumn();
            /* 兼容原逻辑，循环时使用的是小于等于 */
            $columnCnt = Coordinate::columnIndexFromString($columnH);
        }      
        /* 获取总行数 */
		$rowCnt = $currSheet->getHighestRow();		
        $data   = [];
        /* 读取内容 */
        for ($_row = 1; $_row <= $rowCnt; $_row++) {
            $isNull = true;

            for ($_column = 1; $_column <= $columnCnt; $_column++) {
                $cellName = Coordinate::stringFromColumnIndex($_column);
                $cellId   = $cellName . $_row;
                $cell     = $currSheet->getCell($cellId);

                if (isset($options['format'])) {
                    /* 获取格式 */
                    $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                    /* 记录格式 */
                    $options['format'][$_row][$cellName] = $format;
                }

                if (isset($options['formula'])) {
                    /* 获取公式，公式均为=号开头数据 */
                    $formula = $currSheet->getCell($cellId)->getValue();

                    if (0 === strpos($formula, '=')) {
                        $options['formula'][$cellName . $_row] = $formula;
                    }
                }

                if (isset($format) && 'm/d/yyyy' == $format) {
                    /* 日期格式翻转处理 */
                    $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                }

                $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());

                if (!empty($data[$_row][$cellName])) {
                    $isNull = false;
                }
            }

            /* 判断是否整行数据为��，是的话删除该行数据 */
            if ($isNull) {
                unset($data[$_row]);
            }
        }
      

        return $data;
    } catch (\Exception $e) {
        throw $e;
    }
}
function tochar($chr){
	// 根据字段个数，设置表头排序字母
	$letter_str = '';
	$kk=-1;
	for($i='A',$k=0; $i<='Z'; $i++,$k++){
		//if($k == count($tableheader)){
		if ($i==$chr) {			
			$kk=$k;
		}

		// 最后一个取消逗号
		if($k ==$kk){
			// (count($tableheader)-1)){
		
			$letter_str .= $i;
			break;
		} else {
			$letter_str .= $i.',';
		}
	}

	$letter = explode(',', $letter_str);
	return $letter;

}
/**
 * 数字转字母 （类似于Excel列标）
 * @param Int $index 索引值
 * @param Int $start 字母起始值
 * @return String 返回字母
 */
function IntToChr($index, $start = 65) {
    $str = '';
    if (floor($index / 26) > 0) {
        $str .= IntToChr(floor($index / 26)-1);
    }
    return $str . chr($index % 26 + $start);
}
 
/**
 * 测试
 */
function test() {
    echo IntToChr(0); //# A
    echo IntToChr(1); //# B
    // ...
    echo IntToChr(27); //# AB
}


function read_csv($file)
{
    setlocale(LC_ALL,'zh_CN');//linux系统下生效
    $data = null;//返回的文件数据行
    if(!is_file($file)&&!file_exists($file))
    {
        die('文件错误');
    }
    $cvs_file = fopen($file,'r'); //开始读取csv文件数据
    $i = 0;//记录cvs的���
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
