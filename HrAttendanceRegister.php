
<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
$Title = _('Attendance');
$ViewTopic = 'HumanResource';
$BookMark = 'Attendance';
include('includes/header.php');
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	// BEGIN: Leave Types  array.
/*
	echo'<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
*/
echo '<tr><form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
<div>
  <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
  <input type="hidden" name="Months" value="' . $_POST['Months'] . '" />
  <input type="hidden" name="Department" value="' . $_POST['Department']. '" />
    
  <input type="hidden" name="EmployeeID" value="' . $myrow['employee_id'] . '" />';
    if (isset($_GET['Department'])) {
    	$SelectedDEPT = $_GET['Department'];
    } elseif (isset($_POST['Department'])){
    	$SelectedStockItem = $_POST['Department'];
    } else {
    	unset($SelectedDEPT);
    }

    if (isset($Errors)) {
    	unset($Errors);
    }

    $Errors = array();

//    if (!isset($EN) or ($EN=='')){
      echo '<table class="selection">
             <tr><td>';
    if (isset($SelectedEmployee)) {
      echo _('For the Employee') . ': ' . $SelectedEmployee . ' ' . _('and') . ' <input type="hidden" name="$SelectedEmployee" value="' . $SelectedEmployee . '" />';
    }
    echo  _('Department') . ':
            <select name="Department"> 
            <option value="">select department</option>';
    $sql = "SELECT departmentid, description FROM departments";
    $resultDepartments = DB_query($sql);
    while ($myrowdept=DB_fetch_array($resultDepartments)){
  		if (isset($_POST['Department'])){
  			if ($myrowdept['departmentid'] == $_POST['Department']){
  				echo '<option selected="selected" value="' . $myrowdept['departmentid'] . '">' . $myrowdept['description'] . '</option>';
  			} else {
  				echo '<option';

          if (isset($_POST['SearchAttendance']) and $_POST['Department']==$myrowdept['departmentid']) {
               echo ' selected="selected"';
          }

            echo  ' value="' . $myrowdept['departmentid'] . '">' . $myrowdept['description'] . '</option>';
  			}
  		} elseif ($myrowdept['departmentid']==$_SESSION['UserStockLocation']){
  				 echo '<option selected="selected" value="' . $myrowdept['departmentid'] . '">' . $myrowdept['description'] . '</option>';
  		} else {
  				 echo '<option value="' . $myrowdept['departmentid'] . '">' . $myrowdept['description'] . '</option>';
  		}
  	}
    $months_string = "";
    
      echo '</select> '
            ._('Attendance Period') . ':';
            SelectPeriod($_SESSION['CompanyRecord'][1]['companynumber']);         
      echo '<input type="submit" name="SearchAttendance" value="' . _('Search') . '" />
          
          </td>
  			</tr>
  			</table><br />
				</div>
        </form>';
        $_POST['Months']=date('m',strtotime( PeriodGetDate($_POST['ERPPrd'])));
