<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    SocialAds
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;



/**
 * View to edit
 *
 * @since  1.6
 */
class SocialadsViewThirdPartyPreviewAds extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	protected $canSave;

	/**
	 * Display the view
	 *
	 * @param   STRING  $tpl  layout name
	 *
	 * @return  views display
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();
		$db    = Factory::getDBO();
		$doc = Factory::getDocument();
		$id    = $app->input->get('id', '', 'int');
		$zoneid    = $app->input->get('zoneid', '', 'int');
		$this->thirdPartId = $id;

		if (!$id)
		{
			echo "something went wrong";

			return;
		}

		$this->params  = $app->getParams('com_socialads');
		$currentBSViews = $this->params->get('bootstrap_version', "bs3");
		$this->bsVersion = $currentBSViews;

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_socialads/tables');
		$adDisplayedRecords = Table::getInstance('thirdpartyaddisplayed', 'SocialadsTable', array('dbo', $db));
		$adDisplayedRecords->load(array('third_party_id' => $id));

		$this->ads    = explode(",", $adDisplayedRecords->ad_ids);
		$result = [];

		if (count($this->ads) && !empty($this->ads) && (implode(",", $this->ads)))
		{
			$query = "SELECT a.ad_id
			FROM #__ad_data as a 
			LEFT JOIN #__ad_campaign as c ON c.id = a.camp_id  
			WHERE a.ad_id IN (". implode(",", $this->ads) .") 
			AND a.ad_zone = $zoneid 
			AND a.state = 1 
			AND a.ad_approved = 1 
			AND (
				(NOW() BETWEEN c.start_date AND c.end_date)
				OR (c.start_date <= NOW() AND c.end_date is null )
				OR (c.end_date >= NOW() AND c.start_date is null )
				OR (c.end_date is NULL AND c.start_date is null )
			)";

			$db->setQuery($query);
			$result = $db->loadColumn();
		}

		$this->withspecifiedZonesAd = $result;
		$this->_prepareDocument();
		$doc->addScript(Uri::root(true) . '/media/com_sa/vendors/flowplayer/flowplayer-3.2.13.min.js');

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_SOCIALADS_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
