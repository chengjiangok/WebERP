<?php
/* $Id: Z_CheckAllocs.php 6941 2014-10-26 23:18:08Z daintree $*/
/*This page adds the total of allocation records and compares this to the recorded allocation total in DebtorTrans table */

include('includes/session.php');
$Title ='检查各种单据编号';
include('includes/header.php');


echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Inventory Adjustment') . '" alt="" />' . ' ' . $Title . '</p>';

//if (!isset($SelectedCategory)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of stock categorys will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
	    $SysTypo=array(18=>"采购订单",30=>"销售订单",40=>"工作单");
		$sql = "SELECT `typeid`, `typename`, `typeno` FROM `systypes` WHERE typeid IN (17,18,25,26,28,45,30,33,35,36,38,39,40,49)";
		$result = DB_query($sql);
	
		echo '<br />
			<table class="selection">
				<tr>
					<th class="ascending">' . _('Code') . '</th>					
					<th class="ascending">' . _('Description')  . '</th>' . '	
					<th class="ascending">类别</th>' . '				
					<th class="ascending">编号</th>' . '				
					<th class="ascending">单据编号</th>' . '
					<th class="ascending">' . _('Stock') . '</th>' . '
				</tr>';
	
		$k=0; //row colour counter
	
		while ($myrow = DB_fetch_array($result)) {
			$typeno=0;
			if ($myrow['typeid']==30){
				$sql="SELECT MAX(orderno)   no  FROM `salesorders` ";
				$Result=DB_query($sql);
				$row=DB_fetch_assoc($Result);
				//print_r($row);
				if (!empty($row)){
				
			
					$typeno=$row['no'];
				}
			}elseif	($myrow['typeid']==18){
				$sql="SELECT MAX(orderno) no FROM `purchorders`";
				$Result=DB_query($sql);
				$row=DB_fetch_assoc($Result);
			
				if (!empty($row)){
				
			
					$typeno=$row['no'];
				}
			}elseif	($myrow['typeid']==17||$myrow['typeid']==28||$myrow['typeid']==25||$myrow['typeid']==39){
				$sql="SELECT MAX(`stkmoveno` ) no FROM `stockmoves` WHERE  `type`=".$myrow['typeid']." ";
				$Result=DB_query($sql);
				$row=DB_fetch_assoc($Result);
			
				if (!empty($row)){
				
			
					$typeno=$row['no'];
				}
			}elseif	($myrow['typeid']==40){
				$sql="SELECT `wo` FROM `workorders` ORDER BY wo DESC LIMIT 1";
				$Result=DB_query($sql);
				$row=DB_fetch_assoc($Result);
			
				if (!empty($row)){
				
			
					$typeno=$row['wo'];
				}
			}
			$type="";
			if (isset($SysTypo[$myrow['typeid']])){
				$type="编码";
			}
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					
					<td><a href="%sType=%s&amp;TypeNo=%s" onclick="return confirm(\'' . _('Are you sure you wish to delete this stock category? Additional checks will be performed before actual deletion to ensure data integrity is not compromised.') . '\');">修改</a></td>
				</tr>',
					$myrow['typeid'],			 			
					$myrow['typename'],
					$type,
					$myrow['typeno'],
				    $typeno,
				
					htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
					$myrow['typeid'],
					$typeno);
		}
		//END WHILE LIST LOOP
		echo '</table>';
	
echo '<br />
		<div class="centre">
			<input type="submit" name="submit" value="刷新" />
		</div>
		</div>
		</form>';
	
	echo '<br />';
include('includes/footer.php');

?>