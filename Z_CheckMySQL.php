

	<?php
	/* $Id:Z_ERPTableService.php ChengJiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-05-07 13:37:23 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-05-07 16:44:40
 */
	include ('includes/session.php');
	$Title = '转录数据';// Screen identification.
	$ViewTopic= 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
	$BookMark = 'ProfitAndLoss';// Anchor's id in the manual's html document.
	include('includes/SQL_CommonFunctions.inc');
    include('includes/header.php');
echo'<script type="text/javascript">
	function OnClickQuery() {	
		//alert("Yes");
				
		var qy=document.getElementById("QueryType");
		if (qy.checked==true){
		   document.getElementById("QueryType").value=1;
		}else{
		    document.getElementById("QueryType").value=0;
		}
	}
</script>';
	if (!isset($_POST['selectdir'])){
  		 $_POST['selectdir']=$_SESSION['DatabaseName'] ;	 		
	}
	if (!isset($_POST['dbasename'])){
		$_POST['dbasename']=$_SESSION['DatabaseName'] ;	 		
	}
	if (!isset($_POST['MySQLType'])){
		$_POST['MySQLType']=0 ;	 		
	}
	if ($_POST['QueryType']){

		$_POST['QueryType']=1;
	
	}else{
		$_POST['QueryType']=0;
	}	
/*
	if (isset($_SESSION['chkmysql']['QueryType'])){
		$_POST['QueryType']=$_SESSION['chkmysql']['QueryType'];
	
	}else{
		if (!isset($_POST['QueryType'])){

				$_POST['QueryType']=1;
			
		}	
	}*/
	$comdatabase='information_schema';	  
	//连接数据库 导入单��组���
	$db1 = mysqli_connect($host , $DBUser, $DBPassword, $comdatabase, $mysqlport);
	mysqli_set_charset($db1, 'utf8');
	$dberp = mysqli_connect($host , $DBUser, $DBPassword, 'erp_gjw', $mysqlport);
	mysqli_set_charset($dberp, 'utf8');
	
