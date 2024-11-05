<?php

class ToolsController extends Controller {
	protected $logAction = false;

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionLuckyDraw() {
		$this->layout = '/layouts/simple';
		$this->render('luckyDraw');
	}

	public function actionCompetitors() {
		$registrations = Registration::model()->with('user')->findAllByAttributes(array(
			'competition_id'=>$this->iGet('id'),
			'status'=>Registration::STATUS_ACCEPTED,
		), array(
			'order'=>'accept_time ASC, t.id ASC',
		));
		$names = array();
		foreach ($registrations as $registration) {
			$names[] = $registration->user->getAttributeValue('name', true);
		}
		$this->ajaxOK($names);
	}
}
