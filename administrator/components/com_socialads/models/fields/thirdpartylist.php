<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Socialads
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

defined('JPATH_BASE') or die;
use Joomla\CMS\Form\FormField;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

/**
 * Custom Field class for the Joomla Framework.
 *
 * @package  Com_Socialads
 *
 * @since    1.6
 */
class JFormFieldThirdPartyList extends JFormFieldList
{
	protected $type = 'thirdpartylist';
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
		$user = Factory::getUser();
		$userid = $user->id;
		$db	= Factory::getDbo();
		$query	= $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.business_name')));
		$query->from($db->quoteName('#__ad_third_party_enrollment', 'a'));
        $query->where($db->quoteName('a.state'). ' = ' . (int) 1);

		// Get the options.
		$db->setQuery($query);

		$thirdPartyList = $db->loadObjectList();

		$options = array();

		$options[] = HTMLHelper::_('select.option', 0, 'Select Third Party');

		foreach ($thirdPartyList as $c)
		{
			$options[] = HTMLHelper::_('select.option', $c->id, $c->business_name);
		}

		return $options;
	}
}