if (!isset($sqlName)|| count($sqlName)==0){
	$SQL="SELECT `TABLE_NAME`, `TABLE_TYPE`, `tabledate`, `flag`, `notes`
			FROM `tablename` WHERE 1";
			
   $sqlName=array();
   $Result0= mysqli_query($dberp,$SQL); 
   while ($row = mysqli_fetch_array($Result0)){
	   $sqlName[$row['TABLE_NAME']]=array($row['TABLE_TYPE']);
   }	
}
		//$sql="SELECT  SCHEMA_NAME FROM SCHEMATA";
		$filepath='./sql/sqlerp/';
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
    ';

	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	
		  <input type="hidden" name="MySQLType" value="' . $_POST['MySQLType'] . '" />';
	echo '<table class="selection">';
	echo '
			<tr>
			<td>目标数据库:</td>
			<td>'.$_SESSION['DatabaseName'] .'
			</td>
		</tr>
		<tr>
			<td>源数据库:</td>
			<td><input type="text" autofocus="autofocus" name="dbasename" maxlength="20" size="20"   value="'. $_POST['dbasename'] .'"  />
			</td>
		</tr>';
		$MySQLType=array(1=>'财务核算类表',2=>'简易ERP类表',3=>'标准ERP类表',4=>"财务12表",5=>"财务16表",6=>"ERP13表");
		
		 echo '<tr>
		 <td>选择类别</td>
		 <td><select name="MySQLType">';
 
 foreach ($MySQLType as $key=>$val) {
	 if ($key == $_POST['MySQLType']) {
		 echo '<option value="' . $key . '" selected="selected">' . $val . '</option>';
	 } else {
		 echo '<option value="' . $key . '">' . $val . '</option>';
	 }
	 
 }
 
 echo '</select>
		 </td>
		 </tr>';

	echo '<tr>
			<td>查询选择:</td>
			<td><input type="checkbox" name="QueryType"  id="QueryType"   value="1"  '.($_POST['QueryType']==1?"checked":"").'  onclick="OnClickQuery();"/>
			</td>
		 </tr>';
	echo '</table>';
	echo '<div class="page_help_text">
	功能简介：使用标准sql重新生成表结构，把原表改名为原名+1，把原表数据复制到新生成中</br>使用Truncate清除数据， 把另一数据库中同名表的数据复制到系统来
	        <br/>财务核算类  包含AccountType  0,1   简易ERP类表  包含2  标准ERP  4	
		</div>';	
		echo '<div class="centre">	
		<input type="submit" name="SearchTable" value="查询表" />
		<input type="submit" name="CheckDefault" value="查询比对表" />';
	$Table0=array("chartmaster","gltrans","debtorsmaster","debtortrans","suppliers","supptrans","accountsubject","registername","erplogs","fixedassets","fixedassettrans","registeraccount");	
	$Table1=array("banktransaction","bankupload","invoicetrans","invupload");	
	$Table2=array("locstock","purchorders","purchorderdetails","salesorders","salesorderdetails","stockmaster","stockmoves","stockrequest","stockrequestitems","workorders","woitems",'stockcategory','stocksubcategory');		
	if (isset($_POST['SearchTable']))
	    echo'</br><input type="submit" name="ModfiyTable" value="修改表" />';
	
	echo'<br/><br>  	
	</div>';
	
	//if (!isset($_SESSION['chkmysql']['QueryType'])){
		$_SESSION['chkmysql']['QueryType']=	$_POST['QueryType'];
	//}
		
			$sql="SELECT  `TABLE_NAME`, `TABLE_TYPE`, `AccountType`, `CREATE_TIME`, `UPDATE_TIME`, `flag`, `notes`
			FROM `tablename`
			WHERE TABLE_TYPE='BASE TABLE'";// AND  AccountType IN (0,".$_POST['MySQLType'].")";
	$Result0= mysqli_query($dberp,$sql); 
	while ($row = mysqli_fetch_array($Result0)){ 		
		$DefaultTable[$row['TABLE_NAME']]=array( "AccountType"=>$row["AccountType"], "CREATE_TIME"=>$row["CREATE_TIME"], "UPDATE_TIME"=>$row["UPDATE_TIME"], "flag"=>0, "notes"=>$row["notes"],"TableType"=>$row["flag"]);
	} 
    // var_dump($DefaultTable);
	$sql="SELECT   `TABLE_NAME`,  `CREATE_TIME`, `UPDATE_TIME` 
			FROM `TABLES` WHERE `TABLE_SCHEMA`='".$_POST['dbasename']."'  AND TABLE_TYPE='BASE TABLE'";		
			$ERPTableName[$key]["FLAG"]=1;
		
		
	$Result0= mysqli_query($db1,$sql); 			
	while ($row = mysqli_fetch_array($Result0)){
			$FLAG=0; 
		if (isset($DefaultTable[$row['TABLE_NAME']])){
			$DefaultTable[$row['TABLE_NAME']]['flag']=1;
			$FLAG=1;
		}		
		$ERPTableName[$row['TABLE_NAME']]=array(  "CREATE_TIME"=>$row["CREATE_TIME"], "UPDATE_TIME"=>$row["UPDATE_TIME"],"FLAG"=>$FLAG);
	} 	
		//读取���的标准字段
	$sql="SELECT `TABLE_NAME`, `COLUMN_NAME`, `COLUMN_DEFAULT`, `IS_NULLABLE`, `COLUMN_TYPE`, `COLUMN_KEY`, `EXTRA`, `flag` FROM `tablecolumn` WHERE 1";
	$result=mysqli_query($dberp,$sql);
	
	while($row=mysqli_fetch_array($result)){
		$ColStardard[$row['TABLE_NAME']][$row['COLUMN_NAME']]=array( "COLUMN_DEFAULT"=>$row["COLUMN_DEFAULT"],"IS_NULLABLE"=>$row["IS_NULLABLE"], "COLUMN_TYPE"=>$row["COLUMN_TYPE"], "COLUMN_KEY"=>$row["COLUMN_KEY"], "EXTRA"=>$row[ "EXTRA"],"Status"=>0);
	}
	//var_dump($ColStardard);
	if (isset($_POST['SubmitField'])){
		prnMsg($_POST['SubmitField']);
	}
	//读取比对表结构
	$sql="SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION,ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, CHARACTER_OCTET_LENGTH, COLUMN_TYPE, COLUMN_KEY,EXTRA 
		FROM COLUMNS 
		WHERE TABLE_SCHEMA='".$_POST['dbasename']."'";
	$ColERP=array();				
	$Result0= mysqli_query($db1,$sql); 
	while ($row = mysqli_fetch_array($Result0)){ 		
		$ColERP[$row['TABLE_NAME']][$row['COLUMN_NAME']]=array( "COLUMN_DEFAULT"=>$row["COLUMN_DEFAULT"], "ORDINAL_POSITION"=>$row['ORDINAL_POSITION'], "IS_NULLABLE"=>$row["IS_NULLABLE"], "DATA_TYPE"=>$row["DATA_TYPE"], "COLUMN_TYPE"=>$row["COLUMN_TYPE"], "COLUMN_KEY"=>$row["COLUMN_KEY"], "EXTRA"=>$row[ "EXTRA"],"FLAG"=>0);
	}