if(isset($_POST['SearchAttendance'])) {
 //prnMsg( $_POST['Months']);
  
    $InputError = 0;
   // $_POST['Months']=date('m',strtotime(explode('^',$_POST['Months'])[1]));
  if (isset($_POST['AbsentDate'])) {

    if (mb_strlen($_POST['EmployeeID'])==0) {
      $InputError = 1;
      echo '<br />';
      prnMsg(_('Select a Reason First'),'error');
      $Errors[$i] = 'LeaveType';
      $i++;
    }
    if ( $InputError !=1) {
      //print_r($_POST['EmployeeID'].'nnnnnn'); exit();
      $AbsentDays1 =$_POST['AbsentDate'];

      foreach($AbsentDays1 as $absent_day1){
        $date =$AbsentDays1[0];

      }
      $dateyear=date_create($date);
      $year= date_format($dateyear,"Y");

      $sql1="DELETE FROM hremployeeattendanceregister
                    WHERE MONTH(absent_date)='".$_POST['month']."' AND YEAR(absent_date)='".$year."' AND employee_attendance_id='".$_POST['EmployeeID']."'";
      $deleteresult1 = DB_query($sql1);
      $AbsentDays =$_POST['AbsentDate'];
      $LeaveTypes=$_POST['LeaveType'];
      foreach($LeaveTypes as $key=> $absent_day){

        $absentdays = explode("|", $absent_day);
        $absentday=$absentdays[1];
        $reason=$absentdays[0];

        if(in_array($absentday,$AbsentDays)){
            $sqlQuery = "INSERT INTO hremployeeattendanceregister(employee_attendance_id,
                                                                  absent_date,
                                                                  leave_type_id )
                                                                    VALUES ('" . $_POST['EmployeeID'] . "',
                                                                '" .date('Y-m-d',strtotime($absentday)). "',
                                                                '" . $reason . "' )";
          $result = DB_query($sqlQuery);
        }
      }//endfor
    }
  }

      $month_selected = $_POST['Months'];
      $department = $_POST['Department'];
      $days_in_month_array =array();
      $current_year = date('Y');
      for($d=1; $d<=31; $d++)
      {
          $time=mktime(12, 0, 0, $month_selected, $d, $current_year);
          if (date('m', $time)==$month_selected)
              $days_in_month_array[]=date('d', $time);
      }

      //create table header


  echo'<div style="overflow-x:auto;margin:0px 40px">';
      echo '<table class="selection1">';
      echo '<tr>
                <th>Employee</th>';
                foreach($days_in_month_array as $day) {
                  echo '<th>'.$day.'</th>';
                }
                '<th></th>
                 <th></th>';
      echo ' </tr>';
      $sqlemp= "SELECT empid,employee_id,first_name,middle_name,last_name,employee_department 
              FROM hremployees";
      // WHERE employee_department='".$department."'";
      $ErrMsg = _('The employee could not be loaded because');
      $DbgMsg = _('The SQL that was used to get the employees and failed was');
      $result = DB_query($sqlemp,$ErrMsg,$DbgMsg);

  while($myrow = DB_fetch_array($result)) {
    echo'<td>'.$myrow['first_name'].' 
                   </td>';
            $AllAbsentDays = array();
        $sql2="SELECT employee_attendance_id,
                      absent_date,
                      leave_type_id
                    FROM hremployeeattendanceregister 
                    WHERE employee_attendance_id='".$myrow['employee_id']."'";
        $result1 = DB_query($sql2);

    while ($Row1 = DB_fetch_array($result1)) {
      //echo "<h1>".$Row['absent_date']."</h1>";

      $AllAbsentDays[] = $Row1['absent_date'];
    }
   //var_dump($AllAbsentDays);
    $dates_in_month = array();
    foreach($days_in_month_array as $day) {
        $month_date = date('Y-m-d',mktime(0, 0, 0, $month_selected, $day, $current_year));
        $dates_in_month[] = $month_date;
       // print($month_date);echo'<br \>';
        echo '<td>
                 <input type="checkbox" name="AbsentDate[]" value="'. $month_date.'" '.(in_array(($month_date), $AllAbsentDays) ? "checked":"").' />';
        
                 echo  '<select id="LeaveType" name="LeaveType[]">';
        $AllLeaveTypes = array();
        $Query = "SELECT 	hrleavetype_id,
                          leavetype_code
                  FROM hremployeeleavetypes
                   WHERE leavetype_status =1 ";
        $Result4 = DB_query($Query);
        while ($RowType = DB_fetch_array($Result4)) {
          $AllLeaveTypes[$RowType['hrleavetype_id']] = $RowType['leavetype_code'];
        }
        foreach ($AllLeaveTypes as $LeaveType_Id => $LeaveType) {

          if (in_array(($month_date), $AllAbsentDays)) {
            $sql3="SELECT leave_type_id
                    FROM hremployeeattendanceregister 
                    WHERE employee_attendance_id='".$myrow['employee_id']."' 
                    AND absent_date ='".$month_date."'";
            $result3 = DB_query($sql3);
            $myrow3 = DB_fetch_array($result3);

            echo '<option '.(($myrow3['leave_type_id']==$LeaveType_Id) ? ' selected="selected"':'').'value="' . $LeaveType_Id.'|'.$month_date. '">' . $LeaveType . '</option>';

          }else{
            echo '<option  value="'.$LeaveType_Id.'|'.$month_date.'">' . $LeaveType . '</option>';
          }

        }
        echo '</select>';
        echo'</td>';
    }
        echo'<td><input type="submit" name="SearchAttendance" value="' . _('Save') . '" /></td>

                </form></tr>';
  }

      echo '</table></div>
      <style> #LeaveType option{
        width:5px;
         }
      </style> ';
}
include('includes/footer.php');
 ?>
