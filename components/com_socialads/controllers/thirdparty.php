<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    SocialAds
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Component\ComponentHelper;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Campaign controller class.
 *
 * @since  1.6
 */
class SocialadsControllerThirdParty extends SocialadsController
{
	/**
	 * Method to save a user's profile data.
	 *
	 * @return    void
	 *
	 * @since    1.6
	 */
	public function save()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$id    = $app->input->post->get('cid');
		$model = $this->getModel('thirdparty', 'SocialadsModel');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new Exception($model->getError(), 500);
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$input = $app->input;
			$jform = $input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_socialads.edit.thirdparty.data', $jform, array());

			// Redirect back to the edit screen.
			$this->setRedirect(Route::_('index.php?option=com_socialads&view=thirdparty&layout=edit&id=' . $id, false));

			return false;
		}

		$data['id'] = $id;

		// Attempt to save the data.
		$return     = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_socialads.edit.thirdparty.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_socialads.edit.thirdparty.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_socialads&view=thirdparty&layout=edit&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_socialads.edit.thirdparty.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_SOCIALADS_ITEM_SAVED_SUCCESSFULLY'));
		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = 'index.php?option=com_socialads&view=thirdparty';

		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_socialads.edit.thirdparty.data', null);
	}

	/**
	 * Method to set cancel edit operation
	 *
	 * @return void
	 *
	 * @since   2.2
	 */
	public function setCookiees()
	{
		$app = Factory::getApplication();
				$url          = 'index.php?option=com_socialads&task=thirdparty.getImageById&id=12';
				$inputCookie  = Factory::getApplication()->input->cookie;


		// Set cookie data
		$inputCookie->set($name = 'testcookiefrombase', $value = '00000');
		setcookie("TestCookie", 'dfdvar', time()+60*60*24*30, '/');

		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Method to set cancel edit operation
	 *
	 * @return void
	 *
	 * @since   2.2
	 */
	public function getImageById()
	{
		$app   = Factory::getApplication();
		$db    = Factory::getDBO();
		$id    = $app->input->get('id');
		$zoneid    = $app->input->get('zoneid');

		if (!$id || !$zoneid)
		{
			echo "Something went wrong";

			return false;
		}

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_socialads/tables');
		$thirdPartyDetails = Table::getInstance('thirdpartyenrollment', 'SocialadsTable', array('dbo', $db));
		$thirdPartyDetails->load(array('id' => $id));

		if (!$thirdPartyDetails->id)
		{
			echo "Something went wrong";

			return false;
		}

		// Proceed further only if this component folder exists
		if (Folder::exists(JPATH_ROOT . '/components/com_socialads'))
		{
			$lang = Factory::getLanguage();
			$lang->load('mod_socialads', JPATH_SITE);

			// SocialAds config parameters
			$sa_params = ComponentHelper::getParams('com_socialads');

			$saInitClassPath = JPATH_SITE . '/components/com_socialads/init.php';

			if (!class_exists('SaInit'))
			{
				JLoader::register('SaInit', $saInitClassPath);
				JLoader::load('SaInit');
			}

			// Define autoload function
			spl_autoload_register('SaInit::autoLoadHelpers');
			$resultAds   = [];

			$resultAds = SaAdEngineHelper::getInstance()->getAdsForThidParty($id, $zoneid);

			if (count($resultAds))
			{
				$adData     = is_array($resultAds[0]) ? $resultAds[0][0] : $resultAds[0];

				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__ad_data'))
					->where($db->qn('ad_id') . ' = '. $adData->ad_id);

				$db->setQuery($query);

				$adData = $db->loadObject();

				$file         = JPATH_SITE . '/' . $adData->ad_image;

				Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_socialads/tables');
				$adDisplayedRecords = Table::getInstance('thirdpartyaddisplayed', 'SocialadsTable', array('dbo', $db));
				$adDisplayedRecords->load(array('third_party_id' => $id));

				$db    = Factory::getDBO();
				$fielddata          = new stdClass;
				$fielddata->third_party_id      = $id;

				if ($adDisplayedRecords->id)
				{
					$fielddata->id      = $adDisplayedRecords->id;
					$alreadyExistId = explode(",", $adDisplayedRecords->ad_ids);
					$newAdId     = $adData->ad_id;

					if(in_array($newAdId, $alreadyExistId)) 
					{
						if (($key = array_search($newAdId, $alreadyExistId)) !== false) {
							unset($alreadyExistId[$key]);
						}
					}

					array_unshift($alreadyExistId, $newAdId);
					$fielddata->ad_ids = implode(",", $alreadyExistId);
					if (!$db->updateObject('#__third_party_ad_displayed', $fielddata, 'id'))
					{
						echo $db->stderr();

						return false;
					}
				} 
				else 
				{
					$fielddata->ad_ids   = $adData->ad_id;
					if (!$db->insertObject('#__third_party_ad_displayed', $fielddata, 'id'))
					{
						echo $db->stderr();

						return false;
					}
				}

				$file         = JPATH_SITE . '/' . $adData->ad_image;
				$type = 'image/gif';
				header('Content-Type:'.$type);
				header('Content-Length: ' . filesize($file));
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
				readfile($file);
				exit();
			}
		}
	}

	/**
	 * Method to set cancel edit operation
	 *
	 * @return void
	 *
	 * @since   2.2
	 */
	public function getURLById()
	{
		$app   = Factory::getApplication();
		$id    = $app->input->get('id', '', 'int');
		$zoneid    = $app->input->get('zoneid');

		if (!$id || !$zoneid)
		{
			echo "Something went wrong";

			return false;
		}

		$this->setRedirect(Route::_('index.php?option=com_socialads&view=thirdpartypreviewAds&layout=default&tmpl=component&id=' . $id . "&zoneid=" . $zoneid, false));
	}


	/**
	 * Get Ad Html
	 *
	 * @return  html
	 */
	public function getThirdPartyAdHtml()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: *");
		$app   = Factory::getApplication();
		$input = Factory::getApplication()->input;
		$db    = Factory::getDBO();
		$id    = $app->input->get('id');
		$zoneid    = $app->input->get('zoneid');

		if (!$id || !$zoneid)
		{
			echo "Something went wrong";

			return false;
		}


		Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_socialads/tables');
		$thirdPartyDetails = Table::getInstance('thirdpartyenrollment', 'SocialadsTable', array('dbo', $db));
		$thirdPartyDetails->load(array('id' => $id));

		if (!$thirdPartyDetails->id)
		{
			echo "Something went wrong";

			return false;
		}


		// Proceed further only if this component folder exists
		if (Folder::exists(JPATH_ROOT . '/components/com_socialads'))
		{
			$lang = Factory::getLanguage();
			$lang->load('mod_socialads', JPATH_SITE);

			// SocialAds config parameters
			$sa_params = ComponentHelper::getParams('com_socialads');

			$saInitClassPath = JPATH_SITE . '/components/com_socialads/init.php';

			if (!class_exists('SaInit'))
			{
				JLoader::register('SaInit', $saInitClassPath);
				JLoader::load('SaInit');
			}

			// Define autoload function
			spl_autoload_register('SaInit::autoLoadHelpers');
			$resultAds   = [];

			$resultAds = SaAdEngineHelper::getInstance()->getAdsForThidParty($id, $zoneid);

			if (count($resultAds))
			{
				$adData     = is_array($resultAds[0]) ? $resultAds[0][0] : $resultAds[0];
				$adHTML     = SaAdEngineHelper::getAdHtml((int) $adData->ad_id, 1, 0, '', $id);

				Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_socialads/tables');
				$adDisplayedRecords = Table::getInstance('thirdpartyaddisplayed', 'SocialadsTable', array('dbo', $db));
				$adDisplayedRecords->load(array('third_party_id' => $id));

				$db    = Factory::getDBO();
				$fielddata          = new stdClass;
				$fielddata->third_party_id      = $id;

				if ($adDisplayedRecords->id)
				{
					$fielddata->id      = $adDisplayedRecords->id;
					$alreadyExistId = explode(",", $adDisplayedRecords->ad_ids);
					$newAdId     = $adData->ad_id;

					if(in_array($newAdId, $alreadyExistId)) 
					{
						if (($key = array_search($newAdId, $alreadyExistId)) !== false) {
							unset($alreadyExistId[$key]);
						}
					}

					//Remove all alternative ad id belong to same zone if the ad is normal ad
					$checkAlternativeAd = Table::getInstance('ad', 'SocialadsTable', array('dbo', $db));
					$checkAlternativeAd->load(array('ad_id' => $newAdId, 'ad_alternative' => 0));

					if($checkAlternativeAd->ad_id)
					{
						$query = "SELECT a.ad_id
							FROM #__ad_data as a 
							LEFT JOIN #__ad_campaign as c ON c.id = a.camp_id  
							WHERE a.ad_zone = $zoneid 
							AND a.ad_alternative = 1";

						$db->setQuery($query);
						$alternativeAds = $db->loadColumn();

						$alreadyExistId = array_diff($alreadyExistId, $alternativeAds);
					}


					array_unshift($alreadyExistId, $newAdId);
					$fielddata->ad_ids = implode(",", $alreadyExistId);
					if (!$db->updateObject('#__third_party_ad_displayed', $fielddata, 'id'))
					{
						echo $db->stderr();

						return false;
					}
				} 
				else 
				{
					$fielddata->ad_ids   = $adData->ad_id;
					if (!$db->insertObject('#__third_party_ad_displayed', $fielddata, 'id'))
					{
						echo $db->stderr();

						return false;
					}
				}

				header('Content-type: application/html');
				echo $adHTML;
			}

		}

		jexit();
	}
}