if (isset($_GET['Del'])){

		$SQL="DROP TABLE ".$_GET['Del'];
		$Result=DB_query($SQL);
		prnMsg($_GET['Del']."表已经删除！",'info');
}
if(isset($_POST['SearchTable'])){
					
	//检查系统缺少的表			
	$c=0  ;
	echo '<table class="selection">
			<tr>
				<th colspan="9">DATABASE:'.$_POST['dbasename'].' 标准表 '.count($DefaultTable).'个</th></tr>	
			<tr>
				<th>序号</th>				
				<th >名称</th>
				<th>属性</th>				
				<th >创建时间</th>
													
				<th>状态</th>	   
			</tr>';
		$TableHead='';	

		if ($_POST['MySQLType']==1){
			$TableType=array(1,0);
		}elseif($_POST['MySQLType']==2){
			$TableType=array(2);
		}
	foreach ($DefaultTable as $key=>$row){
		$f=0;
		if ($_POST['MySQLType']<=3){
			if (in_array($DefaultTable[$key]['AccountType'],$TableType)){  
				$c++;
				
				$rw=count($ColStardard[$key])+1;
			
				if ($k==1){
					echo '<tr class="EvenTableRows">';
						$k=0;
				} else {
					echo  '<tr class="OddTableRows">';
					$k=1;
				}
					echo '<td >'.$c.'</td>
						<td >'. $key.'</td>
						<td >正常</td>
						<td >'.$row['CREATE_TIME'].'</td>
						<td ><input type="checkbox"  name="selecttable[]"    value="'. $key .'"  '.($_POST['QueryType']==1?"checked":"").'  /></td>	
					</tr>'; 
		
			}  
		}else{
			if ($_POST['MySQLType']==4){
				$Table=$Table0;
			}else	if ($_POST['MySQLType']==5){
				$Table=array_merge($Table0,$Table1);
			}else	if ($_POST['MySQLType']==6){
				$Table=$Table2;
			}
			if (in_array($key,$Table)){  
				$c++;
				
				$rw=count($ColStardard[$key])+1;
				//if ($TableHead==""){	
				if ($k==1){
					echo '<tr class="EvenTableRows">';
						$k=0;
				} else {
					echo  '<tr class="OddTableRows">';
					$k=1;
				}
					echo '<td >'.$c.'</td>
						<td >'. $key.'</td>
						<td >正常</td>
						<td >'.$row['CREATE_TIME'].'</td>
													
						
							
						<td ><input type="checkbox"  name="selecttable[]"    value="'. $key .'"  '.($_POST['QueryType']==1?"checked":"").'  /></td>	
					</tr>'; 
		
			}  
		}
	}	
	echo '</table>';
		//<td ><a href="'.  $RootPath . '/Z_CheckMySQL.php?Modify='.$key.'"   id="href'.$RowIndex.'" name="href'.$RowIndex.'" title=""  >修改</a></td>	  
}elseif(isset($_POST['CheckDefault'])||isset($_GET['Add'])||isset($_GET['Del'])){
	//prnMsg($_POST['MySQLType']);
		//检查系统缺少的表			
		$c=1  ;
		 $r=1;
		 //var_dump($DefaultCol);	 
		// $DefaultTable=array_keys($ColERP) ;//标准表���
		 echo '<table class="selection">
		 <tr><th colspan="5">DATABASE:'.$_POST['dbasename'].' 标准表 '.count($DefaultTable).'个</th></tr>	
		 <tr>
			<th>序号</th>				
			<th>名称</th>
			<th>属性</th>				
			<th>创建时间</th>
			<th >更新时间</th>
			<th></th>									
			<th>状态</th>	   
		 </tr>';
		 foreach ($DefaultTable as $key=>$row){
			if ($DefaultTable[$key]['flag']!=1){ 
			if ($k==1){
				echo '<tr class="EvenTableRows">';
					$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			echo '<td >'.$c.'</td>
				  <td>'. $key.'</td>';
				//echo'<td>正常</td>';			    }else{
				echo'<td>缺失</td>';
			
				echo'<td>'.$row['CREATE_TIME'].'</td>
						<td>'.$row['UPDATE_TIME'].'</td>								
						<td>'.$row['notes'].'</td>';					
					
						if ($DefaultTable[$key]['flag']==1){
				
							echo'<td></td>';
						}else{
						
							echo'<td ><a href="'.  $RootPath . '/Z_CheckTableColumn.php?Add='.$key.'"   id="href'.$RowIndex.'" name="href'.$RowIndex.'" title=""  >添加</a></td>	';
						}							 
				echo'</tr>'; 	
			}			
				  $c++;  				
		}
		  echo '</table>';
  
}elseif(isset($_POST['ModfiyTable'])){
	$db2 = mysqli_connect($host , $DBUser, $DBPassword,$_POST['dbasename'], $mysqlport);
	     mysqli_set_charset($db2, 'utf8');
	if ($_SESSION['DatabaseName']==$_POST['dbasename']){
		prnMsg('原文件改名，使用sql文件创建，然后复制到新表');
		if (count($_POST['selecttable'])>0){
			echo '<br /><table>';
			foreach($_POST['selecttable'] as $row){
				//echo $filepath.$row.'.sql<br/>';
				if(file_exists($filepath.$row.'.sql')) {	 
					//根据flag交易类0,参数类1,基础数据2
					//echo "<br/>".($row);
				
					$SQL="rename table ".$row." to ".$row."_bak";
					$result = DB_query($SQL);
					$SQLScriptFile = file('./sql/sqlerp/'.$row.'.sql');
				
					$ScriptFileEntries = sizeof($SQLScriptFile);
					$ErrMsg = _('The script to upgrade the database failed because');
					$sql ='';
					$InAFunction = false;
					echo '<br /><table>';
				
				
					for ($i=0; $i<=$ScriptFileEntries; $i++) {
				
						$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);
				
						if (mb_substr($SQLScriptFile[$i], 0, 2) == '--') {
							$comment=mb_substr($SQLScriptFile[$i], 2);
						}
				
						if (mb_substr($SQLScriptFile[$i], 0, 2) != '--'
							AND mb_substr($SQLScriptFile[$i], 0, 3) != 'USE'
							AND mb_strstr($SQLScriptFile[$i],'/*')==FALSE
							AND mb_strlen($SQLScriptFile[$i])>1){
				
							$sql .= ' ' . $SQLScriptFile[$i];
				
							//check if this line kicks off a function definition - pg chokes otherwise
							if (mb_substr($SQLScriptFile[$i],0,15) == 'CREATE FUNCTION'){
								$InAFunction = true;
							}
							//check if this line completes a function definition - pg chokes otherwise
							if (mb_substr($SQLScriptFile[$i],0,8) == 'LANGUAGE'){
								$InAFunction = false;
							}
							if (mb_strpos($SQLScriptFile[$i],';')>0 AND ! $InAFunction){
								$sql = mb_substr($sql,0,mb_strlen($sql)-1);
								//prnMsg($sql);
								$result = DB_query($sql, $ErrMsg, $DBMsg, false, false);
								
								switch (DB_error_no()) {
									case 0:
										echo '<tr><td>' . $comment . '</td><td style="background-color:green">' . _('Success') . '</td></tr>';
										break;
									case 1050:
										echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
											_('Table has already been created') . '</td></tr>';
										break;
									case 1060:
										echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
											_('Column has already been created') . '</td></tr>';
										break;
									case 1061:
										echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
											_('Index already exists') . '</td></tr>';
										break;
									case 1062:
										echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
											_('Entry has already been done') . '</td></tr>';
										break;
									case 1068:
										echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
											_('Primary key already exists') . '</td></tr>';
										break;
									default:
										echo '<tr><td>' . $comment . '</td><td style="background-color:red">' . _('Failure').' - '.
											_('Error number').' - '.DB_error_no()  . '</td></tr>';
										break;
								}
								unset($sql);
							}
				
						} //end if its a valid sql line not a comment
					} //end of for loop around the lines of the sql script

					//判断表是否存在
					if ($DefaultTable[$row]['TableType']==0||$DefaultTable[$row]['TableType']==2){
						//清除数据  ，复制bak数据到新表
						$SQL="TRUNCATE ".$row . " ";
						$result = DB_query($SQL);
	
						$sql='DESCRIBE '.$row;
						$result=DB_query($sql);
						//添加判断表是否存在
						
						$SQLScript[]="INSERT INTO  ".$row."( ";
						$Filed=[];
						while ($myrow=DB_fetch_row($result)) {
							$Filed[$myrow[0]]=array($myrow[1],0);
							$SQLScript[]=$myrow[0]." ,";
							
						}

						$SQLScript[count($SQLScript)-1]=substr($SQLScript[count($SQLScript)-1],0,-1).") ";
						//echo $SQL;
						$sql='DESCRIBE '.$row;
						$Result= mysqli_query($db2,$sql); 
	
					
						while ($myrow= mysqli_fetch_row($Result)) {
							if (isset($Filed[$myrow[0]])){
								$Filed[$myrow[0]][1]=1;
							}else{		
								$Filed[$myrow[0]][1]=-1;
							}
							
						}
					
						$SQLScript[]="  SELECT ";
						foreach ($Filed as $key=>$val){
							if ($val[1]==1){
								$SQLScript[]=$key.",";
							}elseif($val[1]==0){
								$SQLScript[]="'0'  " .$key." ,";
							}
	
						}
						$SQLScript[count($SQLScript)-1]=substr($SQLScript[count($SQLScript)-1],0,-1);
						$SQLScript[]=" FROM ".$_POST['dbasename'].".".$row."_bak ;";
						/*
						$SQL="INSERT INTO  ".$row."( ";
						$Filed=[];
						while ($myrow=DB_fetch_row($result)) {
							$Filed[$myrow[0]]=array($myrow[1],0);
							$SQL.=$myrow[0]." ,";
							
						}

						//$SQLScript[count($SQLScript)-1]=substr($SQLScript[count($SQLScript)-1],0,-1).") ";
						$SQL=substr($SQL,0,-1).") ";
						//echo $SQL;
						$sql='DESCRIBE '.$row;
						$Result= mysqli_query($db2,$sql); 
	
					
						while ($myrow= mysqli_fetch_row($Result)) {
							if (isset($Filed[$myrow[0]])){
								$Filed[$myrow[0]][1]=1;
							}else{		
								$Filed[$myrow[0]][1]=-1;
							}
							
						}
					
						$SQL.="  SELECT ";
						foreach ($Filed as $key=>$val){
							if ($val[1]==1){
								$SQL.=$key.",";
							}elseif($val[1]==0){
								$SQL.="'0'  " .$key." ,";
							}
	
						}
						$SQL=substr($SQL,0,-1);
						$SQL.=" FROM ".$_POST['dbasename'].".".$row."_bak ;";*/
						//print_r($Filed);
						/*
						$sql="TRUNCATE ".$row . " ";
						$Result = DB_query($sql);
						$rw=DB_fetch_row($Result);
						if ($rw>0){  */
							//print_r($SQLScript);
							$SQL="";
							for ($i=0; $i<count($SQLScript); $i++) {
								$SQLScript[$i] = trim($SQLScript[$i]);
								$SQL .= ' ' . $SQLScript[$i];
							}
							$Result =  DB_query($SQL, $ErrMsg, $DBMsg, false, false);
					//	}
						echo $SQL;

					}
				
				}else{
					echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
					$row.'.sql文件不存在！</td></tr>';
				}//endif
			}//endforeach
			echo '</table>';
		}
	}else{
		prnMsg('清除当前数据，使用另外数据库数据复制到当前系统');
		
		if (count($_POST['selecttable'])>0){
		
			foreach($_POST['selecttable'] as $row){
				//echo $filepath.$row.'.sql<br/>';
				//if(file_exists($filepath.$row.'.sql')) {	 
					//echo "<br/>".($row);
					$SQL="TRUNCATE ".$row . " ";
					$result = DB_query($SQL);

					$sql='DESCRIBE '.$row;
					$result=DB_query($sql);

					$SQL="INSERT INTO  ".$row."( ";
					$Filed=[];
					while ($myrow=DB_fetch_row($result)) {
						$Filed[$myrow[0]]=array($myrow[1],0);
						$SQL.=$myrow[0]." ,";
						
					}
					$SQL=substr($SQL,0,-1).") ";
					//echo $SQL;
					$sql='DESCRIBE '.$row;
					$Result= mysqli_query($db2,$sql); 

				
					while ($myrow= mysqli_fetch_row($Result)) {
						if (isset($Filed[$myrow[0]])){
							$Filed[$myrow[0]][1]=1;
						}else{		
							$Filed[$myrow[0]][1]=-1;
						}
						
					}
				
					$SQL.="SELECT ";
					foreach ($Filed as $key=>$val){
						if ($val[1]==1){
							$SQL.=$key.",";
						}elseif($val[1]==0){
							$SQL.="'0'  " .$key." ,";
						}

					}
					$SQL=substr($SQL,0,-1);
					$SQL.=" FROM ".$_POST['dbasename'].".".$row." ";

					//print_r($Filed);
					 //echo  $SQL;
					$result = DB_query($SQL);
					/*
					$SQLScriptFile = file('./sql/sqlerp/'.$_GET['Modify'].'.sql');
					$ScriptFileEntries = sizeof($SQLScriptFile);
					$ErrMsg = _('The script to upgrade the database failed because');
					$sql ='';
					$InAFunction = false;
					echo '<br /><table>';
				
				
					for ($i=0; $i<=$ScriptFileEntries; $i++) {
				
						$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);
				
						if (mb_substr($SQLScriptFile[$i], 0, 2) == '--') {
							$comment=mb_substr($SQLScriptFile[$i], 2);
						}
				
						if (mb_substr($SQLScriptFile[$i], 0, 2) != '--'
							AND mb_substr($SQLScriptFile[$i], 0, 3) != 'USE'
							AND mb_strstr($SQLScriptFile[$i],'/*')==FALSE
							AND mb_strlen($SQLScriptFile[$i])>1){
				
							$sql .= ' ' . $SQLScriptFile[$i];
				
							//check if this line kicks off a function definition - pg chokes otherwise
							if (mb_substr($SQLScriptFile[$i],0,15) == 'CREATE FUNCTION'){
								$InAFunction = true;
							}
							//check if this line completes a function definition - pg chokes otherwise
							if (mb_substr($SQLScriptFile[$i],0,8) == 'LANGUAGE'){
								$InAFunction = false;
							}
							if (mb_strpos($SQLScriptFile[$i],';')>0 AND ! $InAFunction){
								$sql = mb_substr($sql,0,mb_strlen($sql)-1);
								//prnMsg($sql);
								$result = DB_query($sql, $ErrMsg, $DBMsg, false, false);
								
								switch (DB_error_no()) {
									case 0:
										echo '<tr><td>' . $comment . '</td><td style="background-color:green">' . _('Success') . '</td></tr>';
										break;
									case 1050:
										echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
											_('Table has already been created') . '</td></tr>';
										break;
									case 1060:
										echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
											_('Column has already been created') . '</td></tr>';
										break;
									case 1061:
										echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
											_('Index already exists') . '</td></tr>';
										break;
									case 1062:
										echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
											_('Entry has already been done') . '</td></tr>';
										break;
									case 1068:
										echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
											_('Primary key already exists') . '</td></tr>';
										break;
									default:
										echo '<tr><td>' . $comment . '</td><td style="background-color:red">' . _('Failure').' - '.
											_('Error number').' - '.DB_error_no()  . '</td></tr>';
										break;
								}
								unset($sql);
							}
				
						} //end if its a valid sql line not a comment
					} //end of for loop around the lines of the sql script
					*/
				
				
			}//endforeach
		
		}
		
		
	}
}


