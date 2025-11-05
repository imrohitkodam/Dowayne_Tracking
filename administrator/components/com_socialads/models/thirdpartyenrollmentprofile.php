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
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Third Party Enrollment Profile model.
 *
 * @since  1.6
 */
class SocialadsModelThirdPartyEnrollmentProfile extends BaseDatabaseModel
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $id  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($id = null)
	{
		if ($id === null) {
			$id = $this->getState('item.id');
		}

		if (!$id) {
			return false;
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			  ->from($db->quoteName('#__ad_third_party_enrollment'))
			  ->where($db->quoteName('id') . ' = ' . (int) $id);

		// Add user-specific filtering for security
		$app = Factory::getApplication();
		$user = Factory::getUser();
		
		if ($app->isClient('administrator')) {
			// In backend, if user is not Super Admin, show only their own enrollments
			$isSuperAdmin = $user->authorise('core.admin');
			
			if (!$isSuperAdmin && $user->id) {
				$query->where($db->quoteName('created_by') . ' = ' . (int) $user->id);
			}
		} else {
			// In frontend, always filter by current user
			if ($user->id) {
				$query->where($db->quoteName('created_by') . ' = ' . (int) $user->id);
			}
		}

		$db->setQuery($query);
		
		try {
			$item = $db->loadObject();
		} catch (RuntimeException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		return $item;
	}

	/**
	 * Method to get location details for the third party enrollment.
	 *
	 * @param   integer  $thirdPartyId  The third party enrollment ID.
	 *
	 * @return  array    Array of location objects.
	 *
	 * @since   1.6
	 */
	public function getLocationDetails($thirdPartyId)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(['id', 'location', 'radius', 'region', 'city', 'country', 'count']))
			->from($db->quoteName('#__ad_third_party_locations'))
			->where($db->quoteName('third_party_id') . ' = ' . (int) $thirdPartyId);

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * Method to get available zones.
	 *
	 * @return  array    Array of zone objects.
	 *
	 * @since   1.6
	 */
	public function getZones()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(['id', 'zone_name', 'img_width', 'img_height', 'is_responsive', 'use_image_ratio', 'img_width_ratio', 'img_height_ratio']))
			->from($db->quoteName('#__ad_zone'))
			->where($db->quoteName('state') . ' = 1')
			->order($db->quoteName('zone_name') . ' ASC');

		$db->setQuery($query);
		$zones = $db->loadObjectList();
		
		// Add 'name' property for template compatibility
		foreach ($zones as $zone) {
			$zone->name = $zone->zone_name;
		}
		
		return $zones;
	}

	/**
	 * Method to get HTML widget code for a specific zone.
	 *
	 * @param   integer  $thirdPartyId  The third party enrollment ID.
	 * @param   integer  $zoneId        The zone ID.
	 *
	 * @return  string   HTML widget code.
	 *
	 * @since   1.6
	 */
	public function getWidgetCode($thirdPartyId, $zoneId)
	{
		$root = \Joomla\CMS\Uri\Uri::root();
		
		$htmlCode = '<div id="displayThirdPartyWidget" style="display:none;">
			<div id="displayThirdPartyAd"></div>
		</div>

		<input type="hidden" id="thirdPartyID" value="' . $thirdPartyId . '">
		<input type="hidden" id="thirdPartyZoneID" value="' . $zoneId . '">

		<noscript>
			<a href="' . $root . 'index.php?option=com_socialads&task=thirdparty.getUrlById&id=' . $thirdPartyId . '&zoneid=' . $zoneId . '" target="_blank"> 
				<img src="' . $root . 'index.php?option=com_socialads&task=thirdparty.getImageById&id=' . $thirdPartyId . '&zoneid=' . $zoneId . '" height="zoneHeight" width="zoneWidth" /> 
			</a>
		</noscript>

		<script type="text/javascript">
			var ad_html = "";
			var id = document.getElementById("thirdPartyID").value;
			var zoneid = document.getElementById("thirdPartyZoneID").value;

			if (window.XMLHttpRequest) {
				xhttp = new XMLHttpRequest();
			}
			else {
				xhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}

			xhttp.open("GET", "' . $root . 'index.php?option=com_socialads&task=thirdparty.getThirdPartyAdHtml&id=" + id + "&zoneid=" + zoneid, false);
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send("id=" + id + "&zoneid=" + zoneid);
			ad_html = xhttp.responseText;
			document.getElementById("displayThirdPartyAd").innerHTML = ad_html;
			document.getElementById("displayThirdPartyWidget").style.display = "";
		</script>';

		return $htmlCode;
	}

	/**
	 * Method to get image link URL.
	 *
	 * @param   integer  $thirdPartyId  The third party enrollment ID.
	 * @param   integer  $zoneId        The zone ID.
	 *
	 * @return  string   Image link URL.
	 *
	 * @since   1.6
	 */
	public function getImageLink($thirdPartyId, $zoneId)
	{
		$root = \Joomla\CMS\Uri\Uri::root();
		return $root . 'index.php?option=com_socialads&task=thirdparty.getImageById&id=' . $thirdPartyId . '&zoneid=' . $zoneId;
	}

	/**
	 * Method to get URL link.
	 *
	 * @param   integer  $thirdPartyId  The third party enrollment ID.
	 * @param   integer  $zoneId        The zone ID.
	 *
	 * @return  string   URL link.
	 *
	 * @since   1.6
	 */
	public function getUrlLink($thirdPartyId, $zoneId)
	{
		$root = \Joomla\CMS\Uri\Uri::root();
		return $root . 'index.php?option=com_socialads&task=thirdparty.getUrlById&id=' . $thirdPartyId . '&zoneid=' . $zoneId;
	}
}
