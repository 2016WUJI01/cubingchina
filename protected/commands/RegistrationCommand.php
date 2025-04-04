<?php

class RegistrationCommand extends CConsoleCommand {

	public function actionCancel($id) {
		$_SERVER['HTTPS'] = 1;
		$_SERVER['HTTP_HOST'] = 'cubing.com';
		$registration = Registration::model()->findByPk($id);
		if ($registration !== null && $this->confirm($registration->user->getCompetitionName() . '-' . $registration->competition->name_zh)) {
			// set registration status to waiting so it will get full refunded
			$registration->status = Registration::STATUS_WAITING;
			$registration->cancel();
		}
	}

	public function actionRefundOverpaid($id) {
		$_SERVER['HTTPS'] = 1;
		$_SERVER['HTTP_HOST'] = 'cubing.com';
		$pay = Pay::model()->findByPk($id);
		if (!$pay) {
			return;
		}
		$registration = $pay->registration;
		$competition = $pay->competition;
		if ($registration && $competition && $this->confirm($registration->user->getCompetitionName() . '-' . $competition->name_zh . '-' . $pay->paid_amount)) {
			$pay->refund($pay->paid_amount);
		}
	}

	public function actionCheckOverPaid($id) {
		$_SERVER['HTTPS'] = 1;
		$_SERVER['HTTP_HOST'] = 'cubing.com';
		$competition = Competition::model()->findByPk($id);
		if (!$competition) {
			return;
		}
		$overPaids = Yii::app()->db->createCommand()
			->setText("SELECT user_id, GROUP_CONCAT(pay.paid_amount) as amounts, GROUP_CONCAT(pay.id) as ids FROM `pay` inner JOIN user on pay.user_id=user.id WHERE type_id=1477 and paid_amount>=:amount  group by user_id having count(pay.id)>1")
			->query([
				':amount'=>$competition->entry_fee * 100,
			]);
		foreach ($overPaids as $overPaid) {
			$amounts = explode(',', $overPaid['amounts']);
			$ids = explode(',', $overPaid['ids']);
			$toRefund = false;
			$registration = Registration::model()->findByAttributes([
				'user_id'=>$overPaid['user_id'],
				'competition_id'=>$competition->id,
			]);
			if (!$registration) {
				continue;
			}
			foreach ($amounts as $amount) {
				if ($amount == $registration->total_fee * 100) {
					$toRefund = true;
					break;
				}
			}
			if (!$toRefund) {
				echo "Not overpaid: ", $registration->user->getCompetitionName(), PHP_EOL;
				continue;
			}
			foreach ($amounts as $i => $amount) {
				if ($amount == $competition->entry_fee * 100) {
					$pay = Pay::model()->findByPk($ids[$i]);
					if ($pay && $pay->refund_amount == 0 && $this->confirm($pay->user->getCompetitionName() . '-' . $pay->paid_amount)) {
						$pay->refund($pay->paid_amount);
					}
				}
			}
		}
		// $registrations = Registration::model()->with([
		// 	'competition',
		// 	'payments'
		// ])->findAllByAttributes([
		// 	// 'status'=>[
		// 	// 	Registration::STATUS_CANCELLED,
		// 	// 	Registration::STATUS_CANCELLED_TIME_END,
		// 	// ],
		// ], [
		// 	'condition'=>'payments.paid_amount>0 and payments.refund_amount=0'
		// ]);
		// $now = time();
		// foreach ($registrations as $registration) {
		// 	echo implode("\t", [
		// 		$registration->user->getCompetitionName(),
		// 		$registration->competition->name_zh,
		// 	]);
		// 	echo "\n";
		// 	foreach ($registration->payments as $payment) {
		// 		$refundPercent = $registration->refundPercent;
		// 		$shouldRefund = $payment->paid_amount * $refundPercent;
		// 		echo "Paid: {$payment->paid_amount}\n";
		// 		echo "Refund Percent: {$refundPercent}\n";
		// 		echo "Should Refund: {$shouldRefund}\n";
		// 		// within 3 months
		// 		if ($this->confirm('refund?')) {
		// 			if ($now - $payment->paid_time < 3 * 30 * 86400) {
		// 				$payment->refund($shouldRefund);
		// 			} else {
		// 				$payment->transfer($shouldRefund);
		// 			}
		// 		}
		// 	}
		// }
	}

	public function actionCheckRefund() {
		$_SERVER['HTTPS'] = 1;
		$_SERVER['HTTP_HOST'] = 'cubing.com';
		$registrations = Registration::model()->with([
			'competition',
			'payments'
		])->findAllByAttributes([
			'status'=>[
				Registration::STATUS_CANCELLED,
				Registration::STATUS_CANCELLED_TIME_END,
			],
		], [
			'condition'=>'competition.refund_type!="none" and payments.paid_amount>0 and payments.refund_amount=0'
		]);
		$now = time();
		foreach ($registrations as $registration) {
			echo implode("\t", [
				$registration->user->getCompetitionName(),
				$registration->competition->name_zh,
			]);
			echo "\n";
			foreach ($registration->payments as $payment) {
				$refundPercent = $registration->refundPercent;
				$shouldRefund = $payment->paid_amount * $refundPercent;
				echo "Paid: {$payment->paid_amount}\n";
				echo "Refund Percent: {$refundPercent}\n";
				echo "Should Refund: {$shouldRefund}\n";
				// within 3 months
				if ($this->confirm('refund?')) {
					if ($now - $payment->paid_time < 3 * 30 * 86400) {
						$payment->refund($shouldRefund);
					} else {
						$payment->transfer($shouldRefund);
					}
				}
			}
		}
	}

