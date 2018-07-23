<?php /*var_dump(['abra'=>'cadabra']);//phpinfo();
$src = ['first'=>1,3,'seccond_arr'=>['thirdarr'=>[1,2,3]],1,'gg'=>1];
echo '<pre>';
var_dump($res,$src,count($src));
echo '</pre>';
*/
class sss
{
    const sss = ' + "sss const string"';
}
//$GLOBALS['_'] =function ($val){return $val;};
$a=0;
$_ =function ($data){return $data;};
$_ENV['s'] = function ($data){return $data;};

function test()
{
    ini_set('xdebug.var_display_max_data',1024);
	xdebug_var_dump([<<<STR
 пример использования при помощи данной функции
 класса - ${!${''}=sss::class . sss::sss}  или ${!${''} = date('Y-m-d H:i:s', strtotime('-1 month'))}
 env - {$_ENV['s'](Object::class)} - удивительное рядом: Object::class возвращает 'Object' несмотря на отсутствие объявления 
 global  - {$GLOBALS['_'](RealGlobal::class)} - удивительное рядом2: RealGlobal::class возвращает 'RealGlobal' несмотря на отсутствие объявления
 фукций - ${!${''}=(implode(' | ', ["массива", Dictonary::class, '"еще что-нибудь"']))} - натуральная черная магия $ {!${''}=
 фукций - ${!${'_'}=(implode(' | ', ["массива", Dictonary::class, '"еще что-нибудь"']))} - натуральная черная магия 
STR
	]);
}
test();
//phpinfo();
die;
?>
