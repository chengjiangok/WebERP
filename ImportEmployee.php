<?php
/* $Id: ImportBankTrans.php  $*/
/*
* @Author: ChengJiang 
* @Date: 2019-04-8  
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-10-07 11:47:23
*/

include ('includes/session.php');
include ('includes/FunctionsAccount.php');
$Title = '工资资料导入';// Screen identificator.
$ViewTopic = 'MyTools';// Filename's id in ManualContents.php's TOC.
$BookMark = 'ImportBankTrans';// Anchor's id in the manual's html document.
include('includes/header.php');

include('includes/SQL_CommonFunctions.inc');
include('includes/CurrenciesArray.php');

	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/bank.png" title="' .// Icon image.
	$Title . '" /> ' .// Icon title.
	$Title . '</p>';// Page title.
    echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
					
		echo '<table cellpadding="2" class="selection">
				<tr>
				<td>单元分组</td>
					<td><select name="UnitsTag" size="1" >';
			foreach($_SESSION['CompanyRecord'] as $key=>$row)	{	
				if ($key!=0){
					if(isset($_POST['UnitsTag']) AND $key==$_POST['UnitsTag']){
						echo '<option selected="selected" value="';			
					}else{
						echo '<option value="';
					}
						echo  $key. '">' .$row['unitstab']  . '</option>';					
				}
			}
		echo'</select>
			  </td>
			  </tr>
			    <tr>
				<td>上传文件(Excel)</td>';
				//if (isset( $ReadEmployeeArr)){
    				echo'<td><input type="file" id="ImportFile"    title="' . _('Select the file that contains the bank transactions in MT940 format') . '" name="ImportFile"> </td>';
				//}else{
				///	echo'<td><input type="file" id="ImportFile"  	autofocus="autofocus"  required="required"  title="' . _('Select the file that contains the bank transactions in MT940 format') . '" name="ImportFile"> </td>';
	echo'</tr>
		</table><br>';
	
	echo'<div class="centre">		  
			<input type="submit" name="Import" value="上传/读取缓存">';
		//	if (isset($_POST['Import'])){
				echo'<input type="submit" name="ImportUpdate" value="更新保存">';
		//	}
	echo'</div><br>';
	$readtype=0;
	$sql="SELECT tag ,uploaddate,hrtype,urlfile FROM hrupload WHERE flg=-1";
	$result=DB_query($sql);	
	$filerow=DB_fetch_array($result);
	//var_dump($row);
	//prnMsg(count($filerow).$row['urlfile']);
	if (isset($filerow)&&count($filerow)>0){
		$readtype=count($filerow);
		echo '<table cellpadding="2" class="selection">
						<tr>
							<th >序号</th>							
							<th >会计期间</th>	
							<th >文件类别</th>
							<th >文件名</th>							
							<th >上传日期</th>
							<th ></th>							
						</tr>'; 
						$RowIndex=0;  
			DB_data_seek($result,0);
			while ($row= DB_fetch_array($result)) {
						
					if ($k==1){
						echo '<tr class="EvenTableRows">';
						$k=0;
					} else {
						echo '<tr class="OddTableRows">';
						$k=1;
					}			
					echo '<td>'.($RowIndex+1).'</td>
							<td>'.$row['period'].'</td>
							<td>'.$row['hrtype'].'</td>
							<td>'.$row['urlfile'].'</td>						
							<td>'.$row['uploaddate']."</td>
							<td ><a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?Del=".$row['hrid']."\" onclick=\"return confirm('你确认要删除该文件吗!');\" >" . _('Delete') . "</a></td>
							</tr>";				
							$RowIndex++;
			}
				echo '</table>';
	}

	echo'</form>';
		$MoveToPath='companies/'.$_SESSION['DatabaseName']."/Employee/";   
		$FileName='';	
		$readtype=0;//是否有上传文件未读取到系统
	
			//检查是否有数组未更新,检验是否有上传未更新文件
	
