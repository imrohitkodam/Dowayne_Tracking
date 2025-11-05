<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Socialads
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

/**
 * Coupons list controller class.
 *
 * @since  1.6
 */
class SocialadsControllerThirdPartyEnrollment extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   boolean  $name    If true, the view output will be cached
	 * @param   boolean  $prefix  If true, the view output will be cached
	 * @param   array    $config  An array of safe url parameters and their variable types, for valid values see {@link
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	public function getModel($name = 'thirdpartyenrollments', $prefix = 'SocialadsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * publish campaign.
	 *
	 * @return void
	 *
	 * @since  3.1.15
	 */
	public function getTotalPopulation()
	{
		$app    = Factory::getApplication();
		$db              = Factory::getDbo();
		$input  = $app->input->get;
		$id     = $input->get('ad_id', '', 'INT');
		$country = $input->get('country', '', 'STRING');
		$points = $input->get('points', '', 'ARRAY');

		$query = $db->getQuery(true);

		$query->select('l.*')
			->select('a.business_name')
			// save for the refernce in future
			// ->select("CAST(REPLACE(SUBSTRING_INDEX(location, ',', 1), '(', '') AS SIGNED) as valueeee_lat")
			// ->select("CAST(REPLACE(SUBSTRING_INDEX(location, ',', -1), ')', '') AS SIGNED) as valueeee_lng")
			// ->where("CAST(REPLACE(SUBSTRING_INDEX(location, ',', 1), '(', '') AS SIGNED) < 1000")
			->from($db->quoteName('#__ad_third_party_enrollment', 'a'))
			->join('LEFT', $db->quoteName('#__ad_third_party_locations', 'l') . 'ON' . $db->quoteName('l.third_party_id') . '=' . $db->quoteName('a.id'))
			->where($db->quoteName('a.state') . ' = 1' )
			//lat check
			->where("CAST(REPLACE(SUBSTRING_INDEX(location, ',', 1), '(', '') AS SIGNED) <= " . (int)$points['ne']['lat'])
			->where("CAST(REPLACE(SUBSTRING_INDEX(location, ',', 1), '(', '') AS SIGNED) >= " . (int)$points['sw']['lat'])
			//lang check
			->where("CAST(REPLACE(SUBSTRING_INDEX(location, ',', -1), '(', '') AS SIGNED) <= " . (int)$points['ne']['lng'])
			->where("CAST(REPLACE(SUBSTRING_INDEX(location, ',', -1), '(', '') AS SIGNED) >= " . (int)$points['sw']['lng'])
			// ->where($db->quoteName('l.country') . ' = ' . $db->quote($country))
			->where($db->quoteName('l.location') . ' != ""'  );

		$db->setQuery($query);
		$result	= $db->loadObjectList();

		echo json_encode($result);
	}

	/**
	 * .
	 *
	 * @return void
	 *
	 * @since  3.1.15
	 */
	public function getTotalPopulationByCityName()
	{
		$app    = Factory::getApplication();
		$db              = Factory::getDbo();
		$input  = $app->input->get;
		$city     = $input->get('city', '', 'string');
		$country  = $input->get('country', '', 'string');
		$query = $db->getQuery(true);

		$query->select('SUM(l.count)')
			->from($db->quoteName('#__ad_third_party_enrollment', 'a'))
			->join('LEFT', $db->quoteName('#__ad_third_party_locations', 'l') . 'ON' . $db->quoteName('l.third_party_id') . '=' . $db->quoteName('a.id'))
			->where($db->quoteName('l.city') . ' = ' . $db->q($city))
			->where($db->quoteName('l.country') . ' = ' . $db->q($country))
			->where($db->quoteName('a.state') . ' = 1' );

		$db->setQuery($query);
		$result	= $db->loadResult();

		echo json_encode($result);
	}
}
