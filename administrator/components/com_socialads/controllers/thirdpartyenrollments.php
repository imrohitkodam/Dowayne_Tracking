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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

/**
 * Coupons list controller class.
 *
 * @since  1.6
 */
class SocialadsControllerThirdPartyEnrollments extends AdminController
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
	public function getModel($name = 'thirdpartyenrollments', $prefix = 'SocialadsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * publish campaign.
	 *
	 * @return void
	 *
	 * @since  3.1.15
	 */
	public function publish()
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app   = Factory::getApplication();
		$cid   = $app->input->get('cid', '', 'array');
		$data  = array('publish' => 1, 'unpublish' => 0);
		$task  = $app->input->get('task', '', 'STRING');
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		if (!is_array($cid) || count($cid) < 1)
		{
			$this->setMessage(Text::sprintf('COM_SOCIALADS_NO_ITEM_SELECTED'), 'warning');
		}
		else
		{
			$model = $this->getModel();

			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);

			// Publish the items.
			try
			{
				$model->publish($cid, $value);

				if ($value === 1)
				{
					$ntext = 'COM_SOCIALADS_N_THIRD_PARTY_ENROLLMENTS_PUBLISHED';
				}
				else
				{
					$ntext = 'COM_SOCIALADS_N_THIRD_PARTY_ENROLLMENTS_UNPUBLISHED';
				}

				$this->setMessage(Text::plural($ntext, count($cid)));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		$this->setRedirect(Route::_('index.php?option=com_socialads&view=thirdpartyenrollments', false));
	}
}
