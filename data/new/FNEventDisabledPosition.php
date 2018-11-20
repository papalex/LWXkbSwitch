<?php

class FNEventDisabledPosition
{
	public function run($args)
	{
		Yii::app()->db->createCommand(<<<SQL
		update groupe set is_disabled=1, user__id={$args['uid']} where groupe_id={$args['gid']}
SQL
					)->query();
	}
}
