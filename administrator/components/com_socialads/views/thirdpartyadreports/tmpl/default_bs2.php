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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

if (JVERSION < '4.0')
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

HTMLHelper::_('bootstrap.renderModal', 'a.modal');
$versionObj = new SaVersion;
$options = array("version" => $versionObj->getMediaVersion());
HTMLHelper::stylesheet('media/com_sa/vendors/font-awesome/css/font-awesome.min.css', $options);

$params       = ComponentHelper::getParams('com_socialads');
$payment_mode = $params->get('payment_mode');

$user         = Factory::getUser();
$userId       = $user->get('id');

$pending_icon = ' icon-clock ';

$listOrder    = $this->state->get('list.ordering');
$listDirn     = $this->state->get('list.direction');
$canOrder     = $user->authorise('core.edit.state', 'com_socialads');
$saveOrder    = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_socialads&task=forms.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'dataList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>

<div class="<?php echo SA_WRAPPER_CLASS; ?> sa-third-party-reports">
<?php
	if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php
	else : ?>
		<div id="j-main-container">
	<?php
	endif; ?>

		<form action="<?php echo Route::_('index.php?option=com_socialads&view=thirdpartyadreports'); ?>" method="post" name="adminForm" id="adminForm">
			<div id="filter-bar" class="btn-toolbar">
				<div class="span12 mt-2">
					<div class="filter-search btn-group pull-left">
						<label for="filter_search" class="element-invisible">
							<?php echo Text::_('JSEARCH_FILTER'); ?>
						</label>
						<input type="text" name="filter_search" id="filter_search" class="form-control"
							placeholder="<?php echo Text::_('COM_SOCIALADS_ADS_SEARCH'); ?>"
							value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
							title="<?php echo Text::_('JSEARCH_FILTER'); ?>" />
					</div>
					<div class="btn-group pull-left">
						<button class="btn hasTooltip btn-outline-secondary" type="submit" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
							<i class="fa fa-search"></i>
						</button>
						<button class="btn hasTooltip btn-outline-secondary" id="clear-search-button" type="button" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>">
							<i class="fa fa-remove"></i>
						</button>
					</div>

					<div class="btn-group pull-right hidden-phone">
						<label for="limit" class="element-invisible">
							<?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
						</label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
					<div class="btn-group pull-right hidden-phone">
						<?php echo HTMLHelper::_('select.genericlist', $this->publish_states, "filter_published", 'class="input-medium form-select" size="1" onchange="document.adminForm.submit();" name="filter_published"', "value", "text", $this->state->get('filter.state')); ?>
					</div>
					<div class="btn-group pull-right hidden-phone">
						<?php
							echo HTMLHelper::_('select.genericlist', $this->zoneOptions, "filter_zonelist", 'class="input-medium form-select" size="1" onchange="document.adminForm.submit();" name="zonelist"', "value", "text", $this->state->get('filter.zonelist'));
						?>
					</div>
					<div class="btn-group pull-right hidden-phone">
						<?php
							echo HTMLHelper::_('select.genericlist', $this->thirdPartyList, "filter_thirdpartylist", 'class="input-medium form-select" size="1" onchange="document.adminForm.submit();" name="thirdpartylist"', "value", "text", $this->state->get('filter.thirdpartylist'));
						?>
					</div>
				</div>

				<div class="span12 sa-mt-2">
					<div class="form-inline fromTodateFields sa-mt-1 span12 pull-left">
						<div class="span4">
							<label for="from" class="hidden-xs"><?php echo Text::_('COM_SOCIALADS_STATS_FROM_DATE'); ?></label>
							<?php
							echo HTMLHelper::_('calendar', $this->state->get('filter.from'), 'from', 'from', '%Y-%m-%d', array(
								'class' => 'inputbox form-control input-xs calenderChange', 'placeholder' => Text::_('COM_SOCIALADS_STATS_FROM_DATE')
							));
							?>
						</div>
						<div class="span4">
							<label for="to" class="hidden-xs"><?php echo Text::_("COM_SOCIALADS_STATS_TO_DATE"); ?></label>
							<?php
							echo HTMLHelper::_('calendar',$this->state->get('filter.to'), 'to', 'to', '%Y-%m-%d', array(
								'class' => 'inputbox form-control input-xs calenderChange', 'placeholder' => Text::_('COM_SOCIALADS_STATS_TO_DATE')
							));
							?>
						</div>
						
					</div>
				</div>
			</div>
			<div class="clearfix"> </div>

			<?php
			if (empty($this->items)): ?>
				<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo Text::_('COM_SOCIALADS_FILTER_SEARCH_NOT_FOUND'); ?>
				</div>
			<?php
			else : ?>
				<div id = "no-more-tables">
					<table class="table mt-3" id="dataList">
						<thead>
							<tr>
								<?php
								if (isset($this->items[0]->state)): ?>
									<th class="tj-width-1 nowrap text-center">
										<?php echo Text::_('JSTATUS'); ?>
									</th>
								<?php
								endif; ?>
								<th class="tj-width-20">
									<?php echo HTMLHelper::_('grid.sort',  'COM_SOCIALADS_ADS_AD_TITLE', 'a.ad_title', $listDirn, $listOrder); ?>
								</th>
								<th class="text-center tj-width-1">
									<?php echo Text::_('COM_SOCIALADS_ADS_AD_PREVIEW'); ?>
								</th>
								<?php
								if ($payment_mode == 'wallet_mode'): ?>
									<th class="tj-width-10">
										<?php echo Text::_('COM_SOCIALADS_ADS_AD_CAMPAGIN'); ?>
									</th>
								<?php
								endif; ?>
								<th class="tj-width-10">
									<?php echo Text::_('COM_SOCIALADS_ADS_AD_TYPE'); ?>
								</th>
								<th class="tj-width-15">
									<?php echo HTMLHelper::_('grid.sort',  'COM_SOCIALADS_ADS_AD_ZONE', 'a.ad_zone', $listDirn, $listOrder); ?>
								</th>
								<th class="tj-width-5">
									<?php echo HTMLHelper::_('grid.sort',  'COM_SOCIALADS_ORDERS_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
								</th>
								<?php
								if ($payment_mode == 'pay_per_ad_mode'): ?>
									<th class="text-center tj-width-5">
										<?php echo HTMLHelper::_('grid.sort', 'COM_SOCIALADS_ADS_PAYMENT_MODE', 'ao.status'); ?>
									</th>
								<?php
								endif; ?>
								<th class="tj-width-20 text-center">
									<?php echo Text::_('COM_SOCIALADS_ADS_AD_STATS'); ?>
								</th>
								<?php
								if (isset($this->items[0]->ad_id)): ?>
									<th class="tj-width-1 nowrap hidden-phone sa-text-right">
										<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'a.ad_id', $listDirn, $listOrder); ?>
									</th>
								<?php
								endif; ?>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($this->items as $i => $item) :
								$j = 0;

								$rowApprovalColor = '';

								switch ($item->ad_approved)
								{
									case 0:
										$rowApprovalColor = "warning";
									break;

									case 1:
										$rowApprovalColor = "";
									break;

									case 2:
										$rowApprovalColor = "error";
									break;
								}
								?>

								<tr class="row<?php echo $i % 2 . ' ' . $rowApprovalColor; ?>" >
									<?php
									if (isset($this->items[0]->state)): ?>
										<td class="text-center tj-width-1" data-title="<?php echo Text::_('JSTATUS'); ?>">
											<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'forms.', '', 'cb'); ?>
										</td>
									<?php
									endif; ?>
									<td data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_TITLE'); ?>" class="tj-width-20">
										<span class="ad-type-img" >
										<?php
										if ($item->ad_guest == 1)
										{
											?>
											<i class="fa fa-user"></i>
											<?php
										}
										elseif (($item->ad_guest == 0) && ($item->ad_alternative == 0))
										{
											?>
											<i class="fa fa-users"></i>
											<?php
										}
										?>
									</span>
									<?php echo htmlspecialchars($item->ad_title, ENT_COMPAT, 'UTF-8');?>

									</td>
									<td class="text-center tj-width-1" data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_PREVIEW'); ?>">
										<?php $link = Route::_('index.php?option=com_socialads&view=previewad&tmpl=component&layout=default&id=' . $item->ad_id); ?>
										<?php
											$itemid = SaCommonHelper::getSocialadsItemid('adsummary');
											$thirdPartyIdforStats       = $this->state->get('filter.thirdpartylist') ? $this->state->get('filter.thirdpartylist') : null;
											$stats = Route::_('index.php?option=com_socialads&view=adsummary&tmpl=component&layout=default&adid=' . $item->ad_id. '&thirdpartyid=' . $thirdPartyIdforStats . '&callfromadreport=1');

											echo HTMLHelper::_(
												'bootstrap.renderModal',
												'previewDetails' . $item->ad_id . 'Modal',
												array(
														'title'       => Text::_('COM_SOCIALADS_INVOICE_ORDER_DETAIL'),
														'backdrop'    => 'static',
														'url'         => $link,
														'height'      => '100px',
														'width'       => '100px',
														'bodyHeight'  => 50,
														'modalWidth'  => 40,
													)
											);

											echo HTMLHelper::_(
												'bootstrap.renderModal',
												'statsDetails' . $item->ad_id . 'Modal',
												array(
														'title'       => Text::_('COM_SOCIALADS_AD_STATS'),
														'backdrop'    => 'static',
														'url'         => $stats,
														'height'      => '400px',
														'width'       => '800px',
														'bodyHeight'  => 70,
														'modalWidth'  => 80,
													)
											); ?>
											<div class="btn-group actions">	
												<a data-toggle="modal" data-target="#previewDetails<?php echo $item->ad_id ?>Modal" class="sa-btn-wrapper btn btn-mini saActions">
													<span class="editlinktip hasTip" title="<?php echo Text::_('COM_SOCIALADS_AD_PREVIEW'); ?>" >
														<img src="<?php echo Uri::root() . '/media/com_sa/images/preview.png'?>">
													</span>
												</a>


												<a data-toggle="modal" data-target="#statsDetails<?php echo $item->ad_id ?>Modal" title="<?php echo Text::_('COM_SOCIALADS_AD_STATS'); ?>" class="sa-btn-wrapper btn btn-mini saActions">
													<i class="fa fa-bar-chart"></i>
												</a>
											</div>
									</td>
									<?php
									if ($payment_mode == 'wallet_mode')
									{
										?>
										<td data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_CAMPAGIN'); ?>" class="tj-width-10">
											<?php echo htmlspecialchars($item->campaign, ENT_COMPAT, 'UTF-8'); ?>
										</td>
									<?php
									}
									?>
									<td data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_TYPE'); ?>" class="tj-width-10">
										<?php
										if ($item->ad_alternative == 1)
										{
											echo Text::_('COM_SOCIALADS_ADS_AD_TYPE_ALT_AD');
										}
										elseif($item->ad_noexpiry == 1)
										{
											echo Text::_('COM_SOCIALADS_ADS_AD_TYPE_UNLTD_AD');
										}
										elseif($item->ad_affiliate == 1)
										{
											echo Text::_('COM_SOCIALADS_ADS_AD_TYPE_AFFI');
										}
										else
										{
											if ($item->ad_payment_type == 0)
											{
												echo Text::_('COM_SOCIALADS_ADS_AD_TYPE_IMPRS');
											}
											elseif ($item->ad_payment_type == 1)
											{
												echo Text::_('COM_SOCIALADS_ADS_AD_TYPE_CLICKS');
											}
											else if ($item->ad_payment_type == 4)
											{
												echo Text::_('COM_SOCIALADS_CHARGE_ADS_TOGETHER');
											}
											else
											{?>
												<img src="<?php echo Uri::root(true) . '/media/com_sa/images/start_date.png' ?>">
													<?php echo $item->ad_startdate; ?>
													<br/>
												<?php
												if(($item->ad_enddate != '0000-00-00') )			//if not 0 then	only show end date
												{?>
													<img src="<?php echo Uri::root(true) . '/media/com_sa/images/end_date.png' ?>">
													<?php echo $item->ad_enddate;
												} ?>
											<?php
											}
										} ?>
									</td>
									<td data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_ZONE'); ?>" class="tj-width-15">
										<?php
										echo htmlspecialchars(($item->zone_name ? $item->zone_name : '-'), ENT_COMPAT, 'UTF-8');
										?>
									</td>
									<td class="tj-width-5" data-title="<?php echo Text::_('COM_SOCIALADS_ORDERS_CREATED_BY'); ?>">
										<?php echo htmlspecialchars($item->created_by, ENT_COMPAT, 'UTF-8'); ?>
									</td>

									<?php

									// Popover for ad credits and availability
									$out_of = 0;

									if ($payment_mode == 'pay_per_ad_mode')
									{
										// If camp ad is there den they dont have credits..
										if ($item->camp_id != 0 && !$item->bid_value)
										{
											$out_of = '';
										}
										elseif($item->bid_value > 0)
										{
											$out_of = $item->bid_value;
										}
										elseif($item->ad_alternative== 1 || $item->ad_noexpiry== 1 || $item->ad_affiliate == 1)
										{
											$out_of = Text::_('COM_SOCIALADS_ADS_UNLIMITED');
										}
										elseif($item->ad_payment_type == 2)
										{
											$out_of = '';
										}
										else
										{
											$out_of = $item->ad_credits_balance;
										}
									}
									?>

									<td class="tj-width-20" data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_STATS'); ?>">
										<table>
											<tr>
												<td class="font-weight-bold sa-text-left"><?php echo Text::_('COM_SOCIALADS_ADS_AD_NO_OF_CLICKS'); ?></td>
												<td class="sa-text-right">
													<?php
													if ($this->state->get('filter.from') || $this->state->get('filter.to') || $this->state->get('filter.thirdpartylist'))
													{
														require_once JPATH_SITE . "/components/com_socialads/helpers/common.php";
				
														$from       = $this->state->get('filter.from') ? $this->state->get('filter.from') : null;
														$to       = $this->state->get('filter.to') ? $this->state->get('filter.to') : null;
														$thirdParty       = $this->state->get('filter.thirdpartylist') ? $this->state->get('filter.thirdpartylist') : null;
														$impAndCount = SaCommonHelper::getImpressionAndClicks($item->ad_id, $from, $to, $thirdParty);
														$clicks = $impAndCount['clicks'];
														$impr = $impAndCount['imp'];
													}
													else 
													{
														$clicks = $item->clicks;
														$impr = $item->impressions;
													}
													// If ad is type is clicks then show available credits
													if ($item->ad_payment_type == 1 && $out_of)
													{
														echo $item->clicks . " / " . $out_of;
													}
													elseif ($clicks)
													{
														echo $clicks;
													}
													else
													{
														echo "0";
													}
													?>
												</td>
											</tr>

											<tr>
												<td class="font-weight-bold sa-text-left"><?php echo Text::_('COM_SOCIALADS_ADS_AD_NO_OF_IMPRESSIONS'); ?></td>
												<td class="sa-text-right">
													<?php
													// If ad is type is impressions then show available credits
													if ($item->ad_payment_type == 0 && $out_of)
													{
														echo $item->impressions . " / " . $out_of;
													}
													elseif ($impr)
													{
														echo $impr;
													}
													else
													{
														echo "0";
													}
													?>
												</td>
											</tr>
										</table>
									</td>
									
									<?php
									if (isset($this->items[0]->ad_id)): ?>
										<td class="hidden-phone tj-width-1 sa-text-right" data-title="<?php echo Text::_('JGRID_HEADING_ID'); ?>">
											<?php echo (int) $item->ad_id; ?>
										</td>
									<?php
									endif;
									?>
								</tr>
							<?php
							endforeach; ?>
						</tbody>
					</table>

					<div>
						<?php echo $this->pagination->getListFooter(); ?>
					</div>

					<!-- Show legends and meaning-->
				</div>
			<?php
			endif;
			?>
			<input type="hidden" name="task" value="" />

			<input type="hidden" id='hidid' name="ad_id" value="" />
			<input type="hidden" id='hidstat' name="status" value="" />
			<input type="hidden" id='hidzone' name="zone" value="" />
			<input type="hidden" id='reason' name="reason" value="" />

			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>

<script type="text/javascript">
	saAdmin.initSaJs();
	var tjListOrderingColumn = "<?php echo $listOrder; ?>";
	Joomla.submitbutton = function(task) {saAdmin.ads.submitButtonAction(task);}
	techjoomla.jQuery(document).ready(function()
	{
		jQuery('.fromTodateFields .calenderChange').change( function () {
			document.adminForm.submit();
		});
	});
</script>
