<?php
/* $Id: GLIncomeCostLedger.php  ChengJiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2019-01-25 16:14:56 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2019-01-01 10:42:54
 2017-09-04 clear
 */
include ('includes/session.php');
require_once 'Classes/PHPExcel.php'; 
$Title ='收入费用报表';
$ViewTopic= 'GeneralLedger';
$BookMark ='GLIncomeCostReport';

include('includes/SQL_CommonFunctions.inc');
if (!isset($_POST['selectprd'])OR $_POST['selectprd']==''){
		$_POST["selectprd"]=$_SESSION['period'];
  	}
	 
if (!isset($_POST['dataformat'])) {
   $_POST['dataformat']=1;
}

if (!isset($_POST['costitem'])){
      $_POST['costitem']=0;
	}
if (!isset($_POST['costrevence'])){
		$_POST['costrevence']=21;
	}
		if (isset($_SESSIN['Tag']) AND $_POST['costitem']>0){
			$sql="SELECT confvalue FROM myconfig WHERE confname = 'settleflag'  AND  costitem=".$_POST['costitem']." limit 1"; 
			}elseif (!isset($_SESSIN['Tag'])) {
			$sql="SELECT confvalue  FROM myconfig WHERE confname='settleflag' limit 1 ;"; 
			}
			if ($_POST['costitem']>0 OR !isset($_POST['costitem']) ){
			$Result = DB_query($sql);
			$row = DB_fetch_array($Result);
			$str=json_decode($row[0],true);
			$sarr=str_split($str[$_POST['selectprd']],1);	
			$wd=0;
			foreach($sarr as $value){
			$wd+=$value;
			}
			$wd=$wd*10;
			}else{
			$wd=0;	
		}

