<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Socialads
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die(';)');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View class for adspreview.
 *
 * @since  1.6
 */
class SocialadsViewThirdPartyLocation extends HtmlView
{
	/**
	 * Display the view
	 *
	 * @param   array  $tpl  An optional associative array.
	 *
	 * @return  array
	 *
	 * @since 1.6
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();
		$input = Factory::getApplication()->input;

		if (!$user->id)
		{
			$msg = Text::_('COM_SOCIALADS_LOGIN_MSG');
			$uri = Uri::getInstance()->toString();
			$url = urlencode(base64_encode($uri));
			$app->enqueueMessage($msg, 'success');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		JLoader::import('thirdparty', JPATH_SITE . '/components/com_socialads/models');

		$this->id    = $input->get('id', 0, 'INT');
		$model        = new SocialadsModelThirdParty();
		$model->setState('id', $this->id);

		$this->locationDetails		= $model->getLocationDetails($this->id);
		$this->zoomSize   = $model->getZoomSize($this->id);

		$params = ComponentHelper::getParams('com_socialads');
		$this->googleMapApiKey = $params->get('google_map_api_key', '');

		parent::display($tpl);
	}
}
