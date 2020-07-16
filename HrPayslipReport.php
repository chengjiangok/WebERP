<?php
/* $Id: HrPayslipReport.php$*/
/* Search for employees  */

include('includes/session.php');
$Title ='工资单查询';
$ViewTopic = 'HumanResource';
$BookMark = 'HumanResource';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

    if (isset($_GET['Department'])) {
    	$SelectedDEPT = $_GET['Department'];
	} 
	if (!isset($_POST['Department'])){
    	$_POST['Department']=0;
    } 
	  echo '<table class="selection">';
	  echo '<tr>
		  <td>' . _('Select Period To')  . '</td>		  
		  <td >';
		    SelectPeriod($_SESSION['period']);
		  /*<select name="ERPPrd" size="1" >';
			if (($_SESSION['period']-$_SESSION['startperiod'])<36){	  					
				$sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".$_SESSION['startperiod'] ."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
			}else{
				$sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".$_SESSION['startperiod'] ."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
			}
			$pmsql=$sql;
			$periods = DB_query($sql);
		
		while ($myrow=DB_fetch_array($periods,$db)){	
			if(isset($_POST['ERPPrd']) AND $myrow['periodno']==$_POST['ERPPrd']){	
				echo '<option selected="selected" value="';
			} else {
				echo '<option value ="';
			}
			echo   $myrow['periodno']. '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		}   */
    echo '</select></td>';
		$rang=array('0'=>'月度', '3'=>'季度','12'=>'本年','24'=>'上年','36'=>'前年');
    echo '<td>  范围
		<select name="PrdRange" size="1" style="width:80px" >';
		if (($_SESSION['janr']-$_SESSION['startperiod'])<=0 ){
			unset($rang[36]);
			unset($rang[24]);
		}elseif (($_SESSION['janr']-$_SESSION['startperiod'])<=12 ){
			unset($rang[36]);		
		}
		foreach($rang as $key=>$val){			
			if (isset($_POST['PrdRange'])&& $key==$_POST['PrdRange']){
				echo '<option selected="True" value ="';
			}else{
				echo '<option value ="';
			}
			echo $key.'">'.$val.'</option>';		
		}		
	echo'</select>
		</td></tr>';

	  echo'<tr>
		<td>单元分组</td>
			<td colspan="2"><select name="UnitsTag" size="1" >';
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
	  <td >'. _('Department') .'</td>
	  <td colspan="2">';
    
	echo'<select name="Department"> 
	        <option value="0">选择全部</option>';
    $sql = "SELECT departmentid, description FROM departments";
    $resultDepartments = DB_query($sql);
    while ($myrow=DB_fetch_array($resultDepartments)){
  			if (isset($_POST['Department'])){
  				if ($myrow['departmentid'] == $_POST['Department']){
  					 echo '<option selected="selected" value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
  				} else {
  					 echo '<option value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
  				}
  			} elseif ($myrow['departmentid']==$_SESSION['UserStockLocation']){
  				 echo '<option selected="selected" value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
  			} else {
  				 echo '<option value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
  			}
  		}

		  echo '</select></td></tr>';
		  echo '<tr><td colspan="3">';
		  if (isset($SelectedEmployee)) {
			echo _('For the Employee') . ': ' . $SelectedEmployee . ' ' . _('and') . ' 
			<input type="hidden" name="$SelectedEmployee" value="' . $SelectedEmployee . '" />';
		  }
		  echo'员工工号: <input type="text" name="EN" autofocus="autofocus" maxlength="8" size="9" />
		
		       姓\名:   <input type="text" name="Ename"  maxlength="8" size="9" /></td>
  			</tr>
  			</table>
			  <br />';
			  echo '<div class="centre">
			  <input type="submit" name="SearchEmployee" value="' . _('Search') . '" />	
		  </div>';
		$SQL="SELECT `period`, `tag`,SUM(`income`) income,SUM( `socialsecurity`+ `medicalinsurance`+ `unemployment`) smu, sum(taxsavings) taxtotal ,COUNT(*) empcount FROM `hrtaxpayslips` GROUP BY period,tag";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result)>0){
			echo '<table class="selection">';
			echo'<tr>
					<th >月份</th>
					<th >分组</th>
					<th >员工人数</th>				
					<th >工资额合计</th>			
					<th >社保合计</th>
					<th >所得税合计</th>						
				</tr>';
			
			$k = 1;// Row colour counter.
			$RowIndex=1;
			$EmpAmo=0;
			$CostRdt=0;
			while ($row = DB_fetch_array($Result)) {
				if($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				
				echo	'<td>'.PeriodGetDate($row['period']).'</td>			      
						<td class="text">'. $row['tag']. '</td>
						<td class="text">'. $row['empcount']. '</td>  
						<td class="text">'. $row['income']. '</td>
						<td class="text">'. $row['smu']. '</td>
						<td class="text">'. $row['taxtotal']. ' </td>
						  				
					
					</tr>';
					$RowIndex++;
					$EmpCount+=$row['empcount'];
					$EmpAmo+=$row['income'];
					$EmpSMU+=$row['smu'];
					$TaxTotal+=$row['taxtotal'];
			}// END foreach($Result as $row).
			echo'<tr>
						<td colspan="2" class="text"></td>
						<td class="text">'. $EmpCount. '</td>

						<td class="text">'. number_format($EmpAmo,2). '</td>
						<td class="text">'. number_format($EmpSMU,2). '</td>
						<td class="text">'. number_format($TaxTotal,2). '</td>
					
						
					</tr>';
			echo '</table>
				<br />';
		}
		echo'</div>
        </form>';

    if(isset($_POST['SearchEmployee'])) {

    		  $base_sql =	"SELECT	a.`employee_id`,
									`period`,
									a.`tag`,
									a.`empid`,
									`startdate`,
									first_name,
									b.employee_department,
									c.description,
									`enddate`,
									`income`,
									`exemptincome`,
									`socialsecurity`,
									`medicalinsurance`,
									`unemployment`,
									`housingfund`,
									`childeducation`,
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
									a.`flg`
								FROM	`hrtaxpayslips` a
								LEFT JOIN hremployees b ON a.employee_id=b.employee_id
								LEFT JOIN departments c ON b.employee_department= c.departmentid
								
								WHERE	a.tag='".$_POST['UnitsTag']."' ";
				
				//	JOIN hremployeesalarystructures ON hremployees.empid = hremployeesalarystructures.employee_id
    if(isset($_POST['PrdRange']) && $_POST['PrdRange']==0){
    		$SQL = $base_sql."AND  period=".$_POST['ERPPrd'];

	}

	if(isset($_POST['Department']) && $_POST['Department'] !=0){
      $SQL = $base_sql."AND  employee_department=".$_POST['Department']."";
    }
    elseif(isset($_POST['Ename']) && $_POST['Ename'] != "") {
      $SQL = $base_sql."AND  first_name LIKE '%".$_POST['Ename']."%'";
	}
	if ($SQL==''){
		$SQL=$base_sql;
	}
	$SQL.=" ORDER BY b.employee_department,a.`employee_id`";
	//prnMsg($SQL);
    $Result = DB_query($SQL);
    if (DB_num_rows($Result)>0){
		echo '<table class="selection">';
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
		
		$k = 1;// Row colour counter.
		$RowIndex=1;
		$EmpAmo=0;
		$CostRdt=0;
    	while ($row = DB_fetch_array($Result)) {
    		if($k == 1) {
    			echo '<tr class="OddTableRows">';
    			$k = 0;
    		} else {
    			echo '<tr class="EvenTableRows">';
    			$k = 1;
    		}
				//	$sql2 ="SELECT departmentid,description FROM departments WHERE departmentid =".$row['employee_department']."";
					$result2 = DB_query($sql2);
					$deparmentDetails = DB_fetch_array($result2);
    		/*The SecurityHeadings array is defined in config.php */
			echo	'<td>'.$RowIndex.':'.$row['employee_id'].'</td>			      
					<td class="text">'. $row['first_name']. '</td>
					<td class="text">'. $row['description']. '</td>
					<td class="text">'. $row['startdate']. '</td>
					<td class="text">'. $row['enddate']. ' </td>
    				<td class="text">'. $row['income']. '</td>    				
    				<td class="text">'. $row['gender']. '</td>
    				<td class="centre">'. $row['date_of_birth']. '</td>
					<td class="text">'. $row['nationality']. '</td>
					<td class="text"></td>
					<td class="text">'. $row['home_address']. '</td>
    				<td class="text">'. $row['marital_status']. '</td>
					<td class="text">'. number_format($row['gross_pay'],2). '</td>
					<td class="text">'. number_format($row['net_pay'],2). '</td>
					<td class="text">'. $row['home_address']. '</td>
					<td class="text">'. $row['marital_status']. '</td>
					<td class="text">'. $row['costreduction']. '</td>
					<td class="text">'. $row['home_address']. '</td>
    				<td class="text">'. $row['marital_status']. '</td>
				</tr>';
				$RowIndex++;
				$EmpAmo+=$row['income'];
				$CostRdt+=$row['costreduction'];
		}// END foreach($Result as $row).
		echo'<tr>
					<td colspan="5" class="text"></td>
					<td class="text">'. number_format($EmpAmo,2). '</td>
					<td class="text">'. number_format($row['net_pay'],2). '</td>
					<td class="text">'. $row['home_address']. '</td>
					<td class="text">'. $row['marital_status']. '</td>
					<td class="text">'. $row['costreduction']. '</td>
					<td class="text">'. $row['home_address']. '</td>
					<td class="text">'. $row['marital_status']. '</td>
					<td class="text">'. $row['costreduction']. '</td>
					<td class="text">'. $row['home_address']. '</td>
					<td class="text">'. $row['marital_status']. '</td>
					<td class="text">'. $row['marital_status']. '</td>
					<td class="text">'.number_format($CostRdt,2). '</td>
					<td class="text">'. $row['home_address']. '</td>
					<td class="text">'. $row['marital_status']. '</td>
				</tr>';
    	echo '</table>
			<br />';
	}else{
		prnMsg('查询期间无工资资料!','info');
	}
    }
include('includes/footer.php');
?>
