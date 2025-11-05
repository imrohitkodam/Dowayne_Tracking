<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    SocialAds
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\Factory;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Socialads model.
 *
 * @since  1.6
 */
class SocialadsModelThirdParty extends FormModel
{
	protected $item = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_socialads.edit.thirdparty.id');
		}
		else
		{
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_socialads.edit.thirdparty.id', $id);
		}

		if (!$id)
		{
			$id = $this->getItemIdByAuthUser();
			Factory::getApplication()->setUserState('com_socialads.edit.thirdparty.id', $id);
		}

		if ($app->isClient('administrator'))
		{
			Factory::getApplication()->setUserState('com_socialads.edit.thirdparty.id', null);
			$id = $app->input->getInt('id');
		} 

		$this->setState('thirdparty.id', $id);

		if ($app->isClient('site')) {
			$params = $app->getParams();
		} else {
			$params = ComponentHelper::getParams('com_socialads');
		}

		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('thirdparty.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return    mixed    Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		if ($this->item === null)
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('thirdparty.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table !== false && $table->load($id))
			{
				$user = Factory::getUser();
				$id   = $table->id;
				$canEdit = $user->authorise('core.edit', 'com_socialads') || $user->authorise('core.create', 'com_socialads');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_socialads'))
				{
					$canEdit = $user->id == $table->created_by;
				}

				if (!$canEdit)
				{
					throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 500);
				}

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->item = ArrayHelper::toObject($properties, 'JObject');
			}
		}

		return $this->item;
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $type    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModelLegacy
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'ThirdPartyEnrollment', $prefix = 'SocialadsTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_socialads/tables');

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get item id using auth user
	 *
	 *
	 * @return    boolean        True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItemIdByAuthUser()
	{
		$table = $this->getTable();
		$user         = Factory::getUser();

		$table->load(array( 'created_by' => $user->id ));

		return $table->id;
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  boolean|object JForm    A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_socialads', 'thirdparty', array('control'   => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_socialads.edit.thirdparty.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed   The user id on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		$app  = Factory::getApplication();
		$id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('thirdparty.id');
		$state = (!empty($data['state'])) ? 1 : 0;
		$isNew = $id ? false : true;
		$user  = Factory::getUser();

		if ($id)
		{
			// Check the user can edit this item
			$authorised = $user->authorise('core.edit', 'com_socialads') || $authorised = $user->authorise('core.edit.own', 'com_socialads');

			if ($user->authorise('core.edit.state', 'com_socialads') !== true && $state == 1)
			{
				// The user cannot edit the state of the item.
				$data['state'] = 0;
			}
		}
		else
		{
			// Check the user can create new items in this section
			$authorised = $user->authorise('core.create', 'com_socialads');

			if ($user->authorise('core.edit.state', 'com_socialads') !== true && $state == 1)
			{
				// The user cannot edit the state of the item.
				$data['state'] = 0;
			}
		}

		if ($authorised !== true)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		if ($app->isClient('administrator'))
		{
			$hostUserId = isset($data['created_by']) ? (int) $data['created_by'] : 0;
		
			$data['created_by'] = $hostUserId;
		}
		else
		{
			$data['created_by'] = $user->id;
		}


		$table = $this->getTable();

		if ($table->save($data) === true)
		{
			$thirdPartyId = $table->id;
			$data = Factory::getApplication()->input->get('jform', array(), 'array');
			$db = Factory::getDbo();
			$populationAreas = $data['populationAreas'] ? json_decode($data['populationAreas']) : [];
			if (count($populationAreas))
			{
				$query	= $db->getQuery(true);
				$query->select($db->quoteName('id'));
				$query->from($db->quoteName('#__ad_third_party_locations', 'a'));
				$query->where($db->quoteName('a.third_party_id') . ' = ' . $thirdPartyId);
				$db->setQuery($query);
				$alreadyPresentThirdPartyLocations = $db->loadColumn();
				$newPresentThirdPartyLocations = array_column($populationAreas, 'id');

				$deletedlocations = array_diff($alreadyPresentThirdPartyLocations, $newPresentThirdPartyLocations);

				if (count($deletedlocations))
				{
					$query = "DELETE FROM #__ad_third_party_locations WHERE id IN (" . implode(',', $deletedlocations) . ")";

					$db->setQuery($query);

					if (!$db->execute())
					{
						echo $db->stderr();

						return false;
					}
				}

				foreach ($populationAreas as $area)
				{
					$fielddata          = new stdClass;
					$fielddata->third_party_id   = $thirdPartyId;
					$fielddata->location = "(" . $area->location->lat . ",". $area->location->lng . ")";
					$fielddata->radius  = $area->radius;
					$fielddata->city  = $area->city;
					$fielddata->region  = $area->region;
					$fielddata->country  = $area->country;
					$fielddata->count  = $area->count;

					if ($area->id)
					{
						$fielddata->id = $area->id;
					}

					if ($fielddata->id)
					{
						if (!$db->updateObject('#__ad_third_party_locations', $fielddata, 'id'))
						{
							echo $db->stderr();

							return false;
						}
					}
					elseif (!$db->insertObject('#__ad_third_party_locations', $fielddata, 'id'))
					{
						echo $db->stderr();

						return false;
					}
				}
			}
			else 
			{
				$query = "DELETE FROM #__ad_third_party_locations WHERE third_party_id=" . $thirdPartyId;

				$db->setQuery($query);

				if (!$db->execute())
				{
					echo $db->stderr();

					return false;
				}
			}

			return $thirdPartyId;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to validate the Organization contact form data from server side.
	 *
	 * @param   \JForm  $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   2.5.0
	 */
	public function validate($form, $data, $group = null)
	{
		$return = true;
		$app   = Factory::getApplication();
		$input = $app->input;
		$discountdata = $conditionData = array();
		$promoData = $input->post->get('jform', '', 'array');

		$data   = parent::validate($form, $data, $group);

		return ($return === true) ? $data: false;
	}

	/**
	 * Method to get the form data.
	 *
	 * @param   array  $thid_party_id .
	 *
	 * @return  mixed   The user id on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getLocationDetails($thid_party_id)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->select($db->qn('location'))
			->select($db->qn('radius'))
			->select($db->qn('region'))
			->select($db->qn('city'))
			->select($db->qn('country'))
			->select($db->qn('count'))
			->from($db->qn('#__ad_third_party_locations'))
			->where($db->qn('third_party_id') . ' = '. $thid_party_id);

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 *
	 * @since	2.1
	 */
	public function getZoomSize($thid_party_id)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('map_zoom_size'))
			->from($db->qn('#__ad_third_party_enrollment'))
			->where($db->qn('id') . ' = '. $thid_party_id);

		$db->setQuery($query);

		return $db->loadResult();
	}
}
