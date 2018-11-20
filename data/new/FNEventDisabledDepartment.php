<?php

class FNEventDisabledDepartment
{
	public function run($args)
	{
		Yii::app()->db->createCommand(<<<SQL		
		update groupe set is_disabled=1, user__id={$args['uid']} 
			where groupe_id in (select gt.child_id from groupe_tree_cache gt
												inner join groupe gp on gp.groupe_id=gt.parent_id
												inner join groupe g on g.groupe_id=gt.child_id
														and g.groupe_type=gp.groupe_type
												where gt.parent_id={$args['gid']})
SQL
			)->query();	
	}
}
