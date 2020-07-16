<?php
/* $Id: PaSelectProject.php 7751 2018-04-13 16:34:26Z raymond $*/
/* Search for project  */

include('includes/session.php');
$Title = _('Search Projects');
$ViewTopic = 'Projects';
$BookMark = 'Projects';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

    if (isset($_GET['ProjectID'])) {
    	$PID = $_GET['ProjectID'];
    } elseif (isset($_POST['ProjectID'])){
    	$PID = $_POST['ProjectID'];
    } else {
    	unset($EN);
    }
    if (isset($_GET['ProjectType'])) {
    	$SelectedDEPT = $_GET['ProjectType'];
    } elseif (isset($_POST['ProjectType'])){
    	$SelectedStockItem = $_POST['ProjectType'];
    } else {
    	unset($SelectedDEPT);
    }
//    if (!isset($EN) or ($EN=='')){
      echo '<table class="selection"><tr><td>';
    if (isset($SelectedProject)) {
      echo _('For the Project') . ': ' . $SelectedProject . ' ' . _('and') . ' <input type="hidden" name="$SelectedProject" value="' . $SelectedProject . '" />';
    }
    echo _('Project number') . ': <input type="text" name="EN" autofocus="autofocus" maxlength="8" size="9" />&nbsp; ' . _('Type') . ':<select name="ProjectType"> <option value="">search by project type</option>';
    $sql = "SELECT project_type_id, project_type_name FROM paprojecttypes";
    $resultProjectTypes = DB_query($sql);
    while ($myrow=DB_fetch_array($resultProjectTypes)){
  				if ($myrow['project_type_id'] == $_POST['ProjectType']){
  					 echo '<option selected="selected" value="' . $myrow['project_type_id'] . '">' . $myrow['project_type_name'] . '</option>';
  				} else {
  					 echo '<option value="' . $myrow['project_type_id'] . '">' . $myrow['project_type_name'] . '</option>';
  				}

  		}

  		echo '</select> &nbsp;&nbsp'._('Project name') . ': <input type="text" name="ProjectName"  maxlength="8" size="9" />&nbsp';
      echo '<input type="submit" name="SearchProject" value="' . _('Search') . '" />
  			&nbsp;&nbsp;<a href="' . $RootPath . '/PaProjects.php?New=Yes">' . _('New Project') . '</a></td>
  			</tr>
  			</table>
  			<br />
				</div>
        </form>';
//    }
    if(isset($_POST['SearchProject'])) {

    	echo '<table class="selection">
    			<tr>
    				<th class="ascending">', _('Project ID'), '</th>
    				<th class="ascending">', _('Project Name'), '</th>
            <th class="ascending">', _('Project Type'), '</th>
    				<th class="ascending">', _('Project Status'), '</th>
    				<th class="ascending">', _('Begin Date'), '</th>
    				<th class="ascending">', _('End Date'), '</th>
    				<th class="noprint" colspan="2">&nbsp;</th>
    			</tr>';
			$base_sql =	"SELECT id,project_id,
    					project_name,
    					project_category,
              project_type,
    					project_manager,
    					project_status,
    					begin_date,
              end_date,
    					status
    				FROM paprojects WHERE";
      if(isset($PID) && $PID !=""){
    	$Sql = $base_sql." paprojects.project_id LIKE '%".$EN."%'";

    }
    elseif(isset($_POST['ProjectType']) && $_POST['ProjectType'] != "")
    {
      $Sql = $base_sql." project_type=".$_POST['ProjectType']."";
    }
    elseif(isset($_POST['ProjectName']) && $_POST['ProjectName'] != "")
    {
      $Sql = $base_sql." project_name LIKE '%".$_POST['ProjectName']."%'";
    }

    $Result = DB_query($Sql);

    	$k = 1;// Row colour counter.
    	while ($MyRow = DB_fetch_array($Result)) {
    		if($k == 1) {
    			echo '<tr class="OddTableRows">';
    			$k = 0;
    		} else {
    			echo '<tr class="EvenTableRows">';
    			$k = 1;
    		}
					$sql2 ="SELECT project_type_id, project_type_name FROM paprojecttypes WHERE project_type_id =".$MyRow['project_type']."";
					$result2 = DB_query($sql2);
					$TypeDetails = DB_fetch_array($result2);
    		/*The SecurityHeadings array is defined in config.php */
    		echo	'<td class="text">'. $MyRow['project_id']. '</td>
    				<td class="text">'. $MyRow['project_name']. '</td>
    				<td class="text">'. $TypeDetails['project_type_name']. ' </td>
    				<td class="text">'. $MyRow['project_status']. '</td>
    				<td class="text">'. $MyRow['begin_date']. '</td>
    				<td class="text">'. $MyRow['end_date']. '</td>
    				<td class="noprint"><a href="PaProjects.php?ProjectID='. $MyRow['id']. '">'. _('Edit'). '</a></td>
    				<td class="noprint"><a href="PaProjects.php?ProjectID='. $MyRow['id']. '&amp;delete=1" onclick="return confirm(\'', _('Are you sure you wish to delete this project?'), '\');">'. _('Delete'). '</a></td>
    			</tr>';
    	}// END foreach($Result as $MyRow).
    	echo '</table>
    		<br />';
    }
include('includes/footer.php');
?>
