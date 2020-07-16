

<?php

/* $Id: FixedAssetSimple.php 7494 2017-04-14 09:53:53Z chengjiang $ */

/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:57
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-04-30 16:53:11
 */
include('includes/session.php');
$Title = _('Fixed Assets');

$ViewTopic = 'FixedAssets';
$BookMark = 'AssetItems';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

echo '<a href="' . $RootPath . '/SelectFixedAsset.php">' . _('Back to Select') . '</a><br />' . "\n";

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .
		_('Fixed Asset Items') . '" alt="" />' . ' ' . $Title . '</p>';

/* If this form is called with the AssetID then it is assumed that the asset is to be modified  */

if (isset($_GET['Select'])){
	$ROW =json_decode(str_replace('\"','"',urldecode($_GET['Select'])),JSON_UNESCAPED_UNICODE);
	$_SESSION['UrlFix']='?Select='.urlencode(json_encode($ROW,JSON_UNESCAPED_UNICODE)); 
	
} else{
	unset($ROW);
	prnMsg('页面引导错误！','info');
	echo "<script>window.close();</script>";

	exit;
}

$New=0;
$SupportedImgExt = array('png','jpg','jpeg');

if (isset($_FILES['ItemPicture']) AND $_FILES['ItemPicture']['name'] !='') {
	$ImgExt = pathinfo($_FILES['ItemPicture']['name'], PATHINFO_EXTENSION);

	$result    = $_FILES['ItemPicture']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $ImgExt;
	//But check for the worst
	if (!in_array ($ImgExt, $SupportedImgExt)) {
		prnMsg(_('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['ItemPicture']['type'] == 'text/plain' ) {  //File Type Check
		prnMsg( _('Only graphics files can be uploaded'),'warn');
         	$UploadTheFile ='No';
	}
	foreach ($SupportedImgExt as $ext) {
		$file = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $ext;
		if (file_exists ($file) ) {
			$result = unlink($file);
			if (!$result){
				prnMsg(_('The existing image could not be removed'),'error');
				$UploadTheFile ='No';
			}
		}
	}

	if ($UploadTheFile=='Yes'){
		$result  =  move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $filename);
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
	}
 /* EOR Add Image upload for New Item  - by Ori */
}

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();
$InputError = 0;

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	//first off validate inputs sensible
	$i=1;


	if (!isset($_POST['Description']) or mb_strlen($_POST['Description']) > 50 OR mb_strlen($_POST['Description'])==0) {
		$InputError = 1;
		prnMsg (_('The asset description must be entered and be fifty characters or less long. It cannot be a zero length string either, a description is required'),'error');
		$Errors[$i] = 'Description';
		$i++;
	}
	if (mb_strlen($_POST['LongDescription'])==0) {
		$InputError = 1;
		prnMsg (_('The asset long description cannot be a zero length string, a long description is required'),'error');
		$Errors[$i] = 'LongDescription';
		$i++;
	}

	if (mb_strlen($_POST['BarCode']) >20) {
		$InputError = 1;
		prnMsg(_('The barcode must be 20 characters or less long'),'error');
		$Errors[$i] = 'BarCode';
		$i++;
	}

	if (trim($_POST['AssetCategoryID'])==''){
		$InputError = 1;
		prnMsg(_('There are no asset categories defined. All assets must belong to a valid category,'),'error');
		$Errors[$i] = 'AssetCategoryID';
		$i++;
	}
	if (trim($_POST['AssetLocation'])==''){
		$InputError = 1;
		prnMsg(_('There are no asset locations defined. All assets must belong to a valid location,'),'error');
		$Errors[$i] = 'AssetLocation';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['DepnRate']))
		OR filter_number_format($_POST['DepnRate'])>100
		OR filter_number_format($_POST['DepnRate'])<0){

		$InputError = 1;
		prnMsg(_('The depreciation rate is expected to be a number between 0 and 100'),'error');
		$Errors[$i] = 'DepnRate';
		$i++;
	}
	if (filter_number_format($_POST['DepnRate'])>0 AND filter_number_format($_POST['DepnRate'])<1){
		prnMsg(_('Numbers less than 1 are interpreted as less than 1%. The depreciation rate should be entered as a number between 0 and 100'),'warn');
	}


	if ($InputError !=1){
	
		if ($_POST['submit']==_('Insert New Fixed Asset') ) { /*so its an existing one */
       
			$sql = "INSERT INTO fixedassets (description,
											longdescription,
											assetcategoryid,
											assetlocation,
											depntype,
											depnrate,
											barcode,
											qty,
											units,
											cost,
											serialno,
											disposaldate,
											datepurchased)
								VALUES (
									'" . $_POST['Description'] . "',
									'" . $_POST['LongDescription'] . "',
									'" . $_POST['AssetCategoryID'] . "',
									'" . $_POST['AssetLocation'] . "',
									'" . $_POST['DepnType'] . "',
									'" . filter_number_format($_POST['DepnRate']). "',
									'" . $_POST['BarCode'] . "',
									'" . $_POST['Qty'] . "',
									'" . $_POST['Units'] . "',
									'".$ROW[3]."',
									'" . $_POST['SerialNo'] . "' ,
									'0000-00-00',
									'".$ROW[4]."')";
			$ErrMsg =  _('The asset could not be added because');
			$DbgMsg = _('The SQL that was used to add the asset failed was');
			$result = DB_query($sql, $ErrMsg, $DbgMsg);
			$NewAssetID = DB_Last_Insert_ID($db,'fixedassets', 'assetid');
		   // foreach($_POST['chkbx'] as $value){
			
		     $sql="INSERT INTO fixedassettrans( assetid,
			                                  transtype,
											  transdate,
											  transno,
											  periodno,
											  inputdate,
											  fixedassettranstype,
											  amount) 
				    VALUES (
						'".$NewAssetID ."',
						'".$ROW[5]."',
							'".$ROW[4]."',
								'".$ROW[1]."',
								'".$ROW[2]."',
										'".$ROW[4]."',
											'cost',
												'".$ROW[3]."')";
	     	 
				$result = DB_query($sql, $ErrMsg, $DbgMsg);
                $sql="UPDATE gltrans 
			          	SET posted=1 
					  WHERE transno='".$ROW[1]."'  AND periodno='".$ROW[2]."'  AND  account LIKE '1601%'";
					 
				$result = DB_query($sql, $ErrMsg, $DbgMsg);
			}
			if (DB_error_no() ==0) {
				$result = DB_Txn_Commit();
				prnMsg( _('The new asset has been added to the database with an asset code of:') . ' ' . $NewAssetID,'success');
				unset($_POST['LongDescription']);
				unset($_POST['Description']);
				unset($_POST['BarCode']);
				unset($_POST['SerialNo']);
				unset($_SESSION['UrlFix']);
				echo '<meta http-equiv="refresh" content="9.1;  url='. $RootPath . '/SelectFixedAsset.php" />';
				
			}//ALL WORKED SO RESET THE FORM VARIABLE			
			
			
         

		
	} else {
		echo '<br />' .  "\n";
		prnMsg( _('Validation failed, no updates or deletes took place'), 'error');
	}

} elseif (isset($_POST['delete']) AND mb_strlen($_POST['delete']) >1 ) {
	//the button to delete a selected record was clicked instead of the submit button
     $rdoarr=explode('^',$_POST['rdodel']);
	 $CancelDelete=0;
	//what validation is required before allowing deletion of assets ....  maybe there should be no deletion option?
	/*$result = DB_query("SELECT cost,
								accumdepn,
								accumdepnact,
								costact
						FROM fixedassets INNER JOIN fixedassetcategories
						ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
						WHERE assetid='" . $AssetID . "'");
	*/
	$sql="SELECT  `account`, `amount` FROM `gltrans` WHERE periodno=".$ROW[2]." AND transno='".$ROW[1]."'  AND( LEFT(account,4)='1601' OR  LEFT(account,4)='1602')";
	$result=DB_query($sql);
	while($row = DB_fetch_array($result)){
		if (substr($row['account'],0,4)=='1601'){
			
			if($rdoarr[1]!=abs($row['amount'])){
				$CancelDelete=1;
			}
		}elseif (substr($row['account'],0,4)=='1602'){
			if($rdoarr[2]!=$row['amount']){
				$CancelDelete+=2;
			}
		}
	}
	if ($CancelDelete!=0) {
		prnMsg($rdoarr[2].	$CancelDelete.'你选择的固定资产原值和累计折旧不等于会计凭证的金额！'.$rdoarr[1],'info');
		include('includes/footer.php');
		exit;
	}
	/*
	$result = DB_query("SELECT * FROM purchorderdetails WHERE assetid='" . $AssetID . "'");
	if (DB_num_rows($result) > 0){
		$CancelDelete =1;
		prnMsg(_('There is a purchase order set up for this asset. The purchase order line must be deleted first'),'error');
	}*/
	if ($CancelDelete==0) {
		$result = DB_Txn_Begin();		
		//$sql="DELETE FROM fixedassets WHERE assetid='" . $AssetID . "'";
		//$result=DB_query($sql, _('Could not delete the asset record'),'',true);		
		// Delete the AssetImage
		foreach ($SupportedImgExt as $ext) {
			$file = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $ext;
			if (file_exists ($file) ) {
				unlink($file);
			}
		}
        if (isset($_POST['rdodel'])){
		
			$sql="UPDATE `fixedassets` SET `disposaldate`='".$ROW[4]."', cost=0  , accumdepn =0 WHERE assetid='".$rdoarr[0]  ."'";
			$result = DB_query($sql);
			$sql="INSERT INTO fixedassettrans( assetid,
												transtype,
												transdate,
												transno,
												periodno,
												inputdate,
												fixedassettranstype,
												amount) 
										VALUES (
												'".$rdoarr[0]."',
												'".$ROW[5]."',
												'".$ROW[4]."',
												'".$ROW[1]."',
												'".$ROW[2]."',
												'".$ROW[4]."',
												'cost',
													'".$ROW[3]."')";
							

			$result = DB_query($sql);
			$sql="INSERT INTO fixedassettrans( assetid,
												transtype,
												transdate,
												transno,
												periodno,
												inputdate,
												fixedassettranstype,
												amount) 
										VALUES (
												'".$rdoarr[0]."',
												'".$ROW[5]."',
												'".$ROW[4]."',
												'".$ROW[1]."',
												'".$ROW[2]."',
												'".$ROW[4]."',
												'depn',
												'".(-$rdoarr[2])."')";
		$result = DB_query($sql);
		$sql="UPDATE gltrans 
				SET posted=1
				WHERE transno='".$ROW[1]."'  
				 AND periodno='".$ROW[2]."'  
				 AND ( LEFT(account,4)='1601' OR  LEFT(account,4)='1602')";
		$result = DB_query($sql);
		if($result){
			$result = DB_Txn_Commit();
			prnMsg('固定资产更新成功！','info' );
		}

		}else{
			prnMsg('你没有选择固定资产！'  );
		}

	} //end if OK Delete Asset
} /* end if delete asset */