if (isset($_GET['Modify'])){
	

	
	if(file_exists($filepath.$_GET['Modify'].'.sql')) {	      
		$SQL="rename table ".$_GET['Modify']." to ".$_GET['Modify']."Bak";

		/*$SQLScriptFile = file('./sql/sqlerp/'.$_GET['Modify'].'.sql');

			echo '<br /><table>';
			echo '</table>';*/
		prnMsg($AddSql.$_GET['Modify']."结构修改成功！",'info');
	}else{
		prnMsg($AddSql.$_GET['Modify']."表修改需要的sql文件不存在！",'info');
	}
	//exit;
}elseif (isset($_GET['Add'])){

	prnMsg(_('If there are any failures then please check with your system administrator').
		'. '._('Please read all notes carefully to ensure they are expected'),'info');

	$SQLScriptFile = file('./sql/sqlerp/'.$_GET['Add'].'.sql');
	// var_dump($SQLScriptFile);
	$ScriptFileEntries = sizeof($SQLScriptFile);
	$ErrMsg = _('The script to upgrade the database failed because');
	$sql ='';
	$InAFunction = false;
	echo '<br /><table>';


	for ($i=0; $i<=$ScriptFileEntries; $i++) {

		$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);

		if (mb_substr($SQLScriptFile[$i], 0, 2) == '--') {
			$comment=mb_substr($SQLScriptFile[$i], 2);
		}

		if (mb_substr($SQLScriptFile[$i], 0, 2) != '--'
			AND mb_substr($SQLScriptFile[$i], 0, 3) != 'USE'
			AND mb_strstr($SQLScriptFile[$i],'/*')==FALSE
			AND mb_strlen($SQLScriptFile[$i])>1){

			$sql .= ' ' . $SQLScriptFile[$i];

			//check if this line kicks off a function definition - pg chokes otherwise
			if (mb_substr($SQLScriptFile[$i],0,15) == 'CREATE FUNCTION'){
				$InAFunction = true;
			}
			//check if this line completes a function definition - pg chokes otherwise
			if (mb_substr($SQLScriptFile[$i],0,8) == 'LANGUAGE'){
				$InAFunction = false;
			}
			if (mb_strpos($SQLScriptFile[$i],';')>0 AND ! $InAFunction){
				$sql = mb_substr($sql,0,mb_strlen($sql)-1);
				//prnMsg($sql);
				$result = DB_query($sql, $ErrMsg, $DBMsg, false, false);
				
				switch (DB_error_no()) {
					case 0:
						echo '<tr><td>' . $comment . '</td><td style="background-color:green">' . _('Success') . '</td></tr>';
						break;
					case 1050:
						echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
							_('Table has already been created') . '</td></tr>';
						break;
					case 1060:
						echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
							_('Column has already been created') . '</td></tr>';
						break;
					case 1061:
						echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
							_('Index already exists') . '</td></tr>';
						break;
					case 1062:
						echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
							_('Entry has already been done') . '</td></tr>';
						break;
					case 1068:
						echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
							_('Primary key already exists') . '</td></tr>';
						break;
					default:
						echo '<tr><td>' . $comment . '</td><td style="background-color:red">' . _('Failure').' - '.
							_('Error number').' - '.DB_error_no()  . '</td></tr>';
						break;
				}
				unset($sql);
			}

		} //end if its a valid sql line not a comment
	} //end of for loop around the lines of the sql script

	echo '</table>';
	prnMsg($AddSql.$_GET['Add']."表添加成功！",'info');



}

echo '</div></form>';
include('includes/footer.php');
?>
