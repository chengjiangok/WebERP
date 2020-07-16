
<?php
/* $Id: StockSubCategory.php  290 2017/1/26 5:39:36Z ChengJiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-04-04 16:38:00 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-11-02 07:07:39
 */
include('includes/session.php');

$Title = '物料子类';
$ViewTopic= 'Inventory';
$BookMark = 'InventoryCategories';
include('includes/header.php');


echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Inventory Adjustment') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['SelectedCategory'])){
	$SelectedCategory = mb_strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])){
	$SelectedCategory = mb_strtoupper($_POST['SelectedCategory']);
}

   $stocktype=array('A'=>'装配物料','K'=>'套装物料','M'=>'生产物料','G'=>'虚拟物料','B'=>'采购物料','D'=>'服务及劳务');
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;	
if (mb_strlen($_POST['SubCategoryDspn']) >20 or mb_strlen($_POST['SubCategoryDspn'])==0) {
		$InputError = 1;
		prnMsg(_('The Sales category description must be twenty characters or less long and cannot be zero'),'error');
	} 
	if (isset($SelectedCategory) AND $InputError !=1) {

		/*SelectedCategory could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE stocksubcategory 
		         SET  subcategorydspn = '" . $_POST['SubCategoryDspn'] . "'
				 WHERE subcategoryid = '" . $SelectedCategory. "'";
		$ErrMsg = _('Could not update the stock category') . $_POST['CategoryDescription'] . _('because');
		$result = DB_query($sql,$ErrMsg);
		prnMsg(_('Updated the stock category record for') . ' ' . $_POST['CategoryDescription'],'success');

	} elseif ($InputError !=1) {

	   /*Selected category is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new stock category form */

		$sql = "INSERT INTO stocksubcategory
		                    (`code`,
		                     `categoryid`,		                    
		                      `subcategoryid`,
							  stocktype,
		                       `subcategorydspn`,
		                       flg)
		                     VALUES ('" . $_POST['SubCategoryID'] ."','" .
											explode('^', $_POST['CategoryID'])[0] . "','" .											
										   $_POST['SubCategoryID'] . "','" .
										   	explode('^', $_POST['CategoryID'])[1] . "','" .
											$_POST['SubCategoryDspn'] . "',
											0)";
		
		$ErrMsg = _('Could not insert the new stock category') . $_POST['CategoryDescription'] . _('because');
		$result = DB_query($sql,$ErrMsg);
		prnMsg(_('A new stock category record has been added for') . ' ' . $_POST['CategoryDescription'],'success');

	}
	//run the SQL from either of the above possibilites
	unset($_POST['code']);
	unset($_POST['SubCategoryDspn']);
	unset($_POST['CategoryID']);	
	unset($_POST['subcategoryid']);
} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'StockMaster'

	$sql= "SELECT stockid FROM stockmaster WHERE stockmaster.categoryid LIKE '" . $SelectedCategory . "%'";
	$result = DB_query($sql);

	if (DB_num_rows($result)>0) {
		prnMsg(_('Cannot delete this stock category because stock items have been created using this stock category') .
			'<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('items referring to this stock category code'),'warn');

	} else {
	$sql="DELETE FROM stocksubcategory WHERE code='" . $SelectedCategory . "'";
				$result = DB_query($sql);
				prnMsg(_('The stock category') . ' ' . $SelectedCategory . ' ' . _('has been deleted') . ' !','success');
				unset ($SelectedCategory);
			
		
	} //end if stock category used in debtor transactions
}

