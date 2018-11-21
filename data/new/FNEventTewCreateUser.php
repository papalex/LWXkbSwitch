<?php
class FNEventTewCreateUser
{
	/**
	 * @param $args
	 */
	public function run($args)
	{
		$period_files_id = $args;
		$connectionStr = str_replace(';',' ',substr(Yii::app()->db->connectionString,strlen('pgsql:'))).' user='.Yii::app()->db->username.' password='.Yii::app()->db->password;
		$connectionStr = 'host=10.101.3.188 port=5432 dbname=common user='.Yii::app()->db->username.' password='.Yii::app()->db->password;
		z("Начата выгрузка МТС в 1С " );
		//REMARK в следующем запросе оставлены параметры и бинды т.к. полученный результат предыдущего, им соответствует
		$bd = $bd ?? '1970-01-01';

		$uid='?';
		$pp='?';
		$pmp='?';
		$pf='?';
		$office='?';
		$uids = Yii::app()->db->createCommand(<<<SQL
select user_id from "user" where fms=$fms_ and is_disabled =1 and birthday = $bd;
SQL
		)->queryColumn();
		Yii::app()->db->CreateCommand(<<<SQL
update "user" set is_disabled=0, 
		is_visible=0, 
		is_ldap=0, 
		user__id='$uid', 
		private_phone=coalesce('$pp', null), 
		private_mobile_phone=coalesce('$pmp', null), 
		photo_filename=coalesce('$pf', null), 
		dismissal_date=null, 
		pregnancy_date=null
where user_id=$uid;

delete from user_groupe where user_id=$uid;
insert into sendmail (email, subject, body, user_id) values 
	('borisov@finvest.biz', 'Восстановление ранее работавшего', 'Восстановление ранее работавшего', 4000);
SQL
		)->query();
	Yii::app()->admin_db->CreateCommand(<<<MySQL
		INSERT INTO system_job (operator_id, user_id, job_type, date_apply, from_system, status, office_id) VALUES 
	 (4000, {$uid}, 'user_restore', now(), 'suz.rp.ru', '0', {$office})
MySQL
	 	)->query();


	}
}