<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    SocialAds
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Ads helper class for Geo ads
 *
 * @package  SocialAds
 * @since    3.1
 */
class SaAdsHelperMap extends SaAdsHelper
{
	/**
	 * Fetch target data for geo targetting ads
	 *
	 * @param   array   $params      SocialAds module parameters
	 * @param   string  $adType      Ad type - e.g. Geo
	 * @param   string  $engineType  Server to access geo targeting
	 *
	 * @return  array  Geolocation data of loggedin user
	 */
	public static function getAdTargetData($params, $adType, $engineType = 'local')
	{
		return;
	}

	/**
	 * Fetch geo targetted ads based on geo data collected
	 *
	 * @param   array   $params  SocialAds module parameters
	 * @param   array   $data    Ad target data
	 * @param   string  $adType  Ad type - e.g. Geo
	 *
	 * @return  array  Array of ad ids
	 */
	public static function getAds($params, $data, $adType = '')
	{
		$thirdPartyId = $params['third_party_id'] ? $params['third_party_id'] : 0;

		if (!$thirdPartyId)
		{
			return array();
		}

		$saParams = ComponentHelper::getParams('com_socialads');

		if (SaAdEngineHelper::$_fromemail == 1)
		{
			return;
		}

		$result_ads    = array();
		$function_name = "map";

		$camp_join     = SaAdEngineHelper::getQueryJoinCampaigns();
		$common_where  = SaAdEngineHelper::getQueryWhereCommon($params, $function_name);
		$common_where  = implode(' AND ', $common_where);

		$db    = Factory::getDbo();

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_socialads/tables');
		$thirdPartyDetails = Table::getInstance('thirdpartyenrollment', 'SocialadsTable', array('dbo', $db));
		$thirdPartyDetails->load(array('id' => $thirdPartyId));

		$resultAdIdArray = array();

		$query = "SELECT a.ad_id, amtr.location, amtr.radius
		 FROM #__ad_data as a
		$camp_join
		LEFT JOIN #__ad_map_target as amtr ON amtr.ad_id = a.ad_id 
		 WHERE $common_where 
		 AND a.ad_image IS NOT NULL
		 AND amtr.location != '' 
		 AND amtr.location_on = 0 
		 ORDER by a.ad_created_date ";

		$db->setQuery($query);

		$result = $db->loadObjectList();

		foreach ($result as $key => $adDetails) 
		{
			$distance    = self::calculateCircleOverlapPercentage($adDetails, $thirdPartyDetails);
			if($distance)
			{
				array_push($resultAdIdArray, $adDetails->ad_id);
			}
		}

		$query = "SELECT a.ad_id
		 FROM #__ad_data as a
		$camp_join
		LEFT JOIN #__ad_map_target as amtr ON amtr.ad_id = a.ad_id 
		 WHERE $common_where 
		 AND a.ad_image IS NOT NULL
		 AND amtr.city != '' 
		 AND amtr.location_on = 1 
		 ORDER by a.ad_created_date ";

		$db->setQuery($query);

		$result = $db->loadColumn();

		if (count($result))
		{
			$resultAdIdArray = array_merge($resultAdIdArray, $result);
		}

		if (count($resultAdIdArray))
		{
			$query = "SELECT a.ad_id
			FROM #__ad_data as a
			WHERE a.ad_id IN (". implode(",", $resultAdIdArray) .")
			ORDER by a.ad_created_date ";
			$db->setQuery($query);
			$result = $db->loadObjectList();

			return $result;
		}

		return array();
	}

	/**
	 * 
	 *
	 * @param   string   aParam  Param
	 *
	 * @return  void
	 */
	public static function calculateCircleOverlapPercentage($adDetails, $thirdPartyDetails)
	{
		$adsCircle = $adDetails->location;
		$adsCircle    = substr($adsCircle, 1, strlen($adsCircle));
		$adsCircleArray = explode(",", $adsCircle);

		$thirdPartyCircle = $adDetails->location;
		$thirdPartyCircle    = substr($thirdPartyCircle, 1, strlen($thirdPartyCircle));
		$thirdPartyCircleArray = explode(",", $thirdPartyCircle);

		$lat1 = (float)$thirdPartyCircleArray[0];
		$lon1 = (float)$thirdPartyCircleArray[1];
		$radius1 = $thirdPartyDetails->radius;
		$lat2 = (float)$adsCircleArray[0];
		$lon2 = (float)$adsCircleArray[0];
		$radius2      = $adDetails->radius;

		$earthRadius = 3958.8; // in meters
		$lat1 = deg2rad($lat1);
		$lon1 = deg2rad($lon1);
		$lat2 = deg2rad($lat2);
		$lon2 = deg2rad($lon2);

		$deltaLat = $lat2 - $lat1;
		$deltaLon = $lon2 - $lon1;

		$a = sin($deltaLat / 2) * sin($deltaLat / 2) + cos($lat1) * cos($lat2) * sin($deltaLon / 2) * sin($deltaLon / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));

		$distance = $c;

		// Convert radii to meters (assuming the Earth's radius is approximately 6371000 miles to meter)
		$radius1Meters = $radius1;
		$radius2Meters = $radius2;

		// If the distance is greater than the sum of the radii, there is no overlap
		if ($distance >= ($radius1Meters + $radius2Meters)) {
			return 0.0;
		}

		// If one circle is entirely inside the other, the overlap percentage is equal to the smaller circle's area percentage
		if ($distance + min($radius1Meters, $radius2Meters) <= max($radius1Meters, $radius2Meters)) {
			$smallerCircleArea = M_PI * pow(min($radius1Meters, $radius2Meters), 2);
			$totalArea = M_PI * pow(max($radius1Meters, $radius2Meters), 2);
			return ($smallerCircleArea / $totalArea) * 100;
		}

		// Calculate the angles and areas of the circular segments
		$angle1 = 2 * acos((pow($radius1Meters, 2) - pow($radius2Meters, 2) + pow($distance, 2)) / (2 * $radius1Meters * $distance));
		$angle2 = 2 * acos((pow($radius2Meters, 2) - pow($radius1Meters, 2) + pow($distance, 2)) / (2 * $radius2Meters * $distance));

		$segmentArea1 = 0.5 * pow($radius1Meters, 2) * ($angle1 - sin($angle1));
		$segmentArea2 = 0.5 * pow($radius2Meters, 2) * ($angle2 - sin($angle2));

		// Calculate the overlap area
		$overlapArea = $segmentArea1 + $segmentArea2;

		// Calculate the total area of both circles
		$totalArea = M_PI * pow($radius1Meters, 2) + M_PI * pow($radius2Meters, 2);

		// Calculate the overlap percentage
		return ($overlapArea / $totalArea) * 100;
	}
}
