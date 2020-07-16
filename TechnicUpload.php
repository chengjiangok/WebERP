
<?php
/* $Id:  chengjiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-07-01 06:21:54 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-07-01 06:41:34
 */
include('includes/DefineImportBankTransClass.php');

include ('includes/session.php');
$Title = '导入工艺单';
$ViewTopic = 'Production';// Filename's id in ManualContents.php's TOC.
$BookMark = 'ImportTechnic';
include('includes/header.php');
echo'<script type="text/javascript">
		function sltproduct(obj){
		
  			window.location.href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?myslt="+obj.value ;
 		}
		 function computetime1(myslt){
        	var jsn=document.getElementById("techquota").value;
	        var sltval =myslt.value;					
            var techcode=(document.getElementById("technics").value).split("^")[0];
			
            var list=(sltval.split("^")[1]).split("*"); 
		    var maxspec=Math.max.apply(this,  list);// 数组中的最大值
			var obj = eval(jsn);		//var obj= JSON.parse(jsn);
			var qut=0;			
				for(var i=0; i<obj.length; i++)  
  				{ 
   					 if ((maxspec>= obj[i].specmin) &&( maxspec<=obj[i].specmax) && (obj[i].techid=techcode))
					{ 
						qut=obj[i].quota;
						break;
					}
  				}   
			document.getElementById("extraquant").value=qut;		
		}
		function computetime(myslt){
             //alert(myslt.value);
        	 var jsn=document.getElementById("techquota").value;
			 var sltval = document.getElementById("companents").value;
             //var techcode=Number((document.getElementById("technics").value).split("^")[0]);
		 	var techcode=Number((myslt.value).split("^")[0]);
			 // console.log(techcode); 
              var list=(sltval.split("^")[0]).split("*"); 		    
			 //console.log(sltval.split("^")[0]); 
			    var maxspec=Number(Math.max.apply(this,  list));
			//	alert(maxspec);
				var obj= JSON.parse(jsn);				
				var temp = []; 	
					var str="";			
				for(var i=0; i<obj.length; i++)  
  				{  
   					 temp[i]= (function(n){				  
					if (Number(obj[n].techid)==techcode){		
						if ((maxspec>=Number( obj[n].specmin)) && (maxspec<=Number(obj[n].specmax)))
						{	   
							str= obj[n].quota;   
						}   
					}
       				 })(i);  
                
  				}
			
				document.getElementById("extraquant").value=str;
				     //根据id查找对象，
			  	
			    //alert("hello");  
         		 var sltobj=document.getElementById("equipment");  
               //添加一个选项  
     		    // obj.add(new Option("文本","值"));				     //这个只能在IE中有效  
          	    sltobj.options.length=0;  
			  	sltobj.options.add(new Option("数控中心","1")); //这个兼容IE与firefox  
 	            sltobj.options.add(new Option("电火花","2")); //这个兼容IE与firefox  

		}
	function fun() {  
          window.location.reload();
    }  
</script>';
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/bank.png" title="' .// Icon image.
	$Title.'" /> ' .// Icon title.
	$Title . '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');
