<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class post
{
	
}
error_reporting(E_ALL);
echo 'Test';
$dsn = 'sqlite:data/blog.db';
$db = new PDO($dsn);
$stmt = $db->query('select * from tbl_post');
$result = $stmt->fetchAll(PDO::FETCH_CLASS,'post');
var_dump($result);
?>
<style>

tr:nth-child(odd){background-color:lightgray;}
tr:nth-child(even){background-color:gray;}
.selected{background-color:yellow !important;}
.selected:after{content:" :rowid=" attr(data-rowid);}
</style>
<script type="text/javascript">
	window.onload = function()
	{
		/*(function(ELEMENT) {
    ELEMENT.matches = ELEMENT.matches || ELEMENT.mozMatchesSelector || ELEMENT.msMatchesSelector || ELEMENT.oMatchesSelector || ELEMENT.webkitMatchesSelector;
    ELEMENT.closest = ELEMENT.closest || function closest(selector) {
        if (!this) return null;
        if (this.matches(selector)) return this;
        if (!this.parentElement) {return null}
        else return this.parentElement.closest(selector)
      };
}(Element.prototype));*/
		document.querySelector('.tbl_post').onclick = function(e)
		{
			event = e || window.event;
			var tar = event.target || event.srcElement;
			var row = tar.closest('[data-rowid]');//'.row'
			var butt = tar.getAttribute('data-buttontype');
			switch (butt)
			{
				case 'delete' :
				console.log(butt + ' rowid: '+row.getAttribute('data-rowid'));
				break;
				case 'edit' :
				console.log(butt + ' rowid: '+row.getAttribute('data-rowid'));
				break;
				default :
				row.classList.toggle('selected');
				document.location.replace('#row id is: ' + row.getAttribute('data-rowid'));
			}
			
			/*var quer = row.classList.contains('row');
			while (!quer)
			{
				row = row.parentElement;
				quer = row.classList.contains('row');
			}
			*/
			
			
			//if (tar)
			//document.location.reload();
		}
	}
	</script>
<table class="tbl_post">
	<tr class='row' data-rowid='<?=$result[0]->id?>'>
		<td><div><?=$result[0]->title?></div></td>
		<td><div><?=$result[0]->tags?></div></td>
		<td><div><?=$result[0]->create_time?></div></td>
		<td><div><?=$result[0]->status?></div></td>
		<td><div><button data-buttontype='edit'>E</button><button data-buttontype='delete'>D</button></div></td>
	</tr>
	<tr class='row' data-rowid='<?=$result[1]->id?>'>
		<td><div><?=$result[1]->title?></div></td>
		<td><div><?=$result[1]->tags?></div></td>
		<td><div><?=$result[1]->create_time?></div></td>
		<td><div><?=$result[1]->status?></div></td>
		<td><div><button data-buttontype='edit'>E</button><button data-buttontype='delete'>D</button></div></td>
	</tr>
	<tr class='row' data-rowid='<?=$result[0]->id+2?>'>
		<td><div><?=$result[0]->title?></div></td>
		<td><div><?=$result[0]->tags?></div></td>
		<td><div><?=$result[0]->create_time?></div></td>
		<td><div><?=$result[0]->status?></div></td>
		<td><div><button data-buttontype='edit'>E</button><button data-buttontype='delete'>D</button></div></td>
	</tr>
	<tr class='row' data-rowid='<?=$result[1]->id+2?>'>
		<td><div><?=$result[1]->title?></div></td>
		<td><div><?=$result[1]->tags?></div></td>
		<td><div><?=$result[1]->create_time?></div></td>
		<td><div><?=$result[1]->status?></div></td>
		<td><div><button data-buttontype='edit'>E</button><button data-buttontype='delete'>D</button></div></td>
	</tr>
</table>