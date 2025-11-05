<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Socialads
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access.
defined('_JEXEC') or die(';)');

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;


include_once JPATH_COMPONENT . '/controller.php';

/**
 * Checkout controller class.
 *
 * @package     SocialAds
 * @subpackage  com_socialads
 * @since       1.0
 */
class SocialadsControllerCheckout extends BaseController
{
	/**
	 * Function to load state
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	public function loadState()
	{
		$jinput  = Factory::getApplication()->input;
		$country = $jinput->get('country', '', 'INT');
		$callfor = $jinput->get('callfor', '');
		require_once JPATH_SITE . '/components/com_tjfields/helpers/geo.php';
		$tjGeoHelper = TjGeoHelper::getInstance('TjGeoHelper');

		if (!$callfor)
		{
			$states      = (array) $tjGeoHelper->getRegionList($country, 'com_socialads');
		}
		else 
		{
			$country = $jinput->get('country', '');
			$db = Factory::getDbo();
			$query	= $db->getQuery(true);
			$query->select('DISTINCT(region)')
				->from($db->quoteName('#__ad_third_party_enrollment', 'a'))
				->join('LEFT', $db->quoteName('#__ad_third_party_locations', 'l') . 'ON' . $db->quoteName('l.third_party_id') . '=' . $db->quoteName('a.id'))
				->where($db->quoteName('a.state') . ' = 1' )
				->where($db->quoteName('l.country') . ' = ' . $db->quote($country));

			$db->setQuery($query);
			$thirdPartyRegionList = $db->loadColumn();
			$states = array();

			foreach ($thirdPartyRegionList as $key => $region)
			{
				$states[$key]['id'] = $region;
				$states[$key]['region'] = $region;
			}
		}

		echo json_encode($states);
		jexit();
	}

	/**
	 * Function to add place holder
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	public function adsPlaceOrder()
	{
		$jinput = Factory::getApplication()->input;

		$state = $model->getuserState('India');
		echo json_encode($state);
		jexit();
	}

	/**
	 * Function to load state
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	public function loadCity()
	{
		$jinput  = Factory::getApplication()->input;
		$country = $jinput->get('country', '', 'INT');
		$callfor = $jinput->get('callfor', '');
		require_once JPATH_SITE . '/components/com_tjfields/helpers/geo.php';
		$tjGeoHelper = TjGeoHelper::getInstance('TjGeoHelper');

		if (!$callfor)
		{
			$cities      = (array) $tjGeoHelper->getCityList($country, 'com_socialads');
		}
		else 
		{
			$country = $jinput->get('country', '');
			$db = Factory::getDbo();
			$query	= $db->getQuery(true);
			$query->select('DISTINCT(city)')
				->from($db->quoteName('#__ad_third_party_enrollment', 'a'))
				->join('LEFT', $db->quoteName('#__ad_third_party_locations', 'l') . 'ON' . $db->quoteName('l.third_party_id') . '=' . $db->quoteName('a.id'))
				->where($db->quoteName('a.state') . ' = 1' )
				->where($db->quoteName('l.country') . ' = ' . $db->quote($country));

			$db->setQuery($query);
			$thirdPartyCityList = $db->loadColumn();
			$cities = array();

			foreach ($thirdPartyCityList as $key => $city)
			{
				$cities[$key]['id'] = $city;
				$cities[$key]['city'] = $city;
			}
		}

		echo json_encode($cities);
		jexit();
	}
}