include('includes/CurrenciesArray.php');
   $category='H80';

    echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="ImportFormat" value="' . $_POST['ImportFormat'] . '" />
		<input type="hidden" name="unittag" value="' .$_POST['unittag'] . '" />
		<table class="selection">';
		$sql="SELECT code, subcategorydspn, flg FROM stocksubcategory WHERE stocktype='M' AND LEFT(categoryid,1)=1 AND code LIKE '".$category."%'";		
		$result=DB_query($sql);
		echo '<tr>
				<td>产品分类:</td>
				<td><select name="product" id="product" onchange="sltproduct(this)">'	;
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['product']) AND $_POST['product']==$myrow['code']){
				echo '<option selected="selected" value="' . $myrow['code'] . '">' . $myrow['code'].' - ' .htmlspecialchars($myrow['subcategorydspn'], ENT_QUOTES,'UTF-8', false) . '</option>';
			} else {
				echo '<option value="' . $myrow['code'] . '">' . $myrow['code'].' - ' .htmlspecialchars($myrow['subcategorydspn'], ENT_QUOTES,'UTF-8', false)  . '</option>';
			}
		}
		echo '</select></td>
		      <td><select name="stock" style="width:150px">'	;
			    
				$sql="SELECT stockid code, categoryid, description, longdescription, units FROM stockmaster WHERE stockid LIKE '".$category."%'";
				$result=DB_query($sql);
				while ($myrow=DB_fetch_array($result)){
					if (isset($_POST['stock']) AND $_POST['stock']==$myrow['code']){
						echo '<option selected="selected" value="' . $myrow['code'] . '">'  .htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8', false) . '</option>';
					} else {
						echo '<option value="' . $myrow['code'] . '">'  .htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8', false)  . '</option>';
					
					}
				}
		echo '</select>
				</td></tr>';
		echo'<tr>
				 <td>查询方式</td>
	             <td colspan="2"><input type="radio"   name="query" value="0" checked/>全部
				 <input type="radio"    name="query" value="1"  />未完成
				  <input type="radio"    name="query" value="2"/>完成
		         </td>
			 </tr>';
		echo'<tr><td colspan="3"><HR style="border:1 dashed #987cb9" width="90%" SIZE=1>
</td></tr>';
	 echo'<tr>
				 <td>上传文件(Excel)</td>
	             <td colspan="2"><input type="file" id="ImportFile"  title="' . _('Select the file that contains the bank transactions in MT940 format') . '" name="ImportFile">
		         </td>
			 </tr>
        </table>';
	echo '<div class="page_help_text">使用简介：上传保存，只需要选择文件，然后点击上传即可，<br>
	                                    查询文件，直接点击，查询已经上传的所有文件，查询组件，需要选择模具编号，然后点击查询！
		</div>';
     echo'<div class="centre">
		     <input type="submit" name="Search" value="查询文件">
		    <input type="submit" name="showcomp" value="查询组件">
			<input type="submit" name="Import" value="上传保存">
			
		</div>
        </form>';

