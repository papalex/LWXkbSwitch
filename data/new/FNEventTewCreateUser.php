<?php
class FNEventTewCreateUser
{
	/**
	 * @param $args
	 */
	public function run($args)
	{
		z("Начато создание пользователя" );
		$params = Yii::app()->db->CreateCommand(<<<SQL
		select 
			(select replace(replace(substring(trim(_varchar) from 1 for 20), '"', ''), '''', '') from task_param
					where task_id=tid
				and task_diag_param='PRIVATE_PHONE') as pp,
			(select replace(replace(substring(trim(_varchar) from 1 for 20), '"', ''), '''', '') from task_param
				where task_id=tid
			  and task_diag_param='PRIVATE_MOBILE') as pmp,
			(select _datetime bd from task_param
				where task_id=tid
					and task_diag_param='BIRTH_DATE') as bd,			
			(select _int from task_param
				where task_id=tid
			  and task_diag_param='IS_REINCARNATION') as isreinc;
			(select replace(replace(replace(_varchar, '"', ''), '''', ''), '  ', ' ') from task_param
				where task_id=tid
				and task_diag_param='FMS') as fms_,
			(select _int from task_param
				where task_id=tid
				and task_diag_param='OFFICE') as office,
			replace(replace(substring(trim(ph._varchar) from 1 for 256), '"', ''), '''', '') as pf, replace(replace(substring(trim(ph._text) from 1 for 256), '"', ''), '''', '') as lpf,
		st.task_id as tid, st.user_id as uid from subtask 
		left join task_param ph
		on ph.task_id=tid
			  and ph.task_diag_param='PHOTO'
			  and ph.is_file=1
			  and ph.is_readonly=1
		where subtask_id=sid) as tp;
			  
SQL
		)->queryRow();
		$bd = $bd ?? '1970-01-01';
		$uid=$params['uid'];;
		$pp=$params['pp']?"'{$params['pp']}'":"null";//если '0' или '' или 0 то null иначе в кавычках;
		$pmp=$params['pmp']?"'{$params['pmp']}'":"null";
		$pf=$params['pf']?"'{$params['pf']}'":"null";
		$office=$params['office']?"'{$params['office']}'":"null";
		$fms_ =$params['fms_'];
		if ($params['isreinc']==1)
		{
			$uids = Yii::app()->db->createCommand(<<<SQL
select user_id from "user" where fms='$fms_' and is_disabled =1 and birthday = $bd;
SQL
			)->queryColumn();
			Yii::app()->db->CreateCommand(<<<SQL
update "user" set is_disabled=0, 
		is_visible=0, 
		is_ldap=0, 
		user__id='$uid',
		private_phone={$pp},
		private_mobile_phone={$pmp},
		photo_filename={$pf},
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
			)->query(); //:90
		}
		else
		{
			$uids=Yii::app()->db->CreateCommand(<<<SQL
			insert into "user" (fms, is_disabled, user__id, private_phone, private_mobile_phone, birthday, photo_filename, is_visible) values (
						'{$fms_}', 0, '$uid', {$pp} ,{$pmp} , {$bd}, {$pf}, 0) returning user_id
SQL
						)->queryScalar();
		}
		//append office
		Yii::app()->db->CreateCommand("insert into user_groupe (user_id, groupe_id, user__id) values ($uids, {$office}, $uid)")->query();
		
		//create_ldap=
		$u=User::model()->findByPk($uids);
		$u->CreateLDAPUser();
		$up=User_param::model()->findByPk($uids);
		$up->krp_company=Yii::app()->db->CreateCommand("select  kcdp.krp_company from task_param tp
			inner join krp_company_department_post kcdp on kcdp.krp_company_department_post_id=tp._int
		where tp.task_id=tid
			  and tp.task_diag_param='KRP_COMPANY'
														order by tp.task_param_id desc
														limit 1")->queryScalar() ;
		$up->user__id=$uid;
		$up->ta_email_inner_user_id=null;
		$up->ta_email_inter_user_id=null;
		$up->ta_contact_id=null;
		$up->ta_company_id=null;
		$up->ta_client_id=null;
		$up->ta_project_ccc_id=null;
		$up->ta_reminder_id=null;
		$up->ta_task_id=null;
		$up->ta_subtask_id=null;
		$up->ta_document_id=null;
		$up->ta_knowledge_id=null;
		$up->ta_mailing_id=null;
		$up->ta_email_list_id=null;
		$up->ta_mail_template_id=null;
		$up->save();
		//:130
		$postmail = Yii::app()->db->createCommand(<<<SQL
	select 
			dept_post.department_groupe_id as dgid, 
			replace(replace(department_name, '"', ''), '''', '') as department,
			replace(replace(post_groupe.name, '"', ''), '''', '') as post
		from department_post dept_post
		left join  groupe dept
			on dept.groupe_id = dept_post.department_groupe_id
			left join groupe as post_groupe on  post_groupe.groupe_id=dept_post.post_groupe_id
		where department_post_id=
				(select _int from task_param where task_id=tid and task_diag_param='DEPARTMENT_POSITION2');	
SQL
		)->queryRow();
		//:139
	}
	public function resamplePhoto($args)
	{
		$src=upload_dir_project."/workflow/".$args['lpf'];
		$dest=$_SERVER["DOCUMENT_ROOT"].User::$imagePath."/".$args['pf'];
		if (file_exists($src) && !is_dir($src))
		{
			if (copy($scr, $dest))
			{
				chown($dest, "wwwrun");
				// Заресэмплить
				UserController::resamplePhoto($args['pf']);
			}
		}
	}
	public function getOld()
	{
		return 
		<<<SQL
		create function fn_td_event_tew_create_user(sid bigint)
	returns SETOF character varying
language plpgsql
as $$
declare
	tid bigint;
	uid integer;

	fms_ varchar;
	create_user varchar;

	office integer;
	append_office varchar;

	append_post varchar;

	create_ldap varchar;

	resample_photo varchar;

	dpid bigint;
	dgid bigint;
	pgid bigint;
	department varchar;
	post varchar;
	cabinet varchar;
	estimated_date date;
	office_name varchar;
	log_in varchar;
	message varchar;
	make_aho_to varchar;
	create_new_ct varchar;
	pp varchar;
	pmp varchar;
	bd date;
	pf varchar;
	lpf varchar;
	pm bigint;
	isok smallint;
	isreinc bigint;
BEGIN
	select task_id, user_id into tid, uid from subtask where subtask_id=sid;
	select _int into isok from task_param where task_id=tid and task_diag_param='IS_OK';

	if ((isok is null) or (isok=0)) then

		select replace(replace(substring(trim(_varchar) from 1 for 20), '"', ''), '''', '') into pp from task_param
		where task_id=tid
			  and task_diag_param='PRIVATE_PHONE';
		select replace(replace(substring(trim(_varchar) from 1 for 20), '"', ''), '''', '') into pmp from task_param
		where task_id=tid
			  and task_diag_param='PRIVATE_MOBILE';
		select _datetime into bd from task_param
		where task_id=tid
			  and task_diag_param='BIRTH_DATE';
		select replace(replace(substring(trim(_varchar) from 1 for 256), '"', ''), '''', ''), replace(replace(substring(trim(_text) from 1 for 256), '"', ''), '''', '') into pf, lpf from task_param
		where task_id=tid
			  and task_diag_param='PHOTO'
			  and is_file=1
			  and is_readonly=1;
		select _int into isreinc from task_param
		where task_id=tid
			  and task_diag_param='IS_REINCARNATION';
		select replace(replace(replace(_varchar, '"', ''), '''', ''), '  ', ' ') into fms_ from task_param
		where task_id=tid
			  and task_diag_param='FMS';
		select _int into office from task_param
		where task_id=tid
			  and task_diag_param='OFFICE';

		-- Генерация кода создания пользователя в СУЗ, LDAP, заданий в Helpdesc

		create_user=null;

		if (bd is null) then bd='1970-01-01'; end if;

		if (isreinc=1) then -- Пользователь уже у нас работал и есть в базе
			select '$uid=' || cast(u.user_id as varchar) || ';'
				   || 'Yii::app()->db->CreateCommand("update \"user\" set is_disabled=0, is_visible=0, is_ldap=0, user__id=' || uid
				   || ', private_phone=' || coalesce('''' || pp || '''', 'null')
				   || ', private_mobile_phone=' || coalesce('''' || pmp || '''', 'null')
				   || ', photo_filename=' || coalesce('''' || pf || '''', 'null')
				   || ', dismissal_date=null, pregnancy_date=null'
				   || ' where user_id=$uid;'
				   || ' delete from user_groupe where user_id=$uid;'
				   || ' insert into sendmail (email, subject, body, user_id) values '
				   || '(''borisov@finvest.biz'', ''Восстановление ранее работавшего'', ''Восстановление ранее работавшего'', 4000);'
				   || '")->query();'
				   || 'Yii::app()->admin_db->CreateCommand("INSERT INTO system_job (operator_id, user_id, job_type, date_apply, from_system, status, office_id) VALUES "'
				   || '."(4000, {$uid}, ''user_restore'', now(), ''suz.rp.ru'', ''0'', ' || office || ')")->query();'
			into create_user from "user" u
			where u.fms=fms_
				  and u.is_disabled=1
				  and u.birthday=bd;
		end if;

		if (create_user is null) then
			create_user='$uid=Yii::app()->db->CreateCommand("insert into \"user\" (fms, is_disabled, user__id, private_phone, private_mobile_phone, birthday, photo_filename, is_visible) values ('''
						|| fms_ || ''', 0, ' || uid || ', ' || coalesce('''' || pp || '''', 'null') || ', '
						|| coalesce('''' || pmp || '''', 'null') || ', ' || coalesce('''' || bd || '''', 'null') || ', '
						|| coalesce('''' || pf || '''', 'null') || ', 0) returning user_id")->queryScalar();';
		end if;

		append_office='Yii::app()->db->CreateCommand("insert into user_groupe (user_id, groupe_id, user__id) values ($uid, ' || office || ', ' || uid ||')")->query();';

		create_ldap='$u=User::model()->findByPk($uid);'
					|| '$u->CreateLDAPUser();'
					|| '$up=User_param::model()->findByPk($uid);'
					|| '$up->krp_company=' || coalesce((select '"' || kcdp.krp_company || '"' from task_param tp
			inner join krp_company_department_post kcdp on kcdp.krp_company_department_post_id=tp._int
		where tp.task_id=tid
			  and tp.task_diag_param='KRP_COMPANY'
														order by tp.task_param_id desc
														limit 1), 'null') || ';'
					|| '$up->user__id=' || uid || ';'
					|| '$up->ta_email_inner_user_id=null;'
					|| '$up->ta_email_inter_user_id=null;'
					|| '$up->ta_contact_id=null;'
					|| '$up->ta_company_id=null;'
					|| '$up->ta_client_id=null;'
					|| '$up->ta_project_ccc_id=null;'
					|| '$up->ta_reminder_id=null;'
					|| '$up->ta_task_id=null;'
					|| '$up->ta_subtask_id=null;'
					|| '$up->ta_document_id=null;'
					|| '$up->ta_knowledge_id=null;'
					|| '$up->ta_mailing_id=null;'
					|| '$up->ta_email_list_id=null;'
					|| '$up->ta_mail_template_id=null;'
					|| '$up->save();';

		select _int into dpid from task_param where task_id=tid and task_diag_param='DEPARTMENT_POSITION2';
		select department_groupe_id into dgid from department_post where department_post_id=dpid;
		select replace(replace(department_name, '"', ''), '''', '') into department from groupe where groupe_id=dgid;
		select post_groupe_id into pgid from department_post where department_post_id=dpid;
		select replace(replace(name, '"', ''), '''', '') into post from groupe where groupe_id=pgid;

		select _datetime into estimated_date from task_param where task_id=tid and task_diag_param='NEW_ESTIMATED_DATE';
		if (estimated_date is null) then
			select _datetime into estimated_date from task_param where task_id=tid and task_diag_param='ESTIMATED_DATE';
		end if;

		select name into office_name from groupe where groupe_id=office;

		select _text into cabinet from task_param where task_id=tid and task_diag_param='CABINET';

		message='На работу выходит новый сотрудник:<br/>'
				|| 'ФИО - ' || fms_ || '<br/>'
				|| 'Дата выхода - ' || estimated_date || '<br/>'
				|| 'Офис - ' || office_name || '<br/>'
				|| 'Кабинет - ' || coalesce(cabinet, '') || '<br/>'
				|| 'Подразделение - ' || department || '<br/>'
				|| 'Должность - ' || post || '<br/>'
				|| 'Ранее работал в КРП - ' || case when isreinc=1 then 'Да' else 'Нет' end;

		select login into log_in from "user" where user_id=(select fn_get_manager_for_role((select fn_get_assistant(dgid))));

		if (log_in is null) then
			select u.login into log_in from "user" u
			where u.user_id=(select ug.user_id from user_groupe ug
				inner join groupe g on g.groupe_id=ug.groupe_id
									   and g.code='HR_DIRECTOR'
							 order by user_groupe_id
							 limit 1)
			limit 1;
		end if;

		make_aho_to='$uname=Yii::app()->helpdesk_db->CreateCommand("select id from glpi_users where name=''' || log_in || '''")->queryScalar();
		$uname=$uname ? $uname : Yii::app()->helpdesk_db->CreateCommand("select id from glpi_users where name=''glpi''")->queryScalar();
		Yii::app()->helpdesk_db->CreateCommand("insert into glpi_tickets (entities_id, name, `date`, status, requesttypes_id, content, urgency, impact, priority, itilcategories_id, date_mod) values
							(7, ''Организация рабочего места нового сотрудника - ' || fms_ || ''', now(), 1, 1, ''' || message || ''', 3, 3, 3, 138, now());")->query();
		$id=Yii::app()->helpdesk_db->CreateCommand("select last_insert_id();")->queryScalar();
		Yii::app()->helpdesk_db->CreateCommand("insert into glpi_tickets_users (tickets_id, users_id, type, use_notification) values ($id, $uname, 1, 1)")->query();
		Yii::app()->helpdesk_db->CreateCommand("insert into glpi_tickets (entities_id, name, `date`, status, requesttypes_id, content, urgency, impact, priority, itilcategories_id, date_mod) values
							(2, ''Организация рабочего места нового сотрудника - ' || fms_ || ''', now(), 1, 1, ''' || message || ''', 3, 3, 3, 110, now());")->query();
		$id=Yii::app()->helpdesk_db->CreateCommand("select last_insert_id();")->queryScalar();
		Yii::app()->helpdesk_db->CreateCommand("insert into glpi_tickets_users (tickets_id, users_id, type, use_notification) values ($id, $uname, 1, 1)")->query();';

		-- Задание назначения на должность формируем в конце обработки первого задания т.к. только тогда можно однозначно выяснить user_id, идентификация по ФИО и офису не однозначна

		append_post='Yii::app()->db->CreateCommand("insert into user_groupe (user_id, groupe_id, user__id) values ($uid, ' || pgid || ', ' || uid ||')")->query();';

		create_new_ct='$scr=str_replace("''", "''"."''", ''Yii::app()->db->CreateCommand("update \"user\" set is_visible=1, user__id=4836 where user_id=''.$uid.''/*' || tid || '*/")->query();'');'
					  || 'Yii::app()->workflow_db->CreateCommand("insert into crond_task (process_date, script) values (''' || estimated_date || ''',''$scr'')")->query();';

		insert into crond_task (script) values (create_user || append_office || create_ldap || make_aho_to || append_post || create_new_ct);

		if ((pf is not null) and (lpf is not null)) then -- Для обработки фотки ставим отдельное задание, вероятность что оно навернется достаточна большая
			-- Скопировать файл
			resample_photo='$src=upload_dir_project."/workflow/' || lpf || '";'
						   || '$dest=$_SERVER["DOCUMENT_ROOT"].User::$imagePath."/' || pf || '";'
						   || 'if (file_exists($src) && !is_dir($src))'
						   || '{'
						   || 'if (copy($scr, $dest))'
						   || '{'
						   || 'chown($dest, "wwwrun");'
						   -- Заресэмплить
						   || 'UserController::resamplePhoto(''' || pf || ''');'
						   || '}'
						   || '}';
			insert into crond_task (script) values (resample_photo);
		end if;

		-- Рассылает уведомления в АХО и Техотдел о смене даты выхода сотрудника
		if ((select _int from task_param
		where task_diag_param='CONFIRM_NEW_DATE_EMPLOYEE'
			  and task_id=tid
			 limit 1)=1) then
			insert into sendmail (email, subject, body, return_name)
				(select email, 'СУЗ - изменена дата выхода нового сотрудника', '<html><body>Дата выхода - '
																			   || coalesce((select _varchar from task_param
				where task_diag_param='FMS' and task_id=tid), 'Сотрудника определить не удалось. Процесс - ' || cast(tid as varchar))
																			   || ' изменена c - '
																			   || coalesce((select to_char(_datetime, 'DD.MM.YYYY') from task_param
				where task_diag_param='ESTIMATED_DATE' and task_id=tid ), 'Изначальную дату не задали. Процесс - ' || cast(tid as varchar))
																			   || ' на - '
																			   || coalesce((select to_char(_datetime, 'DD.MM.YYYY') from task_param
				where task_diag_param='NEW_ESTIMATED_DATE' and task_id=tid ), 'Новую дату не задали. Процесс - ' || cast(tid as varchar))
																			   || '</body></html>', 'СУЗ' from "user" u
					inner join user_groupe ug on ug.user_id=u.user_id
					inner join groupe g on g.groupe_id=ug.groupe_id
				 where g.code in ('KRP_AHO', 'TECH_DEPARTMENT', '_KRP_AHO', '_TECH_DEPARTMENT', 'HEAD_SECRETARY', '_HEAD_SECRETARY', 'SECRETARY'));
		end if;

		-- Отмечаем что пользователя уже создали
		if (isok is null) then insert into task_param (task_id, _int, task_diag_param, user_id) values (tid, 1, 'IS_OK', uid);
		else update task_param set _int=1 where task_diag_param='IS_OK' and task_id=tid;
		end if;
	end if;

	return query (select cast(null as varchar));

END;
$$;


SQL;
		
		
	}
}
