<?php

class FNEventResubordinate
{
	public function run($args)
	{
		//['ogid','ngid','uid','gid']
		Yii::app()->db->createCommand(<<<SQL
		update groupe_tree set parent_id=(select fn_get_assistant({$args['ngid']})), user__id={$args['uid']}
				where parent_id={$args['ogid']}
					and child_id={$args['gid']}
SQL
					)->query();
	}
}
