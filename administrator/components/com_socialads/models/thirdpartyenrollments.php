	<?php

	/**
	 * @version    SVN:<SVN_ID>
	 * @package    Com_Socialads
	 * @author     Techjoomla <extensions@techjoomla.com>
	 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
	 * @license    GNU General Public License version 2, or later
	 */
	defined('_JEXEC') or die;

	use Joomla\CMS\Factory;
	use Joomla\CMS\Table\Table;
	use Joomla\CMS\MVC\Model\ListModel;
	use Joomla\CMS\Component\ComponentHelper;

	/**
	 * Methods supporting a list of Socialads records.
	 *
	 * @since  1.6
	 */
	class SocialadsModelThirdPartyEnrollments extends ListModel
	{
		/**
		 * Constructor.
		 *
		 * @param   array  $config  An optional associative array of configuration settings.
		 *
		 * @see  JController
		 *
		 * @since  1.6
		 */
		public function __construct($config = array())
		{
			if (empty($config['filter_fields']))
			{
				$config['filter_fields'] = array(
					'id', 'a.id',
					'business_name', 'a.business_name',
					'created_by', 'a.created_by',
					'created_by_username', 'u.username'
				);
			}

			parent::__construct($config);
		}

		/**
		 * Returns a Table object, always creating it.
		 *
		 * @param   string  $type    The table type to instantiate
		 * @param   string  $prefix  A prefix for the table class name. Optional.
		 * @param   array   $config  Configuration array for model. Optional.
		 *
		 * @return  JTable    A database object
		 */
		public function getTable($type = 'thirdpartyenrollment', $prefix = 'SocialadsTable', $config = array())
		{
			return Table::getInstance($type, $prefix, $config);
		}


		/**
		 * Method to auto-populate the model state.
		 *
		 * @param   integer  $ordering   An optional associative array of configuration settings.
		 * @param   integer  $direction  An optional associative array of configuration settings.
		 *
		 * @return  integer
		 *
		 * Note. Calling getState in this method will result in recursion.
		 */
		protected function populateState($ordering = null, $direction = null)
		{
			// Initialise variables.
			$app = Factory::getApplication('administrator');

			// Load the filter state.
			$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
			$this->setState('filter.search', $search);

			$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
			$this->setState('filter.state', $published);

			// Load the parameters.
			$params = ComponentHelper::getParams('com_socialads');
			$this->setState('params', $params);

			// List state information.
			parent::populateState('a.id', 'asc');
		}

		/**
		 * Method to get a store id based on model configuration state.
		 *
		 * This is necessary because the model is used by the component and
		 * different modules that might need different sets of data or different
		 * ordering requirements.
		 *
		 * @param   string  $id  A prefix for the store id.
		 *
		 * @return  string   A store id.
		 *
		 * @since  1.6
		 */
		protected function getStoreId($id = '')
		{
			// Compile the store id.
			$id .= ':' . $this->getState('filter.search');
			$id .= ':' . $this->getState('filter.state');

			return parent::getStoreId($id);
		}

		/**
		 * Build an SQL query to load the list data.
		 *
		 * @return	JDatabaseQuery
		 *
		 * @since	1.6
		 */
		protected function getListQuery()
		{
			// Create a new query object.
			$db = $this->getDbo();
			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->select(
					$this->getState(
							'list.select', 'DISTINCT a.*'
					)
			);
			$query->select('SUM(l.count) as population_count');
			$query->select('u.username as created_by_username');
			$query->from($db->quoteName('#__ad_third_party_enrollment', 'a'));
			$query->join('LEFT', $db->quoteName('#__ad_third_party_locations', 'l') . 'ON' . $db->quoteName('l.third_party_id') . '=' . $db->quoteName('a.id'));
			$query->join('LEFT', $db->quoteName('#__users', 'u') . 'ON' . $db->quoteName('u.id') . '=' . $db->quoteName('a.created_by'));
			$query->group('l.third_party_id');

			// Filter by published state
			$published = $this->getState('filter.state');

			if (is_numeric($published))
			{
				$query->where($db->quoteName('a.state'). ' = ' . (int) $published);
			}
			elseif ($published === '')
			{
				$query->where('(a.state IN (0, 1))');
			}

			// Filter by search in title
			$search = $this->getState('filter.search');

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where($db->quoteName('a.id'). ' = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
							$query->where('( a.id LIKE ' . $search .
							'  OR  a.business_name LIKE ' . $search .
							' )'
							);
				}
			}

			// Add the list ordering clause.
			$orderCol = $this->state->get('list.ordering');
			$orderDirn = $this->state->get('list.direction');

			if ($orderCol && $orderDirn)
			{
				$query->order($db->escape($orderCol . ' ' . $orderDirn));
			}

			return $query;
		}

		/**
		 * To get the values from table
		 *
		 * @return  items
		 *
		 * @since  1.6
		 */
		public function getItems()
		{
			$items = parent::getItems();

			return $items;
		}

		/**
		 * Method to change the published state of one or more records.
		 *
		 * @param   array    &$pks   A list of the primary keys to change.
		 * @param   integer  $value  The value of the published state.
		 *
		 * @return  boolean  True on success.
		 *
		 * @since   3.1.15
		 */
		public function publish(&$pks, $value = 1)
		{
			$app     = Factory::getApplication();
			$context = $app->input->get('option');
			$pks     = (array) $pks;
			$user    = Factory::getUser();
			$table   = $this->getTable();

			// Check if there are items to change
			if (!count($pks))
			{
				return true;
			}

			// Attempt to change the state of the records.
			if (!$table->publish($pks, $value, $user->get('id')))
			{
				$this->setError($table->getError());

				return false;
			}

			return true;
		}


		public function delete(&$pks)
		{
			$app     = Factory::getApplication();
			$context = $app->input->get('option');
			$pks     = (array) $pks;
			$user    = Factory::getUser();
			$table   = $this->getTable();

			if (!count($pks))
			{
				return true;
			}

			foreach ($pks as $pk)
			{
				if (!$table->delete($pk))
				{
					$this->setError($table->getError());
					return false;
				}
			}

			return true;
		}
	}
