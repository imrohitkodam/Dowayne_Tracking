<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.keepalive');

$canState = Factory::getUser()->authorise('core.edit.state','com_socialads');
$canEdit = Factory::getUser()->authorise('core.edit','com_socialads');
if($this->item->state == 1){
	$state_string = Text::_("COM_SOCIALADS_COUPONS_PUBLISHED");
	$state_value = 1;
}
else
{
	$state_string = Text::_("COM_SOCIALADS_COUPONS_UNPUBLISHED");
	$state_value = 0;
}
?>

<form action="<?php echo 'index.php?option=com_socialads&layout=edit&id=' . (int) $this->item->id; ?>" method="post"
	enctype="multipart/form-data" name="adminForm" id="campaign-form" class="form-validate">
	<div class="form-horizontal">
		<div class="row">
			<div class="col-md-10 form-horizontal">
				<fieldset class="adminform">
					<?php if ($canEdit):?>
						<div class="form-group row">
							<div class="form-label col-md-4 col-sm-4"><?php echo $this->form->getLabel('created_by'); ?></div>
							<div class="col-md-8 col-sm-8"><?php echo $this->form->getInput('created_by'); ?></div>
						</div>
						<div class="form-group row">
							<?php if(!$canState): ?>
							<div class="form-label col-md-4 col-sm-4"><?php echo $this->form->getLabel('state'); ?></div>
							<div class="col-md-8 col-sm-8"><?php echo $state_string; ?></div>
							<input type="hidden" name="jform[state]" value="<?php echo $state_value; ?>" />
							<?php else: ?>
								<div class="form-label col-md-4 col-sm-4"><?php echo $this->form->getLabel('state'); ?></div>
								<div class="col-md-8 col-sm-8"><?php echo $this->form->getInput('state'); ?></div>
							<?php endif; ?>
						</div>
						<div class="form-group row">
							<div class="form-label col-md-4 col-sm-4"><?php echo $this->form->getLabel('campaign'); ?></div>
							<div class="col-md-8 col-sm-8"><?php echo $this->form->getInput('campaign'); ?></div>
						</div>
						<div class="form-group row">
							<?php
							$params = ComponentHelper::getParams('com_socialads');
							$currency = $params->get('currency');
							?>
							<div class="form-label col-md-4 col-sm-4"><?php echo $this->form->getLabel('daily_budget'); ?></div>
							<div class="col-md-8 col-sm-8">
								<div class="input-append input-group">
									<?php echo $this->form->getInput('daily_budget'); ?>
									<span class="input-group-text"><?php echo SaCommonHelper::getCurrencySymbol(); ?></span>
								</div>
							</div>
						</div>

						<div class="form-group row">
							<?php
							$params = ComponentHelper::getParams('com_socialads');
							$currency = $params->get('currency');
							?>
							<div class="form-label col-md-4 col-sm-4"><?php echo $this->form->getLabel('total_budget'); ?></div>
							<div class="col-md-8 col-sm-8">
								<div class="input-append input-group">
									<?php echo $this->form->getInput('total_budget'); ?>
									<span class="input-group-text"><?php echo SaCommonHelper::getCurrencySymbol(); ?></span>
								</div>
							</div>
						</div>


						<div class="form-group row mt-2">
							<div class="form-label col-md-4 col-sm-4"><?php echo $this->form->getLabel('start_date'); ?></div>
							<div class="col-md-8 col-sm-8"><?php echo $this->form->getInput('start_date'); ?></div>
						</div>
						<div class="form-group row">
							<div class="form-label col-md-4 col-sm-4"><?php echo $this->form->getLabel('end_date'); ?></div>
							<div class="col-md-8 col-sm-8"><?php echo $this->form->getInput('end_date'); ?></div>
						</div>

						<?php 
							// While editing the campaign check and display the payment link based on the balance and total budget
							if ((int) $this->item->id)
							{
								?>

								<div class="form-group row">
									<div class="form-label col-md-4 col-sm-4"><?php echo Text::_('COM_SOCIALADS_FORM_LBL_CAMPAIGN_WALLET_BALANCE'); ?></div>
									<div class="col-md-8 col-sm-8">
										<input type="text" disabled readonly class="form-control availableBalance" value="<?php echo number_format($this->walletBalance, 2); ?>">
										<div role="alert" class="linkTab alert alert-info mt-3 <?php echo ($this->item->total_budget > $this->walletBalance) ? '' : 'd-none'; ?>">
											<?php
												$itemId = SaCommonHelper::getSocialadsItemid('payment');
												$link = Route::_(Uri::root() . 'index.php?option=com_socialads&view=payment&Itemid=' . $itemId);
												echo Text::sprintf('COM_SOCIALADS_NOTICE_AND_PAYMENT_LINK', $link);
											?>							
										</div>
									</div>
								</div>
								<?php
							}
					endif;?>
				</fieldset>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
			<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

<script type="text/javascript">
	var currency="<?php echo SaCommonHelper::getCurrencySymbol(); ?>";
	saAdmin.campaign.initCampaignJs();
	Joomla.submitbutton = function(task){saAdmin.campaign.campaignSubmitButton(task);}
</script>