if (isset($_SESSION['UrlFix']) AND strlen($_SESSION['UrlFix'])>3){
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .$_SESSION['UrlFix']. '"  method="post" name="form">';
}else{
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" name="form">';
}
echo'<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="Select" value="' . $ROW . '"/>';


if (is_array($ROW)){//OR $ROW=='') {

/*If the page was called without $AssetID passed to page then assume a new asset is to be entered other wise the form showing the fields with the existing entries against the asset will show for editing with a hidden AssetID field. New is set to flag that the page may have called itself and still be entering a new asset, in which case the page needs to know not to go looking up details for an existing asset*/

	$New = 1;
	echo '<tr><td><input type="hidden" name="New" value="" /></td></tr>';

	$_POST['LongDescription'] = '';
	$_POST['Description'] = '';
	$_POST['AssetCategoryID']  = '';
	$_POST['SerialNo']  = '';
	$_POST['AssetLocation']  = '';
	$_POST['DepnType']  = '';
	$_POST['BarCode']  = '';
	$_POST['DepnRate']  = 9.5;

} elseif ($InputError!=1) { // Must be modifying an existing item and no changes made yet - need to lookup the details

	$sql = "SELECT assetid,
				description,
				longdescription,
				assetcategoryid,
				serialno,
				assetlocation,
				datepurchased,
				depntype,
				depnrate,
				cost,
				accumdepn,
				barcode,
				disposalproceeds,
				disposaldate
			FROM fixedassets
			WHERE assetid ='" . $AssetID . "'";

	$result = DB_query($sql);
	$AssetRow = DB_fetch_array($result);

	$_POST['LongDescription'] = $AssetRow['longdescription'];
	$_POST['Description'] = $AssetRow['description'];
	$_POST['AssetCategoryID']  = $AssetRow['assetcategoryid'];
	$_POST['SerialNo']  = $AssetRow['serialno'];
	$_POST['AssetLocation']  = $AssetRow['assetlocation'];
	$_POST['DepnType']  = $AssetRow['depntype'];
	$_POST['BarCode']  = $AssetRow['barcode'];
	$_POST['DepnRate']  = locale_number_format($AssetRow['depnrate'],2);

	echo '<tr>
			<td>' . _('Asset Code') . ':</td>
			<td>' . $AssetID . '</td>
		</tr>';
	echo '<tr><td><input type="hidden" name="AssetID" value="'.$AssetID.'"/></td></tr>';

} else { // some changes were made to the data so don't re-set form variables to DB ie the code above
	echo '<tr>
			<td>' . _('Asset Code') . ':</td>
			<td>' . $AssetID . '</td>
		</tr>';
	echo '<tr><td><input type="hidden" name="AssetID" value="' . $AssetID . '"/></td></tr>';
}
if ( $ROW[3]>0){
	echo '<table class="selection">';

echo '<tr>
<th colspan="2">'. $ROW[4].' 记字' . $ROW[1].' 科目码'. $ROW[0].' 金额:'. $ROW[3].'</th>

</tr>';

	if (isset($AssetRow['disposaldate']) AND $AssetRow['disposaldate'] !='0000-00-00'){
		echo '<tr>
				<td>' . _('Asset Already disposed on') . ':</td>
				<td>' . ConvertSQLDate($AssetRow['disposaldate']) . '</td>
			</tr>';
	}

	if (isset($_POST['Description'])) {
		$Description = $_POST['Description'];
	} else {
		$Description ='';
	}

	

	echo '<tr>
			<td>' . _('Asset Description') . ' (' . _('short') . '):</td>
			<td><input ' . (in_array('Description',$Errors) ?  'class="inputerror"' : '' ) .' type="text" required="required" title="' . _('Enter the description of the item. Up to 50 characters can be used.') . '" name="Description" size="52" maxlength="50" value="' . $Description . '" /></td>
		</tr>';

	if (isset($_POST['LongDescription'])) {
		$LongDescription = AddCarriageReturns($_POST['LongDescription']);
	} else {
		$LongDescription ='';
	}
	echo '<tr>
			<td>' . _('Asset Description') . ' (' . _('long') . '):</td>
			<td><textarea ' . (in_array('LongDescription',$Errors) ?  'class="texterror"' : '' ) .'  name="LongDescription" required="required" title="' . _('Enter the lond description of the asset including specs etc. Up to 255 characters are allowed.') . '" cols="40" rows="4">' . stripslashes($LongDescription) . '</textarea></td>
		</tr>';

	if ($New!=1) { //ie not new at all!

		echo '<tr>
				<td>' .  _('Image File (' . implode(", ", $SupportedImgExt) . ')') . ':</td>
				<td><input type="file" id="ItemPicture" name="ItemPicture" />
				<br /><input type="checkbox" name="ClearImage" id="ClearImage" value="1" > '._('Clear Image').'
				</td>';

		$imagefile = reset((glob($_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE)));
		if (extension_loaded ('gd') && function_exists ('gd_info') && file_exists ($imagefile) ) {
			$AssetImgLink = '<img src="GetStockImage.php?automake=1&textcolor=FFFFFF&bgcolor=CCCCCC'.
				'&StockID='.urlencode('ASSET_' . $AssetID).
				'&text='.
				'&width=64'.
				'&height=64'.
				'" />';
		} else if (file_exists ($imagefile)) {
			$AssetImgLink = '<img src="' . $imagefile . '" height="64" width="64" />';
		} else {
			$AssetImgLink = _('No Image');
		}

		if ($AssetImgLink!=_('No Image')) {
			echo '<td>' . _('Image') . '<br />' . $AssetImgLink . '</td></tr>';
		} else {
			echo '</td></tr>';
		}

		// EOR Add Image upload for New Item  - by Ori
	} //only show the add image if the asset already exists - otherwise AssetID will not be set - and the image needs the AssetID to save

	if (isset($_POST['ClearImage']) ) {
		foreach ($SupportedImgExt as $ext) {
			$file = $_SESSION['part_pics_dir'] . '/ASSET_' . $AssetID . '.' . $ext;
			if (file_exists ($file) ) {
				//workaround for many variations of permission issues that could cause unlink fail
				@unlink($file);
				if(is_file($imagefile)) {
				prnMsg(_('You do not have access to delete this item image file.'),'error');
				} else {
					$AssetImgLink = _('No Image');
				}
			}
		}
	}


	echo '<tr>
			<td>' . _('Asset Category') . ':</td>
			<td><select name="AssetCategoryID">';

	$sql = "SELECT `categoryid`, `categorydescription`, `costact`, `depnact`, `disposalact`, `accumdepnact`, `defaultdepnrate`, `defaultdepntype` FROM `fixedassetcategories`  ";
	$ErrMsg = _('The asset categories could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
	$result = DB_query($sql,$ErrMsg,$DbgMsg);

	while ($myrow=DB_fetch_array($result)){
		if (!isset($_POST['AssetCategoryID']) or $myrow['categoryid']==$_POST['AssetCategoryID']){
			echo '<option selected="selected" value="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		} else {
			echo '<option value="'. $myrow['categoryid'] . '">' . $myrow['categorydescription']. '</option>';
		}
		$category=$myrow['categoryid'];
	}
	echo '</select><a target="_blank" href="'. $RootPath . '/FixedAssetCategories.php">' . ' ' . _('Add or Modify Asset Categories') . '</a></td></tr>';
	if (!isset($_POST['AssetCategoryID'])) {
		$_POST['AssetCategoryID']=$category;
	}

	if (isset($AssetRow) AND ($AssetRow['datepurchased']!='0000-00-00' AND $AssetRow['datepurchased']!='')){
		echo '<tr>
				<td>' . _('Date Purchased') . ':</td>
				<td>' . ConvertSQLDate($AssetRow['datepurchased']) . '</td>
			</tr>';
	}

	$sql = "SELECT locationid, locationdescription FROM fixedassetlocations ";
	$ErrMsg = _('The asset locations could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve asset locations and failed was');
	$result = DB_query($sql,$ErrMsg,$DbgMsg);

	echo '<tr>
			<td>' . _('Asset Location') . ':</td>
			<td><select name="AssetLocation">';

	while ($myrow=DB_fetch_array($result)){
		if ($_POST['AssetLocation']==$myrow['locationid']){
			echo '<option selected="selected" value="' . $myrow['locationid'] .'">' . $myrow['locationdescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['locationid'] .'">' . $myrow['locationdescription'] . '</option>';
		}
	}
	echo '</select>
		<a target="_blank" href="'. $RootPath . '/FixedAssetLocations.php">' . ' ' . _('Add Asset Location') . '</a></td>
		</tr>';
		/*
	echo'<tr>
			<td>' . _('Bar Code') . ':</td>
			<td><input ' . (in_array('BarCode',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" name="BarCode" size="22" maxlength="20" value="' . $_POST['BarCode'] . '" /></td>
		</tr>';*/
	echo'<tr>
			<td>' . _('Serial Number') . ':</td>
			<td><input ' . (in_array('SerialNo',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" name="SerialNo" size="32" maxlength="30" value="' . $_POST['SerialNo'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Depreciation Type') . ':</td>
			<td><select name="DepnType">';

	if (!isset($_POST['DepnType'])){
		$_POST['DepnType'] = 0; //0 = Straight line - 1 = Diminishing Value
	}
	if ($_POST['DepnType']==0){ //straight line
		echo '<option selected="selected" value="0">' . _('Straight Line') . '</option>';
		echo '<option value="1">' . _('Diminishing Value') . '</option>';
	} else {
		echo '<option value="0">' . _('Straight Line') . '</option>';
		echo '<option selected="selected" value="1">' . _('Diminishing Value') . '</option>';
	}

	echo '</select></td>
		</tr>
		<tr>
			<td>年折旧率:</td>
			<td><input ' . (in_array('DepnRate',$Errors) ?  'class="inputerror number"' : 'class="number"' ) .'  type="text" name="DepnRate" size="4" maxlength="4" value="' . $_POST['DepnRate'] . '" />%</td>
		</tr>';
		
		echo'<tr>
				<td>单位:</td>
				<td><input   type="text" name="Units" size="7" maxlength="7" value="' . $_POST['Units'] . '" /></td>
			</tr>
			<tr>
				<td>数量:</td>
				<td><input   type="text" name="Qty" size="7" maxlength="7" value="' . $_POST['Qty'] . '" /></td>
			</tr>
		</table>';

		$transno=0;
		$str=explode('-',$_POST['unittag']);	   

	if (isset($AssetRow)){
		echo '<table>
			<tr>
				<th colspan="2">' . _('Asset Financial Summary') . '</th>
			</tr>
			<tr>
				<td>' . _('Accumulated Costs') . ':</td>
				<td class="number">' . locale_number_format($AssetRow['cost'],POI) . '</td>
			</tr>
			<tr>
				<td>' . _('Accumulated Depreciation') . ':</td>
				<td class="number">' . locale_number_format($AssetRow['accumdepn'],POI) . '</td>
			</tr>';
		if ($AssetRow['disposaldate'] != '0000-00-00'){
			echo'<tr>
				<td>' . _('Net Book Value at disposal date') . ':</td>
				<td class="number">' . locale_number_format($AssetRow['cost']-$AssetRow['accumdepn'],POI) . '</td>
			</tr>';
			echo'<tr>
				<td>' . _('Disposal Proceeds') . ':</td>
				<td class="number">' . locale_number_format($AssetRow['disposalproceeds'],POI) . '</td>
			</tr>';
			echo'<tr>
				<td>' . _('P/L after disposal') . ':</td>
				<td class="number">' . locale_number_format(-$AssetRow['cost']+$AssetRow['accumdepn']+$AssetRow['disposalproceeds'],POI) . '</td>
			</tr>';

		}else{
			echo'<tr>
				<td>' . _('Net Book Value') . ':</td>
				<td class="number">' . locale_number_format($AssetRow['cost']-$AssetRow['accumdepn'],POI) . '</td>
			</tr>';
		}
		/*Get the last period depreciation (depn is transtype =44) was posted for */
		$result = DB_query("SELECT periods.lastdate_in_period,
									max(fixedassettrans.periodno)
						FROM fixedassettrans INNER JOIN periods
						ON fixedassettrans.periodno=periods.periodno
						WHERE transtype=44
						GROUP BY periods.lastdate_in_period
						ORDER BY periods.lastdate_in_period DESC");

		$LastDepnRun = DB_fetch_row($result);
		if(DB_num_rows($result)==0){
			$LastRunDate = _('Not Yet Run');
		} else {
			$LastRunDate = ConvertSQLDate($LastDepnRun[0]);
		}
		echo '<tr>
				<td>' . _('Depreciation last run') . ':</td>
				<td>' . $LastRunDate . '</td>
			</tr>
			</table>';
	}
}else{
	//固定资产退出
     
	$New=0;
	 $sql = "SELECT
					v_fixedassets.assetid,
					v_fixedassets.assetcategoryid,
					v_fixedassets.description,
					costbfwd,
					depnbfwd,
					fixedassets.serialno,
					fixedassets.datepurchased,
					fixedassets.disposaldate,
					fixedassetlocations.parentlocationid,
					fixedassetlocations.locationdescription,
					fixedassetcategories.categorydescription
				FROM
					v_fixedassets
				LEFT JOIN fixedassets ON v_fixedassets.assetid = fixedassets.assetid
				INNER JOIN fixedassetcategories ON v_fixedassets.assetcategoryid = fixedassetcategories.categoryid
				INNER JOIN fixedassetlocations ON v_fixedassets.assetcategoryid = fixedassetlocations.locationid
						WHERE  fixedassets.disposaldate='0000-00-00'
						AND costbfwd=ABS( ".$ROW[3]." )
						ORDER BY fixedassets.assetid";

		$result = DB_query($sql);
		if (DB_num_rows($result)>0){
			$New=-1;
		echo '<br />
		<table width="80%" cellspacing="1" class="selection">';
		$Heading='<tr>
				<th width="15" >序号</th>
				<th width="15" >' . _('Asset ID') . '</th>
				<th>' . _('Description') . '</th>
				<th>部门</th>
				<th>设备类别</th>
				<th>' . _('Date Acquired') . '</th>
				<th>年初原值</th>
				<th>累计折旧</th>				
				<th>' . _('NBV').'</th>
				<th>处置日期 </th>
				<th>选择</th>

			</tr>';
		echo $Heading;
		$r=0;
		while ($myrow = DB_fetch_array($result)) {				
				
					if ($k==1){
						echo '<tr class="EvenTableRows">';
						$k=0;
					} else {
						echo '<tr class="OddTableRows">';
						$k++;
					}	
					echo '  <td style="vertical-align:top ">' . $r . '</td>
							<td style="vertical-align:top ">' . $myrow['assetid'] . '</td>
							<td style="vertical-align:top">' . $myrow['description'] . '</td>
							<td>' . $myrow['locationdescription'] . '<br /></td>
						<td>' . $myrow['categorydescription'] . '</td>
						<td style="vertical-align:top">' . ConvertSQLDate($myrow['datepurchased']) . '</td>
						<td style="vertical-align:top" class="number">' . locale_number_format($myrow['costbfwd'], POI) . '</td>
						<td style="vertical-align:top" class="number">' . locale_number_format($myrow['depnbfwd'], POI) . '</td>
						<td style="vertical-align:top" class="number">' . locale_number_format($myrow['costbfwd'] - $myrow['depnbfwd'], POI) . '</td>
						<td style="vertical-align:top" class="number">' . ConvertSQLDate($myrow['disposaldate'] ). '</td>
						<td><input  type="radio" name="rdodel"  value="' . $myrow['assetid'] .'^'.$myrow['costbfwd'].'^'.$myrow['depnbfwd']. '" '.($r==0?'checked':'').' /></td>
					</tr>';
				
			
				
			$r++;
		}
		echo '</table>';
	}else{
		prnMsg('没有原值等于选择的固定资产！','info');
	}

}//退出固定资产
if ($New==1) {
	echo '<div class="centre">
			<br />
			<input type="submit" name="submit" value="' . _('Insert New Fixed Asset') . '" />';
} elseif($New==-1){
		echo '<br />
		<div class="centre">
			<input type="submit" name="delete" value="' . _('Delete This Asset') . '" onclick="return confirm(\'确认删除该固定资产？\');" />';
}

echo '</div>
      </div>
	</form>';
include('includes/footer.php');
?>
