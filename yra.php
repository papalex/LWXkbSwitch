<?php

$args = [
	'status'   => FILTER_SANITIZE_ENCODED,
	'buttonid'   => FILTER_SANITIZE_ENCODED,
	];
$filtred = filter_var_array($_POST, $args);
if (isset($filtred['buttonid']))
{
	if ($filtred['buttonid'] === 'status1')
	{
		echo json_encode([
			'status1'=>['status'=>1,'buttonid'=>$filtred['buttonid']],
			'otherbutton'=>['status'=>3,'buttonid'=>'otherbutton']
		]);
	}
	else
	{
		echo json_encode([
			'status1'=>['status'=>4,'buttonid'=>$filtred['buttonid']],			
		]);
	}
	die;

}
?>
<html>
  <head>
    <!-- <meta http-equiv='refresh' content='3'/> !-->
    <title>ESP8266 country house Demo</title>
	<script>
	var buttons = ['mybutt','but2','status1']
	function whenReturn(params,valuesFromPHP)
	{
		values = JSON.parse(valuesFromPHP);
		if (typeof params === 'string' && params !== 'all')
		{
			returnedButtons = [params];//[roomref];
		}
		else if (params === 'all')
		{
			returnedButtons = buttons;
		}
		for(r=0;r<returnedButtons.length;r++)
		{
			var buttId = returnedButtons[r];
			var button = document.getElementById(buttId);
			var valu = values[buttId];
			if (valu)
			{
				if (valu.status === 1){
					button.classList.toggle('status1');// с тоглами могут получаться интересные эффекты
					button.classList.toggle('status2');
					button.classList.remove('status3');
				}
				else if (valu.status === 4)
				{
					button.classList.remove('status1');				
					button.classList.remove('status2');
					button.classList.add('status3');
				}
				document.getElementsByClassName('info')[0].textContent = 'кнопка: '+
						(valu.buttonid === undefined ? 0 : valu.buttonid)
						+ ' статус : ' + (valu.status === undefined ? 0 : valu.status);
			}
		
		}
	}
function callPHP(url,params,callbacktag,json)
{
	//инициализация запроса к серверу
	var httpc = new XMLHttpRequest(); // simplified for clarity != IE 6 and 5.5
	var retval;
	url = url === '' ? "inphp.php" : url;
	httpc.open("POST", url); // sending as POST
	httpc.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	var parmloc = params;// для передачи параметра в указанную функцию его надо сделать локальным (так называемое замыкание)
	var sendparms ='';
	if (typeof params !== 'string')
	{
		//это для случая с несколькими параметрами
		var k = Object.keys(params);
		for(c=0;c<k.length;c++){ sendparms += k[c]+'='+params[k[c]] +(c == k.length - 1 ? '': '&');};
		parmloc = params['buttid'];
	}
	else
	{
		//в нашем случае мы знаем, что передается buttonid
		sendparms = 'buttonid=' + params;
	}	
	httpc.onreadystatechange = function() { //Call a function when the state changes.
	if(httpc.readyState === 4 && httpc.status === 200) { // complete and no errors
		retval = httpc.responseText;//TODO remove unsafety	
		callbacktag(parmloc,retval);
			}
		//тут можно внести обработку ошибок сервера например if (httpc.status === 404)...
		};
	httpc.send(sendparms);
}
</script>
<style>
	li {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: black; 
  box-shadow: inset 2px 2px 4px rgba(0,0,0,.4), inset -2px -2px 4px rgba(255,255,255,.6);
}
@keyframes traffic {
 100%{background: #FB000D;}
}
@keyframes traffic1 {
  100%{background: #FED21D;}
}
@keyframes traffic2 {
  100%{background: #7DFA04;}
}
ul{
	list-style: none;
}
li{
	 margin-bottom:5px;
}
.status1 { 
  animation: traffic 1s linear alternate infinite;
}
.status2{
	background: #FED21D;
}
li:nth-of-type(2) {
 
  animation: traffic1 1s linear alternate infinite;
}
li:nth-of-type(3) {
  animation: traffic2 1.5s linear alternate infinite;
}
.status3
{
	animation: traffic2 1.5s linear alternate infinite;
}

</style>
  </head>
  <body>
	<div class="container1">
<kbd>animation-fill-mode: backwards;</kbd>
<ul>
  <li id="status1" class="status1"></li>
  <li onclick="callPHP('/yra.php','status1',whenReturn);"></li>
  <li onclick="callPHP('/yra.php',{'buttid':'status1','buttonid':'status3'},whenReturn);"></li>
</ul>
</div>
	  <div class="info">&nbsp;</div>
  </body>
</html>
