<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    SocialAds
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

/**
 * Methods supporting a list of Socialads records.
 *
 * @since  1.6
 *
 */
class SocialadsModelWallet extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'option',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   STRING  $ordering   ordering
	 *
	 * @param   STRING  $direction  direction
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Load the month state.
		$month = $app->getUserStateFromRequest($this->context . '.month', 'month', '', 'int');
		$this->setState('month', $month);

        // Load the year state.
		$year = $app->getUserStateFromRequest($this->context . '.year', 'year', '', 'int');
		$this->setState('year', $year);

		// Load the user state.
		$user = $app->getUserStateFromRequest($this->context . '.user', 'user', '', 'int');
		$this->setState('user', $user);

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->input->getInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

		if ($list = $app->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array'))
		{
			foreach ($list as $name => $value)
			{
				// Extra validations
				switch ($name)
				{
					case 'fullordering':
						$orderingParts = explode(' ', $value);

						if (count($orderingParts) >= 2)
						{
							// Latest part will be considered the direction
							$fullDirection = end($orderingParts);

							if (in_array(strtoupper($fullDirection), array('ASC', 'DESC', '')))
							{
								$this->setState('list.direction', $fullDirection);
							}

							unset($orderingParts[count($orderingParts) - 1]);

							// The rest will be the ordering
							$fullOrdering = implode(' ', $orderingParts);

							if (in_array($fullOrdering, $this->filter_fields))
							{
								$this->setState('list.ordering', $fullOrdering);
							}
						}
						else
						{
							$this->setState('list.ordering', $ordering);
							$this->setState('list.direction', $direction);
						}
						break;

					case 'ordering':
						if (!in_array($value, $this->filter_fields))
						{
							$value = $ordering;
						}
						break;

					case 'direction':
						if (!in_array(strtoupper($value), array('ASC','DESC','')))
						{
							$value = $direction;
						}
						break;

					case 'limit':
						$limit = $value;
						break;

					// Just to keep the default case
					default:
						$value = $value;
						break;
				}

				$this->setState('list.' . $name, $value);
			}
		}

		// Receive & set filters
		if ($filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array'))
		{
			foreach ($filters as $name => $value)
			{
				$this->setState('filter.' . $name, $value);
			}
		}

		$ordering = $app->input->get('filter_order');

		if (!empty($ordering))
		{
			$list             = $app->getUserState($this->context . '.list');
			$list['ordering'] = $app->input->get('filter_order');
			$app->setUserState($this->context . '.list', $list);
		}

		$orderingDirection = $app->input->get('filter_order_Dir');

		if (!empty($orderingDirection))
		{
			$list              = $app->getUserState($this->context . '.list');

			if (!in_array($orderingDirection, array('acs', 'desc')))
			{
				// Dont change - Default ordering direction is ASC as default ordering column is ordering
				$list['direction'] = 'asc';
			}
			else
			{
				$list['direction'] = $orderingDirection;
			}

			$app->setUserState($this->context . '.list', $list);
		}

		$list = $app->getUserState($this->context . '.list');

		if (empty($list['ordering']))
		{
			$list['ordering'] = 'ordering';
		}

		if (empty($list['direction']))
		{
			$list['direction'] = 'asc';
		}

		if (isset($list['ordering']))
		{
			$this->setState('list.ordering', $list['ordering']);
		}

		if (isset($list['direction']))
		{
			$this->setState('list.direction', $list['direction']);
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 *
	 * @since    1.6
	 */
	// protected function getListQuery()
	// {
	// 	// Create a new query object.
	// 	$user = Factory::getUser();
	// 	$db    = $this->getDbo();
	// 	$query = $db->getQuery(true);

    //     $mainframe = Factory::getApplication();
    //     $month = $this->getState('month');
    //     $year = $this->getState('year');
    //     $year = $this->getState('year');
    //     $user_id = $this->getState('user');

	// 	$whr = '';
	// 	$whr1 = '';

	// 	if ($month && $year)
	// 	{
	// 		$whr = " AND month(cdate) =" . $month . "   AND year(cdate) =" . $year . "  ";
	// 		$whr1 = " AND month(DATE(FROM_UNIXTIME(a.time))) =" . $month . "  AND year(DATE(FROM_UNIXTIME(a.time))) =" . $year . "  ";
	// 	}
	// 	elseif ($month == '' && $year)
	// 	{
	// 		$whr = " AND year(cdate) =" . $year . "  ";
	// 		$whr1 = " AND year(DATE(FROM_UNIXTIME(a.time))) =" . $year . "  ";
	// 	}

	// 	$query = "SELECT DATE(FROM_UNIXTIME(a.time)) as time,a.spent as spent,type_id,a.earn as credits,balance,comment
	// 	FROM #__ad_wallet_transc as a WHERE a.user_id = " . $user_id . " " . $whr1 . " ORDER BY a.time DESC";

	// 	return $query;

	// 	// $db = $this->getDbo();
	// 	// $query = $db->getQuery(true);

	// 	// $month   = $this->getState('month');
	// 	// $year    = $this->getState('year');
	// 	// $user_id = $this->getState('user');

	// 	// $query->select([
	// 	// 	'o.id AS order_id',
	// 	// 	'o.status AS order_status',
	// 	// 	'o.coupon',
	// 	// 	'o.payee_id',
	// 	// 	'COALESCE(DATE(FROM_UNIXTIME(a.time)), DATE(o.cdate)) AS time',
	// 	// 	'a.spent',
	// 	// 	'COALESCE(a.earn, o.original_amount) AS credits',
	// 	// 	'a.balance',
	// 	// 	"COALESCE(a.comment, 'COM_SOCIALADS_WALLET_ADS_PAYMENT') AS comment"
	// 	// ])
	// 	// ->from($db->quoteName('#__ad_orders', 'o'))
	// 	// ->leftJoin($db->quoteName('#__ad_wallet_transc', 'a') . ' ON a.type_id = o.id')
	// 	// ->where('o.payee_id = ' . (int) $user_id)
	// 	// ->order('o.id DESC');

	// 	// if ($month && $year)
	// 	// {
	// 	// 	$query->where("MONTH(COALESCE(DATE(FROM_UNIXTIME(a.time)), DATE(o.cdate))) = " . (int) $month);
	// 	// 	$query->where("YEAR(COALESCE(DATE(FROM_UNIXTIME(a.time)), DATE(o.cdate))) = " . (int) $year);
	// 	// }
	// 	// elseif ($year)
	// 	// {
	// 	// 	$query->where("YEAR(COALESCE(DATE(FROM_UNIXTIME(a.time)), DATE(o.cdate))) = " . (int) $year);
	// 	// }

	// 	// return $query;
	// }

		protected function getListQuery()
		{
			$db = $this->getDbo();
			$user_id = (int) $this->getState('user');
			$month   = (int) $this->getState('month');
			$year    = (int) $this->getState('year');

			// Query 1: Wallet entries (confirmed)
			$query1 = $db->getQuery(true);
			$query1
				->select([
					$db->quoteName('o.id', 'order_id'),
					$db->quoteName('o.status', 'order_status'),
					$db->quoteName('o.coupon'),
					$db->quoteName('o.payee_id'),
					$db->quoteName('a.time', 'sort_time'),
					'DATE(FROM_UNIXTIME(' . $db->quoteName('a.time') . ')) AS ' . $db->quoteName('time'),
					$db->quoteName('a.spent'),
					$db->quoteName('a.earn', 'credits'),
					$db->quoteName('a.balance'),
					$db->quoteName('a.comment'),
					'0 AS ' . $db->quoteName('is_pending')
				])
				->from($db->quoteName('#__ad_wallet_transc', 'a'))
				->innerJoin(
					$db->quoteName('#__ad_orders', 'o') . ' ON ' .
					$db->quoteName('a.type_id') . ' = ' . $db->quoteName('o.id')
				)
				->where($db->quoteName('o.payee_id') . ' = ' . $db->quote($user_id));

			// Add date filters for query1
			if ($month && $year) {
				$query1
					->where('MONTH(FROM_UNIXTIME(' . $db->quoteName('a.time') . ')) = ' . $db->quote($month))
					->where('YEAR(FROM_UNIXTIME(' . $db->quoteName('a.time') . ')) = ' . $db->quote($year));
			} elseif ($year) {
				$query1->where('YEAR(FROM_UNIXTIME(' . $db->quoteName('a.time') . ')) = ' . $db->quote($year));
			}

			// Query 2: Pending orders NOT in wallet
			$query2 = $db->getQuery(true);
			$query2
				->select([
					$db->quoteName('o.id', 'order_id'),
					$db->quoteName('o.status', 'order_status'),
					$db->quoteName('o.coupon'),
					$db->quoteName('o.payee_id'),
					'UNIX_TIMESTAMP(NOW()) AS ' . $db->quoteName('sort_time'),
					'DATE(' . $db->quoteName('o.cdate') . ') AS ' . $db->quoteName('time'),
					'NULL AS ' . $db->quoteName('spent'),
					$db->quoteName('o.original_amount', 'credits'),
					'NULL AS ' . $db->quoteName('balance'),
					$db->quote('COM_SOCIALADS_WALLET_ADS_PAYMENT') . ' AS ' . $db->quoteName('comment'),
					'1 AS ' . $db->quoteName('is_pending')
				])
				->from($db->quoteName('#__ad_orders', 'o'))
				->leftJoin(
					$db->quoteName('#__ad_wallet_transc', 'a') . ' ON ' .
					$db->quoteName('a.type_id') . ' = ' . $db->quoteName('o.id')
				)
				->where($db->quoteName('o.payee_id') . ' = ' . $db->quote($user_id))
				->where($db->quoteName('o.status') . ' = ' . $db->quote('P'))
				->where($db->quoteName('a.type_id') . ' IS NULL');

			// Add date filters for query2
			if ($month && $year) {
				$query2
					->where('MONTH(' . $db->quoteName('o.cdate') . ') = ' . $db->quote($month))
					->where('YEAR(' . $db->quoteName('o.cdate') . ') = ' . $db->quote($year));
			} elseif ($year) {
				$query2->where('YEAR(' . $db->quoteName('o.cdate') . ') = ' . $db->quote($year));
			}

			// Combine with UNION and add ORDER BY
			$query1->union($query2);
			$query1->order($db->quoteName('sort_time') . ' DESC');

			return $query1;
		}

	/**
	 * Method to get item data
	 *
	 * @return  form data
	 *
	 * @since   2.2
	 */

	public function getItems()
	{
		$ad_stat = parent::getItems();
		
		$all_info = $camp_name = $coupon_code = $ad_title = array();
		
		// Store the actual available balance separately
		$actualWalletBalance = 0;
		$walletBalance = 0;
		
		if (!empty($ad_stat))
		{
			// Get and preserve the actual available balance
			$actualWalletBalance = $this->getWalletBalance($this->getState('user'));
			
			foreach ($ad_stat as $key)
			{
				// Campaign name query
				$db = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select(array('campaign'))
					->from($db->quoteName('#__ad_campaign'))
					->where($db->quoteName('id') . " = " . $db->quote($key->type_id));
				
				$db->setQuery($query);
				$camp_name[$key->type_id] = $db->loadObjectList();
				
				// Coupon code query
				$db = Factory::getDbo();
				$query = $db->getQuery(true);
				$query->select(array('coupon','status'))
					->from($db->quoteName('#__ad_orders'))
					->where($db->quoteName('id') . " = " . $db->quote($key->type_id));
				
				$db->setQuery($query);
				$orderData = $db->loadObject();
				$coupon_code[$key->type_id] = $orderData->coupon ?? '';
				$key->status = $orderData->status;
				
				if ($key->order_status == 'C')
				{
					$walletBalance = $key->balance;
					break;
				}
				
				$ad_til = explode('|', $key->comment);
				
				if (isset($ad_til[1]))
				{
					$query  = $db->getQuery(true);
					$query->select($db->quoteName('ad_title'));
					$query->from($db->quoteName('#__ad_data'));
					$query->where($db->quoteName('ad_id') . ' = ' . $ad_til[1]);
					
					$this->_db->setQuery($query);
					$ad_title[$ad_til[1]] = $this->_db->loadresult();
				}
			}
		}
		
		// Use the actual available balance
		$all_info['wallet_balance'] = $actualWalletBalance;
		
		array_push($all_info, $ad_stat, $camp_name, $coupon_code, $ad_title);
		return $all_info;
	}

	// public function getItems()
	// {
	// 	$ad_stat = parent::getItems();
	

    //     $all_info = $camp_name = $coupon_code = $ad_title = array();

	// 	$walletBalance = 0;

	// 	if (!empty($ad_stat))
	// 	{
	// 		// $walletBalance = $ad_stat[0]->balance;
	// 		$walletBalance = $this->getWalletBalance($this->getState('user'));
	// 		// print_r($walletBalance);die;

	// 		foreach ($ad_stat as $key)
	// 		{
	// 			// To get campaign name
	// 			$db = Factory::getDbo();
	// 			$query = $db->getQuery(true);
	// 			$query->select(array('campaign'))
	// 				->from($db->quoteName('#__ad_campaign'))
	// 				->where($db->quoteName('id') . " = " . $db->quote($key->type_id));

	// 			$db->setQuery($query);
	// 			$camp_name[$key->type_id] = $db->loadObjectList();

	// 			// To get coupon code
	// 			$db = Factory::getDbo();
	// 			$query = $db->getQuery(true);
	// 			// $query->select(array('coupon'))
	// 			$query->select(array('coupon','status'))
	// 				->from($db->quoteName('#__ad_orders'))
	// 				->where($db->quoteName('id') . " = " . $db->quote($key->type_id));

	// 			$db->setQuery($query);
	// 			$orderData = $db->loadObject();
	// 			$coupon_code[$key->type_id] = $orderData->coupon ?? '';
	// 			$key->status = $orderData->status;

	// 			if ($key->order_status == 'C')
	// 			{
	// 				$walletBalance = $key->balance;
	// 				break;
	// 			}

	// 			$ad_til = explode('|', $key->comment);

	// 			if (isset($ad_til[1]))
	// 			{
	// 				$query	= $db->getQuery(true);
	// 				$query->select($db->quoteName('ad_title'));
	// 				$query->from($db->quoteName('#__ad_data'));
	// 				$query->where($db->quoteName('ad_id') . ' = ' . $ad_til[1]);

	// 				$this->_db->setQuery($query);
	// 				$ad_title[$ad_til[1]] = $this->_db->loadresult();
	// 			}

	// 			// $walletBalance = $key->balance;
	// 		}
	// 	}

	// 	$all_info['wallet_balance'] = $walletBalance;
	// 	// echo "<pre>";print_r($all_info['wallet_balance']);die();

	// 	array_push($all_info, $ad_stat, $camp_name, $coupon_code, $ad_title);
	// 	//echo"<pre>";print_r($all_info);die;
	// 	return $all_info;
	// }

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return   JTable    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'ad_wallet_transc', $prefix = 'SocialadsTable', $config = array())
	{
		$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	public function getWalletBalance($userId)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
				$this->getState(
						'list.select', 'DISTINCT a.*'
				)
		);
		$query->from($db->quoteName('#__ad_wallet_transc', 'a'));
		$query->select('username AS created_by, u.email, SUM(a.earn) as total_earn, SUM(a.spent) as total_spent, (SUM(a.earn) - SUM(a.spent)) as total_payment');
		$query->join('INNER', $db->quoteName('#__users', 'u') . 'ON' . $db->quoteName('u.id') . '=' . $db->quoteName('a.user_id'));
		$query->where($db->quoteName('a.user_id') . ' = ' . $userId);
		$query->group('a.user_id');

		$db->setQuery($query);
		$result = $db->loadObject();
		
		if ($result)
		{
			return $result->total_payment;
		}
		else 
		{
			return 0;
		}
	}
}
