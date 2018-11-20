<?php

class FNEventDisabledDepartment
{
	public function run($args)
	{
		if ($model=User::model()->findByPk($args['uid']))
		{
			$model->dismissal_date=$args['dod'];
			$model->uid=4000;
			$model->save();
		}
	}
}
