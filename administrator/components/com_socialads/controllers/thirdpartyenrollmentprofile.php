<?php
/**
 * @package     SocialAds
 * @subpackage  com_socialads
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Response\JsonResponse;

/**
 * Third Party Enrollment Profile controller class.
 *
 * @since  1.0.0
 */
class SocialadsControllerThirdPartyEnrollmentProfile extends BaseController
{
    /**
     * Method to display the view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link InputFilter::clean()}.
     *
     * @return  BaseController  This object to support chaining.
     *
     * @since   1.0.0
     */
    public function display($cachable = false, $urlparams = array())
    {
        $input = $this->input;
        $id = $input->getInt('id', 0);

        if (!$id) {
            throw new Exception('Invalid enrollment ID', 404);
        }

        // Set the view and layout
        $input->set('view', 'thirdpartyenrollmentprofile');
        $input->set('layout', 'default');

        return parent::display($cachable, $urlparams);
    }

    /**
     * Method to get HTML widget code via AJAX.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function getWidgetCode()
    {
        // Check for request forgeries
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $input = $this->input;
        $enrollmentId = $input->getInt('enrollment_id', 0);
        $zoneId = $input->getInt('zone_id', 0);

        if (!$enrollmentId || !$zoneId) {
            echo new JsonResponse(null, 'Invalid parameters', true);
            return;
        }

        try {
            $model = $this->getModel('ThirdPartyEnrollmentProfile');
            $widgetCode = $model->getWidgetCode($enrollmentId, $zoneId);

            if ($widgetCode) {
                echo new JsonResponse($widgetCode);
            } else {
                echo new JsonResponse(null, 'Failed to generate widget code', true);
            }
        } catch (Exception $e) {
            echo new JsonResponse(null, $e->getMessage(), true);
        }

        Factory::getApplication()->close();
    }
}
