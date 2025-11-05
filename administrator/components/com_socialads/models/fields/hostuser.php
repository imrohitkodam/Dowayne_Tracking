<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('list');

class JFormFieldHostuser extends JFormFieldList
{
    protected $type = 'hostuser';

    public function getOptions()
    {
        $app   = Factory::getApplication();
        $input = $app->input;
        $id    = (int) $input->getInt('id');
        
        $options = [];
        $db      = Factory::getDbo();

        // Get group ID for 'Social Ads Hosts'
        $groupTitle = 'Social Ads Hosts';

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__usergroups'))
            ->where($db->quoteName('title') . ' = ' . $db->quote($groupTitle));

        $db->setQuery($query);
        $hostGroupId = (int) $db->loadResult();

        if ($hostGroupId > 0)
        {
            // Get user IDs from the enrollment table
            $subQuery = $db->getQuery(true)
                ->select($db->quoteName('created_by'))
                ->from($db->quoteName('#__ad_third_party_enrollment'))
                ->where($db->quoteName('id') . '!= ' . (int) $id);
      
            // Get eligible users NOT in enrollment table
            $query = $db->getQuery(true)
                ->select('u.id, u.name')
                ->from($db->quoteName('#__users', 'u'))
                ->join('INNER', $db->quoteName('#__user_usergroup_map', 'm') . ' ON u.id = m.user_id')
                ->where('m.group_id = ' . (int) $hostGroupId)
                ->where('u.block = 0')
                ->where('u.id NOT IN (' . $subQuery . ')')
                ->order('u.name ASC');

            $db->setQuery($query);
            $users = $db->loadObjectList();

            // Add select option
            $options[] = HTMLHelper::_('select.option', '', Text::_('COM_SOCIALADS_SELECT_HOST_USER'));

            foreach ($users as $user)
            {
                $options[] = HTMLHelper::_('select.option', $user->id, $user->name);
            }
        }

        return $options;
    }

}
