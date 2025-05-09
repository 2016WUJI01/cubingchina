<?php
use EasyWeChat\Factory;

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController {
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout = '/layouts/main';
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs = array();
	protected $captchaAction = 'site/captcha';
	protected $zh2Hant;
	protected $logAction = true;
	protected $minIEVersion = '8.0';
	private $_IEVersion;
	private $_user;
	private $_description;
	private $_keywords;
	private $_title;
	private $_navibar;
	private $_weiboShareDefaultText;
	private $_weiboSharePic;
	private $_wechatOffcialAccount;

	public function filters() {
		return array(
			'accessControl',
		);
	}

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('@'),
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function setIsAjaxRequest($isAjaxRequest = true) {
		if ($isAjaxRequest) {
			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		} else {
			unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		}
	}

	public function getWechatOfficialAccount($config = []) {
		if (($officialAccount = $this->_wechatOffcialAccount) === null) {
			$options = [
				'debug'=>YII_DEBUG,
				'app_id'=>Env::get('WECHAT_APP_ID'),
				'secret'=> Env::get('WECHAT_SECRET'),
			];
			$officialAccount = Factory::officialAccount($options);
			$this->_wechatOffcialAccount = $officialAccount;
		}
		$clientScript = Yii::app()->clientScript;
		$baseUrl = Yii::app()->request->getBaseUrl(true);

		if (isset($config['jsConfig'])) {
			$clientScript->registerScriptFile('https://res.wx.qq.com/open/js/jweixin-1.0.0.js');
			$jssdk = $officialAccount->jssdk;
			$jssdk->setUrl($baseUrl . Yii::app()->request->url);
			try {
				$config = $jssdk->buildConfig($config['jsConfig'], YII_DEBUG);
			} catch (Exception $e) {
				$config = '{}';
			}
			Yii::app()->clientScript->registerScript('wx.config', "wx.config({$config})");
		}
		if (isset($config['oauth'])) {
			$scopes = $config['oauth']['scopes'] ?? ['snsapi_base'];
			$oauth = $officialAccount->oauth;
			$oauth->scopes($scopes);
			$session = Yii::app()->session;
			if ($this->action->id !== 'wechatLogin' && $session->get(Constant::WECHAT_SESSION_KEY) === null) {
				$session->add(Constant::CURRENT_URL_KEY, Yii::app()->request->url);
				$redirectUrl = $oauth->redirect(Yii::app()->params->baseUrl . '/site/wechatLogin/' . DEV);
				$this->redirect($redirectUrl);
				Yii::app()->end();
			}
		}
		return $officialAccount;
	}

	public function getIsInWechat() {
		return preg_match('{MicroMessenger}i', Yii::app()->request->getUserAgent());
	}

	public function getCacheKey() {
		$args = func_get_args();
		array_unshift($args, $this->action->id);
		array_unshift($args, $this->id);
		return implode('_', $args);
	}

	public function getAttributeName($name = 'name') {
		if (Yii::app()->language[0] == 'z' && Yii::app()->language[1] == 'h') {
			$name .= '_zh';
		}
		return $name;
	}

	public function setReferrer($section = null, $referrer = null) {
		if ($referrer === null) {
			$referrer = Yii::app()->request->getUrlReferrer();
		}
		$referrer = CHtml::normalizeUrl($referrer);
		if ($section === null) {
			$section = md5(serialize(array(
				$this->id,
				$this->action->id,
				$_GET,
			)));
		}
		if (!isset($_SESSION['referrer'][$section])) {
			$_SESSION['referrer'][$section] = $referrer;
		}
	}

	public function getReferrer($section = null, $destroy = true) {
		if ($section === null) {
			$section = md5(serialize(array(
				$this->id,
				$this->action->id,
				$_GET,
			)));
		}
		$referrer = isset($_SESSION['referrer'][$section]) ? $_SESSION['referrer'][$section] : Yii::app()->request->getUrlReferrer();
		if ($destroy) {
			unset($_SESSION['referrer'][$section]);
		}
		return $referrer;
	}

	public function getNavibar() {
		if ($this->_navibar === null) {
			$this->setNavibar(array(
				array(
					'label'=>Html::fontAwesome('home', 'a') . Yii::t('common', 'Home'),
					'url'=>array('/site/index'),
					'itemOptions'=>array(
						'class'=>'nav-item',
					),
				),
				array(
					'label'=>Html::fontAwesome('cubes', 'a') . Yii::t('common', 'Competitions'),
					'url'=>array('/competition/index'),
					'itemOptions'=>array(
						'class'=>'nav-item dropdown',
					),
				),
				array(
					'label'=>Html::fontAwesome('newspaper-o', 'a') . Yii::t('common', 'Results') . Html::fontAwesome('angle-down', 'b'),
					'url'=>'#',
					'active'=>$this->id === 'results',
					'itemOptions'=>array(
						'class'=>'nav-item dropdown',
					),
					'linkOptions'=>array(
						'class'=>'dropdown-toggle',
						'data-toggle'=>'dropdown',
						'data-hover'=>'dropdown',
						'data-delay'=>0,
						'data-close-others'=>'false',
					),
					'items'=>array(
						array(
							'url'=>array('/results/person'),
							'label'=>Html::fontAwesome('users', 'a') . Yii::t('common', 'Persons'),
						),
						array(
							'url'=>array('/results/competition'),
							'label'=>Html::fontAwesome('archive', 'a') . Yii::t('Competitions', 'Competitions'),
						),
						array(
							'url'=>array('/results/rankings'),
							'label'=>Html::fontAwesome('trophy', 'a') . Yii::t('common', 'Rankings'),
						),
						array(
							'url'=>array('/results/records'),
							'label'=>Html::fontAwesome('flag-checkered', 'a') . Yii::t('common', 'Records'),
						),
						array(
							'url'=>array('/results/statistics'),
							'label'=>Html::fontAwesome('bar-chart', 'a') . Yii::t('common', 'Statistics'),
						),
					),
				),
				array(
					'label'=>Html::fontAwesome('wrench', 'a') . Yii::t('common', 'Tools') . Html::fontAwesome('angle-down', 'b'),
					'url'=>'#',
					'itemOptions'=>array(
						'class'=>'nav-item dropdown',
					),
					'linkOptions'=>array(
						'class'=>'dropdown-toggle',
						'data-toggle'=>'dropdown',
						'data-hover'=>'dropdown',
						'data-delay'=>0,
						'data-close-others'=>'false',
					),
					'items'=>array(
						array(
							'url'=>array('/tools/luckyDraw'),
							'label'=>Html::fontAwesome('gift', 'a') . Yii::t('common', 'Lucky Draw'),
						),
						array(
							'url'=>'/static/score-card.xlsx',
							'label'=>Html::fontAwesome('tasks', 'a') . Yii::t('common', 'Score Card'),
						),
					),
				),
				array(
					'label'=>Html::fontAwesome('info-circle', 'a') . Yii::t('common', 'More Info') . Html::fontAwesome('angle-down', 'b'),
					'url'=>'#',
					'itemOptions'=>array(
						'class'=>'nav-item dropdown',
					),
					'linkOptions'=>array(
						'class'=>'dropdown-toggle',
						'data-toggle'=>'dropdown',
						'data-hover'=>'dropdown',
						'data-delay'=>0,
						'data-close-others'=>'false',
					),
					'items'=>array(
						array(
							'url'=>array('/faq/index'),
							'label'=>Html::fontAwesome('question-circle', 'a') . Yii::t('common', 'FAQ'),
						),
						array(
							'url'=>array('/site/page', 'view'=>'about'),
							'label'=>Html::fontAwesome('file-text-o', 'a') . Yii::t('common', 'About'),
						),
						array(
							'url'=>array('/site/page', 'view'=>'contact'),
							'label'=>Html::fontAwesome('pencil-square-o', 'a') . Yii::t('common', 'Contact'),
						),
						array(
							'url'=>array('/site/page', 'view'=>'links'),
							'label'=>Html::fontAwesome('link', 'a') . Yii::t('common', 'Links'),
						),
						array(
							'url'=>array('/site/page', 'view'=>'disclaimer'),
							'label'=>Html::fontAwesome('list-alt', 'a') . Config::getConfig('disclaimer')->getAttributeValue('title'),
						),
					),
				),
			));
		}
		return $this->_navibar;
	}

	public function setNavibar($navibar) {
		$isGuest = Yii::app()->user->isGuest;
		$user = $this->user;
		$applied = !$isGuest && Competition::getAppliedCount($user) > 0;
		$navibar = array_merge($navibar, array(
			array(
				'label'=>'<i class="fa fa-user"></i> <i class="fa fa-angle-down"></i>',
				'url'=>'#',
				'active'=>$this->id === 'user',
				'itemOptions'=>array(
					'class'=>'nav-item dropdown',
				),
				'linkOptions'=>array(
					'class'=>'dropdown-toggle',
					'data-toggle'=>'dropdown',
					'data-hover'=>'dropdown',
					'data-delay'=>0,
					'data-close-others'=>'false',
				),
				'items'=>array(
					array(
						'label'=>Yii::t('common', 'Profile'),
						'url'=>array('/user/profile'),
					),
					array(
						'label'=>Yii::t('common', 'My Registration'),
						'url'=>array('/user/competitions'),
					),
					array(
						'label'=>Yii::t('common', 'Competition History'),
						'url'=>array('/user/competitionHistory'),
						'visible'=>!$isGuest && $user->wcaid != '',
					),
					array(
						'label'=>Yii::t('common', 'My Homepage'),
						'url'=>$isGuest ? '' : array('/results/p', 'id'=>$user->wcaid),
						'visible'=>!$isGuest && $user->wcaid != '',
					),
					array(
						'label'=>Yii::t('common', 'My Annual Summary', [
							'{year}'=>Summary::getCurrentYear(),
						]),
						'url'=>$isGuest ? '' : array('/summary/person', 'id'=>$user->wcaid, 'year'=>Summary::getCurrentYear()),
						'visible'=>!$isGuest && $user->wcaid != '',
					),
					array(
						'label'=>Yii::t('common', 'My Certificates'),
						'url'=>$isGuest ? '' : array('/user/cert'),
						'visible'=>!$isGuest && $user->hasCerts,
					),
					array(
						'label'=>Yii::t('common', 'Board'),
						'url'=>array('/board/competition/index'),
						'visible'=>Yii::app()->user->checkRole(User::ROLE_ORGANIZER) || $applied || Yii::app()->user->checkPermission('caqa_member') || Yii::app()->user->checkPermission('wct') || Yii::app()->user->checkPermission('users_management'),
					),
					array(
						'label'=>Yii::t('common', 'Apply for Competition'),
						'url'=>array('/board/competition/apply'),
						'visible'=>!Yii::app()->user->checkRole(User::ROLE_ORGANIZER) && Yii::app()->user->checkRole(User::ROLE_CHECKED) && !$applied,
					),
					array(
						'label'=>Yii::t('common', 'Logout'),
						'url'=>array('/site/logout'),
					),
				),
				'visible'=>!$isGuest,
			),
			array(
				'label'=>Yii::t('common', 'Login'),
				'url'=>array('/site/login'),
				'itemOptions'=>array(
					'class'=>'nav-item visible-xs',
				),
				'visible'=>$isGuest,
			),
			array(
				'label'=>Yii::t('common', 'Register'),
				'url'=>array('/site/register'),
				'itemOptions'=>array(
					'class'=>'nav-item visible-xs',
				),
				'visible'=>$isGuest,
			),
			array(
				'label'=>'Language' . Html::fontAwesome('angle-down', 'b'),
				'url'=>'#',
				'itemOptions'=>array(
					'class'=>'nav-item dropdown visible-xs',
				),
				'linkOptions'=>array(
					'class'=>'dropdown-toggle',
					'data-toggle'=>'dropdown',
					'data-hover'=>'dropdown',
					'data-delay'=>0,
					'data-close-others'=>'false',
				),
				'items'=>array(
					array(
						'label'=>'简体中文',
						'url'=>$this->getLangUrl('zh_cn'),
					),
					array(
						'label'=>'繁体中文',
						'url'=>$this->getLangUrl('zh_tw'),
					),
					array(
						'label'=>'English',
						'url'=>$this->getLangUrl('en'),
					),
				),
			)
		));
		$this->_navibar = $navibar;
	}

	public function init() {
		if(isset($_REQUEST['lang']) && $_REQUEST['lang'] != '') {
			$this->setLanguage($_REQUEST['lang'], true);
		} else if(isset($_COOKIE['language']) && $_COOKIE['language'] != '') {
			$this->setLanguage($_COOKIE['language']);
		} else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$languages = Yii::app()->params->languages;
			$acceptLanguage = strtolower(str_replace('-', '_', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
			$pos = strlen($acceptLanguage);
			$userLanguage = false;
			foreach ($languages as $language) {
				$temp = strpos($acceptLanguage, $language);
				if ($temp !== false && $temp < $pos) {
					$pos = $temp;
					$userLanguage = $language;
				}
			}
			if ($userLanguage !== false) {
				$this->setLanguage($userLanguage);
			}
		}
		parent::init();
	}

	public function setLanguage($language, $setCookie = false) {
		if (!in_array($language, Yii::app()->params->languages)) {
			return;
		}
		Yii::app()->language = $language;
		if ($setCookie) {
			$_COOKIE['language'] = $language;
			setcookie('language', $language, time() + 365 * 86400, '/', DEV ? null : '.cubing.com');
		}
	}

	public function getIsCN() {
		return Yii::app()->language == 'zh_cn' || Yii::app()->language == 'zh_tw';
	}

	public function getIEClass() {
		if ($this->_IEVersion !== null) {
				return 'ie' . intval($this->_IEVersion);
		}
		return '';
	}

	public function getLangUrl($lang = 'zh_cn') {
		$params = $_GET;
		$params['lang'] = $lang;
		return $this->createUrl($this->route, $params);
	}

	public function translateTWInNeed($data) {
		if (Yii::app()->language !== 'zh_tw') {
			return $data;
		}
		if ($this->zh2Hant === null) {
			include APP_PATH . '/protected/data/ZhConversion.php';
			$this->zh2Hant = $zh2Hant;
		}
		if (is_string($data)) {
			return strtr($data, $this->zh2Hant);
		} elseif (is_array($data)) {
			$data = var_export($data, true);
			$data = strtr($data, $this->zh2Hant);
			$data = eval('return ' . $data . ';');
			return $data;
		}
	}

	protected function beforeAction($action) {
		$userAgent = Yii::app()->request->getUserAgent();
		if ($this->isInWechat) {
			$this->getWechatOfficialAccount([
				'oauth'=>[
					'scopes'=>['snsapi_userinfo'],
				]
			]);
		}
		if ($this->module === null) {
			$clientScript = Yii::app()->clientScript;
			$map = json_decode(file_get_contents(APP_PATH . '/public/f/assets-map.json'), true);
			foreach ($map['chunks'] as $name=>$files) {
				foreach ($files as $file) {
					$ext = pathinfo($file, PATHINFO_EXTENSION);
					$type = explode('?', $ext)[0];
					$clientScript->packages['main'][$type][] = ltrim($file, '/');
				}
			}
			// don't load jquery
			$clientScript->packages['jquery'] = false;
			$clientScript->registerPackage('main');
		}
		if (preg_match('{MSIE ([\d.]+)}', $userAgent, $matches) && version_compare($this->_IEVersion = $matches[1], $this->minIEVersion, '<')
			&& !($this->id == 'site' && $action->id == 'page' && $this->sGet('view') == 'please-update-your-browser')
		) {
			$this->redirect(array('/site/page', 'view'=>'please-update-your-browser'));
		}
		if ($this->logAction) {
			$params = array(
				'get'=>$_GET,
				'post'=>$_POST,
				'cookie'=>$_COOKIE,
				'session'=>$_SESSION,
				'server'=>$_SERVER,
			);
			Yii::log(json_encode($params), 'test', $this->id . '.' . $action->id);
		}
		if ($this->module === null && $this->action->id !== 'error' && $this->id !== 'pay' && $this->action->id !== 'scan' && $this->id !== 'git') {
			$app = Yii::app();
			$request = $app->request;
			$request->enableCsrfValidation = true;
			$request->csrfTokenName = md5($app->user->stateKeyPrefix . $app->getId() . $app->user->name);
			$request->validateCsrfToken(new CEvent($this));
		}
		$this->setPageTitle(Yii::app()->name);
		if (!Yii::app()->user->isGuest && $this->user && $this->user->isBanned()
			&& $this->id !== 'site' && $action->id !== 'banned' && $action->id !== 'logout'
		) {
			$this->redirect(array('/site/banned'));
		}
		return parent::beforeAction($action);
	}

	public function setWeiboShareDefaultText($weiboShareDefaultText, $appendTitle = true) {
		$weiboShareDefaultText = strip_tags($weiboShareDefaultText);
		$weiboShareDefaultText = preg_replace('{[\r\n]+}', ' ', $weiboShareDefaultText);
		if ($appendTitle) {
			$weiboShareDefaultText .= $this->getPageTitle();
		}
		$this->_weiboShareDefaultText = rawurlencode($weiboShareDefaultText);
	}

	public function getWeiboShareDefaultText() {
		if ($this->_weiboShareDefaultText === null) {
			$this->_weiboShareDefaultText = rawurlencode($this->getPageTitle());
		}
		return $this->_weiboShareDefaultText;
	}

	public function setWeiboSharePic($weiboSharePic) {
		if (is_array($weiboSharePic)) {
			$weiboSharePic[] = Yii::app()->params->weiboSharePic;
			$weiboSharePic = array_map(function($pic) {
				return urlencode($pic);
			}, $weiboSharePic);
			$weiboSharePic = implode('||', $weiboSharePic);
		} else {
			$weiboSharePic = urlencode($weiboSharePic);
		}
		$this->_weiboSharePic = $weiboSharePic;
	}

	public function getWeiboSharePic() {
		if ($this->_weiboSharePic === null) {
			$this->_weiboSharePic = urlencode(Yii::app()->params->weiboSharePic);
		}
		return $this->_weiboSharePic;
	}

	public function setDescription($description) {
		$description = strip_tags($description);
		$description = preg_replace('{[\r\n]+}', ' ', $description);
		$this->_description = $description;
	}

	public function getDescription() {
		if ($this->_description === null) {
			$this->_description = Yii::t('common', Yii::app()->params->description);
		}
		return $this->_description;
	}

	public function setKeywords($keywords) {
		if (is_array($keywords)) {
			$keywords = implode(',', array_map(function($keyword) {
				return Yii::t('common', $keyword);
			}, $keywords));
		}
		$this->_keywords = $keywords;
	}

	public function getKeywords() {
		if ($this->_keywords === null) {
			$this->setKeywords(Yii::app()->params->keywords);
		}
		return $this->_keywords;
	}

	public function appendKeywords($keywords) {
		$oldKeywords = explode(',', $this->getKeywords());
		if (!is_array($keywords)) {
			$keywords = array($keywords);
		}
		foreach ($keywords as $keyword) {
			$oldKeywords[] = $keyword;
		}
		$this->setKeywords($oldKeywords);
	}

	public function setTitle($title) {
		$this->_title = Yii::t('common', $title);
	}

	public function getTitle() {
		// if ($this->_title === null) {
		// 	$this->_title = Yii::t('common', Yii::app()->name);
		// }
		return $this->_title;
	}

	public function setPageTitle($pageTitle) {
		if (is_string($pageTitle)) {
			return parent::setPageTitle(Yii::t('common', $pageTitle));
		} elseif (is_array($pageTitle)) {
			$pageTitle[] = Yii::t('common', Yii::app()->name);
			return parent::setPageTitle(implode(' - ', array_map(function($s) {
				return Yii::t('common', strip_tags($s));
			}, $pageTitle)));
		}
	}

	protected function getCaptchaAction() {
		if(($captcha = Yii::app()->getController()->createAction($this->captchaAction)) === null) {
			if(strpos($this->captchaAction,'/') !== false) {
				if(($ca = Yii::app()->createController($this->captchaAction)) !== null) {
					list($controller,$actionID) = $ca;
					$captcha = $controller->createAction($actionID);
				}
			}
			if($captcha === null) {
				throw new CException(Yii::t('yii','CCaptchaValidator.action "{id}" is invalid. Unable to find such an action in the current controller.',
						array('{id}'=>$this->captchaAction)));
			}
		}
		return $captcha;
	}

	public function getUser() {
		if ($this->_user !== null) {
			return $this->_user;
		}
		return $this->_user = User::model()->findByPk(Yii::app()->user->id);
	}

	public function ajaxReturn($status, $data, $message = null) {
		if ($message === null) {
			$message = Constant::getAjaxMessage($status);
		}
		echo json_encode([
			'status'=>$status,
			'data'=>$data,
			'message'=>$message,
		]);
		Yii::app()->end();
	}

	public function ajaxOK($data) {
		$this->ajaxReturn(Constant::STATUS_OK, $data);
	}

	public function ajaxError($status, $message = null) {
		$this->ajaxReturn($status, null, $message);
	}

	public function sendForm($url, $params) {
		$this->render('/common/sendForm', [
			'url'=>$url,
			'params'=>$params,
		]);
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param int $defaultValue the default parameter value if the GET parameter does not exist.
	 * @return int the intvaled GET parameter value
	 */
	public function iGet($name, $defaultValue = 0) {
		return isset($_GET[$name]) ? intval($_GET[$name]) : $defaultValue;
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param string $defaultValue the default parameter value if the GET parameter does not exist.
	 * @return string the strvaled GET parameter value
	 */
	public function sGet($name, $defaultValue = '') {
		return isset($_GET[$name]) ? trim(strval($_GET[$name])) : $defaultValue;
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param array $defaultValue the default parameter value if the GET parameter does not exist.
	 * @return array the strvaled GET parameter value
	 */
	public function aGet($name, $defaultValue = array()) {
		return isset($_GET[$name]) ? (array)$_GET[$name] : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param int $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return int the intvaled POST parameter value
	 */
	public function iPost($name, $defaultValue = 0) {
		return isset($_POST[$name]) ? intval($_POST[$name]) : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the POST parameter name
	 * @param string $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return string the strvaled POST parameter value
	 */
	public function sPost($name, $defaultValue = '') {
		return isset($_POST[$name]) ? trim(strval($_POST[$name])) : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the POST parameter name
	 * @param array $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return array the strvaled POST parameter value
	 */
	public function aPost($name, $defaultValue = array()) {
		return isset($_POST[$name]) ? (array)$_POST[$name] : $defaultValue;
	}

	/**
	 * Returns the named REQUEST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the REQUEST parameter name
	 * @param int $defaultValue the default parameter value if the REQUEST parameter does not exist.
	 * @return int the intvaled REQUEST parameter value
	 */
	public function iRequest($name, $defaultValue = 0) {
		return isset($_REQUEST[$name]) ? intval($_REQUEST[$name]) : $defaultValue;
	}

	/**
	 * Returns the named REQUEST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the REQUEST parameter name
	 * @param string $defaultValue the default parameter value if the REQUEST parameter does not exist.
	 * @return string the strvaled REQUEST parameter value
	 */
	public function sRequest($name, $defaultValue = '') {
		return isset($_REQUEST[$name]) ? trim(strval($_REQUEST[$name])) : $defaultValue;
	}

	/**
	 * Returns the named REQUEST parameter value.
	 * If the REQUEST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the REQUEST parameter name
	 * @param array $defaultValue the default parameter value if the REQUEST parameter does not exist.
	 * @return array the strvaled REQUEST parameter value
	 */
	public function aRequest($name, $defaultValue = array()) {
		return isset($_REQUEST[$name]) ? (array)$_REQUEST[$name] : $defaultValue;
	}

}