	public function actionClearWaitingList() {
		$competitions = Competition::model()->findAllByAttributes([
			'status'=>Competition::STATUS_SHOW,
		], [
			'condition'=>'reg_end<' . time() . ' AND reg_end>' . (time() - 7 * 86400),
		]);
		$_SERVER['HTTPS'] = 1;
		$_SERVER['HTTP_HOST'] = 'cubing.com';
		foreach ($competitions as $competition) {
			$registrations = Registration::model()->findAllByAttributes([
				'competition_id'=>$competition->id,
				'status'=>Registration::STATUS_WAITING,
			]);
			foreach ($registrations as $registration) {
				if (!$registration->cancel(Registration::STATUS_CANCELLED_TIME_END)) {
					echo 'Failed: ', $registration->user->getCompetitionName() . PHP_EOL;
				}
			}
		}
	}

	public function actionClearDisqualified($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $competition->has_qualifying_time && $this->confirm($competition->name_zh)) {
			$_SERVER['HTTPS'] = 1;
			$_SERVER['HTTP_HOST'] = 'cubing.com';
			$registrations = Registration::getRegistrations($competition);
			foreach ($registrations as $registration) {
				$unmetEvents = $registration->getUnmetEvents();
				$acceptedEvents = $registration->getAcceptedEvents();
				$disqualifiedEvents = [];
				foreach ($acceptedEvents as $registrationEvent) {
					if (in_array($registrationEvent->event, $unmetEvents)) {
						$registrationEvent->disqualify();
						$disqualifiedEvents[] = $registrationEvent;
					}
				}
				if (count($disqualifiedEvents) === count($acceptedEvents)) {
					$this->logDisqualified($registration, $disqualifiedEvents, true);
					$registration->disqualify();
				} elseif (count($disqualifiedEvents) > 0) {
					$this->logDisqualified($registration, $disqualifiedEvents, false);
					Yii::app()->mailer->sendRegistrationEventsDisqualified($registration);
					$registration->save();
				}
			}
		}
	}

	public function actionClearWaitingEvents() {
		$competitions = Competition::model()->findAllByAttributes([
			'status'=>Competition::STATUS_SHOW,
			'competitor_limit_type'=>Competition::COMPETITOR_LIMIT_BY_EVENT,
		], [
			'condition'=>'reg_end<' . time() . ' AND reg_end>' . (time() - 7 * 86400),
		]);
		$_SERVER['HTTPS'] = 1;
		$_SERVER['HTTP_HOST'] = 'cubing.com';
		$now = time();
		foreach ($competitions as $competition) {
			$registrationEvents = RegistrationEvent::model()->with('registration')->findAllByAttributes([
				'status'=>RegistrationEvent::STATUS_WAITING,
			], [
				'condition'=>'registration.competition_id=:competition_id AND registration.status=:status',
				'params'=>[
					':status'=>Registration::STATUS_ACCEPTED,
					':competition_id'=>$competition->id,
				],
			]);
			$userRegistrations = [];
			foreach ($registrationEvents as $registrationEvent) {
				if (!isset($userRegistrations[$registrationEvent->registration->id])) {
					$userRegistrations[$registrationEvent->registration->id] = [
						'registration'=>$registrationEvent->registration,
						'events'=>[],
						'fee'=>0,
					];
				}
				$userRegistrations[$registrationEvent->registration->id]['events'][] = $registrationEvent;
				$userRegistrations[$registrationEvent->registration->id]['fee'] += $registrationEvent->fee;
			}
			foreach ($userRegistrations as $userRegistration) {
				$payment = null;
				$shouldRefund = $userRegistration['fee'] * 100;
				$registration = $userRegistration['registration'];
				foreach ($registration->payments as $p) {
					if ($p->isPaid() && $p->paid_amount > $shouldRefund) {
						$payment = $p;
						break;
					}
				}
				if ($payment === null) {
					echo 'No payment: ', $registration->user->getCompetitionName(), PHP_EOL;
					continue;
				}
				echo implode("\t", [
					$registration->user->getCompetitionName(),
					$registration->competition->name_zh,
				]);
				echo "\n";
				echo implode("\n", array_map(function($event) {
					return $event->event . ': ' . $event->fee;
				}, $userRegistration['events']));
				echo "\n";
				echo "Paid: {$payment->paid_amount}\n";
				echo "Should Refund: {$shouldRefund}\n";
				// within 3 months
				if ($this->confirm('refund?')) {
					foreach ($userRegistration['events'] as $event) {
						$event->cancel();
					}
					if ($now - $payment->paid_time < 3 * 30 * 86400) {
						$payment->refund($shouldRefund);
					} else {
						$payment->transfer($shouldRefund);
					}
				}

			}
		}
	}

	private function logDisqualified($registration, $disqualifiedEvents, $disqualified) {
		echo implode("\t", [$registration->user->getCompetitionName(), $registration->user->wcaid, $disqualified ? 'disqualified' : '', implode("\t", $disqualifiedEvents)]), "\n";
	}
}
