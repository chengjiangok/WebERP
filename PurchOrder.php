

	<?php

	/* $Id: PuchInquiry.php  ChengJiang $ */
	/*
 * @Author: ChengJiang 
 * @Date: 2018-10-14 13:37:32 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-14 13:57:38
 */

	include('includes/session.php');

	$Title ='采购计划收货单查询';
	/* webERP manual links before header.php */
	$ViewTopic= 'AccountsPayable';
	$BookMark = 'SupplierInvoice';
	include('includes/header.php');
	include('includes/SQL_CommonFunctions.inc');

	if (isset($_GET['D'])&&isset($_GET['F'])){
		$IssueNO=$_GET['D'];
		$IssueTyp=$_GET['F'];
	}else{
		
		exit;
	}
	if ($IssueTyp=="P"){
		$OrderType=17;
		$PDFFormat=array(0=>'A5_Landscape',1=>'采购计划收货单',2=>'14',3=>'供应商');
	}else{
		$OrderType=25;
	
		 $PDFFormat=array(0=>'A5_Landscape',1=>'简易收货单',2=>'14',3=>'供应商');
	}
	$PaperSize=$PDFFormat[0];//默认页设置  
	//-----------
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . ': ' . $SupplierName . '</p>';
			$sql="SELECT grnno,
					c.suppname,
					a.stkmoveno,
					a.stockid,
					type,
					transno,
					loccode,
					accountdate,
					trandate,
					a.userid,
					debtorno supplierid,
					branchcode,
					price,
					prd,
					reference,
					qty,
					discountpercent,
					standardcost,
					show_on_inv_crds,
					newqoh,
					newamount,
					hidemovt,
					narrative
					
				FROM
					stockmoves a
				LEFT JOIN grns b ON	a.transno = b.grnbatch
				LEFT JOIN suppliers c ON a.debtorno=c.supplierid
				WHERE type='.$OrderType.' AND connectid='".$_GET['D']."'
				ORDER BY transno,c.suppname";
	//	if (isset($_POST['SearchSuppliers'])) {			
			$result=DB_query($sql);			
				echo '<table width="90%" cellpadding="4"  class="selection">
					<tr>
						<th >序号</th>
						<th >收货单号</th>
						<th >供应商编码名称</th>
						<th >日期</th>					
						<th >合同号</br>计划单</th>
						<th >数量</th>
						<th >价格</th>
						<th >金额</th>
						<th >税率</th>
						<th >税额</th>
						<th >合计</th>
						<th >摘要</th>
						<th ></th>
					</tr>';
					$RowIndex=1;
					$k=0;
					$rr=0;
					$rw=1;
					$suppno='';
					$supacc='-1';
					$Total=0;
					$suptyp=2;
					$TaxTotal=0;
					$TotalAll=0;
					$TaxTotalAll=0;
				$TransNO=0;
				$SuppID=0;
			while($row=DB_fetch_array($result)){
				
				
					if ($k==1){
						echo '<tr class="EvenTableRows">';
																
						$k=0;
					}else {
						echo '<tr class="OddTableRows">';
						$k=1;
					}
					echo '	<td>'.$RowIndex.'</td>';
				if ($TransNO!=$row['transno']){
					    $URL_Edit= $RootPath .'/PDFPurchPlanOrder.php?F='.$AuthorPrice.'&D=' . $myrow['dispatchid'] ;
				
						$TransNO=$row['transno'];			
				
				
					echo'<td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$row['transno'].'</a></td>';

					if ($SuppID!=$row['supplierid']){
						$SuppID=$row['supplierid'];
						echo'<td>'.$row['supplierid'].$row['suppname'].'</td>';
					}else{
						echo'<td></td>';				}
						
						echo'<td >'.$row['trandate'].'</td>
							<td ></td>';
				}else{
					
				echo '  <td></td>
						<td></td>
						<td ></td>
						<td ></td>';					
				}			
				echo'<td class="number">'.locale_number_format(round($row['qty'],2),2).'</td>
					<td class="number">'.locale_number_format(round($row['price'],2),2).'</td>
					<td ></td>
					<td class="number">'.locale_number_format(round($taxtotal,2),2).'</td>
					<td ></td>
					<td ></td>
					<td ></td>
					<td><input type="checkbox" name="chkbx[]" value="'.$RowIndex.'"   ></td>											
					</tr>';
					
					$RowIndex++;
					
			}//end while
				
			
				echo '<tr>
						<td></td>
						<td colspan="3">总计</td>				
						<td class="number">'.locale_number_format($TotalAll,2).'</td>
						<td class="number">'.locale_number_format($TaxTotalAll,2).'</td>
						<td class="number">'.locale_number_format(($TotalAll+$TaxTotalAll),2).'</td>
						<td ></td>
						<td ></td>
					</tr>';
				echo'</table>';			
		echo '<div class="centre">';
			
		echo'<input type="submit" name="ExportCSV" value="导出CSV" />
		     <input type="submit" name="Reset" value="导出PDF" /><br>';
			


	

	include('includes/footer.php');
	?>
