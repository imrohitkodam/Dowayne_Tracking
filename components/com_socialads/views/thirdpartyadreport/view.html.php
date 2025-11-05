<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Socialads
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Toolbar\Toolbar;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Router\Route;

// Import Csv export button
jimport('techjoomla.tjcsv.csv');

/**
 * View class for a list of Socialads.
 *
 * @since  1.6
 */
class SocialadsViewThirdPartyAdReport extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

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
		// *Important- If any Ad id in session, clear it
		// @TODO - needs to be handled while create ad function ends
		Factory::getSession()->clear('ad_id');
		$this->user = Factory::getUser();
		$this->session = Factory::getSession();
		$this->input = Factory::getApplication()->input;
		$this->mainframe = Factory::getApplication();
		$this->params     = $this->mainframe->getParams('com_socialads');
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		$model = $this->getModel();
		$this->thirdPartyId = $model->getThirdPartyId();

		FormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$currentBSViews = $this->params->get('bootstrap_version', "bs3");
		$this->bsVersion = $currentBSViews;
		$itemId       = SaCommonHelper::getSocialadsItemid('adform');
		$this->socialAdsitemId = $itemId;
		$this->thirdpartyid = $model->getThirdPartyId();

		if (!$this->user->id)
		{
			$msg = Text::_('COM_SOCIALADS_LOGIN_MSG');
			$uri = Uri::getInstance()->toString();
			$url = urlencode(base64_encode($uri));
			$this->mainframe->enqueueMessage($msg, 'success');
			$this->mainframe->redirect(Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId . '&return=' . $url, false));
		}

		if (!$this->thirdPartyId)
		{
			$msg = "Please Register as third party first";
			$this->mainframe->enqueueMessage($msg, 'success');
			$this->mainframe->redirect(Route::_('index.php?option=com_socialads&view=thirdParty', false));
		}

		// Get campains list
		$zoneslist = FormHelper::loadFieldType('zoneslist', false);

		// Get zones list
		$this->zonesoptions = $zoneslist->getOptions();

		$this->publish_states = array(
		'' => Text::_('JOPTION_SELECT_PUBLISHED'),
		'1'  => Text::_('JPUBLISHED'),
		'0'  => Text::_('JUNPUBLISHED')
		);

		$this->adstatus = array(
		'' => Text::_('COM_SOCIALADS_ADS_STATUS'),
		'1'  => Text::_('COM_SOCIALADS_ADS_VALID'),
		'0'  => Text::_('COM_SOCIALADS_ADS_EXPIRED')
		);

		// Setup toolbar
		$this->addTJtoolbar();

		parent::display($tpl);
	}

	/**
	 * Setup ACL based tjtoolbar
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	protected function addTJtoolbar()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_socialads/helpers/socialads.php';

		$smallButtonClass = JVERSION < '4.0' ? 'btn-small' : 'btn-sm';

		// Add toolbar buttons
		jimport('techjoomla.tjtoolbar.toolbar');
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right');

		$tjbar->appendButton('thirdpartyadreport.thirdPartyAdReportCsvExport', 'COM_SOCIALADS_ADS_CSV_EXPORT', '', 'class="btn '. $smallButtonClass .' btn-warning"');

		$this->toolbarHTML = $tjbar->render();
	}
}
