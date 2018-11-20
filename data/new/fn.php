<?php
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

	$subtaskId = $args;
	$result = Yii::app()->db->createCommand(<<<SQL
	select s.task_id as tid, coalesce(t.shadow_initiator, t.initiator) as uid from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=$subtaskId;
SQL
	)->query();
	$taskId = $result['tid'];
	$userId = $result['uid'];
	// Выясняем все ли согласовали
	if (Yii::app()->db->createCommand(<<<SQL
	(select count(*)-sum(_int) from task_param
	where task_diag_param in ('APPROVE_ZAM_HEAD_OF_STAFF_TRANSFER_TO_ANOTHER_POSITION', 'IN_CONCERT_WITH_UNIT_MANAGER_TRANSLATION_ANOTHER_POST')
		  and task_id=tid)=0)
SQL
		)->queryScalar())
	{
		$groupeId = Yii::app()->db->createCommand(<<<SQL
		select _int into gid from task_param
		where task_id=$taskId
			  and task_diag_param='UNIT_STAFF';
SQL
			)->queryScalar();
		$userId2 = 
			;
		)->queryScalar();
		insert into crond_task ("script", process_date)
		values ('Yii::app()->db->createCommand("delete from user_groupe where user_id=' || uid2 || ' and groupe_id in (select groupe_id from groupe where groupe_type in (select _fn_get_org_groupe_type()));
					insert into user_groupe (user_id, groupe_id, user__id) values (' (select cast(_int as integer) into uid2 from task_param
		where task_id=$taskId
			  and task_diag_param='INVITATION_EMPLOYEE'), ' || gid || ', ' || uid || ')")->query();',
				(select _datetime from task_param
				where task_id=tid
					  and task_diag_param='DATE_TRANSFER_TO_ANOTHER_POSITION'));
	}

	return query (select cast(null as varchar));

END;
$$;
?>