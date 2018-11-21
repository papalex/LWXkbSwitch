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
		update user_param set salary=(select _int from task_param
			where task_id=tid
				  and task_diag_param='SALARY_OKLAD')
			 , user__id=uid where user_id= (select _int from task_param
			where task_id=tid
				  and task_diag_param='INVITATION_EMPLOYEE') ;
	end if;

	return query (select cast(null as varchar));

END;
$$;
ALTER FUNCTION fn_td_event_sc_change_salary(bigint)
OWNER TO postgres;
GRANT EXECUTE ON FUNCTION fn_td_event_sc_change_salary(bigint) TO postgres;
GRANT EXECUTE ON FUNCTION fn_td_event_sc_change_salary(bigint) TO web;

create or replace function fn_td_event_scs_change_salary(sid bigint)
	returns SETOF character varying
language plpgsql
as $$
declare
	tid bigint;
	uid integer;

	min_ bigint;
	max_ bigint;
	gid bigint;

BEGIN
	select s.task_id, t.initiator
	into tid, uid
	from subtask s
		inner join task t on t.task_id=s.task_id
	where s.subtask_id=sid;
	select _int into min_ from task_param
	where task_id=tid
		  and task_diag_param='STAFF_CHANGE_SALARY_MIN';
	select _int into max_ from task_param
	where task_id=tid
		  and task_diag_param='STAFF_CHANGE_SALARY_MAX';
	select _int into gid from task_param
	where task_id=tid
		  and task_diag_param='UNIT_STAFF';
	if ((min_ is not null)
		and (max_ is not null)
		and (gid is not null)
		and (uid is not null)
		and (select _int from task_param
	where task_id=tid
		  and task_diag_param='IS_STAFF_CHANGE_SALARY_APROVE_HEAD_SBE')=1) then
		update groupe_param set min_salary=min_ , max_salary= max_ , user__id=uid where groupe_id= gid;
	end if;


	return query (select cast(null as varchar));

END;
$$;

ALTER FUNCTION fn_td_event_scs_change_salary(bigint)
OWNER TO postgres;
GRANT EXECUTE ON FUNCTION fn_td_event_scs_change_salary(bigint) TO postgres;
GRANT EXECUTE ON FUNCTION fn_td_event_scs_change_salary(bigint) TO web;	
