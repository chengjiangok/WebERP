
<?php
/* $Id: Z_CheckGLTransBalance.php 7320 2015-06-13 03:43:34Z tehonu $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-10-10 06:52:03 
 * @Last Modified by:   ChengJiang 
 * @Last Modified time: 2017-10-10 06:52:03 
 */
include('includes/session.php');
$Title='检查借贷不平凭证';//_('Check Period Sales Ledger Control Account');
include('includes/header.php');
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Dispatch') .
'" alt="" />' . ' ' . $Title . '</p>';
$sql = "SELECT gltrans.type,
systypes.typename,
gltrans.typeno,
periodno,
SUM(amount) AS nettot
FROM gltrans,
systypes
WHERE gltrans.type = systypes.typeid
GROUP BY gltrans.type,
systypes.typename,
typeno,
periodno
HAVING ABS(SUM(amount))>= " . 1/pow(10,$_SESSION['CompanyRecord']['decimalplaces']) . "
ORDER BY gltrans.counterindex";

$OutOfWackResult = DB_query($sql);


$RowCounter =DB_num_rows($OutOfWackResult);
if ($RowCounter>0){
echo '<table>';

$Header = '<tr>
			<th>' . _('Type') . '</th>
			<th>' . _('Number') . '</th>
			<th>' . _('Period') . '</th>
			<th>' . _('Difference') . '</th>
		</tr>';

echo $Header;


while ($OutOfWackRow = DB_fetch_array($OutOfWackResult)){

	if ($RowCounter==18){
		$RowCounter=0;
		echo $Header;
	} else {
		$RowCounter++;
	}
	echo '<tr>
	<td><a href="' . $RootPath . '/GLTransInquiry.php?TypeID=' . $OutOfWackRow['type'] . '&TransNo=' . $OutOfWackRow['typeno'] . '">' . $OutOfWackRow['typename'] . '</a></td>
	<td class="number">' . $OutOfWackRow['typeno'] . '</td>
	<td class="number">' . $OutOfWackRow['periodno'] . '</td>
	<td class="number">' . locale_number_format($OutOfWackRow['nettot'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
	</tr>';

}
echo '</table>';
}else{
	prnMsg('没有借贷不平衡的会计凭证存在！','info');
}
include('includes/footer.php');
?>