if (!isset($SelectedCategory)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of stock categorys will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT s.`categoryid`, categorydescription, `subcategoryid`, `code`, `subcategorydspn`, s.stocktype FROM `stocksubcategory` s LEFT JOIN stockcategory  c ON c.categoryid=s.categoryid";
	$result = DB_query($sql);

	echo '<br />
		<table class="selection">
			<tr>
				<th class="ascending">' . _('Code') . '</th>
				
				<th class="ascending">' . _('Description')  . '</th>' . '
				<th class="ascending">' . _('Category')  . '</th>' . '
				<th class="ascending">' . _('Stock Type') . '</th>' . '
				<th class="ascending">旧编码</th>' . '
			
				<th class="ascending">' . _('Stock') . '</th>' . '
				<th colspan="2">' . _('Maintenance') . '</th>
			</tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf('<td>%s</td>
				<td>%s</td>
				<td>[%s]%s</td>				
				<td>%s</td>
				<td>%s</td>
			
				<td><a href="%sSelectedCategory=%s">' . _('Edit') . '</a></td>
				<td><a href="%sSelectedCategory=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this stock category? Additional checks will be performed before actual deletion to ensure data integrity is not compromised.') . '\');">' . _('Delete') . '</a></td>
			</tr>',
				$myrow['subcategoryid'],			 			
				$myrow['subcategorydspn'],
				$myrow['categoryid'],
				$myrow['categorydescription'],
				$stocktype[$myrow['stocktype']],
				$myrow['code'],			
			
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$myrow['subcategoryid'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$myrow['subcategoryid']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

echo '<br />';

if (isset($SelectedCategory)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" >' . _('Show All Stock Categories') . '</a>';
}

echo '<form id="CategoryForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<div>';
echo '<br />';

echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedCategory)) {
	//editing an existing stock category
		/*if (!isset($_POST['UpdateTypes'])) {
				$sql = "SELECT categoryid,
								stocktype,
								categorydescription,
								stockact,
								adjglact,
								issueglact,
								purchpricevaract,
								materialuseagevarac,
								wipact,
								defaulttaxcatid
							FROM stockcategory
							WHERE categoryid='" . $SelectedCategory . "'";

				$result = DB_query($sql);
				$myrow = DB_fetch_array($result);

				$_POST['CategoryID'] = $myrow['categoryid'];
				$_POST['StockType']  = $myrow['stocktype'];
				$_POST['CategoryDescription']  = $myrow['categorydescription'];
				$_POST['StockAct']  = $myrow['stockact'];
				$_POST['AdjGLAct']  = $myrow['adjglact'];
				$_POST['IssueGLAct']  = $myrow['issueglact'];
				$_POST['PurchPriceVarAct']  = $myrow['purchpricevaract'];
				$_POST['MaterialUseageVarAc']  = $myrow['materialuseagevarac'];
				$_POST['WIPAct']  = $myrow['wipact'];
				$_POST['DefaultTaxCatID']  = $myrow['defaulttaxcatid'];
			}*/
	echo '<input type="hidden" name="SelectedCategory" value="' . $SelectedCategory . '" />';
	echo '<input type="hidden" name="CategoryID" value="' . $_POST['CategoryID'] . '" />';
	echo '<table class="selection">
	        <tr><th colspan="2">物料子类编辑</th></tr>
			<tr>
				<td>' . _('Category Code') . ':</td>
				<td>' .  $SelectedCategory. '</td>';	
				
} else { //end of if $SelectedCategory only do the else when a new record is being entered
	if (!isset($_POST['CategoryID'])) {
		$_POST['CategoryID'] = '';
	}
	echo '<table class="selection">';
	
			
echo '<tr>
		<td>' . _('Category') . ':</td>
		<td><select name="CategoryID" onchange="ReloadForm(ItemForm.UpdateCategories)">';

$sql = "SELECT `categoryid`,
               categorydescription,
			    stocktype
				 FROM stockcategory ";
$ErrMsg = _('The stock categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$result = DB_query($sql,$ErrMsg,$DbgMsg);

while ($myrow=DB_fetch_array($result)){
	if (!isset($_POST['CategoryID']) OR  $myrow['categoryid'].'^'.$myrow['stocktype']==$_POST['CategoryID']){
		echo '<option selected="selected" value="'. $myrow['categoryid'].'^'.$myrow['stocktype'] . '">' . $myrow['categorydescription'] . '</option>';
	} else {
		echo '<option value="'. $myrow['categoryid'] .'^'.$myrow['stocktype'].  '">' . $myrow['categorydescription'] . '</option>';
	}
	$Category=$myrow['categoryid'].'^'.$myrow['stocktype'];
}

if (!isset($_POST['CategoryID'])) {
	$_POST['CategoryID']=$Category;
}

echo '</select>	</tr>';
			
			
}

//SQL to poulate account selection boxes



if (!isset($_POST['SubCategoryDspn'])) {
	$_POST['SubCategoryDspn'] = '';
}
if (!isset($SelectedCategory)) {
echo '<tr>
				<td>' .  _('Item Code'). ':</td>
						
				<td><input type="text" ' . (in_array('SubCategoryID',$Errors) ?  'class="inputerror"' : '' ) .'" data-type="no-illegal-chars" autofocus="autofocus" required="required"  value="'.$StockID.'" name="SubCategoryID" size="20" maxlength="20"  title ="'._('Input the stock code, the following characters are prohibited:') . ' \' &quot; + . &amp; \\ &gt; &lt;" placeholder="'._('alpha-numeric only').'" /></td>
			</tr>';
}
echo '<tr><td><label for="SubCategoryDspn">' . _('Category Description') .
	':</label></td><td><input id="CategoryDescription" maxlength="20" name="SubCategoryDspn" required="required" size="22" title="' .
	_('A description of the inventory category is required') .
	'" type="text" value="' . $_POST['SubCategoryDspn'] .
	'" /></td></tr>';




echo'</table>';


echo '<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
    </div>
	</form>';

include('includes/footer.php');
?>
