<?php

class FNEventCreatePosition
{
	public function run($args)
	{
		Yii::app()->db->createCommand(<<<SQL
		delete from user_groupe where user_id={$args['uid2']} and groupe_id in (select groupe_id from groupe where groupe_type in (select _fn_get_org_groupe_type()));
		insert into user_groupe (user_id, groupe_id, user__id) values ({$args['uid2']}, {$args['gid']}, {$args['uid']});
SQL
		)->query();
		
		$uid=$args['uid']; $pname=$args['pname'];
		$puid=$args['puid'] ?? Yii::app()->db->createCommand("insert into post (name, is_visible) values ('{$pname}', 1) returning post")->queryScalar();
	}
}