if (!isset($_POST['CSV'])) {
	include  ('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		$Title. '"/> ' .// Icon title.
		$Title . '</p>';// Page title.
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	      <input type="hidden" name="selectprd" value="' . $_POST['selectprd'] . '" />
		  <input type="hidden" name="dataformat" value="' . $_POST['dataformat'] . '" />
		  <input type="hidden" name="costrevence" value="' . $_POST['costrevence'] . '" />
      	  <input type="hidden" name="costitem" value="' . $_POST['costitem'] . '" />';
	echo '<table class="selection">';   
 	echo '<tr>
  	      <td>选择报表:</td>
	      <td><select name="costrevence" size="1" >';
       $sql="SELECT reportid, reportname FROM reporttype WHERE rpttype=2 " ;
       //21成本  22收益
			$result = DB_query($sql);
		while ($myrow=DB_fetch_array($result)){	
				if(isset($_POST['costrevence']) AND $myrow['reportid']==$_POST['costrevence']){	
			 		echo '<option  selected="selected"  value="';
			 	}else{
			 		echo '<option  value="';
			 	}
			 	echo  $myrow['reportid'] . '">' . $myrow['reportname'] . '</option>';
		}
    /*if (isset($_POST['costrevence'])&& $_POST['costrevence']==21){
		$_POST['costrevence']='5001';
	}else{
		$_POST['costrevence']='6001';
	}	*/		
	echo	'</select>
	      </td>
	      </tr>';
	echo '<tr>
	        <td>' . _('Select Period To')  . '</td>
          <td >
		    <select name="selectprd" size="1" >';					
   	if (($_SESSION['period']-$_SESSION['startperiod'])<36){	  					
  		 $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".$_SESSION['startperiod'] ."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}else{
		 $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".(floor($_SESSION['startperiod']/12)*12-23 )."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}
   $periods = DB_query($sql);
   while ($myrow=DB_fetch_array($periods,$db)){	
		if(isset($_POST['selectprd']) AND $myrow['periodno']==$_POST['selectprd']){	
			echo '<option selected="selected" value="';
		} else {
			echo '<option value ="';
		}
		echo   $myrow['periodno']. '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
	}  
    echo '</select>';
    echo '<tr>
			<td>格式</td>
			<td>
			    <input type="radio" name="dataformat" value="1"  '.($_POST['dataformat']==1 ? 'checked':"").' >年度台账          
                <input type="radio" name="dataformat" value="2"   '.($_POST['dataformat']==2 ? 'checked':"").' >选择期 
            </td>
       </tr>'; 
	echo '<tr>
	   <td>单元分组</td>
		<td>';
		SelectUnitsTag();
  
		echo'</td></tr>';
	echo '	</table>
		<br />';
	echo '<div class="centre">
			<input type="submit" name="Search" value="查询" />
			<input type="submit" name="CSV" value="导出CSV" /></div>';
}
/*
if (isset($_POST['crtExcel'])) {
	if ($ListCount ==0) {
	prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	}else{
     //set_include_path(PATH_SEPARATOR .'Classes/PHPExcel' . PATH_SEPARATOR . get_include_path()); 
    // require_once 'Classes/PHPExcel/Writer/Excel5.php';     // 用于其他低版本xls 
   $objExcel = new PHPExcel(); 
   //$objWriter = new PHPExcel_Writer_Excel5($objExcel);     // 用于其他版本格式 
    //设置文档基本属性 
   $objProps = $objExcel->getProperties(); 
   $objProps->setCreator("Zeal Li"); 
   $objProps->setLastModifiedBy("Zeal Li"); 
   $objProps->setTitle("Office XLS Test Document"); 
   $objProps->setSubject("Office XLS Test Document, Demo"); 
   $objProps->setDescription("Test document, generated by PHPExcel."); 
   $objProps->setKeywords("office excel PHPExcel"); 
   $objProps->setCategory("Test"); 
   //设置当前的sheet索引，用于后续的内容操作。一般只有在使用多个sheet的时候才需要显示调用。 
   //缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0  
   $objExcel->setActiveSheetIndex(0); 
   $objExcel->getSheet(0)->setTitle('收益成本表'); 
   $objSheet1 = $objExcel->getActiveSheet();    
   	$r=1;
   	$k=1;  
   	$itemstr=array( '科目编码' ,'科目名称' , '序号','本期金额','本年合计');		
	//显式指定内容类型  	 
      $objSheet1->getColumnDimension('A')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('B')->setWidth(30);   
	  $objSheet1->getColumnDimension('C')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('D')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('E')->setAutoSize(true); 
	  $objExcel->getActiveSheet()->setCellValue('A'.$k, $itemstr[0]);
      $objExcel->getActiveSheet()->setCellValue('B'.$k, $itemstr[1]);
      $objExcel->getActiveSheet()->setCellValue('C'.$k, $itemstr[2]);
      $objExcel->getActiveSheet()->setCellValue('d'.$k, $itemstr[3]);
	  $objExcel->getActiveSheet()->setCellValue('E'.$k, $itemstr[4]);
	 while ($myrow = DB_fetch_array($Result) ){	
   // foreach($mulit_arr as $k=>$v){
    $k ++;
    $objExcel->getActiveSheet()->setCellValue('A'.$k, $myrow['rptno']);
    $objExcel->getActiveSheet()->setCellValue('B'.$k, $myrow['title']);
    $objExcel->getActiveSheet()->setCellValue('C'.$k, $myrow['showlist']);
    $objExcel->getActiveSheet()->setCellValue('D'.$k, number_format($myrow['amountm'], 2, '.', ''));
    $objExcel->getActiveSheet()->setCellValue('E'.$k, number_format($myrow['amountq'], 2, '.', ''));
   }
   //写入类容
   $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
    if ($_POST['costrevence']=='6001'){
       $outputFileName = 'companies/'.$_SESSION['DatabaseName'].'/reports/收入成本表_' . periodymstr($_POST['selectprd'],0).'.xlsx';
	}else{
        $outputFileName ='companies/'.$_SESSION['DatabaseName'].'/reports/成本_' .periodymstr($_POST['selectprd'],0).'.xlsx';
	}
	$RootPath=dirname(__FILE__ ) ;
    $objWriter->save($RootPath.'/'.$outputFileName); 
    echo '<p><a href="' .  $outputFileName . '">' . _('click here') . '</a> ' . '下载文件'. '<br />';
   }
}else
*/
if (isset($_POST['CSV'])OR isset($_POST['Search']) ){	
	$inarr=array('6001','6051','6301');
	if ($_POST['costrevence']==22 && $_POST['dataformat']==1){
		//prnMsg('收入成本年度232');
		$p=$_POST['selectprd'];
		if ($_POST['selectprd']%12==0){
			$n=12; 
		}else{
			$n=$_POST['selectprd']%12;
		}
		$j=$_POST['selectprd']-$n;
		$str='';
		//$sql="SELECT  account , title, itemtype , jd FROM reportitem WHERE reportNO LIKE '7".$_POST['costitem']."%' ORDER BY showlist";
		//读取明细科目
		$sql="SELECT t3.accountcode,t3.currcode,
					t3.accountname
				FROM chartmaster t3 
				WHERE t3.accountcode  NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
			( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) and left(t3.accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603')    ORDER BY t3.accountcode";
		$sql="SELECT accountcode ,accountname FROM `chartmaster` WHERE left(accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603') ORDER BY accountcode";
		$result = DB_query($sql);
		//$AccountArr=array();
		$h=33; 
		$AccountStr='';
		while ($row=DB_fetch_array($result)) {
			$AccountArr[$row['accountcode']]=$row['accountname'];
			if ($AccountStr==''){
     			$AccountStr.=substr($row['accountcode'],0,4).',';
			}else{
				$AccountStr.=','.substr($row['accountcode'],0,4);
			}
		}
		//$itemarr=array();
		$p=$_POST['selectprd'];
		if ($_POST['selectprd']%12==0){
			$n=12; 
		}else{
			$n=$_POST['selectprd']%12;
		}
		$j=$_POST['selectprd']-$n;
		$str='';
		DB_data_seek($result,0);
		$dd=array_fill(0,$n,"");    
		$sql="SELECT `periodno`,  
		             `account`,
		              sum(toamount(amount,periodno,periodno,periodno,1,flg)) amountj,
					  sum(toamount(amount,periodno,periodno,periodno,-1,flg)) amountd 
		      FROM gltrans 
			  WHERE account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603')) 
				 AND periodno >=".$_SESSION['janr']."  AND periodno <=".$_POST['selectprd']." 
				 GROUP BY periodno,account 
				 ORDER BY periodno,account"; 
		$result=DB_query($sql);
		//$itemarr=array();
		//查询数据转数组
		while ($row=DB_fetch_array($result)){
          	$key= $row['periodno']-$_SESSION['janr']+1;
				$itemarr[$row['account']][$key]=array($row['amountj'],$row['amountd']);
			}
		$sql="SELECT  account ,
					  sum(sumamount(amount,periodno,0,".$_SESSION['janr']."-1)) amount 
				FROM gltrans
				WHERE periodno>=0 AND periodno<".$_SESSION['janr']."
					 AND  account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603'))
					  GROUP BY account";
		$result=DB_query($sql);
		//查询年初数据
		while ($row=DB_fetch_array($result)){
          	$itemarr[$row['account']][0][0]=$row['amount'];
		}
		$sql="SELECT  account ,
					 sum(sumamount(amount,periodno,0,".$_POST['selectprd'].")) amount 
					 FROM gltrans 
					 WHERE periodno>=0 AND periodno<=".$_POST['selectprd']." 
					 AND  account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603'))
					  GROUP BY account";
		$result=DB_query($sql);
		//期末合计数据
		while ($row=DB_fetch_array($result)){
          	$itemarr[$row['account']][13][0]=$row['amount'];
		}
		//汇总科目
		$sql="SELECT t3.accountname, t3.accountcode ,t3.currcode
					FROM chartmaster t3 
						WHERE t3.accountcode  in(SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
		( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) 
		 AND left(t3.accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603')
		  AND used>=0 ORDER BY t3.accountcode";
        $result=DB_query($sql);
		//归总科目合计数据
		$detotal=0;
		$crtotal=0;
		while ($row=DB_fetch_array($result)){
			  //$itemarr[$row['account']][13][0]=$row['amount'];
			 for ($i=0;$i<=$n;$i++){
					foreach ($itemarr as $key=>$val) {
							if ($row['accountcode']==substr($key,0,strlen($row['accountcode']))){
								$itemarr[$row['accountcode']][$i][0]+=(is_null($val[$i][0])?0:$val[$i][0]);
							    $itemarr[$row['accountcode']][$i][1]+=is_null($val[$i][1])?0:$val[$i][1];
								$itemarr[$row['accountcode']][13][0]+=is_null($val[$i][0])?0:$val[$i][0];
								$itemarr[$row['accountcode']][13][1]+=is_null($val[$i][1])?0:$val[$i][1];
						         //prnMsg($val[0][0].'='.$val[0][1]);	
						}
					}
			  }
		}
	 }elseif ($_POST['costrevence']==21 && $_POST['dataformat']==1){
		// prnMsg('年度成本费用21']);  
		//读取明细科目
		$sql="SELECT t3.accountcode,t3.currcode,
					t3.accountname
				FROM chartmaster t3 
				WHERE t3.accountcode  NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
			( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) and left(t3.accountcode,4) IN ('5001','5101','6801','6601','6602','6603')    ORDER BY t3.accountcode";
		$sql="SELECT accountcode ,accountname FROM `chartmaster` WHERE left(accountcode,4) IN ('5001','5101','6601','6602','6603') ORDER BY accountcode";
		$result = DB_query($sql);
		$AccountArr=array();
		$h=33; 
		$AccountStr='';
		while ($row=DB_fetch_array($result)) {
			$AccountArr[$row['accountcode']]=$row['accountname'];
			if ($AccountStr==''){
     			$AccountStr.=substr($row['accountcode'],0,4).',';
			}else{
				$AccountStr.=','.substr($row['accountcode'],0,4);
			}
		}
		//$itemarr=array();
		$p=$_POST['selectprd'];
		if ($_POST['selectprd']%12==0){
			$n=12; 
		}else{
			$n=$_POST['selectprd']%12;
		}
		$j=$_POST['selectprd']-$n;
		$str='';
		DB_data_seek($result,0);
		$dd=array_fill(0,$n,"");    
		$sql="SELECT `periodno`, `account`,
		        sum(toamount(amount,periodno,periodno,periodno,1,flg)) amountj,
				sum(toamount(amount,periodno,periodno,periodno,-1,flg)) amountd 
				FROM gltrans WHERE account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('5001','5101','6601','6602','6603'))
				 AND periodno >=".$_SESSION['janr']."  AND periodno <=".$_POST['selectprd']." 
				  GROUP BY periodno,account 
				  ORDER BY periodno,account"; 
		$result=DB_query($sql);
		//$itemarr=array();
		//查询数据转数组
		while ($row=DB_fetch_array($result)){
          	$key= $row['periodno']-$_SESSION['janr']+1;
				$itemarr[$row['account']][$key]=array($row['amountj'],$row['amountd']);
			}
		$sql="SELECT  account ,sum(sumamount(amount,periodno,0,".$_SESSION['janr']."-1)) amount FROM gltrans WHERE periodno>=0 AND periodno<".$_SESSION['janr']." AND  account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('5001','5101','6601','6602','6603')) GROUP BY account";
		$result=DB_query($sql);
		//查询年初数据
		while ($row=DB_fetch_array($result)){
          	$itemarr[$row['account']][0][0]=$row['amount'];
		}
		$sql="SELECT  account ,sum(sumamount(amount,periodno,0,".$_POST['selectprd'].")) amount FROM gltrans WHERE periodno>=0 AND periodno<=".$_POST['selectprd']." AND  account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('5001','5101','6601','6602','6603')) GROUP BY account";
		$result=DB_query($sql);
		//期末合计数据
		while ($row=DB_fetch_array($result)){
          	$itemarr[$row['account']][13][0]=$row['amount'];
		}
		//汇总科目
		$sql="SELECT t3.accountname, t3.accountcode ,t3.currcode
		FROM chartmaster t3 
		WHERE t3.accountcode  in(SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
		( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) ))  AND left(t3.accountcode,4) IN ('5001','5101','6601','6602','6603') AND used>=0 ORDER BY t3.accountcode";
        $result=DB_query($sql);
		//汇总科目合计数据
		$detotal=0;
		$crtotal=0;
		while ($row=DB_fetch_array($result)){
			  //$itemarr[$row['account']][13][0]=$row['amount'];
			 for ($i=0;$i<=$n;$i++){
					foreach ($itemarr as $key=>$val) {
							if ($row['accountcode']==substr($key,0,strlen($row['accountcode']))){
								$itemarr[$row['accountcode']][0][0]+=(is_null($val[0][0])?0:$val[0][0]);
							    $itemarr[$row['accountcode']][0][1]+=is_null($val[0][1])?0:$val[0][1];
								$itemarr[$row['accountcode']][$i][0]+=(is_null($val[$i][0])?0:$val[$i][0]);
							    $itemarr[$row['accountcode']][$i][1]+=is_null($val[$i][1])?0:$val[$i][1];
								$itemarr[$row['accountcode']][13][0]+=is_null($val[13][0])?0:$val[13][0];
								$itemarr[$row['accountcode']][13][1]+=is_null($val[13][1])?0:$val[13][1];
						         //prnMsg($val[0][0].'='.$val[0][1]);	
						}
					}
			  }
		}
	}elseif ($_POST['costrevence']==21 && $_POST['dataformat']==2){
	   //  prnMsg('成本本期 21成本 '); 
			 //  $itemarr=array();
			   $p=$_POST['selectprd'];
			   if ($_POST['selectprd']%12==0){
				   $n=12; 
			   }else{
				   $n=$_POST['selectprd']%12;
			   }
			   $j=$_POST['selectprd']-$n;
				//读取明细科目
						$sql="SELECT t3.accountcode,t3.currcode,
										t3.accountname
									FROM chartmaster t3 
									WHERE t3.accountcode  NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
								( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) and left(t3.accountcode,4) IN ('5001','5101','6801','6601','6602','6603')    ORDER BY t3.accountcode";
				$sql="SELECT accountcode ,accountname FROM `chartmaster` WHERE left(accountcode,4) IN ('5001','5101','6601','6602','6603') ORDER BY accountcode";
				$result = DB_query($sql);
				//$AccountArr=array();
				$h=33; 
				$AccountStr='';
				while ($row=DB_fetch_array($result)) {
					$AccountArr[$row['accountcode']]=$row['accountname'];
					if ($AccountStr==''){
						$AccountStr.=substr($row['accountcode'],0,4).',';
					}else{
						$AccountStr.=','.substr($row['accountcode'],0,4);
					}
				}
				//			$dd=array_fill(0,$n,"");    
				$sql="SELECT `periodno`, `account`,sum(toamount(amount,periodno,periodno,periodno,1,flg)) amountj, sum(toamount(amount,periodno,periodno,periodno,-1,flg)) amountd FROM gltrans WHERE account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('5001','5101','6601','6602','6603')) AND  periodno =".$_POST['selectprd']."  GROUP BY periodno,account ORDER BY periodno,account"; 
				$result=DB_query($sql);
			//$itemarr=array();
				//查询数据转数组
				while ($row=DB_fetch_array($result)){
				//$key= $row['periodno']-$_SESSION['janr']+1;
					$itemarr[$row['account']][$j]=array($row['amountj'],$row['amountd']);
				}
				$sql="SELECT  account ,sum(sumamount(amount,periodno,0,".$_POST['selectprd']."-1)) amount FROM gltrans WHERE periodno>=0 AND periodno<".$_POST['selectprd']." AND  account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('5001','5101','6601','6602','6603')) GROUP BY account";
				$result=DB_query($sql);
				//查询期初数据
				while ($row=DB_fetch_array($result)){
				$itemarr[$row['account']][0][0]=$row['amount'];
				}
				$sql="SELECT  account ,sum(sumamount(amount,periodno,0,".$_POST['selectprd'].")) amount FROM gltrans WHERE periodno>=0 AND periodno<=".$_POST['selectprd']." AND  account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('5001','5101','6601','6602','6603')) GROUP BY account";
				$result=DB_query($sql);
				//期末合计数据
				while ($row=DB_fetch_array($result)){
				$itemarr[$row['account']][13][0]=$row['amount'];
				}
				//汇总科目
				$sql="SELECT t3.accountname, t3.accountcode ,t3.currcode
				FROM chartmaster t3 
				WHERE t3.accountcode  in(SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
				( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) ))  AND left(t3.accountcode,4) IN ('5001','5101','6601','6602','6603') AND used>=0 ORDER BY t3.accountcode";
				$result=DB_query($sql);
				//归总科目合计数据
				$detotal=0;
				$crtotal=0;
				while ($row=DB_fetch_array($result)){
				//$itemarr[$row['account']][13][0]=$row['amount'];
					foreach ($itemarr as $key=>$val) {
						if ($row['accountcode']==substr($key,0,strlen($row['accountcode']))){
							$itemarr[$row['accountcode']][0][0]+=(is_null($val[0][0])?0:$val[0][0]);
							$itemarr[$row['accountcode']][0][1]+=is_null($val[0][1])?0:$val[0][1];
							$itemarr[$row['accountcode']][$j][0]+=(is_null($val[$j][0])?0:$val[$j][0]);
							$itemarr[$row['accountcode']][$j][1]+=is_null($val[$j][1])?0:$val[$j][1];
							$itemarr[$row['accountcode']][13][0]+=is_null($val[13][0])?0:$val[13][0];
							$itemarr[$row['accountcode']][13][1]+=is_null($val[13][1])?0:$val[13][1];
							//prnMsg($val[0][0].'='.$val[0][1]);	
						}
					}
				}
	}elseif ($_POST['costrevence']==22 && $_POST['dataformat']==2){
		//收入本期   //22成本  
			   $p=$_POST['selectprd'];
			   if ($_POST['selectprd']%12==0){
				   $n=12; 
			   }else{
				   $n=$_POST['selectprd']%12;
			   }
			   $j=$_POST['selectprd']-$n;
			   //读取明细科目
				$sql="SELECT t3.accountcode,t3.currcode,
						t3.accountname
					FROM chartmaster t3 
					WHERE t3.accountcode  NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
				( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) and left(t3.accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603')    ORDER BY t3.accountcode";
				$sql="SELECT accountcode ,accountname FROM `chartmaster` WHERE left(accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603') ORDER BY accountcode";
				$result = DB_query($sql);
				$AccountArr=array();
				$h=33; 
				$AccountStr='';
				while ($row=DB_fetch_array($result)) {
				$AccountArr[$row['accountcode']]=$row['accountname'];
				if ($AccountStr==''){
					$AccountStr.=substr($row['accountcode'],0,4).',';
				}else{
					$AccountStr.=','.substr($row['accountcode'],0,4);
				}
				}
				//$itemarr=array();
				$sql="SELECT `periodno`, `account`,sum(toamount(amount,periodno,periodno,periodno,1,flg)) amountj, sum(toamount(amount,periodno,periodno,periodno,-1,flg)) amountd FROM gltrans WHERE account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603'))  AND periodno =".$_POST['selectprd']." GROUP BY periodno,account ORDER BY periodno,account"; 
				$result=DB_query($sql);
				//$itemarr=array();
				//查询数据转数组
				while ($row=DB_fetch_array($result)){
				//$key= $row['periodno']-$_SESSION['janr']+1;
					$itemarr[$row['account']][$j]=array($row['amountj'],$row['amountd']);
				}
				$sql="SELECT  account ,sum(sumamount(amount,periodno,0,".$_POST['selectprd']."-1)) amount FROM gltrans WHERE periodno>=0 AND periodno<".$_POST['selectprd']." AND  account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603')) GROUP BY account";
				$result=DB_query($sql);
				//查询期初初数据
				while ($row=DB_fetch_array($result)){
					$itemarr[$row['account']][0][0]=$row['amount'];
				}
				$sql="SELECT  account ,sum(sumamount(amount,periodno,0,".$_POST['selectprd'].")) amount FROM gltrans WHERE periodno>=0 AND periodno<=".$_POST['selectprd']." AND  account in (SELECT accountcode FROM `chartmaster` WHERE length(accountcode)>4 AND left(accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603')) GROUP BY account";
				$result=DB_query($sql);
				//期末合计数据
				while ($row=DB_fetch_array($result)){
					$itemarr[$row['account']][13][0]=$row['amount'];
				}
				//汇总科目
				$sql="SELECT t3.accountname, t3.accountcode ,t3.currcode
				FROM chartmaster t3 
				WHERE t3.accountcode  in(SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
				( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) ))  AND left(t3.accountcode,4) IN ('6001','6051','6301','6401','6403','6711','6801','6601','6602','6603') AND used>=0 ORDER BY t3.accountcode";
				$result=DB_query($sql);
				//归总科目合计数据
				$detotal=0;
				$crtotal=0;
				while ($row=DB_fetch_array($result)){
				//$itemarr[$row['account']][13][0]=$row['amount'];
				//for ($i=0;$i<=$n;$i++){
					foreach ($itemarr as $key=>$val) {
							if ($row['accountcode']==substr($key,0,strlen($row['accountcode']))){
								$itemarr[$row['accountcode']][0][0]+=(is_null($val[0][0])?0:$val[0][0]);
							    $itemarr[$row['accountcode']][0][1]+=is_null($val[0][1])?0:$val[0][1];
								$itemarr[$row['accountcode']][$j][0]+=(is_null($val[$j][0])?0:$val[$j][0]);
								$itemarr[$row['accountcode']][$j][1]+=is_null($val[$j][1])?0:$val[$j][1];
								$itemarr[$row['accountcode']][13][0]+=is_null($val[13][0])?0:$val[13][0];
							   // $itemarr[$row['accountcode']][13][1]+=is_null($val[13][1])?0:$val[13][1];
								//prnMsg($val[0][0].'='.$val[0][1]);	
						}
					}
				}
	}
	  //  $Result = DB_query($SQL, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was:'));
	//$	$ListCount=DB_num_rows($Result);
}
if (isset($_POST['CSV'])) {
	if($_POST['dataformat']==2){//选择期
			$CSVListing .= iconv("UTF-8","gbk//TRANSLIT",'科目码') .','. iconv("UTF-8","gbk//TRANSLIT",'科目名称') .','.iconv("UTF-8","gbk//TRANSLIT",'序号') .','. iconv("UTF-8","gbk//TRANSLIT", '期初余额');
				$CSVListing .=','. iconv("UTF-8","gbk//TRANSLIT",'借方发生额'). ','. iconv("UTF-8","gbk//TRANSLIT",'贷方发生额');
			$CSVListing .= ','.  iconv("UTF-8","gbk//TRANSLIT",'期末余额' ). "\n";
			$k=0;
			$R=1;
			ksort($itemarr,SORT_STRING);
		foreach ($itemarr as $key=>$val) {
			$CSVListing .='"'.$key.'","'.	iconv("UTF-8","gbk//TRANSLIT",$AccountArr[$key]).'","'.$R.'","';
			if(in_array(substr($key,0,4),$inarr)){												
				$CSVListing .=locale_number_format(-$itemarr[$key][0][0],2);
			}else{
				$CSVListing .=locale_number_format($itemarr[$key][0][0],2);
			}
			$detotal=0;
			$crtotal=0;
			    $CSVListing .='","'.locale_number_format($itemarr[$key][$j][0],2);
				$CSVListing .='","'.locale_number_format($itemarr[$key][$j][1],2);
			if(in_array(substr($key,0,4),$inarr)){												
				$CSVListing .='","'.locale_number_format(-$itemarr[$key][13][0],2);
			}else{
				$CSVListing .='","'.locale_number_format($itemarr[$key][13][0],2);
			}		
		//	$CSVListing .='","'.locale_number_format($detotal,2);
			$CSVListing .='"'. "\n";		
				$R++;			
		}
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
		header("Content-disposition: attachment; filename=".iconv("UTF-8","gbk//TRANSLIT",'费用表_').  date('Y-m-d')  .'.csv');
		header("Pragma: public");
		header("Expires: 0");
		//echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $CSVListing;
		exit;
   }else{
			$CSVListing .= iconv("UTF-8","gbk//TRANSLIT",'科目码') .','. iconv("UTF-8","gbk//TRANSLIT",'科目名称') .','.iconv("UTF-8","gbk//TRANSLIT", '序号').','. iconv("UTF-8","gbk//TRANSLIT",'年初金额');
			for ($i=1;$i<=$n;$i++){
				$CSVListing .= ','. iconv("UTF-8","gbk//TRANSLIT",GetMonthText($i).'金额');
			}
			$CSVListing .= ','.  iconv("UTF-8","gbk//TRANSLIT",'期末余额' ) .','.  iconv("UTF-8","gbk//TRANSLIT",'本年借方累计' ). ','.  iconv("UTF-8","gbk//TRANSLIT",'本年贷方累计' ). "\n";
			$R=1;
			//数组排序
			ksort($itemarr,SORT_STRING);
		foreach ($itemarr as $key=>$val) {
			$CSVListing .='"'.$key.'","'.	iconv("UTF-8","gbk//TRANSLIT",$AccountArr[$key]).'","'.$R.'","';
			if(in_array(substr($key,0,4),$inarr)){												
					$CSVListing .=locale_number_format($itemarr[$key][0][1],2);
				}else{
					$CSVListing .=locale_number_format($itemarr[$key][0][0],2);
				}
				$detotal=0;
				$crtotal=0;
			for ($i=1;$i<=$n;$i++){
				if(in_array(substr($key,0,4),$inarr)){
					$CSVListing .='","'.locale_number_format($itemarr[$key][$i][1],2);
				}else{
					$CSVListing .='","'.locale_number_format($itemarr[$key][$i][0],2);
				}
				$detotal+=round($itemarr[$key][$i][0],0);
				$crtotal+=round($itemarr[$key][$i][1],0);
			}
			if(in_array(substr($key,0,4),$inarr)){												
				$CSVListing .='","'.locale_number_format($itemarr[$key][13][1],2);
			}else{
				$CSVListing .='","'.locale_number_format($itemarr[$key][13][0],2);
			}		
			$CSVListing .='","'.locale_number_format($detotal,2);
			$CSVListing .='","'.locale_number_format($crtotal,2).'"'. "\n";
			$R++;		
		}
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
		header("Content-disposition: attachment; filename=".iconv("UTF-8","gbk//TRANSLIT",'费用表_').  date('Y-m-d')  .'.csv');
		header("Pragma: public");
		header("Expires: 0");
		//echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $CSVListing;
		exit;
   }
}elseif (isset($_POST['Search'])) {	
	 echo '<table class="selection">';
		if($_POST['dataformat']==2){//选择期
	     echo  '<tr>
					<th colspan="7" height="2">
						<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
						</div> 
					</th></tr>
				<tr>
				<th>科目编码</th>
					<th>科目名称</th>
					<th>' . _('Line')  . '</th>
					<th> 期初余额</th>
					<th> 选择期借方</th>
					<th> 选择期贷方</th>
					<th> 期末余额</th>
			</tr>';
			$k=0;
			$R=1;
			//数组排序
			ksort($itemarr,SORT_STRING);
		foreach ($itemarr as $key=>$val) {
			$ActEnquiryURL ='<a href="'. $RootPath . '/GLAccountInquiry.php?FromPeriod='.$_POST["selectprd"] . '&amp;ToPeriod='.$_POST["selectprd"] . '&amp;Account=' . $key . '&amp;Search=Yes">' . htmlspecialchars($key,ENT_QUOTES,'UTF-8',false). '</a>';
			if (empty($ActEnquiryURL)){
				$ActEnquiryURL =htmlspecialchars($key,ENT_QUOTES,'UTF-8',false);
			}
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			}else{
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			echo '<td>'.$ActEnquiryURL.'</td>	
					<td>'.$AccountArr[$key].'</td>									
					<td>'.$R.'</td>	';
			if(in_array(substr($key,0,4),$inarr)){												
				echo'	<td class="number">'.locale_number_format(-$itemarr[$key][0][1],2).'</td>';
			}else{
				echo'	<td class="number">'.locale_number_format($itemarr[$key][0][0],2).'</td>';
			}	
				echo'<td class="number">'.locale_number_format($itemarr[$key][$j][0],2).'</td>											
				     <td class="number">'.locale_number_format($itemarr[$key][$j][1],2).'</td>';
			if(in_array(substr($key,0,4),$inarr)){												
				echo'	<td class="number">'.locale_number_format(-$itemarr[$key][13][0],2).'</td>';
			}else{
				echo'	<td class="number">'.locale_number_format($itemarr[$key][13][0],2).'</td>';
			}						
				$R++;			
		}
	}else{
		//年度台账
		echo  '<tr>
		<th colspan="'.($n+7).'" height="2">
			<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
			</div> 
		</th></tr>
		<tr>
		    <th>科目编码</th>
			<th>科目名称</th>
			<th>' . _('Line')  . '</th>';
		//	if($_POST['costrevence']==21){
				echo'<th>年初数</th>';
		//	}
			for ($i=1;$i<=$n;$i++){
				echo '<th>'. GetMonthText($i).'金额</th>';
			}
			//if($_POST['costrevence']==21){
				echo'<th>期末余额</th>';
			//}
		echo'<th>本年借方累计</th>
		     <th>本年贷方累计</th>
		</tr>';
			$k=0;
			$R=1;
			ksort($itemarr,SORT_STRING);
		foreach ($itemarr as $key=>$val) {
			$ActEnquiryURL = '<a href="'. $RootPath . '/GLAccountInquiry.php?FromPeriod='.($_POST['selectprd']-$n+1).'&amp;ToPeriod='.$_POST['selectprd'] . '&amp;Account=' . $key . '&amp;Search=Yes">' . htmlspecialchars($key,ENT_QUOTES,'UTF-8',false). '</a>';
			if (empty($ActEnquiryURL)){
				$ActEnquiryURL =htmlspecialchars($key,ENT_QUOTES,'UTF-8',false);
			}
			if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
			} else {
			echo '<tr class="OddTableRows">';
			$k=1;
			}
			echo '<td>'.$ActEnquiryURL.'</td>	
			<td>'.$AccountArr[$key].'</td>				
					<td>'.$R.'</td>	';	
			if(in_array(substr($key,0,4),$inarr)){												
			echo'	<td class="number">'.locale_number_format($itemarr[$key][0][1],2).'</td>';
			}else{
				echo'	<td class="number">'.locale_number_format($itemarr[$key][0][0],2).'</td>';
			}
					$detotal=0;
					$crtotal=0;
					for ($i=1;$i<=$n;$i++){
						if(in_array(substr($key,0,4),$inarr)){
							echo'<td class="number">'.locale_number_format($itemarr[$key][$i][1],2).'</td>';
						}else{
							echo'<td class="number">'.locale_number_format($itemarr[$key][$i][0],2).'</td>';
						}
						$detotal+=round($itemarr[$key][$i][0],0);
						$crtotal+=round($itemarr[$key][$i][1],0);
					}
			if(in_array(substr($key,0,4),$inarr)){												
				echo'	<td class="number">'.locale_number_format($itemarr[$key][13][1],2).'</td>';
			}else{
				echo'	<td class="number">'.locale_number_format($itemarr[$key][13][0],2).'</td>';
			}		
			echo '<td class="number">'.locale_number_format($detotal,2).'</td>
			      <td class="number">'.locale_number_format($crtotal,2).'</td>';	
				$R++;			
		}
	}
		echo '</table>';
}
echo '</div>
    </form>';
include('includes/footer.php');
?>
