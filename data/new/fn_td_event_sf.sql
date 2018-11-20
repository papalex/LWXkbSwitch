
--common.public.fn_td_event_sf_create_department
--common.public.fn_td_event_sf_create_department_itr
--common.public.fn_td_event_sf_create_position
create or replace function fn_td_event_sf_create_position(sid bigint)
	returns SETOF character varying
language plpgsql
as $$
declare
	tid bigint;
	uid integer;

	pname varchar(127);
	puid varchar(24);
	pgt varchar(64);
	pgid integer;
	smin integer;
	smax integer;
	v integer;

	script varchar;

BEGIN
	select s.task_id, coalesce(t.shadow_initiator, t.initiator) into tid, uid from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=sid;

	-- Выясняем все ли согласовали
	if ((select count(*)-sum(_int) from task_param
	where task_diag_param in ('IS_STAFF_CHANGE_SALARY_APROVE_ZAM', 'IS_STAFF_CHANGE_SALARY_APROVE_HEAD', 'IS_STAFF_CHANGE_SALARY_APROVE_HEAD_SBE')
		  and task_id=tid)=0) then

		select _varchar into pname from task_param
		where task_id=tid
			  and task_diag_param='STAFF_LIST_POSITION_NEW';

		script='$uid=' || uid || '; $pname="' || pname ||'";';

		-- Проверяем есть ли такая должность в справочнике
		select post into puid from post
		where name=pname;
		if (puid is null) then
			script=script || '$puid=Yii::app()->db->createCommand("insert into post (name, is_visible) values (''{$pname}'', 1) returning post")->queryScalar();';
		else
			script=script || '$puid="' || puid || '";';
		end if;

		-- Создаем должность
		select _int into pgid from task_param
		where task_id=tid
			  and task_diag_param='STAFF_LIST_POSITION';
		script=script || '$pgid=' || pgid || ';';
		select groupe_type into pgt from groupe
		where groupe_id=pgid;

		script=script || '$gid=Yii::app()->db->createCommand("insert into groupe (name, post, is_disabled, groupe_type, user__id)'
			   || ' values (''{$pname}'', ''{$puid}'', 0, ''' || pgt || ''', {$uid}) returning groupe_id")->queryScalar();';

		-- Настраиваем доппараметры должности
		select _int into smin from task_param
		where task_id=tid
			  and task_diag_param='STAFF_CHANGE_SALARY_MIN';
		select _int into smax from task_param
		where task_id=tid
			  and task_diag_param='STAFF_CHANGE_SALARY_MAX';
		select _int into v from task_param
		where task_id=tid
			  and task_diag_param='STAFF_LIST_STAFFING';

		script=script
			   || ' if (Yii::app()->db->createCommand("select count(*) from groupe_param where groupe_id={$gid}")->queryScalar())'
			   || ' Yii::app()->db->createCommand("update groupe_param set min_salary=' || smin ||', max_salary=' || smax ||', vacancy=' || v || ', user__id={$uid} where groupe_id={$gid}")->query();'
			   || ' else Yii::app()->db->createCommand("insert into groupe_param set (min_salary, max_salary, vacancy, user__id, groupe_id) values (' || smin ||', ' || smax ||', ' || v || ', {$uid}, {$gid})")->query();';

		-- Подчиняем должность
		script=script || 'Yii::app()->db->createCommand("insert into groupe_tree (parent_id, child_id, user__id) values ((select fn_get_assistant({$pgid})), {$gid}, {$uid});")->query();';

		insert into crond_task ("script", process_date)
		values (script, (select _datetime from task_param
		where task_id=tid
			  and task_diag_param='APPLY_DATE'));
	end if;

	return query (select cast(null as varchar));

END;
$$;


----------------------------fn_td_event_dae_dismissal_user-----------------------
create function fn_td_event_dae_dismissal_user(sid bigint)
	returns SETOF character varying
language plpgsql
as $$
declare
	tid bigint;
	uid integer;
BEGIN
	select s.task_id, t.initiator
	into tid, uid
	from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=sid;

	if (((select count(*) from task_param
	where task_id=tid
		  and task_diag_param in ('APPROVAL_OF_DEPUTY_DIRECTOR_FOR_WORK_WITH_PERSONNEL_OF_EMPLO',
								  'IN_COORDINATION_WITH_THE_DIVISION_MANAGER_EMPLOYEE', 'IN_CONCERT_WITH_THE_HEAD_OF_SBE_EMPLOYEE'))
		 -(select sum(_int) from task_param
	where task_id=tid
		  and task_diag_param in ('APPROVAL_OF_DEPUTY_DIRECTOR_FOR_WORK_WITH_PERSONNEL_OF_EMPLO',
								  'IN_COORDINATION_WITH_THE_DIVISION_MANAGER_EMPLOYEE', 'IN_CONCERT_WITH_THE_HEAD_OF_SBE_EMPLOYEE')))=0) then

		insert into crond_task (script) values
			('if ($model=User::model()->findByPk(' || (select _int from task_param
			where task_id=tid
				  and task_diag_param='INVITATION_EMPLOYEE') || '))'
			 || '{'
			 || ' $model->dismissal_date="' || (select _datetime from task_param
			where task_id=tid
				  and task_diag_param='DATE_OF_DISMISSAL') || '";'
			 || ' $model->uid=4000;'
			 || ' $model->save();'
			 || '}' );
	end if;

	return query (select cast(null as varchar));

END;
$$;





