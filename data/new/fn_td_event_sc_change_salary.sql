create or replace function fn_td_event_sc_change_salary(sid bigint)
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

	if (
			(((select _int from task_param
				where task_id=tid
					and task_diag_param='IS_LIKHAREV')=1) 
				and (((select _int from task_param
						where task_id=tid
								and task_diag_param='IS_STAFF_CHANGE_SALARY_APROVE_HEAD_SBE')=1))
			)
		or (
				((select _int from task_param
				where task_id=tid
					and task_diag_param='IS_LIKHAREV')=0)
			and (
				((select _int from task_param
	where task_id=tid
		  and task_diag_param='IS_STAFF_CHANGE_SALARY_APROVE_HEAD')=1)))) 
	then
		insert into crond_task (script) values
			('Yii::app()->db->createCommand("update user_param set salary=' || (select _int from task_param
			where task_id=tid
				  and task_diag_param='SALARY_OKLAD')
			 || ', user__id=' || uid || ' where user_id=' || (select _int from task_param
			where task_id=tid
				  and task_diag_param='INVITATION_EMPLOYEE') || '")->query();');
	end if;

	return query (select cast(null as varchar));

END;
$$;

