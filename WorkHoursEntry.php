

<?php
/* $Id: `WorkHoursEntry.php`  $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-06-19 14:29:57 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-01-27 06:33:20
 */
include('includes/session.php');
$Title = '工时统计';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

echo'<script type="text/javascript">
		function sltproduct(obj){
		
  			window.location.href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?myslt="+obj.value ;
 		}
		 function computetime1(myslt,typ){
			var jsn=document.getElementById("techquota").value;
	        var sltval =myslt.value;					
            var techcode=(document.getElementById("technics").value).split("^")[0];
			
            var list=(sltval.split("^")[0]).split("*"); 
		    var maxspec=Math.max.apply(this,  list);// 数组中的最大值
			var obj = eval(jsn);		//var obj= JSON.parse(jsn);		
			var qut="";
			var temp = []; 	
			
			for(var i=0; i<obj.length; i++)  
			{ 
				temp[i]= (function(n){				  
					if (Number(obj[n].techid)==techcode){		
						if ((typ=Number(obj[n].techtype))&&(maxspec>=Number( obj[n].specmin)) && (maxspec<=Number(obj[n].specmax)))
						{	   
							qut= obj[n].quota;   
						}   
					}
				})(i);  
			}  			
			document.getElementById("extraquant").value=qut;
			//---------------
			 var selectobj=document.getElementById("equipment"); 
			 var jsnobj=document.getElementById("devicejsn").value;  
			var devobj=eval(jsnobj);				
			selectobj.options.length=0; 
				
			for(var i=0; i<devobj.length; i++)  {
					temp[i]= (function(n){				  
					if (Number(devobj[n].techid)==techcode){		
						selectobj.options.add(new Option(devobj[n].description,devobj[n].assetid));
					}
       			 })(i);    
			}
			if (Number(selectobj.options.length)<0){
					selectobj.options.add(new Option("添加设备","0"));
			}		
		}
		
		
		function computetime(myslt,typ){           
        	 var jsn=document.getElementById("techquota").value;
			 var sltval = document.getElementById("components").value;
        
		 	var techcode=Number((myslt.value).split("^")[0]);
			 // console.log(techcode); 
            var list=(sltval.split("^")[0]).split("*"); 	
			var maxspec=Number(Math.max.apply(this,  list));
			var obj= JSON.parse(jsn);				
			var temp = []; 	
			var qut="";			
				for(var i=0; i<obj.length; i++)  
  				{ 
   					 temp[i]= (function(n){				  
					if (Number(obj[n].techid)==techcode){		
						if ((typ=Number(obj[n].techtype))&&(maxspec>=Number( obj[n].specmin)) && (maxspec<=Number(obj[n].specmax)))
						{	   
							qut= obj[n].quota;   
						}   
					}
       				 })(i);  
  				}  
			document.getElementById("extraquant").value=qut;
						  	
			    var selectobj=document.getElementById("equipment");  
         		var jsnobj=document.getElementById("devicejsn").value;  
				var devobj=eval(jsnobj);
				
			selectobj.options.length=0; 
				
			for(var i=0; i<devobj.length; i++)  {
					temp[i]= (function(n){				  
					if (Number(devobj[n].techid)==techcode){		
						selectobj.options.add(new Option(devobj[n].description,devobj[n].assetid));
					}
       			 })(i);    
			}
			
            if (Number(selectobj.options.length)<1){
				selectobj.options.add(new Option("添加设备","0"));
			}	
		}
	function fun() {  
         location.reload();
    }  
</script>';
echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
	</p>';
