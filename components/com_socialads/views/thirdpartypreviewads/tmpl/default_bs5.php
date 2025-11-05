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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');

if (JVERSION < '4.0')
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_socialads', JPATH_ADMINISTRATOR);

?>

<div class="<?php echo SA_WRAPPER_CLASS; ?> thirdparty front-end-edit">
	<div class="page-header">
		<h1>
			Preview Ads
		</h1>
	</div>
	<div class="container">
		<div class="row">
			<?php foreach ($this->ads as $i => $ad)
			{ 
				if (in_array($ad, $this->withspecifiedZonesAd))
				{ ?>
					<div class="col-md-12">
						<?php
							echo SaAdEngineHelper::getAdHtml((int) $ad, 1, 0, '', $this->thirdPartId);
						?>
					</div>
					<?php
				}
			}?>
		</div>
	</div>
	
</div>
