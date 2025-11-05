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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

/**
 * Ads helper class for Guest ads
 *
 * @package  SocialAds
 * @since    3.1
 */
class SaAdsHelperGuest extends SaAdsHelper
{
	/**
	 * Fetch target data for guest ads [non targetted ads]
	 *
	 * @param   array   $params  SocialAds module parameters
	 * @param   string  $adType  Ad type - e.g. Alt
	 *
	 * @return  void
	 */
	public static function getAdTargetData($params, $adType, $engineType = 'local')
	{
		return;
	}

	/**
	 * Fetch alternative guest ads [non targetted ads] based on guest ads data collected
	 *
	 * @param   array   $params  SocialAds module parameters
	 * @param   array   $data    Ad target data
	 * @param   string  $adType  Ad type - e.g. Alt
	 *
	 * @return  array  Array of ad ids
	 */
	// public static function getAds($params, $data, $adType = '')
	// {
	// 	$saParams = ComponentHelper::getParams('com_socialads');

	// 	$db            = Factory::getDbo();
	// 	$camp_join     = SaAdEngineHelper::getQueryJoinCampaigns();
	// 	$function_name = "guest";
	// 	$common_where  = SaAdEngineHelper::getQueryWhereCommon($params, $function_name);
	// 	$common_where  = implode(' AND ', $common_where);

	// 	$query = "SELECT a.ad_id
	// 	 FROM #__ad_data as a " .
	// 	$camp_join . "
	// 	 WHERE a.ad_guest = 1
	// 	 AND " . $common_where;

	// 	if ($saParams->get('geo_targeting'))
	// 	{
	// 		$query .= " AND a.ad_id NOT IN (SELECT ad_id
	// 		 FROM #__ad_geo_target
	// 		 ) ";
	// 	}

	// 	$query .= " ORDER by a.ad_created_date ";

	// 	$db->setQuery($query);
	// 	$result = $db->loadObjectList();

	// 	return $result;
	// }

	public static function getAds($params, $data, $adType = '')
	{
		$saParams = ComponentHelper::getParams('com_socialads');
		$db       = Factory::getDbo();
		$app      = Factory::getApplication();
		$input    = $app->input;
		$inputId  = $input->get('id'); // third_party_id from input

		$camp_join     = SaAdEngineHelper::getQueryJoinCampaigns();
		$function_name = "guest";
		$common_where  = SaAdEngineHelper::getQueryWhereCommon($params, $function_name);
		$common_where  = implode(' AND ', $common_where);

		// Build main query using query builder
		$query = $db->getQuery(true);
		$query->select($db->quoteName('a.ad_id'))
			->from($db->quoteName('#__ad_data', 'a'));

		// Add campaign join - directly append since it already has JOIN syntax
		if (!empty($camp_join)) {
			$query->join('LEFT', $db->quoteName('#__ad_campaign', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.camp_id'));
		}

		$query->where($db->quoteName('a.ad_guest') . ' = 1')
			->where($common_where);

		// Add geo targeting subquery
		if ($saParams->get('geo_targeting'))
		{
			$subQuery = $db->getQuery(true);
			$subQuery->select($db->quoteName('ad_id'))
					->from($db->quoteName('#__ad_geo_target'));

			$query->where($db->quoteName('a.ad_id') . ' NOT IN (' . $subQuery . ')');
		}

		$query->order($db->quoteName('a.ad_created_date'));

		$db->setQuery($query);
		$ads = $db->loadObjectList();

		$filteredAds = [];

		foreach ($ads as $ad)
		{
			$adId = (int) $ad->ad_id;

			// Get map targeting ID for this ad
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__ad_map_target'))
				->where($db->quoteName('ad_id') . ' = ' . $db->quote($adId));
			$db->setQuery($query);
			$adMapTargetId = (int) $db->loadResult();

			if ($adMapTargetId)
			{
				// Get all third_party_id entries for this ad_map_target_id
				$query = $db->getQuery(true)
					->select($db->quoteName('third_party_id'))
					->from($db->quoteName('#__ad_map_target_locations'))
					->where($db->quoteName('ad_map_target_id') . ' = ' . $db->quote($adMapTargetId))
					->where($db->quoteName('third_party_id') . ' IS NOT NULL');
				$db->setQuery($query);
				$thirdPartyIds = $db->loadColumn();

				if (in_array($inputId, $thirdPartyIds))
				{
					$filteredAds[] = $ad;
				}
			}
			else
			{
				// No targeting enabled – allow ad
				$filteredAds[] = $ad;
			}
		}

		return $filteredAds;
	}
}