//	$wkharr=array();
if (isset($_GET['wkhdate'])){
	$wkhdate = $_GET[''];
} else {
	$wkhdate=Date('Y-m-d');
}
		$result=DB_query("SELECT techid,
								techtype,
								specmin,
								specmax,								
								quota								
							FROM technicsquota");
			 $techextarr=array();
			$i=0;
		while ($row=DB_fetch_array($result)){

		  $techextarr[$i]=array('techid'=>$row['techid'],'techtype'=>$row['techtype'],'specmax'=>$row['specmax'],'specmin'=>$row['specmin'],'quota'=>$row['quota']);
		   $i++;
		}
		$rowjsn=json_encode( $techextarr);
		$result=DB_query(" SELECT device.assetid,
								description,
								device.techid
							FROM device
							LEFT JOIN  fixedassets AS f ON  f.assetid = device.assetID WHERE device.assetID!=''");
	 	$techdevicearr=array();
		$i=0;
		while ($row=DB_fetch_array($result)){

		  $techdevicearr[$i]=array('techid'=>$row['techid'],'assetid'=>$row['assetid'],'description'=>$row['description']);
		   $i++;
		}
		$devicejsn=json_encode( $techdevicearr);
		//var_dump($devicejsn);
   $wkhflg=0;	  
if (isset($_SESSION['workhours'])){
	$wkhflg=1;
}


$urlstr='';
if (isset($_GET['myslt'])){
	$_POST['product']=$_GET['myslt'];
	$urlstr="?myslt=".$_GET['myslt'];
}
if(isset($_POST['clearcache'])){
	//include('GLAccounts.php');
	//	header('Location:GLAccounts.php?GLreturn=GL');
	$urlstr='';
	unset($_SESSION['wkhary']);
	unset($_SESSION['workhours']);
	//include('Location:WorkHoursEntry.php');
		$url='WorkHoursEntry.php';
	echo '<script type="text/javascript">  
            window.location.href="'.$url.'" 
          </script>';  
	//prnMsg(count($_SESSION['wkhary']).'-'. var_dump($_POST['extraquantar']),'info');
}

echo '<form method="post" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .$urlstr.  '" name="form1">';	

echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	   <input type="hidden" id="devicejsn" name="devicejsn" value=' . $devicejsn . ' />
	  <input type="hidden" id="techquota" name="techquota" value=' . $rowjsn . ' />';	       
  if (isset($_SESSION['workhours'])){
				$sltstk=$_SESSION['workhours'][0]['product'];
			$_POST['product']=$_SESSION['workhours'][0]['product'];
			}elseif(isset($_GET['myslt'])){
				$sltstk=$_GET['myslt'];
				$_POST['product']=$_GET['myslt'];
			}elseif (isset($_POST['product'])) {
				$sltstk=$_POST['product'];

			}
if (isset($_POST['submit'])) { 
	
		if (!isset($_SESSION['workhours'])){
			$_SESSION['workhours']=array();
		
			$_SESSION['workhours'][0]=array('id'=>'0','dt'=>$_POST['wkhdate'],'emplayee'=>$_POST['emplayee'],'product'=>$_POST['product'],'flg'=>1);
		}
		if (isset($_SESSION['wkhary'])){
			$r=count($_SESSION['workhours']);
			$i=0;
		
			foreach($_SESSION['wkhary'] as $val){
				if ((is_numeric($_POST['quantar'][$i]) && $_POST['quantar'][$i]!=0)|| (is_numeric($_POST['extraquantar'][$i])&& $_POST['extraquantar'][$i]!=0)){	
					$_SESSION['workhours'][$i+$r]=array('id'=>$r+$i,'comp'=>$val['comp'],'spec'=>$val['spec'],'program'=>$val['program'],'techid'=>$val['techid'],'technics'=>$val['technics'],'device'=>$_POST['device'][$i],'starttime'=>$_POST['starttime'][$i],'endtime'=>$_POST['endtime'][$i],'planqut'=>$val['planqut'],'quant'=>$_POST['quantar'][$i],'remark'=>$_POST['remarkar'][$i],'extraquant'=>$_POST['extraquantar'][$i],'flg'=>1);
				if ($str==''){
			
				}
				}
			$i++;
			}
		}
		
		//手工单笔录入
		if ($_POST['components']!="" &&((is_numeric($_POST['quant'])&&$_POST['quant']!=0)||(is_numeric($_POST['extraquant'])&&$_POST['extraquant']!=""))){
		$r=count($_SESSION['workhours']);
			$_SESSION['workhours'][$r]=array('id'=>$r,'comp'=>$_POST['components'],'program'=>$_POST['program'],'techid'=>explode('^',$_POST['technics'])[0],'technics'=>explode('^',$_POST['technics'])[1],'device'=>$_POST['equipment'],'starttime'=>$_POST['starttm'],'endtime'=>$_POST['endtm'],'planqut'=>$_POST['planqut'],'quant'=>$_POST['quant'],'remark'=>$_POST['remark'],'extraquant'=>$_POST['extraquant'],'flg'=>1);
		}
		//var_dump($_SESSION['workhours']);


		
}elseif(isset($_GET['Delete'])){  
	unset($_SESSION['workhours'][$_GET['Delete']]);
 	prnMsg($_GET['Delete'],'info');
	//	$_SESSION['JournalDetail']->Remove_GLEntry($_GET['Delete']);
	
}
//var_dump($_SESSION['workhours']);
echo '<br /><table class="selection">';
echo '<tr><th class="label" colspan="12">工时单 </th></tr>';
if (isset($_SESSION['workhours'])){
	$_POST['wkhdate'] =$_SESSION['workhours'][0]['dt'];
}else{
	$_POST['wkhdate'] = Date($_SESSION['DefaultDateFormat']);
}

echo '<tr>
		<td colspan="12" class="centre">' . _('Date') . ':
		<input type="text" name="wkhdate" size="12" maxlength="12" value="' . $_POST['wkhdate'] .'" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" /></td>
	</tr>';

echo '<tr><td colspan="6">模具选择:';
if (isset($_SESSION['workhours'])){
echo  $_SESSION['workhours'][0]['product'];
echo '</td>
  		<td colspan="6"></td></tr>';
echo '<tr>

		<td colspan="6">操作者:';
	echo $_SESSION['workhours'][0]['emplayee'] .'</td>';

}else{
echo'<select name="product" id="product" onChange="sltproduct(this)" >';

	$proresult=DB_query("SELECT stockid, stockno, categoryid, description FROM stockmaster WHERE stockid LIKE 'H80%'");
	//$row=0;
		if (!isset($_GET['myslt'])||!isset($_POST['product'])){
				echo '<option selected="True" value="0">请选择</option>';
		}
	while ($Row = DB_fetch_array($proresult)){	
			
		if (isset($_GET['myslt']) && $Row['stockid']==explode('^',$_GET['myslt'])[0] ){	
			//1,2
			if (explode('^',$_GET['myslt'])[1]==1 ){
				echo '<option selected="True" value="' . $Row['stockid'] .'^1">' . $Row['description'] . '</option>';
			}else{
				echo '<option selected="True" value="' . $Row['stockid'] .'^2">' . $Row['description'] . '电极</option>';
			}
		}else{
				echo '<option value="' . $Row['stockid'].'^1"  >' . $Row['description'] . '</option>';
				echo '<option value="' . $Row['stockid'].'^2"  >' . $Row['description'] . '附件</option>';
		}
		
	}
echo '</select>';
echo '</td>
		  <td colspan="6"></td></tr>';
echo '<tr>
		<td colspan="6">操作者:<select name="emplayee" >';
			$LocResult = DB_query("SELECT empid, empname FROM employfile WHERE job=1");
		while ($LocRow = DB_fetch_array($LocResult)){
		
		if ($_POST['employee']==$LocRow['empid'].'^'. $LocRow['empname']){
				echo '<option selected="True" value="' . $LocRow['empid'].'^'. $LocRow['empname'] .'">' . $LocRow['empname'] . '</option>';
			} else {
				echo '<option value="' . $LocRow['empid'] .'^'. $LocRow['empname'].'">' . $LocRow['empname'] . '</option>';
			}
		
	}	
echo '</select></td>';
}
echo'<td colspan="6"></td></tr>';
echo'<input type="hidden" name="product" value="' . $_POST['product'] . '" />';
echo' <tr>
			<th>序号</th>
			<th width="30">零件编号</th>
			<th>工序编号</th>
			<th >工艺</th>
			<th>设备</th>
			<th>计划工时</th>
			<th>开始时</th>			
			<th>完成时</th>
			<th>实时</th>
			<th>辅助工时</th>
			<th>备注</th>
			<th></th>
		</tr>';		
	 $r=0;
	 $j=0;
    foreach($_SESSION['workhours'] as $val){
	
			if ($j==1) {
				echo '<tr class="OddTableRows">';
				$j=0;
			} else {
				echo '<tr class="EvenTableRows">';
				$j++;
			}
        if ($r>0){
           printf('	<td>%s</td>					
						<td>%s</td>
						<td >%s</td>
						<td >%s</td>
						<td >%s</td>
						<td class="number">%s</td>
						<td >%s</td>
						<td >%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td >%s</td>
						<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=%s">' . _('Delete') . '</a></td>
					</tr>',
					    $val['id'],
						$val['comp'],
						$val['program'],
						$val['technics'],
						$val['device'],
						$val['planqut'],
						$val['starttime'],
						$val['endtime'],					
						$val['quant'],
						$val['extraquant'],
						$val['remark'],
						$val['id']);
		}
			$r++;	
	}

echo '</table>';
	$sql="SELECT  compcode, description, spec, technics FROM components WHERE stockid='".explode('^',$_POST['product'])[0]."' AND comptype='".explode('^',$_POST['product'])[1]."'";
	$result=DB_query($sql);
//prnMsg($sql.'该产品没有录入组件！'.DB_num_rows($result),'info');
echo '<table class="selection"  style="table-layout:fixed;word-break:break-all;background:#f2f2f2">
		<tr>
		<th  class="label" colspan="12">输入工时数据 </th></tr>';	
	echo' <tr>
			<th>序号</th>
			<th width="30">零件编号</th>
			<th>工序编号</th>
			<th >工艺</th>
			<th>设备</th>
			<th>计划工时</th>
			<th>开始时</th>			
			<th>完成时</th>
			<th>实时</th>
			<th>辅助工时</th>
			<th>备注</th>
			<th></th>
		</tr>';		
	if (isset($_SESSION['workhours'])){
		$aa= "1";
		$sltstk=$_SESSION['workhours'][0]['product'];
	}elseif(isset($_GET['myslt'])){
		$aa= "11";
	    $sltstk=$_GET['myslt'];
	}elseif (isset($_POST['product'])) {
		$aa= "111";
		$sltstk=$_POST['product'];
	}
if (isset($_POST['Import'])){
	prnMsg($_POST['product'].'='.$_POST['components'],'info');
	if (isset($_POST['product'])){
		if(explode('^',$_POST['product'])[1]==1){
			if(isset($_POST['components'])&&$_POST['components']==0){
				prnMsg('你没有选择组件！','info');
			}else{
				prnMsg($_POST['product'].'-'.count($techextarr),'info');
				$sql="SELECT t.compcode,
							spec,
							program,
							t.description,
							planquanttity,
							t.techid,
							technics.description,
							t.quantity,
							t.flag
						FROM
							technicsprogram AS t
						LEFT JOIN components                        
						ON	components.compcode = t.compcode
                LEFT JOIN technics ON technics.techid=t.techid
				WHERE   t.stockid='".explode('^',$_POST['product'])[0]."'
				AND t.compcode='".explode('^',$_POST['components'])[1]."'";	
				$result=DB_query($sql);
				$SQL=" SELECT device.assetid,
								description,
								device.techid
							FROM device
							LEFT JOIN  fixedassets AS f ON  f.assetid = device.assetID 
							WHERE device.assetID!=''";
			
				$resultDrivce=DB_query($SQL);
				$r=1;
				$j=0;
				while($row=DB_fetch_array($result)){
						$_SESSION['wkhary'][$r-1]=array('id'=>$r,'comp'=>$row['spec'].'^'.$row['compcode'],
						'program'=>$row['program'],'techid'=>$row['techid'],'technic'=>$row['description'],
						'planqut'=>$row['planquanttity'],'flg'=>1);
					    $_POST['quantar']=$row['planquanttity'];
						$maxspec=max(explode('*',$row['spec']));
						
						$_POST['extraquantar']=TenchnicExtra($techextarr,$maxspec,explode('^',$sltstk)[1],$row['techid']);
						//prnMsg($maxspec,$_POST['extraquantar'],'info');
						if ($j==1) {
							echo '<tr class="OddTableRows">';
							$j=0;
						} else {
							echo '<tr class="EvenTableRows">';
							$j++;
						}
						echo'<td >'.$r.'</td>
							<td> '.$row['spec'].' '.$row['compcode'].'</td>
							<td >'.$row['program'].'</td>
							<td >'.$row['description'].'</td>
						    <td><select name="device[]" >';
							DB_data_seek($resultDrivce,0);						
							while($myrow=DB_fetch_array($resultDrivce)){
								if($myrow['techid']==$row['techid']){ 
								echo'<option value="'.$myrow['assetid'].'">'.$myrow['description'].'</option>';
								}
							}
						echo'</select></td>
     						<td>'.$row['planquanttity'].'</td>
							<td><input type="time" name="starttime[]" maxlength="12"  size="10"  value="08:00"  /></td>
							<td><input type="time" name="endtime[]" maxlength="12"  size="10"  value="08:00"  /></td>
	 						<td><input type="text" class="number" name="quantar[]"   maxlength="15"  size="10" style="width: 100%; height: 100%" value="' . locale_number_format($_POST['quantar'],$_SESSION['CompanyRecord']['decimalplaces']) . '" /></td>
							<td><input type="text" class="number" name="extraquantar[]" maxlength="15"  size="10" style="width: 100%; height: 100%"  value="' . locale_number_format( $_POST['extraquantar'],$_SESSION['CompanyRecord']['decimalplaces']) . '" /></td>
							<td><input type="text" name="remarkar[]" maxlength="20"  size="15" style="width: 100%; height: 100%" value="" /></td>
     						<td><input type="checkbox" name="chkbx[]" value="'.$r.'" checked ></td>											
 	  					</tr>';
						$r++;
				}//while
				
			}

		}

	}
	
				
		

}#end if Import
	echo'<tr>
		  <td width="10">'.$insertrow.'</td>';			  
	echo'<td>
		<select name="components" id="components" siz=1 onChange="computetime1(this,'.explode('^',$_POST['product'])[1].')">';
			  if ($sltstk==''){
				  	echo '<option selected="True" value="0">请选择模具</option>';
			  }else{
				if (DB_num_rows($result)==0){
					echo '<option selected="True" value="0">请添加组件</option>';
				}else{  
					DB_data_seek($result,0);
					echo '<option selected="True" value="0">请选择组件</option>';
				while ($Row = DB_fetch_array($result)){
					if (isset($_POST['components']) && $_POST['components']==$Row['spec'] .'^'.$Row['compcode']){
						echo '<option selected="True" value="'.$Row['spec'] .'^' . $Row['compcode'].'">' . $Row['spec'] .' '.  $Row['compcode']. '</option>';
					} else {
						echo '<option value="' .$Row['spec'] .'^'. $Row['compcode'] .'">' . $Row['spec'] .' '.  $Row['compcode']. '</option>';
					}
				}
				}
			  }	
	echo '</select></td>
			<td><input type="text"  name="program" maxlength="20" size="15"  value="'.$_POST['program'].'"  /></td>	';	
	echo '<td><select name="technics" id="technics" siz=1 onChange="computetime(this,'.explode('^',$_POST['product'])[1].')">';

			$result=DB_query("SELECT techid, description, assetid, flag FROM technics ");
			while ($Row = DB_fetch_array($result)){
				if ($_POST['technics']==$Row['techid'].'^'.$Row['description'] ){
					echo '<option selected="True" value="' . $Row['techid'].'^'.$Row['description'] .'">' . $Row['techid'].':'.$Row['description'] . '</option>';
				} else {
					echo '<option value="' . $Row['techid'].'^'.$Row['description'] .'">' .$Row['techid'].':'. $Row['description'] . '</option>';
				}
			}
	echo '</select></td>';
	echo'<td><select name="equipment" id="equipment" >
			<option selected="True" value="0">请选择工艺</option>
			</select>
			</td>';
	
	echo '<td><input type="text" class="number" name="planqut" onchange="eitherOr(this,Credit)" maxlength="12" size="10" value="" /></td>
		  <td><input type="time"  id="starttm" name="starttm" maxlength="12" size="12"  value="08:00"  /></td>	
	 	  <td><input type="time"  id="endtm" name="endtm" maxlength="12" size="12"  value="08:00"  /></td>	
		  <td><input type="text"  id="quant" name="quant" maxlength="12" size="12"  value="'. $_POST['quant'] .'"  /></td>	
		  <td><input type="text" autofocus="autofocus" id="extraquant" name="extraquant" maxlength="12" size="12"  value="'. $_POST['extraquant'] .'"  /></td>	
		  <td><input type="text"  name="remark" maxlength="20" size="15"  value="'. $_POST['remark'] .'"  /></td>	
		  <td></td>
		  </tr>';
	
	echo '</table>';
	

echo '<br /><div class="centre"><button type="submit" name="submit" >添加</button>
		<button type="submit" name="Import">查找导入</button>
		<button type="submit" name="clearcache"  onclick="fun(this)"  >清除缓存</button>
      
	   <button type="submit" name="save"  onclick="fun(this)"  value="btn1">' . _('Save') . '</button>';

echo '</div>';
//var_dump($_POST['device']);

if (isset($_POST['save'])) {
	
	 $r=0;
	  $result=DB_query("SELECT max(wkhono) FROM workhour ");
	  $rowno=DB_fetch_row($result);
      $whkno=1;
	  if (!empty($rowno)){
		
		  $wkhno=$rowno[0]+1;
	  }
	  //var_dump($_SESSION['workhours']);
    foreach($_SESSION['workhours'] as $val){
		if ($r>0){
		if ($_SESSION['workhours'][0]['product']!='' ){	
		$SQL="INSERT INTO   workhour(wkhono,
							empid,
							stockid,
							components,
						    program,
							 techid,
    						technics,
    						device,
							planquantity,
							quantity,
							wkhdate,
							starttime,
    						endtime,
							extraquant,
							remark,
							flag)
					VALUES('".$wkhno."',
						'".explode('^',$_SESSION['workhours'][0]['emplayee'])[0] ."',
						'".$_SESSION['workhours'][0]['product'] ."',
						'".explode('-',$val['comp'])[1]."',
					 	'".$val['program']."',
						'".$val['techid']."',
						'".$val['technics']."',
						'".$val['device']."',
						'". (float)$val['planqut']."',
						'". (float)$val['quant']."',
						'".$_SESSION['workhours'][0]['dt'] ."',
						'".$val['starttime'] ."',
						'".$val['endtime'] ."',
						'".(float)$val['extraquant']."',
						'".$val['remark']."', 0)";
			$result=DB_query($SQL);
		 $sql="INSERT IGNORE INTO	technicsprogram(
									stockid,
									compcode,
									program,
									description,
									planquanttity,
									techid,
									technics,
									quantity,
									flag)
							VALUES(";

		}
		}

			$r++;	
	}
	prnMsg( '存入序号：'.$wkhno.'save ok','info');
	unset($_SESSION['wkhary']);
	unset($_SESSION['workhours']);

	$sql="INSERT  INTO components(
								stockid,
								components,
								 program,
								technics,
								 quantity,
								flag
							)
						VALUES(";
	//	header('Location:
	$url='WorkHoursEntry.php';
	echo '<script type="text/javascript">  
            window.location.href="'.$url.'" 
          </script>';  
  
}
echo '</form>';
include('includes/footer.php');
function AddComponent($stockid,$compid,$descrip,$spec){
    //  DB_Txn_Begin();
	$sql = "INSERT INTO  components(
						stockid,
						compcode,
						description,
						spec,
						 procedur,
						technics,
						quantity,
						flag)
						VALUES(";

				$ErrMsg =  _('The item could not be added because');
				$DbgMsg = _('The SQL that was used to add the item failed was');
				$result = DB_query($sql, $ErrMsg, $DbgMsg,'',true);
				//			DB_Txn_Commit();
				
}
function AddTechnics($stockid,$compid,$descrip,$prog,$planqut){
    //  DB_Txn_Begin();
	$sql = "INSERT INTO technicsprogram(stockid,
						compcode,
						program,
						description,
						planquanttity,
						techid,
						technics,
						quantity,
						flag
					)
				VALUES(";

		$ErrMsg =  _('The item could not be added because');
		$DbgMsg = _('The SQL that was used to add the item failed was');
		$result = DB_query($sql, $ErrMsg, $DbgMsg,'',true);
				//			DB_Txn_Commit();
				
}
function totime($timestr,$hms){
	//时间格式字符返回时、分、秒
	$hmsary=explode(':',str_replace('：',':',$timestr));
	$hmssum=0;
	if($hms=='h'||$hms=='H'){
		$hmssum=  round(($hmsary[0]+$hmsary[1]/60+$hmsary[2]/3600),2);
	}elseif($hms=='M'||$hms=='m'){
		$hmssum=  round(($hmsary[0]*60+$hmsary[1]+$hmsary[2]/60),2);
	}elseif($hms=='S'||$hms=='s'){
		$hmssum=$hmsary[0]*3600+$hmsary[1]*60+$hmsary[2];
	}
    return $hmssum;
}

function TenchnicExtra($data,$spec,$typ,$techid){
	//返回附加工时
	$quot=0;
	foreach($data as $val){
    if (((int)$val['techid']==(int)$techid) &&( (int)$val['specmin']>=(int)$spec) && ((int)$spec<=(int)$val['specmax']) && ((int)$val['techtype']==(int)$typ)){
		   $quot= $val['quota'];
		   break;
	   }
	}
    return $quot;
}
function AddStock($stockid,$dsp,$unit,$mb){
	//$typ=array('H80'=>'模具工时',262=>'设备工时',263=>'设备功耗',264=>'操作工时');
   
    DB_Txn_Begin();
				$sql = "INSERT INTO stockmaster (stockid,
												description,
												longdescription,
												categoryid,
												units,
												mbflag,
												eoq,
												discontinued,
												controlled,
												serialised,
												perishable,
												volume,
												grossweight,
												netweight,
												barcode,
												discountcategory,
												taxcatid,
												decimalplaces,
												shrinkfactor,
												pansize)
							VALUES ('".$stockid."',
								'" . $dsp . "',
								'" . $dsp . "',
								'" . substr($stockid,0,3) . "',
								'".$unit."',
								'".$mb."',
								0,	0,	0,  0,	0,	0,	0,	0,	'',	0,						
								0,	4,  0,0)";

				$ErrMsg =  _('The item could not be added because');
				$DbgMsg = _('The SQL that was used to add the item failed was');
				$result = DB_query($sql, $ErrMsg, $DbgMsg,'',true);
				if (DB_error_no() ==0) {
					//now insert the language descriptions
					$ErrMsg = _('Could not update the language description because');
					$DbgMsg = _('The SQL that was used to update the language description and failed was');
					$result = DB_query("INSERT INTO stockdescriptiontranslations (stockid,
																							language_id,
																							descriptiontranslation,
																							longdescriptiontranslation)
													VALUES('" . $stockid . "',
													            'zh_CN.utf8', 
																'zh_CN_utf8',
																	'zh_CN_utf8'													
																)",$ErrMsg,$DbgMsg,true);
				
				

					$result = DB_query("INSERT INTO stockitemproperties (stockid,
													stkcatpropid,
													value)
													VALUES ('" . $stockid . "',
														'" .  substr($stockid,0,3) . "',
														'0')",
								$ErrMsg,$DbgMsg,true);
				
					//Add data to locstock

					$sql = "INSERT INTO locstock (loccode,
													stockid)
										SELECT locations.loccode,
										'" . $stockid . "'
										FROM locations";

					$ErrMsg =  _('The locations for the item') . ' ' . $stockid .  ' ' . _('could not be added because');
					$DbgMsg = _('NB Locations records can be added by opening the utility page') . ' <i>Z_MakeStockLocns.php</i> ' . _('The SQL that was used to add the location records that failed was');
					$InsResult = DB_query($sql,$ErrMsg,$DbgMsg,true);
					DB_Txn_Commit();
				}
}
?>
