----------------fn_td_event_sf_resubordinate------------------
----------------fn_td_event_sf_change_position----------------
----------------fn_td_event_sf_disabled_position--------------
----------------fn_td_event_sf_disabled_position_itr----------
----------------fn_td_event_sf_disabled_department------------
----------------fn_td_event_sf_disabled_department_itr--------
-----------fn_td_event_sf_create_position_itr---тольк удалил закоменченое и неиспользуемое----
----------------fn_td_event_dae_dismissal_user----------------
create or replace function fn_td_event_sf_change_position(sid bigint)
	returns SETOF character varying
language plpgsql
as $$
declare
	tid bigint;
	uid integer;

	gid bigint;
	uid2 bigint;

BEGIN
	select s.task_id, coalesce(t.shadow_initiator, t.initiator) into tid, uid from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=sid;

	-- Выясняем все ли согласовали
	if ((select count(*)-sum(_int) from task_param
	where task_diag_param in ('APPROVE_ZAM_HEAD_OF_STAFF_TRANSFER_TO_ANOTHER_POSITION', 'IN_CONCERT_WITH_UNIT_MANAGER_TRANSLATION_ANOTHER_POST')
		  and task_id=tid)=0) then

		select _int into gid from task_param
		where task_id=tid
			  and task_diag_param='UNIT_STAFF';

		select cast(_int as integer) into uid2 from task_param
		where task_id=tid
			  and task_diag_param='INVITATION_EMPLOYEE';

		insert into crond_task ("script", process_date)
		values('Yii::import("core_app.components.action.FNEventChangePosition");(new FNEventChangePosition)->run(["uid2"=>"' || uid2 || '","uid"=>"' || uid || '","gid"=>"' || gid ||'");',
				(select _datetime from task_param
				where task_id=tid
					  and task_diag_param='DATE_TRANSFER_TO_ANOTHER_POSITION'));
	end if;

	return query (select cast(null as varchar));

END;
$$;
create function fn_td_event_sf_resubordinate(sid bigint)
	returns SETOF character varying
language plpgsql
as $$
declare
	tid bigint;
	uid integer;

	ogid integer;
	ngid integer;
	gid integer;

	script varchar;

BEGIN
	select s.task_id, coalesce(t.shadow_initiator, t.initiator) into tid, uid from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=sid;

	-- Выясняем все ли согласовали
	if ((select count(*)-sum(_int) from task_param
	where task_diag_param in ('IS_IN_CONCERT_WITH_THE_DEPUTY_DIRECTOR_ON_WORK_WITH_PERSONNEL', 'IS_IN_CONCERT_WITH_UNIT_MANAGER')
		  and task_id=tid)=0) then

		select _int into ngid from task_param
		where task_id=tid
			  and task_diag_param='STAFF_LIST_POSITION_NEW';
		select _int into gid from task_param
		where task_id=tid
			  and task_diag_param='UNIT_STAFF';

		select t into ogid from fn_get_department_for_role(gid) as t;

		insert into crond_task ("script", process_date)
		values ('Yii::import("core_app.components.action.FNEventResubordinate");(new FNEventResubordinate)->run(["tid"=>"' || tid || '","ngid"=>"' || ngid || '","uid"=>"' || uid || '","ogid"=>"' || ogid || '","gid"=>"' || gid ||'");', (select _datetime from task_param
		where task_id=tid
			  and task_diag_param='APPLY_DATE_SUBORDINATION_POSITIONS'));
	end if;

	return query (select cast(null as varchar));

END;
$$;


create function fn_td_event_sf_disabled_position(sid bigint)
	returns SETOF character varying
language plpgsql
as $$
declare
	tid bigint;
	uid integer;

	gid integer;

BEGIN
	select s.task_id, coalesce(t.shadow_initiator, t.initiator) into tid, uid from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=sid;

	-- Выясняем все ли согласовали
	if ((select count(*)-sum(_int) from task_param
	where task_diag_param in ('IS_STAFF_CHANGE_SALARY_APROVE_HEAD_SBE', 'IS_STAFF_CHANGE_SALARY_APROVE_ZAM', 'IS_APPROVAL_OF_THE_HEAD')
		  and task_id=tid)=0) then

		select _int into gid from task_param
		where task_id=tid
			  and task_diag_param='UNIT_STAFF';
		--FNEventDisabledPosition
		insert into crond_task ("script", process_date)
		values('Yii::import("core_app.components.action.FNEventDisabledPosition");(new FNEventDisabledPosition)->run(["uid"=>"' || uid || '","gid"=>"' || gid ||'");', 
		(select _datetime from task_param
		where task_id=tid
			  and task_diag_param='APPLY_DATE'));
	end if;

	return query (select cast(null as varchar));

END;
$$;

create function fn_td_event_sf_disabled_position_itr(sid bigint)
	returns SETOF character varying
language plpgsql
as $$
declare
	tid bigint;
	uid integer;
	gid integer;

BEGIN
	select s.task_id, coalesce(t.shadow_initiator, t.initiator) into tid, uid from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=sid;

	-- Выясняем все ли согласовали
	if ((select count(*)-sum(_int) from task_param
	where task_diag_param in ('COORDINATED_DEPUTY_ZAM_DIR_HR_REDUCTION_POSTS_ITR')
		  and task_id=tid)=0) then

		select _int into gid from task_param
		where task_id=tid
			  and task_diag_param='UNIT_STAFF_ITR_NEW';