if (isset($_POST['Import'])&& isset($_FILES['ImportFile'])){
    $msg='';
	if(isset($_FILES['ImportFile']) && $_FILES['ImportFile']['name']!=''){
 
		//prnMsg($_FILES['ImportFile']['name'],'info');
		$file_size=$_FILES['ImportFile']['size'];  
		if($file_size>2*1024*1024) {  
			prnMsg("文件过大，不能上传大于2M的文件",'info');  
				include('includes/footer.php');
				exit;
				$ReadTheFile ='No';
     
  	    }  
	
		$file_type=$_FILES['ImportFile']['type'];  
		
		if($file_type!="application/vnd.ms-excel" && $file_type!='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {  
			prnMsg( "文件类型只能为Excel格式",'info');   
				include('includes/footer.php');
				exit;
				$ReadTheFile ='No';
			} 
			//查询是否已经上传文件
			$dir= mb_substr($_SESSION['EDI_MsgSent'],0, strrpos($_SESSION['EDI_MsgSent'],'/'))."/workhour/".substr($_SESSION['lastdate'],0,4)."/";  
           
				//获取某目录下所有文件、目录名（不包括子目录下文件、目录名）  
				$handler = opendir($dir);  
				while (($filename = readdir($handler)) !== false) {//务必使用!==，防止目录下出现类似文件名“0”等情况  
					if ($filename != "." && $filename != "..") {  
							$files[] = $filename ;  
					}  
				}  
			  
				closedir($handler);  
				
			
			foreach ($files as $value) {  
				if (substr( $_FILES['ImportFile']['name'],0,strrpos( $_FILES['ImportFile']['name'],"."))==substr($value,0,(strrpos($value,".")-4))){
				  prnMsg('已经上传过该文件！','info');
				  include ('includes/footer.php');
				  exit;
				}
			}  
			
		
			if(is_uploaded_file($_FILES['ImportFile']['tmp_name'])) {  
				//if($_FILES['ImportFile']['tmp_name']=='') {  
				$uploaded_file=$_FILES['ImportFile']['tmp_name'];  
				$move_to_path= mb_substr($_SESSION['EDI_MsgSent'],0, strrpos($_SESSION['EDI_MsgSent'],'/')).'/workhour/'.substr($_SESSION['lastdate'],0,4).'/';
					//判断该用户文件夹是否已经有这个文件夹  
				if(!file_exists($user_path)) {  
					mkdir($user_path);  
				}      
				$file_true_name= preg_replace("/[^A-Za-z0-9\.\-\_]/","",$_FILES['ImportFile']['name']); 

				$file_name=substr($file_true_name,0,strrpos($file_true_name,".")).'_'.rand(100,999).substr($file_true_name,strrpos($file_true_name,".")); 
		
				if(move_uploaded_file($uploaded_file,$move_to_path.$file_name)) { 
					
						$sql="INSERT INTO technicupload(
														stockid,
														components,
														techid,
														technicprogram,
														urlfile,
														uploaddate,
														remark,
														flg
													)	
												VALUES('',
												'',
												'',
												'',
												'".$file_name."',
												'". date('Y-m-d h:m:s',time())."',
												'',	0)";
								$result=DB_query($sql);

				//prnMsg( $_FILES['ImportFile']['name']."上传成功",'info');  
				$msg=$_FILES['ImportFile']['name']."上传成功!";
				} else {  
				prnMsg("上传失败",'info');  
				}  
			} else {  
				prnMsg("上传失败!",'info');  
		} 
	}
	
	  $insertrow=0;		
	if ( $_FILES['ImportFile']['error']==0){
		$inputFileName=mb_substr($_SESSION['EDI_MsgSent'],0, strrpos($_SESSION['EDI_MsgSent'],'/'))."/workhour/".substr($_SESSION['lastdate'],0,4)."/".$file_name;
		
				require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
			
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

				$objreader= new PHPExcel_Reader_Excel5();
				$objPHPExcel = $objreader->load($inputFileName);
				//或者你也可以用PHPExcel_IOFactory中的createReader()方法来声明你的对象，
				$inputFileType = 'Excel5';
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($inputFileName);
				$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
				$sheet = $objPHPExcel->getSheet(0); // 读取第一個工作表
				$excelRow = $sheet->getHighestRow(); // 取得总行数
				$excelColumm = $sheet->getHighestColumn(); // 取得总列数
				$startrow=31;
				$endrow=0;

			 $flagerr=0;
			 $title=str_replace(' ','',$sheet->getCell("A1")->getValue());	
  		 $substr=array('CNC程序','CNC加工','电极下料');
		$tl=0;
		for($i=0;$i<3;$i++){
			if ( gettype(mb_strpos($title,$substr[$i]))=="integer"){
			$tl=$i;
			}
		}		
		if ( $tl==0){
			echo $substr[$tl].'-'.$tl.'<br>';
		}elseif($tl==1){
			echo $substr[$tl].'-'.$tl.'<br>';
		}elseif($tl==2){
			echo $substr[$tl].'-'.$tl.'<br>';
		}
	if ($tl==0){
		//CNC程序清单	
                $stkid=$category.explode('-',$sheet->getCell("E2")->getValue())[0];
				$sql="UPDATE  technicupload
								SET  stockid = '".$stkid."',
									components = '".$category.$sheet->getCell("E2")->getValue()."'
							WHERE  urlfile  ='".$file_name."'";
			$result=DB_query($sql);	
			$sql="INSERT  IGNORE INTO  components( stockid,
							compcode,
								comptype,
							description,
							spec,
							netspec,
							procedur,
							technics,
							weight,
							quantity,
							flag	)
					VALUES('".$stkid."',
							'".$category.$sheet->getCell("E2")->getValue()."',
							'1',
							'".$category.$sheet->getCell("E2")->getValue()."',
							'".str_replace('X','*',str_replace('x','*',$sheet->getCell("J3")->getValue()))."',
							'',
							'','',
							'0','1','0')";	
		$result=DB_query($sql);
		$startrow=15;						     					     					     
		$endrow=0;
				
		for($i=$excelRow;$i>0;$i--){
			if ($sheetData[$i]['B']!=''){
			$endrow=$i;
			break;
			}

		}
	
		 for ($r=$startrow; $r <=$endrow ; $r++) { 	

			$sql="INSERT IGNORE  INTO technicsprogram(
								stockid,
								compcode,
								program,
								description,
								planquanttity,
								techid,
								technics,
								quantity,
								flag)
						VALUES('".$stkid."',
						'".$category.$sheet->getCell("E2")->getValue()."',
						'".$sheet->getCell("B".$r)->getValue()."',
						'".$sheet->getCell("D".$r)->getValue().' '.$sheet->getCell("J".$r)->getValue()."',
						'".$sheet->getCell("I".$r)->getValue()."',
						'1',
						'".$sheet->getCell("J".$r)->getValue()."',
						'1','0'	)";	
				$result=DB_query($sql);	
			} 
	}elseif ($tl==1){
	    //'CNC加工程式清单			
            $stkid=strtoupper($sheet->getCell("T2")->getValue());
			$sql="UPDATE  technicupload
								SET  stockid = '".$stkid."',
									components = '".$category.$sheet->getCell("L2")->getValue()."'
																
								WHERE  urlfile  ='".$file_name."'";
			$result=DB_query($sql);	
			$sql="INSERT IGNORE  INTO  components( stockid,
							compcode,
								comptype,
							description,
							spec,
							netspec,
							procedur,
							technics,
							weight,
							quantity,
							flag	)
					VALUES('".$stkid."',
							'".$category.$sheet->getCell("L2")->getValue()."',
							'1',
							'".$category.$sheet->getCell("L2")->getValue()."',
							'".str_replace('X','*',str_replace('x','*',$sheet->getCell("Z20")->getValue()))."',
							'',
							'','',
							'0','1','0')";	
		$result=DB_query($sql);
		$startrow=31;						     					     					     
		$endrow=0;
				
		for($i=$excelRow;$i>0;$i--){
			if ($sheetData[$i]['C']!=''){
			$endrow=$i;
			break;
			}

		}
		//	prnMsg($endrow.'='.$excelRow,'info');
		 for ($r=$startrow; $r <=$endrow ; $r++) { 	
            $hms= totime($sheet->getCell("AD".$r)->getValue(),'m');
			$sql="INSERT  IGNORE INTO technicsprogram(
								stockid,
								compcode,
								program,
								description,
								planquanttity,
								techid,
								technics,
								quantity,
								flag)
						VALUES('".$stkid."',
						'".$category.$sheet->getCell("L2")->getValue()."',
						'".$sheet->getCell("C".$r)->getValue()."',
						'".$sheet->getCell("I".$r)->getValue().' '.$sheet->getCell("H".$r)->getValue()."',
						'".$hms."',
						'1',
						'".$sheet->getCell("H".$r)->getValue()."',
						'1','0'	)";	
			$result=DB_query($sql);
		
	 	}		
	}elseif ($tl==2){
		//'电极
		    //  $stkid=strtoupper($sheet->getCell("C3")->getValue());
			$stkid=strtoupper($category.explode('-',$sheet->getCell("C3")->getValue())[0]);
			$sql="UPDATE  technicupload
								SET  stockid = '".$stkid."',
									components = '".strtoupper($category.$sheet->getCell("C3")->getValue())."'
								WHERE  urlfile  ='".$file_name."'";
			$result=DB_query($sql);	
			$startrow=5;						     					     					     
			$endrow=0;
				
		for($i=$excelRow;$i>0;$i--){
			if ($sheetData[$i]['B']!=''){
			$endrow=$i;
			break;
			}

		}
		for ($r=$startrow; $r <=$endrow ; $r++) {
            if(is_numeric($sheet->getCell("J".$r)->getValue())){
				$weight=$sheet->getCell("J".$r)->getValue();
			}else{
				$weight=0;
			}
			 if(is_numeric($sheet->getCell("F".$r)->getValue())){
				$qut=$sheet->getCell("F".$r)->getValue();
			}else{
				$qut=0;
			}
 			if($sheet->getCell("D".$r)->getValue()==''){
				$spec=$sheet->getCell("E".$r)->getValue();
			}else{
				$spec=$sheet->getCell("D".$r)->getValue();
			}
			$sql="INSERT  IGNORE INTO  components( stockid,
							compcode,
							comptype,
							description,
							spec,
							netspec,
							procedur,
							technics,
							weight,
							quantity,
							flag)
					VALUES('".$stkid."',
							'".strtoupper($category.$sheet->getCell("B".$r)->getValue())."',
							'2',
							'".strtoupper($category.$sheet->getCell("B".$r)->getValue())."',
							'".str_replace('X','*',str_replace('x','*',$spec))."',
							'".str_replace('X','*',str_replace('x','*',$sheet->getCell("E".$r)->getValue()))."',
							'','',
							'".filter_number_format($weight)."',
							'".filter_number_format($qut)."',
							'0')";	
		$resultcomp=DB_query($sql);
		
		if($resultcomp!=1){
            $insertrow++;
	     }   // $hms= totime($sheet->getCell("AD".$r)->getValue(),'m');
			$sql="INSERT IGNORE  INTO technicsprogram(
								stockid,
								compcode,
								program,
								description,
								planquanttity,
								techid,
								technics,
								quantity,
								flag)
						VALUES('".$stkid."',
						'".$category.$sheet->getCell("B".$r)->getValue()."',
						'".$sheet->getCell("B".$r)->getValue()."',
						'电极',
						'0',
						'1',
						'电极加工',
						'".filter_number_format($qut)."',
						'0'	)";	
			$resulttg=DB_query($sql);
			if ($resulttg!=1){
				$insertrow++;
			}
			
		}	//FOR

		if ($insertrow==0){
				$msg.=' 但没有内容插入,文件已删除!';
				$sql="DELETE FROM technicupload					
						WHERE  urlfile='".$file_name."'";
				if (unlink($move_to_path.$file_name)) {
					$result=DB_query($sql);
				}			
		}

		}
		if($msg!=''){
			prnMsg($msg,'info');
		}
		
	}//if
	
}elseif(isset($_POST['Import'])){
	prnMsg($_FILES['ImportFile']['name'].'你没有选择！','info');
}elseif(isset($_POST['Search'])){
		//
			$dir= mb_substr($_SESSION['EDI_MsgSent'],0, strrpos($_SESSION['EDI_MsgSent'],'/')).'/workhour/'.substr($_SESSION['lastdate'],0,4).'/';
	
				//获取某目录下所有文件、目录名（不包括子目录下文件、目录名）  
				$handler = opendir($dir);  
				while (($filename = readdir($handler)) !== false) {//务必使用!==，防止目录下出现类似文件名“0”等情况  
					if ($filename != "." && $filename != "..") {  
							$files[] = $filename ;  
					}  
				}  
			  
				closedir($handler);  
		
			foreach ($files as $value) {  
			
			}  
         $sql="SELECT id,stockid, components, techid, technicprogram, urlfile, uploaddate, remark, flg FROM technicupload WHERE 1";
	$result=DB_query($sql);
	
	echo '<br /><table class="selection">';
	echo '<tr>
	        <th>序号</th>
			<th class="ascending">产品编码</th>
			<th class="ascending">产品名称</th>
			<th class="ascending">组件</th>
			<th class="ascending">文件名</th>
			<th class="ascending">工艺类别</th>
			<th class="ascending">上传日期</th>
			<th>状态</th>
			<th>下载</th>
			<th>删除</th>
		
		</tr>';

	$k=0; //row colour counter
    $n=1;
	
	while ($myrow=DB_fetch_array($result)){
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href='\%s'>下载</a></td>
			<td><a href=\"%sSelectedAccount=%s&amp;delete=1\" onclick=\"return confirm('" . _('Are you sure you wish to delete this account? Additional checks will be performed in any event to ensure data integrity is not compromised.') . "');\">" . _('Delete') . "</a></td>
			</tr>",
			$n,
			$myrow['stockid'],
			htmlspecialchars($myrow['stockid'],ENT_QUOTES,'UTF-8'),
			$myrow['components'],
			$myrow['urlfile'],
			$myrow['techid'],		
			$myrow['uploaddate'],			
			$myrow['flg'],
			$dir .$myrow['urlfile'],								
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',	
			$myrow['id']);
		
			$n++;

	}
	//END WHILE LIST LOOP
	echo '</table>';

}elseif(isset($_POST['showcomp'])){
	//prnMsg($_POST['stock'].'comp选择！','info');
	$sql="SELECT	compcode,
					comptype,
					description,
					spec,
					netspec,
					procedur,
					technics,
					weight,
					quantity,
					flag
				FROM
					components
				WHERE
					stockid ='".$_POST['stock']."'";
	$resultcom=DB_query($sql);
	$sql="SELECT compcode,
					program,
					description,
					planquanttity,
					techid,
					technics,
					quantity,
					flag
				FROM
					technicsprogram
				WHERE
					stockid='".$_POST['stock']."'";
	$resulttp=DB_query($sql);
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="9">'.$_POST['stock'].' 工件</th>
			</tr>
		  <tr>
	        <th>序号</th>
			<th class="ascending">组件</th>
			<th class="ascending">描述</th>
			<th class="ascending">规格</th>
			<th class="ascending">精确规格</th>
			<th>工艺</th>		
			<th>重量(kg)</th>
			<th>数量</th>
			<th></th>		
		</tr>';
	$k=0; //row colour counter
    $n=1;	
	while ($myrow=DB_fetch_array($resultcom)){
		if ($myrow['comptype']==1){
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
			printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%sdel=%s\" onclick=\"return confirm('确定要删除吗？');\">" . _('Delete') . "</a></td>
			</tr>",
			$n,
			$myrow['compcode'],
			$myrow['description'],
			$myrow['spec'],		
			$myrow['netspec'],
			$myrow['technics'],				
			$myrow['weight'],
			$myrow['quantity'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',	
			$myrow['compcode']);		
			$n++;
		}
	}
	echo '<tr>
			<th colspan="9">'.$_POST['stock'].' 电极件</th>
			</tr>
		  <tr>
	        <th>序号</th>
			<th class="ascending">组件</th>
			<th class="ascending">描述</th>
			<th class="ascending">规格</th>
			<th class="ascending">精确规格</th>
			<th>工艺</th>		
			<th>重量(kg)</th>
			<th>数量</th>
			<th></th>		
		</tr>';
		$n=1;
		DB_data_seek($resultcom,0);
		while ($myrow=DB_fetch_array($resultcom)){
		if ($myrow['comptype']==2){
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%sdel=%s\" onclick=\"return confirm('确定要删除?');\">" . _('Delete') . "</a></td>
			</tr>",
			$n,
			$myrow['compcode'],
			$myrow['description'],
			$myrow['spec'],		
			$myrow['netspec'],
			$myrow['technics'],				
			$myrow['weight'],
			$myrow['quantity'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',	
			$myrow['compcode']);		
			$n++;
		}
	}

	//END WHILE LIST LOOP
	echo '</table>';
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="8">'.$_POST['stock'].' 工艺</th>
			</tr>
			<tr>
	        <th>序号</th>
			<th class="ascending">组件</th>
			<th class="ascending">工艺编码</th>
			<th class="ascending">描述</th>		
			<th class="ascending">加工工艺</th>
			<th class="ascending">计划工时</th>			
			<th>数量</th>
			<th></th>
		</tr>';
	$k=0; //row colour counter
    $n=1;	
	while ($myrow=DB_fetch_array($resulttp)){
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
			printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%sdel=%s\" onclick=\"return confirm('确定要删除吗?');\">" . _('Delete') . "</a></td>
			</tr>",
			$n,
			$myrow['compcode'],
			$myrow['program'],	
			$myrow['description'],				
			$myrow['technic'],			
			$myrow['planquanttity'],
			$myrow['quantity'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',	
			$myrow['program']);		
			$n++;
	}
	
	//END WHILE LIST LOOP
	echo '</table>';

}//end isset($_POST['Import']

include ('includes/footer.php');
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

?>