if (isset($_POST['Import'])||isset($_POST['ImportUpdate'])){		
	
	$file_size=$_FILES['ImportFile']['size'];  

	if ($_FILES['ImportFile']['error']==0 && $readtype==0){//选择上传文件正常=0,未选择文件=4
		
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
				//if(!file_exists($user_path)) {  
				//	mkdir($user_path);  
				//}      
				$file_true_name=$_FILES['ImportFile']['name'];  
					$sql="INSERT INTO `hrupload`(	`tag`,
												`hrtype`,
												`urlfile`,
												`uploaddate`,
												`record`,
												`amount`,
												`remark`,
												`flg`)
							          VALUES('".$_POST['UnitsTag'] ."',
									        0,
											'".$FileName."',
											'". date('Y-m-d H:i:s',time())."',
											'0','0',
											'".$file_true_name."',
											'-2')";
						$result=DB_query($sql);
						$hrid=DB_Last_Insert_ID($db,'hrupload','hrid');
						$FileName=$_POST['UnitsTag'].'_'.date('Ymd',time()).substr($file_true_name,strrpos($file_true_name,"."));  

				if(move_uploaded_file($uploaded_file,$MoveToPath.$FileName)) { 
					
						$sql="UPDATE hrupload SET urlfile='".$FileName."' ,flg=-1 WHERE hrid=".$hrid;
						$result=DB_query($sql);
					$msg.= $_FILES['ImportFile']['name']."上传成功!<br>";  
				} else {  
				prnMsg("上传失败",'info');  
				}  
			} else {  
				prnMsg("上传失败!",'info');  
			} 
	
	
		if(!file_exists($MoveToPath.$FileName) && $FileName!='') {  
			$sql="DELETE FROM hrupload 	WHERE urlfile ='".$FileName."'";
			$result=DB_query($sql);	
			prnMsg($FileName.'文件不存在,系统自动删除!','warn');
			include ('includes/footer.php');
			exit;
		}
    }else{
		$FileName=$filerow['urlfile'];
		$readtype=0;
    }
	//if (($readtype==3 ||$readtype==0 )&& $FileName!=''){
	if ($readtype==0){		       
		//下面代码为读取excel写入表
		/** 引入 PHPExcel_IOFactory */
		require_once dirname(__FILE__) . '/Classes/PHPExcel.php';		
		require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
		//用load()加载表格文件
		//调试语句	$FileName="_154_20191006.xls";  //_155_20191006 

		$TmpFile=$MoveToPath.$FileName;
		//prnMsg($TmpFile);	
		if ($FileName==""){
			prnMsg('你没有选择文件!','warn');
			include ('includes/footer.php');
			exit;
		}	
		if(substr($FileName,strrpos($FileName,"."))=='.xls'){
			$reader = new PHPExcel_Reader_Excel5();	
			//prnMsg($TmpFile);				
		}elseif(substr($FileName,strrpos($FileName,"."))=='.xlsx'){
		$reader = new PHPExcel_Reader_Excel2007();	  
		}			
	
			$reader->setReadDataOnly(false);  		
			$objPHPExcel = $reader->load($TmpFile);//utf-8'); // 载入excel文件

			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			$sheet = $objPHPExcel->getSheet(0); // 读取第一個工作表
			// 取得总行数
			$excelRow = $sheet->getHighestRow(); 
			$excelColumm = $sheet->getHighestColumn(); // 取得总列数				
				
			$ReadEmployeeArr=array();//暂存上传内容
			$L=PHPExcel_Cell::columnIndexFromString($excelColumm)-1;	
			prnMsg($excelColumm.'='.$excelRow.'-'.$L.''  .$TmpFile);
			//读取Excel资料	到$ReadEmployeeArr
		    if($L==41){
				$FileType=$L;//境内人员信息列表
			}elseif($L=26){
				$FileType=$L;//工资单
			}
		for ($i =1; $i <=$excelRow; $i++) {
			$colarr=array();
			for ($j =0; $j <=$L; $j++) {
				$k=PHPExcel_Cell::stringFromColumnIndex($j);	
				$colarr[$k]=$sheet->getCell($k.$i)->getValue();								
			}			
			$ReadEmployeeArr[]=$colarr;
		}
		//var_dump($ReadEmployeeArr);			
		if(isset($ReadEmployeeArr)){
			echo '<table cellpadding="2" class="selection">';
			if($FileType==41){
			
				echo'		<tr>
							<th >序号-工号</th>
							<th >姓名</th>			
							<th >证照类型</th>
							<th >身份证号码</th>	
							<th >国家<br></th>				
							<th >性别</th>
							<th >出生日期</th>					
							<th >人员状态</th>				
							<th >雇佣状态</th>
							<th >手机</th>
								
							<th >残疾<br/>烈属</br>孤老</th>
							<th >证号码</th>	
							<th >受雇日期</th>				
							<th >离职日期</th>
							<th >邮箱</th>					
							<th >学历</th>				
							<th >开户银行</th>
							<th >开户账号</th>
							<th >个人投资\比例</th>
							<th >是否境外人</th>
						</tr>';
						$RowIndex=0;
				foreach($ReadEmployeeArr as $row){
					if ($RowIndex>0){
						if ($k==1){
							echo '<tr class="EvenTableRows">';
							$k=0;
						} else {
							echo '<tr class="OddTableRows">';
							$k=1;
						}			
						echo'<td>'.$RowIndex.'['.$row['A'].']</td>';
						echo'<td>'.$row['B'].'</td>		
							<td >'.$row['C'].'</td>				
							<td >'.$row['D']. '</td>
							<td >'.$row['E']. '</td>
							<td >'.$row['F']. '</td>
							<td>'.$row['G'].'</td>		
							<td >'.$row['H'].'</td>				
							<td >'.$row['I']. '</td>
							<td >'.$row['J']. '</td>
						
							<td>'.$row['L'].'</td>		
							<td >'.$row['Q'].'</td>				
							<td >'.$row['R']. '</td>
							<td >'.$row['S']. '</td>
							<td >'.$row['T']. '</td>
							<td>'.$row['U'].'</td>		
							<td >'.$row['V'].'</td>				
							<td ></td>
							<td ></td>
							<td >'.$row['Y']. '</td>';
					
					echo'</tr>';
						
					}
					$RowIndex = $RowIndex + 1;
				}	//end of while loop
			}elseif($FileType==26){//所得工资表
				$SQL="SELECT `employee_id`, `empid`, `tag`, first_name ,`national_id` ,employee_department FROM `hremployees` WHERE 1";
				$Result=DB_query($SQL);
				while($Row=DB_fetch_array($Result)){
					$empname[$Row['national_id']]=array($Row['employee_id'],$Row['empid'],$Row['employee_department']);

				}
					echo'<tr>
							<th >序号-工号</th>
							<th >姓名</th>
							<th >部门</th>			
							<th >所得期间起</th>
							<th >所得期间止</th>	
							<th >本期收入</th>				
							<th >本期免税收入</th>
							<th >基本养老保险</th>					
							<th >基本医疗保险</th>				
							<th >失业保险</th>

							<th >住房公积金</th>								
							<th >子女教育</th>
							<th >住房贷款利息<br>住房租金</th>	
							<th >赡养老人</th>				
							<th >税前扣除合计</th>
							<th >减免税额</th>					
							<th >减除费用</th>				
							<th >已扣税缴额</th>
							<th >备注</th>
							
						</tr>';
						$RowIndex=0;
				foreach($ReadEmployeeArr as $row){
					$ReadEmployeeArr[$RowIndex]['AB']=$empname[$row['D']][0];	
					$ReadEmployeeArr[$RowIndex]['AC']=$empname[$row['D']][2];
					if ($RowIndex>0){
						if ($k==1){
							echo '<tr class="EvenTableRows">';
							$k=0;
						} else {
							echo '<tr class="OddTableRows">';
							$k=1;
						}	
						///$ReadEmployeeArr[$RowIndex]['AB']=$empname[$row['D']][0];	
						//$ReadEmployeeArr[$RowIndex]['AC']=$empname[$row['D']][2];	
						echo'<td>'.$RowIndex.'['.$row['A'].']</td>';
						echo'<td>'.$row['B'].'</td>	
						    <td >'.$ReadEmployeeArr[$RowIndex]['AC'].'</td>							
							<td >'.$row['E']. '</td>
							<td >'.$row['F']. '</td>
							<td>'.$row['G'].'</td>		
							<td >'.$row['H'].'</td>				
							<td >'.$row['I']. '</td>
							<td >'.$row['J']. '</td>
							<td >'.$row['K'].'</td>							
							<td>'.$row['L'].'</td>	

							<td >'.$row['M']. '</td>	
							<td >'.($row['N']+$row['O']). '</td>
							<td >'.$row['P'].'</td>				
							<td ></td>
							<td >'.$row['X']. '</td>
							<td >'.$row['Y']. '</td>
							<td>'.$row['Z'].'</td>		
														
							<td >'.$row['AA']. '</td>';
					
					echo'</tr>';
						
					}
					$RowIndex = $RowIndex + 1;
				}	//end of while loop

				
			}
			echo '</table>';
		//	var_dump($empname);
			
		}//if 
			//end isset($_POST['Import']
		if($_POST['ImportUpdate']&&isset($ReadEmployeeArr)){		
			$RowIndex = 0;	
			$insertrow=0;	
			$result = DB_Txn_Begin();
			if($FileType==41){//列表
				foreach($ReadEmployeeArr as $row){					
					if ($RowIndex>0){					
					$sql="INSERT INTO `hremployees`(	`empid`,
														`user_id`,
														`joining_date`,
														`first_name`,													
														`gender`,
														`employee_position`,
														`employee_grade_id`,
														`job_title`,
														`resume`,
														`employee_department`,
														`status`,
														`date_of_birth`,
														`marital_status`,													
														`father_name`,
														`mother_name`,
														`nationality`,
														`national_id`,													
														`home_address`,													
														`mobile_phone`,
														`email`,
														`manager_id`,
														`bank_name`,
														`bank_account_no`,
														tag												
													)
													VALUES(
														'".$row['A'] ."',
														'',
														'".	$row['Q']."',
														'".	$row['B']."',
														'".	$row['F']."',
														'0',
														'0',
														'',
														'',
														'".$row['H']."',														
														'1',
														'".	$row['G']."',
														'".$row['H']."',
														'',
														'',														
														'".$row['E']."',
														'".$row['D']."',
														'".$row['AH']."',
														'".$row['J']."',
														'',
														'',
														'".$row['U']."',
														'".$row['V']."'	,
														'".$_POST['UnitsTag']."')";
								//	prnMsg($sql);
											
							$result=DB_query($sql);	
							if ($result){
								$insertrow++;
							}
						}
						$RowIndex++;
				}
			}elseif($FileType=26){//工资单
				foreach($ReadEmployeeArr as $row){					
					if ($RowIndex>0){	
						$period=DateGetPeriod(	$row['F']);				
					$sql="INSERT INTO `hrtaxpayslips`(`employee_id`,
													`period`,
													`tag`,
													`empid`,
													`startdate`,
													`enddate`,
													income,
													exemptincome,
													`socialsecurity`,
													`medicalinsurance`,
													`unemployment`,
													`housingfund`,

													` childeducation`,
													`housingloans`,
													`housingrent`,
													`supportelderly`,
													`continuingeducation`,
													`enterpriseannuity`,
													`healthinsurance`,
													`taxdeferredelderly`,
													`deductibledonations`,
													`taxsavings`,

													`costreduction`,
													`taxeswithheld`,
													`flg`)
												VALUES(	'".$row['AB'] ."',
														'".$period."',
														'".$_POST['UnitsTag']."',
														'".	$row['A']."',
														'".	$row['E']."',
														'".	$row['F']."',
														'".	$row['G']."',
														'".	$row['H']."',
														'".	$row['I']."',
														'".	$row['J']."',
														'".	$row['K']."',
														'".	$row['L']."',	

														'".$row['M']."',												
														'".	$row['N']."',
														'".$row['O']."',													
														'".$row['P']."',														
														'".$row['Q']."',
														'".$row['R']."',													
														'".$row['S']."',
														'".$row['T']."',
														'".$row['U']."',
														'".$row['X']."',

														'".$row['Y']."',																										
														'".$row['Z']."'	,
														2)";
							//	prnMsg($sql);
											
							$result=DB_query($sql);	
							if ($result){
								$insertrow++;
							}
						}
						$RowIndex++;
				}

			}	
			if ($insertrow==0 && $readtype==0){//if row 321
				
				$sql="DELETE FROM hrupload 	WHERE urlfile ='".$FileName."'";
				$result=DB_query($sql);	
				$msg.='工资资料的内容没有可以插入的明细！<br>';
				if (unlink($MoveToPath.$FileName)){
					$msg.=$_FILES['ImportFile']['name']."删除文件完成！<br>"; 
				}else{
					$msg.=$_FILES['ImportFile']['name']."删除文件失败！<br>"; 
				}		 
			}//检验上传文件账号是否和选择相同 end
			if ($msg!=''){
				prnMsg($msg,'warn');
			}			  
			if ($insertrow==$RowIndex-1){
				$result = DB_Txn_Commit();
				$sql="UPDATE hRupload SET	flg =0	WHERE urlfile='".$FileName."'";
				$result=DB_query($sql);	

				unset($ReadEmployeeArr);
				prnMsg('缓存更新成功!');
			}else{
				prnMsg('缓存更新没有成功!'.$insertrow.'=='.$RowIndex);
			}
			
		//}else{
			
		}
		
	}

}
include ('includes/footer.php');
?>
