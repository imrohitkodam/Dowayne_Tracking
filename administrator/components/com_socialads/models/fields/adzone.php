<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Socialads
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('list');

/**
 * Custom Field class for the Joomla Framework.
 *
 * @package  Com_Socialads
 *
 * @since    1.6
 */
class JFormFieldAdzone extends JFormFieldList
{
    public $type = 'adzone';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 *
	 * @since	1.6
	 */
	public function getOptions()
	{
		// Initialize variables.
		$options = array();
		$db    = Factory::getDBO();

		$query       = $db->getQuery(true);

		$query->select('DISTINCT (z.id)');
		$query->select('z.zone_name');
		$query->from($db->quoteName('#__ad_zone', 'z'));
		$query->where($db->qn('z.state') . ' = 1');
		$db->setQuery($query);

		$zoneList = $db->loadObjectList();

		$options[] = HTMLHelper::_('select.option', 0, Text::_('COM_SOCIALADS_SELECT_ZONE'));

		foreach ($zoneList as $c)
		{
			$options[] = HTMLHelper::_('select.option', $c->id, $c->zone_name);
		}

		return $options;
	}
}
