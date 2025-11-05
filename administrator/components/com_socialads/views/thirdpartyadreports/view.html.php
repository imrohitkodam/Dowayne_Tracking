<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_SocialAds
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of Socialads.
 *
 * @since  1.6
 */
class SocialadsViewThirdPartyAdReports extends HtmlView
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
		// If any Ad id in session, clear it
		Factory::getSession()->clear('ad_id');

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		$zone_list = $this->get('Zonelist');

		FormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$thirdPartyHelper = FormHelper::loadFieldType('thirdpartylist', false);
		$zones = FormHelper::loadFieldType('Zones', false);

		$this->zoneOptions = $zones->getOptions();
		$this->thirdPartyList = $thirdPartyHelper->getOptions();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		SocialadsHelper::addSubmenu('forms');

		$this->publish_states = array(
			'' => Text::_('COM_SOCIALADS_CHOOSE_STATUS'),
			'1'  => Text::_('JPUBLISHED'),
			'0'  => Text::_('JUNPUBLISHED')
		);

		$this->addToolbar();

		if (JVERSION < '4.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/socialads.php';
		$state = $this->get('State');
		$canDo = SocialadsHelper::getActions($state->get('filter.category_id'));

		ToolBarHelper::custom('thirdpartyadreports.thirdPartyAdReportCsvExport', 'download', 'download', 'COM_SOCIALADS_ADS_CSV_EXPORT', false);
	}
}
