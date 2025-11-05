<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Socialads
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * View class for Third Party Enrollment Profile.
 *
 * @since  1.6
 */
class SocialadsViewThirdPartyEnrollmentProfile extends HtmlView
{
	protected $item;
	protected $params;
	protected $zones;
	protected $locationDetails;

	/**
	 * Display the view
	 *
	 * @param   array  $tpl  An optional associative array.
	 *
	 * @return  array
	 *
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$id = $input->getInt('id', 0);

		if (!$id) {
			throw new Exception(Text::_('COM_SOCIALADS_ERROR_THIRD_PARTY_NOT_FOUND'), 404);
		}

		// Get the model
		$model = $this->getModel('ThirdPartyEnrollmentProfile');
		$this->item = $model->getItem($id);
		
		if (!$this->item || !$this->item->id) {
			throw new Exception(Text::_('COM_SOCIALADS_ERROR_THIRD_PARTY_NOT_FOUND'), 404);
		}

		$this->params = ComponentHelper::getParams('com_socialads');
		$this->zones = $model->getZones();
		$this->locationDetails = $model->getLocationDetails($id);

		// Check for errors.
		if ($this->get('Errors') && count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors));
		}

		parent::display($tpl);
	}
}
