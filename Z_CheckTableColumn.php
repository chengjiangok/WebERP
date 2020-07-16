<?php

/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-03-14 05:10:19
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-06-08 14:29:31
 */
include ('includes/session.php');
	$Title = '表名字段_校验';// Screen identification.
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
		$_POST['MySQLType']='BASE TABLE' ;	 		
	}
	if (!isset($_POST['QueryType'])){
		$_POST['QueryType']=0;
	}	
	$comdatabase='information_schema';	  
	//连接数据库 导入单元组库
	$db1 = mysqli_connect($host , $DBUser, $DBPassword, $comdatabase, $mysqlport);
	mysqli_set_charset($db1, 'utf8');
	$dberp = mysqli_connect($host , $DBUser, $DBPassword, 'erp_gjw', $mysqlport);
	mysqli_set_charset($dberp, 'utf8');

  
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
    ';

	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="QueryType" value="' . $_POST['QueryType'] . '" />
		  <input type="hidden" name="MySQLType" value="' . $_POST['MySQLType'] . '" />';
	echo '<table class="selection">';
	echo '<tr>
				<td>目标数据库:</td>
				<td>'.$_SESSION['DatabaseName'] .'
				</td>
			</tr><tr>
			<td>数据库选择:</td>
			<td><input type="text" autofocus="autofocus" name="dbasename" maxlength="20" size="20"   value="'. $_POST['dbasename'] .'"  />
			</td>
		</tr>';
		$MySQLType=array(1=>'财务核算类表',2=>'简易ERP类表',3=>'标准ERP类表');

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
			<td>查选择:</td>
			<td><input type="checkbox" name="QueryType"  id="QueryType"   value="0"  '.($_POST['QueryType']==1?"checked":"").'  onclick="OnClickQuery();"/>
			</td>
		 </tr>';
	echo '</table>';
	echo '<div class="page_help_text">
	功能简介：标准表使用的是tablename,tablecolumn资料</br>	添加表：使用sql/sqlerp/下的表名文件创建<br/>修改表：原表改名为原表名1，并复制内容到新表，参数类直接新建	
		</div>';	
	
	echo '<div class="centre">	
	        <input type="submit" name="CheckDefault" value="查询比对表" />
			
		
			<input type="submit" name="CheckTableStructure" value="查询修改表" />		
		
		
			<br/><br>  
				
		
		</div>';
		//	<input type="submit" name="UpdateSQL" value="更新升级" />	<input type="submit" name="AddTable" value="添加缺少表" />	<input type="submit" name="CheckTableColumn" value="缺失表结构" />		<input type="submit" name="UpdateTable" value="更新问题表" />
		$sql="SELECT  `TABLE_NAME`, `TABLE_TYPE`, `AccountType`, `CREATE_TIME`, `UPDATE_TIME`, `flag`, `notes`
				FROM `tablename`
				WHERE TABLE_TYPE='BASE TABLE'";// AND  AccountType IN (0,".$_POST['MySQLType'].")";
		$Result0= mysqli_query($dberp,$sql); 
		while ($row = mysqli_fetch_array($Result0)){ 		
			$DefaultTable[$row['TABLE_NAME']]=array( "AccountType"=>$row["AccountType"], "CREATE_TIME"=>$row["CREATE_TIME"], "UPDATE_TIME"=>$row["UPDATE_TIME"],"TableType"=>$row["flag"], "notes"=>$row["notes"],"flag"=>0);
		} 

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
			//读取表的标准字段
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
if(isset($_POST['CheckTableStructure'])){
					
	//检查系统缺少的表			
	$c=0  ;
	echo '<table class="selection">
			<tr>
				<th colspan="9">DATABASE:'.$_POST['dbasename'].' 标准表 '.count($DefaultTable).'个</th></tr>	
			<tr>
				<th>序号</th>				
				<th colspan="2">名称</th>
				<th>属性</th>				
				<th colspan="2">创建时间</th>
				<th colspan="2">更新时间</th>											
				<th>状态</th>	   
			</tr>';
		$TableHead="";	
	foreach ($DefaultTable as $key=>$row){
		$f=0;
		if ($DefaultTable[$key]['flag']==1){  
			$c++;
			
			$rw=count($ColStardard[$key])+1;
		if ($TableHead==""){	
			if ($k==1){
				$TableHead.= '<tr class="EvenTableRows">';
					$k=0;
			} else {
				$TableHead.=  '<tr class="OddTableRows">';
				$k=1;
			}
			$TableHead.= '<td >'.$c.'</td>
				 <td colspan="2">'. $key.'</td>
				 <td >正常</td>
				 <td colspan="2">'.$row['CREATE_TIME'].'</td>
				<td colspan="2">'.$row['UPDATE_TIME'].'</td>								
				
				<td ><a href="'.  $RootPath . '/Z_CheckTableColumn.php?Modify='.$key.'"   id="href'.$RowIndex.'" name="href'.$RowIndex.'" title=""  >修改</a></td>	
			</tr>'; 
		}
			//以下为检查表
		
		
			foreach ($ColStardard[$key] as $Fieldkey=>$val){				
				$f++;
				$Check=0;
				if (isset($ColERP[$key][$Fieldkey])){
					//比对字段ok
					if($val['COLUMN_TYPE']!=$ColERP[$key][$Fieldkey]['COLUMN_TYPE']){
						$Check++;
					}
					if($val['COLUMN_KEY']=='PRI'){
					
						if($val['EXTRA']!=$ColERP[$key][$Fieldkey]['EXTRA']){
							
					
							$Check++;
						}
						if($val['COLUMN_KEY']!=$ColERP[$key][$Fieldkey]['COLUMN_KEY']){
								
						
							$Check++;
						}
						//echo'<td>'.$PRI.$AUTO.'</td>';	
					}
					if ($Check>0){
						if ($TableHead!=""){
							echo $TableHead;
							$TableHead="";
						}
						if ($k==1){
							echo '<tr class="EvenTableRows">';
							$k=0;
						} else {
							echo '<tr class="OddTableRows">';
							$k=1;
						}
						echo'<td ></td>
							 <td>'.$f.'</td>
							 <td>'.$Check. $Fieldkey.'</td>';					
						echo'<td>正常</td>					
							 <td>'.$val['COLUMN_TYPE'].'</td>';
							if($val['COLUMN_TYPE']==$ColERP[$key][$Fieldkey]['COLUMN_TYPE']){
								echo'<td>正常</td>';	
							}else{
								echo'<td>差异</td>';
							}
							$PRI='';
							$AUTO='';
							$ISNULL='';
						
							if($val['COLUMN_KEY']=='PRI'){
								if($val['EXTRA']=='auto_increment'){
									$AUTO="自增";
								}
							
								echo'<td>'.$val['COLUMN_KEY'].'/'.$AUTO.'</td>';	
								if($val['EXTRA']==$ColERP[$key][$Fieldkey]['EXTRA']){
									$AUTO="自增/正常";
								}else{
									$AUTO="自增/缺失";
									$Check++;
								}
								if($val['COLUMN_KEY']==$ColERP[$key][$Fieldkey]['COLUMN_KEY']){
										
									$PRI='PRI正常/';	
								}else{
									$PRI='PRI缺失/';
									$Check++;
								}
								//echo'<td>'.$PRI.$AUTO.'</td>';	
							}else {
								if($val['IS_NULLABLE']=='YES'){
									$ISNULL="空".$val['IS_NULLABLE'];
								}else {
									$ISNULL="值".$val['IS_NULLABLE'];
								}
								$ISNULL.="/".$val['COLUMN_DEFAULT'];
								echo'<td>'.$val['IS_NULLABLE'].'</td>';	
							}
								echo'<td>'.$PRI.$AUTO.$val['IS_NULLABLE'].'</td>';	
							
						echo'<td></td>
							</tr>'; 		
					}
				}else{
					if ($TableHead!=""){
						echo $TableHead;
						$TableHead="";
					}
					//对字段缺失
					if ($k==1){
						echo '<tr class="EvenTableRows">';
						$k=0;
					} else {
						echo '<tr class="OddTableRows">';
						$k=1;
					}
					echo '<td ></td>
						  <td>'.$f.'</td>
						  <td>'. $Fieldkey.'</td>';					
					echo'<td>缺失</td>					
						<td>'.$val['COLUMN_TYPE'].'</td>
						<td>'.$val['IS_NULLABLE'].'</td>
						<td>'.$val['COLUMN_DEFAULT'].'</td>
						<td>'.$val['COLUMN_KEY'].'</td>					
					
					
						<td></td>';
				
					echo'</tr>'; 		
				}
				 
				unset($coltype);	      
			}//end for field
		}  
	}	
	echo '</table>';
			  
}elseif(isset($_POST['CheckDefault'])||isset($_GET['Add'])||isset($_GET['Del'])){
	//prnMsg($_POST['MySQLType']);
			//检查系统缺少的表			
			$c=1  ;
			 $r=1;
			 //var_dump($DefaultCol);	 
			// $DefaultTable=array_keys($ColERP) ;//标准表名
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
			foreach ($ERPTableName as $key=>$row){
				if ($row["FLAG"]==0){
					if ($k==1){
						echo '<tr class="EvenTableRows">';
							$k=0;
					} else {
						echo '<tr class="OddTableRows">';
						$k=1;
					}
					echo '<td >'.$c.'</td>
						  <td>'. $key.'</td>';
					echo'<td >非标准</td>	';

				
						
					echo'<td>'.$row['CREATE_TIME'].'</td>
							<td>'.$row['UPDATE_TIME'].'</td>								
							<td></td>					
							<td ><a href="'.  $RootPath . '/Z_CheckTableColumn.php?Del='.$key.'"  id="href'.$RowIndex.'" name="href'.$RowIndex.'" title=""  >删除</a>
							 
							</tr>'; 				
						  $c++;  				

				}
			}

			  echo '</table>';
	  
}elseif (isset($_GET['Modify'])){
	$filepath='./sql/sqlerp/';
	$db2 = mysqli_connect($host , $DBUser, $DBPassword,$_POST['dbasename'], $mysqlport);
	mysqli_set_charset($db2, 'utf8');
	if ($_SESSION['DatabaseName']==$_POST['dbasename']){
		
		if(file_exists($filepath.$_GET['Modify'].'.sql')) {	      
			
			//if(file_exists($filepath.$_GET['Modify'].'.sql')) {	 
				//根据flag交易类0,参数类1,基础数据2
				//echo "<br/>".($_GET['Modify']);
			
				$SQL="rename table ".$_GET['Modify']." to ".$_GET['Modify']."_bak";
				$result = DB_query($SQL);
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
				prnMsg($_GET['Modify'].'='.$DefaultTable[$_GET['Modify']]['TableType']);
				//判断表是否存在
				if ($DefaultTable[$_GET['Modify']]['TableType']==0||$DefaultTable[$_GET['Modify']]['TableType']==2){
					prnMsg('//清除数据  ，复制bak数据到新表');
					$SQL="TRUNCATE ".$_GET['Modify'] . " ";
					$result = DB_query($SQL);

					$sql='DESCRIBE '.$_GET['Modify'];
					$result=DB_query($sql);
					//添加判断表是否存在
					$SQL="INSERT INTO  ".$_GET['Modify']."( ";
					$Filed=[];
					while ($myrow=DB_fetch_row($result)) {
						$Filed[$myrow[0]]=array($myrow[1],0);
						$SQL.=$myrow[0]." ,";
						
					}
					$SQL=substr($SQL,0,-1).") ";
					//echo $SQL;
					$sql='DESCRIBE '.$_GET['Modify'];
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
					$SQL.=" FROM ".$_POST['dbasename'].".".$_GET['Modify']."_bak";

					//print_r($Filed);
					 echo  $SQL;
					$result = DB_query($SQL);


				}
			
			}else{
				echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
				$_GET['Modify'].'.sql文件不存在！</td></tr>';
			}//endif
	
	}else{
	
			//echo $filepath.$row.'.sql<br/>';
			//if(file_exists($filepath.$row.'.sql')) {	 
				//echo "<br/>".($row);
				$SQL="TRUNCATE ".$_GET['Modify'] . " ";
				$result = DB_query($SQL);

				$sql='DESCRIBE '.$_GET['Modify'];
				$result=DB_query($sql);

				$SQL="INSERT INTO  ".$_GET['Modify']."( ";
				$Filed=[];
				while ($myrow=DB_fetch_row($result)) {
					$Filed[$myrow[0]]=array($myrow[1],0);
					$SQL.=$myrow[0]." ,";
					
				}
				$SQL=substr($SQL,0,-1).") ";
				//echo $SQL;
				$sql='DESCRIBE '.$_GET['Modify'];
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
				$SQL.=" FROM ".$_POST['dbasename'].".".$_GET['Modify']." ";

				//print_r($Filed);
				 //echo  $SQL;
				$result = DB_query($SQL);
			
			
			
		
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
	 //获取目录下所有件，包括子目录

	 function get_allfiles($path,&$files) {  
		if(is_dir($path)){  
			$dp = dir($path);  
			while ($file = $dp ->read()){  
				if($file !="." && $file !=".."){  
					get_allfiles($path."/".$file, $files);  
				}  
			}  
			$dp ->close();  
		}  
		if(is_file($path)){  
			$files[] =  $path;  
		}  
	}  
		
	function get_filenamesbydir($dir){  
		$files =  array();  
		get_allfiles($dir,$files);  
		return $files;  
	}  
	/*
	elseif(isset($_POST['CheckTableColumn'])){
		//检查的表	
	$c=1  ;

			 
			//$DefaultTable=array_keys($ColERP) ;//标准表名
	echo'<table class="selection">
			<tr><th colspan="7">DATABASE:'.$_POST['dbasename'].' 标准表 '.count($DefaultTable).'个</th></tr>					
			<tr>
			<th>序号</th>				
			<th>名称</th>
			<th>属性</th>				
			<th>字段名</th>
			<th >字段类型</th>
			<th>主键、索引</th>									
			<th默认值 空值</th>	   
			</tr>';
			foreach ($DefaultTable as $key=>$row){
			$rw=2;
			$tr='';
			if ($DefaultTable[$key]['TableType']==1){
				$fields=count($ColStardard[$key]);
				//$rw=$fields;
				$fields2=count($ColERP[$key]);
				$FiledErr=[];
				$ColTypeErr=[];
				$Fieldtxt='';
				foreach($ColStardard[$key] as $keyField=>$val){//检查字段遍历
					if (isset($ColERP[$key][$keyField])){//字名检查ok
						$ColERP[$key][$keyField]=1;
						if ($ColERP[$key][$keyField]["COLUMN_TYPE"]!=$val['COLUMN_TYPE']){//字段类型检查
							$ColTypeErr[$ColERP[$key]][$keyField]=array($keyField,$val['COLUMN_TYPE']);
						}
					}else {
						//字段名错误
						$ColERP[$key][$keyField]=-1;
						$ColTypeErr[$ColERP[$key]][$keyField]=array($keyField,$val['COLUMN_TYPE']);
						//$FiledErr[$keyField]=$val['COLUMN_NAME'];
					}
				}
			}else{
				$rw=count($ColStardard[$key])+1;
				if ($rw<=2)	
					$rw=2;
				if ($kk==1){
					echo '<tr class="EvenTableRows">';
					$kk=0;
				} else {
					echo '<tr class="OddTableRows">';
					$kk=1;
				}
				echo'<td  rowspan="'.$rw.'" >'.$c.'</td>
						<td  rowspan="'.$rw.'" >'. $key.'</td>';				
					echo'<td  rowspan="'.$rw.'" >缺失'.$rw.'</td>';
				echo'<td ></td>';				
				echo'<td  >'.$row['UPDATE_TIME'].'</td>								
					<td   >'.$row['notes'].'</td>					
					<td   >'.$row['TableType'].'</td>								 
					</tr>'; 
					if ($rw>2){
						foreach($ColStardard[$key] as $keyField=>$val){//检
						
							if ($k==0){
								echo '<tr class="EvenTableRows">';
								$k=1;
							} else {
								echo '<tr class="OddTableRows">';
								$k=0;
							}
							echo'<td >'.$keyField.'</td>';				
							echo'<td  >'.$val['COLUMN_TYPE'].'</td>								
								<td   >'.$val['COLUMN_KEY'].$val['EXTRA'].'</td>					
								<td   >'.$val['COLUMN_DEFAULT'].$val['IS_NULLABLE'].'</td>								 
								</tr>'; 
						}
					}else{
						if ($k==0){
							echo '<tr class="EvenTableRows">';
							$k=1;
						} else {
							echo '<tr class="OddTableRows">';
							$k=0;
						}
					
						
						echo'<td >'.$keyField.'</td>';				
						echo'<td  >'.$val['COLUMN_TYPE'].'</td>								
							<td   >'.$val['COLUMN_KEY'].$val['EXTRA'].'</td>					
							<td   >'.$val['COLUMN_DEFAULT'].$val['IS_NULLABLE'].'</td>								 
							</tr>'; 
					}				
					$c++;  				
			}
	}
			  echo '</table>';

	
}elseif(isset($_POST['AddTable'])){
    //检查系统缺少的表----放弃
	//if(!isset($DefaultCol)||count($DefaultCol)==0){
		$sql="SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, CHARACTER_OCTET_LENGTH, COLUMN_TYPE, COLUMN_KEY,EXTRA 
			FROM COLUMNS 
			WHERE TABLE_SCHEMA='".$_POST['dbasename']."'";
		$ColERP=array();				
		$Result0= mysqli_query($db1,$sql); 
	while ($row = mysqli_fetch_array($Result0)){ 		
		$ColERP[$row['TABLE_NAME']][$row['COLUMN_NAME']]=array("ORDINAL_POSITION"=>$row["ORDINAL_POSITION"] ,"COLUMN_DEFAULT"=>$row["COLUMN_DEFAULT"], "IS_NULLABLE"=>$row["IS_NULLABLE"], "DATA_TYPE"=>$row["DATA_TYPE"], "COLUMN_TYPE"=>$row["COLUMN_TYPE"], "COLUMN_KEY"=>$row["COLUMN_KEY"], "EXTRA"=>$row[ "EXTRA"]);
	} 
 
	$c=1  ;
	$r=1;
	   //var_dump($ColERP);	 
	   $DefaultTable=array_keys($ColStardard) ;//标表名
	   echo '<table class="selection">	
	  			 <tr><th colspan="10">DATABASE:'.$_POST['dbasename'].' 标准表 '.count($DefaultTable).'个</th></tr>';
	   foreach ($DefaultTable as $val_table){
			
			//$coltype=array();
		   if (isset($ColERP[$val_table])){
	
				//ERP字段和标准核对，异常显示
				foreach($ColERP[$val_table] as $key=>$row){
					//字段名
				
					//字段类型比对
					if (isset($ColStardard[$val_table][$key])){
						if($row['DATA_TYPE']!=$ColStardard[$val_table][$key]['DATA_TYPE']){
						    $coltype[$key][]=array('DATA_TYPE',$ColStardard[$val_table][$key]['DATA_TYPE']);
						}
						//主键
						if($row['COLUMN_KEY']!=$ColStardard[$val_table][$key]['COLUMN_KEY']){
						    $coltype[$key][]=array('COLUMN_KEY',$ColStardard[$val_table][$key]['COLUMN_KEY']);
						}
						//自字段
						if($row['EXTRA']!=$ColStardard[$val_table][$key]['EXTRAE']){//&& $row['EXTRA']!=''){
						    $coltype[$key][]=array('EXTRA',$ColStardard[$val_table][$key]['EXTRAE']);
						}
						//是否为空
						if($row['IS_NULLABLE']!=$ColStardard[$val_table][$key]['IS_NULLABLE']){
							
							$coltype[$key][]=array('IS_NULLABLE',$ColStardard[$val_table][$key]['IS_NULLABLE']);
						}
						//默认值
						if(trim($row['COLUMN_DEFAULT'])!=trim($ColStardard[$val_table][$key]['COLUMN_DEFAULT'])){
							
							$coltype[$key][]=array('COLUMN_DEFAULT',$ColStardard[$val_table][$key]['COLUMN_DEFAULT']);
						}

					}else{
						//if (!isset($ColStardard[$val_table][$key])){
							$coltype[$key][]=array("NoName",$key);
						//}
					}
				}
			
				if ( count($coltype)>0){				
					echo '<tr>
							 <th colspan="10">'.$r.' - '.$val_table.'</th></tr>';
					foreach($coltype as  $key=>$row){
						echo '<tr>
						        <td>字段   '.$key.'</td><td colspan="9">';
						foreach($row as $val){									
							echo $val[0].' '.$val[1];					
						}						
					echo'</tr>';
					}
					echo'</tr>
						<tr>
							<th>序号</th>				
							<th>字段名名称</th>
							<th>字段类型</th>
							<th>字段长度</th>
							<th>空/否</th>
							<th>默认值</th>
							<th>主键</th>			
							<th>自增序列</th>
							<th>检查</th>
							<th>状态</th>									
						</tr>';
					foreach($ColERP[$val_table] as $key=>$row){
						if ($k==1){
							echo '<tr class="EvenTableRows">';
							$k=0;
						} else {
							echo '<tr class="OddTableRows">';
							$k=1;
						}
						echo '<td >'.$row['ORDINAL_POSITION'].'</td>
							  <td>';
								if ( isset($coltype[$key])){
													
									echo'<input tabindex="'.($r+$row['ORDINAL_POSITION']).'" type="submit" name="SubmitField" value="' .$key. '" />';
								}else{
									echo $key;
								}			
								
						   echo'</td>
								<td>'.$row['DATA_TYPE'].'</td>
								<td>'.$row['COLUMN_TYPE'].'</td>
								<td>'.$row['IS_NULLABLE'].'</td>
								<td>'.$row['COLUMN_DEFAULT'].'</td>
								<td>'.$row['COLUMN_KEY'].'</td>					
								<td>'.$row['EXTRA'].'</td>';
							if ( isset($coltype[$key])){
								echo'<td>'.$coltype[$key][0][0].':'.$coltype[$key][0][1].'</td>';
							}else{
								echo'<td></td>';
							}
							echo'<td></td>
							</tr>'; 				
					}
					$r++; 
				}
			 }	 
			 unset($coltype);	      
	   }
	
	   if ($c==1){
		echo '<tr><th colspan="10">经过系统比对数据表和字段正确！</th>';
	   }
	  

}elseif(isset($_POST['UpdateTable'])){
    //检查系缺少的表----放弃
	//if(!isset($DefaultCol)||count($DefaultCol)==0){
		$sql="SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, CHARACTER_OCTET_LENGTH, COLUMN_TYPE, COLUMN_KEY,EXTRA 
			FROM COLUMNS 
			WHERE TABLE_SCHEMA='".$_POST['dbasename']."'";
		$ColERP=array();				
		$Result0= mysqli_query($db1,$sql); 
	while ($row = mysqli_fetch_array($Result0)){ 		
		$ColERP[$row['TABLE_NAME']][$row['COLUMN_NAME']]=array("ORDINAL_POSITION"=>$row["ORDINAL_POSITION"] ,"COLUMN_DEFAULT"=>$row["COLUMN_DEFAULT"], "IS_NULLABLE"=>$row["IS_NULLABLE"], "DATA_TYPE"=>$row["DATA_TYPE"], "COLUMN_TYPE"=>$row["COLUMN_TYPE"], "COLUMN_KEY"=>$row["COLUMN_KEY"], "EXTRA"=>$row[ "EXTRA"]);
	} 
 
	$c=1  ;
	$r=1;
	   //var_dump($ColERP);	 
	  // $DefaultTable=array_keys($ColStardard) ;//准表名
	   echo '<table class="selection">	
	  			 <tr><th colspan="10">DATABASE:'.$_POST['dbasename'].' 标准表 '.count($DefaultTable).'个</th></tr>';
	   foreach ($DefaultTable as $val_table){
			
			//$coltype=array();
		   if (isset($ColERP[$val_table])){
	
				//ERP字段和标准核对，异常显示
				foreach($ColERP[$val_table] as $key=>$row){
					//字段名
				
					//字段类型比对
					if (isset($ColStardard[$val_table][$key])){
						if($row['DATA_TYPE']!=$ColStardard[$val_table][$key]['DATA_TYPE']){
						    $coltype[$key][]=array('DATA_TYPE',$ColStardard[$val_table][$key]['DATA_TYPE']);
						}
						//键
						if($row['COLUMN_KEY']!=$ColStardard[$val_table][$key]['COLUMN_KEY']){
						    $coltype[$key][]=array('COLUMN_KEY',$ColStardard[$val_table][$key]['COLUMN_KEY']);
						}
						//自增字段
						if($row['EXTRA']!=$ColStardard[$val_table][$key]['EXTRAE']){//&& $row['EXTRA']!=''){
						    $coltype[$key][]=array('EXTRA',$ColStardard[$val_table][$key]['EXTRAE']);
						}
						//是否为
						if($row['IS_NULLABLE']!=$ColStardard[$val_table][$key]['IS_NULLABLE']){
							
							$coltype[$key][]=array('IS_NULLABLE',$ColStardard[$val_table][$key]['IS_NULLABLE']);
						}
						//默认值
						if(trim($row['COLUMN_DEFAULT'])!=trim($ColStardard[$val_table][$key]['COLUMN_DEFAULT'])){
							
							$coltype[$key][]=array('COLUMN_DEFAULT',$ColStardard[$val_table][$key]['COLUMN_DEFAULT']);
						}

					}else{
						//if (!isset($ColStardard[$val_table][$key])){
							$coltype[$key][]=array("NoName",$key);
						//}
					}
				}
			
				if ( count($coltype)>0){				
					echo '<tr>
							 <th colspan="10">'.$r.' - '.$val_table.'</th></tr>';
					foreach($coltype as  $key=>$row){
						echo '<tr>
						        <td>字段   '.$key.'</td><td colspan="9">';
						foreach($row as $val){									
							echo $val[0].' '.$val[1];					
						}						
					echo'</tr>';
					}
					echo'</tr>
						<tr>
							<th>序号</th>				
							<th>字段名名称</th>
							<th>字段类型</th>
							<th>字段长度</th>
							<th>空/否</th>
							<th>认值</th>
							<th>主键</th>			
							<th>自增序列</th>
							<th>检查</th>
							<th>状态</th>									
						</tr>';
					foreach($ColERP[$val_table] as $key=>$row){
						if ($k==1){
							echo '<tr class="EvenTableRows">';
							$k=0;
						} else {
							echo '<tr class="OddTableRows">';
							$k=1;
						}
						echo '<td >'.$row['ORDINAL_POSITION'].'</td>
							  <td>';
								if ( isset($coltype[$key])){
													
									echo'<input tabindex="'.($r+$row['ORDINAL_POSITION']).'" type="submit" name="SubmitField" value="' .$key. '" />';
								}else{
									echo $key;
								}			
								
						   echo'</td>
								<td>'.$row['DATA_TYPE'].'</td>
								<td>'.$row['COLUMN_TYPE'].'</td>
								<td>'.$row['IS_NULLABLE'].'</td>
								<td>'.$row['COLUMN_DEFAULT'].'</td>
								<td>'.$row['COLUMN_KEY'].'</td>					
								<td>'.$row['EXTRA'].'</td>';
							if ( isset($coltype[$key])){
								echo'<td>'.$coltype[$key][0][0].':'.$coltype[$key][0][1].'</td>';
							}else{
								echo'<td></td>';
							}
							echo'<td></td>
							</tr>'; 				
					}
					$r++; 
				}
			 }	 
			 unset($coltype);	      
	   }
	
	   if ($c==1){
		echo '<tr><th colspan="10">经过系统比对数据表和字段正确！</th>';
	   }
	  

}
	
	
	elseif (isset($_GET['Add9'])){
	//准备放弃

		    $SQL="SELECT `TABLE_NAME`, `COLUMN_NAME`, `COLUMN_DEFAULT`, `IS_NULLABLE`, `COLUMN_TYPE`, `COLUMN_KEY`, `EXTRA`, `TableType` 
					  FROM `tablecolumn` WHERE TABLE_NAME='".$_GET['Add']."'";
			$Result=mysqli_query($dberp,$SQL);
			$AddSql="CREATE TABLE ".$_GET['Add']." ( ";
		
			while($row=mysqli_fetch_array($Result)){
				if (empty($row['COLUMN_DEFAULT'])){
                      $coldft='0';
				}else{
					$coldft=$row['COLUMN_DEFAULT'];
				}
				$AddSql.=$row['COLUMN_NAME'] .' '.$row['COLUMN_TYPE'].' NOT NULL DEFAULT '.$coldft.' ,' ;

			}
			$AddSql=substr($AddSql,0,-1);
			 $AddSql.="  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; ";
			 
				-- 表的索引 `registeraccount`				--
				ALTER TABLE `registeraccount`
				ADD PRIMARY KEY (`regid`,`registerno`,`tag`),
				ADD UNIQUE KEY `custname` (`regid`);
				ALTER TABLE `suppliers`
				ADD PRIMARY KEY (`supplierid`),
				ADD KEY `supplierid` (`supplierid`) USING BTREE;
				ALTER TABLE `salesorders`
				ADD PRIMARY KEY (`orderno`);			
				-- 表的索引 `supptrans`					
				-- 使用表AUTO_INCREMENT `banktransaction`
				--
				ALTER TABLE `banktransaction`
				MODIFY `banktransid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=480; 
				prnMsg($AddSql.$_GET['Add']."表添加成功！",'info');
			}
					
			*/
?>
