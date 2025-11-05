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

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * ThirdpartyAdReports list controller class.
 *
 * @since  1.6
 */
class SocialadsControllerThirdpartyAdReport extends AdminController
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
	public function getModel($name = 'thirdpartyadreport', $prefix = 'SocialadsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method get CSV report
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function thirdPartyAdReportCsvExport()
	{
		$model   = $this->getModel("thirdpartyadreport");
		$CSVData = $model->thirdPartyAdReportCsvExport();
	}

	/**
	 * Get bar chart data for dashboard
	 *
	 * @return json
	 *
	 * @since 3.1
	 */
	public function getBarChartData()
	{
		$allMonths     = SaCommonHelper::getAllmonths();
		$model         = $this->getModel('thirdpartyadreport');
		$monthlyChartData = $model->monthlyChartData();

		// To assign amount from array monthyincome to array allmonths
		for ($i = 0; $i < count($allMonths); $i++)
		{
			for ($j = 0; $j < count($monthlyChartData); $j++)
			{
				if ($allMonths[$i]['digitmonth'] == $monthlyChartData[$j]->monthsname)
				{
					$allMonths[$i]['clicks'] = $monthlyChartData[$j]->click;
					$allMonths[$i]['impressions'] = $monthlyChartData[$j]->impression;
				}
			}
		}

		// Output json response
		header('Content-type: application/json');
		echo json_encode($allMonths);
		jexit();
	}
}