--функция такая же только параметры другие выбираются
		insert into crond_task ("script", process_date)
		values ('Yii::import("core_app.components.action.FNEventDisabledPosition");(new FNEventDisabledPosition)->run(["uid"=>"' || uid || '","gid"=>"' || gid ||'");'
		, (select _datetime from task_param
		where task_id=tid
			  and task_diag_param='APPLY_DATE_ITR'));
	end if;

	return query (select cast(null as varchar));

END;
$$;

create function fn_td_event_sf_disabled_department(sid bigint)
	returns SETOF character varying
language plpgsql
as $$
declare
	tid bigint;
	uid integer;

	gid integer;

BEGIN
	select s.task_id, coalesce(t.shadow_initiator, t.initiator) into tid, uid from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=sid;

	-- Выясняем все ли согласовали
	if ((select count(*)-sum(_int) from task_param
	where task_diag_param in ('APPROVAL_OF_THE_HEAD_ZAM_REDUCTION_UNIT', 'APPROVAL_OF_THE_HEAD_REDUCTION_UNIT', 'IN_CONCERT_THE_HEAD_SBE_REDUCTION_UNIT')
		  and task_id=tid)=0) then

		select _int into gid from task_param
		where task_id=tid
			  and task_diag_param='STAFF_LIST_POSITION';

		insert into crond_task ("script", process_date)
		values ('Yii::import("core_app.components.action.FNEventDisabledDepartment");(new FNEventDisabledDepartment)->run(["uid"=>"' || uid || '","gid"=>"' || gid ||'");'
		, (select _datetime from task_param
		where task_id=tid
			  and task_diag_param='APPLY_DATE'));
	end if;

	return query (select cast(null as varchar));

END;
$$;

create function fn_td_event_sf_disabled_department_itr(sid bigint)
	returns SETOF character varying
language plpgsql
as $$
declare
	tid bigint;
	uid integer;

	gid integer;

BEGIN
	select s.task_id, coalesce(t.shadow_initiator, t.initiator) into tid, uid from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=sid;

	-- Выясняем все ли согласовали
	if ((select count(*)-sum(_int) from task_param
	where task_diag_param in ('COORDINATED_DEPUTY_ZAM_DIR_HR_REDUCTION_POSTS_ITR')
		  and task_id=tid)=0) then

		select _int into gid from task_param
		where task_id=tid
			  and task_diag_param='STAFF_LIST_POSITION_ITR_NEW';

		insert into crond_task ("script", process_date)
		values ('Yii::import("core_app.components.action.FNEventDisabledDepartment");(new FNEventDisabledDepartment)->run(["uid"=>"' || uid || '","gid"=>"' || gid ||'");'
		, (select _datetime from task_param
		where task_id=tid
			  and task_diag_param='APPLY_DATE_ITR'));
	end if;

	return query (select cast(null as varchar));

END;
$$;

create function fn_td_event_sf_create_position_itr(sid bigint)
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

	gid bigint;

BEGIN
	select s.task_id, coalesce(t.shadow_initiator, t.initiator) into tid, uid from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=sid;

	-- Выясняем все ли согласовали
	if ((select count(*)-sum(_int) from task_param
	where task_diag_param in ('COORDINATED_DEPUTY_ZAM_DIR_HR_ITR')
		  and task_id=tid)=0) then

		select _varchar into pname from task_param
		where task_id=tid
			  and task_diag_param='STAFF_LIST_POSITION_NEW_ITR';

		-- Проверяем есть ли такая должность в справочнике
		select post into puid from post
		where name=pname;
		if (puid is null) then
			insert into post (name, is_visible) values (pname, 1) returning post into puid;
		end if;

		-- Создаем должность
		select _int into pgid from task_param
		where task_id=tid
			  and task_diag_param='STAFF_LIST_POSITION_ITR';

		select groupe_type into pgt from groupe
		where groupe_id=pgid;

		insert into groupe (name, post, is_disabled, groupe_type, user__id) values (pname, puid, 0, pgt, uid) returning groupe_id into gid;
		
		insert into groupe_tree (parent_id, child_id, user__id) values ((select fn_get_assistant(pgid)), gid, uid);

	end if;

	return query (select cast(null as varchar));

END;
$$;


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

		insert into crond_task (script) 
		values ('Yii::import("core_app.components.action.FNEventDismissalUser");(new FNEventDismissalUser)->run(["uid"=>"' || (select _int from task_param
			where task_id=tid
				  and task_diag_param='INVITATION_EMPLOYEE') || '","dod"=>"' || (select _datetime from task_param
			where task_id=tid
				  and task_diag_param='DATE_OF_DISMISSAL') ||'");'
			);
	end if;

	return query (select cast(null as varchar));

END;
$$;


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
		
		script=script || '$pgid=' || pgid || ';';
		select groupe_type from groupe
		where groupe_id=(select _int into pgid from task_param
		where task_id=tid
			  and task_diag_param='STAFF_LIST_POSITION');;

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






