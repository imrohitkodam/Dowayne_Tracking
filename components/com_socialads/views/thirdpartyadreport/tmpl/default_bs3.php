<?php
/**
 * @version     SVN:<SVN_ID>
 * @package     Com_Socialads
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license     GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.renderModal', 'a.modal');
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

if (JVERSION < '4.0')
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('bootstrap.renderModal', 'a.modal');

$versionObj = new SaVersion;
$options = array("version" => $versionObj->getMediaVersion());
HTMLHelper::stylesheet('media/com_sa/vendors/font-awesome/css/font-awesome.min.css', $options);

$ad_params    = ComponentHelper::getParams('com_socialads');
$payment_mode = $ad_params->get('payment_mode');
$userId       = $this->user->get('id');
$listOrder    = $this->state->get('list.ordering');
$listDirn     = $this->state->get('list.direction');
$canOrder     = $this->user->authorise('core.edit.state', 'com_socialads');
$saveOrder    = $listOrder == 'a.ordering';
$totalclicks  = 0;
$totalimpressions = 0;
$totalctr = 0;
?>
<div class="<?php echo SA_WRAPPER_CLASS;?>" id="sa-ads">
	<form action="" method="post" name="adminForm" id="adminForm">
		<div>
			<h1>Third Party Ad Report</h1>
		</div>
		<div id="container-fluid">
			<div class="row">
			<?php
			if (JVERSION >= '3.0'):
			?>
				<div>
					<?php echo $this->toolbarHTML;?>
				</div>
				<div class="col-md-12">
					<div class="clearfix"> </div>
					<hr class="hr-condensed" />

					<div class="filter-search btn-group float-start">
						<label for="filter_search" class="element-invisible">
							<?php echo Text::_('JSEARCH_FILTER'); ?>
						</label>
						<input type="text" name="filter_search" id="filter_search" class="form-control"
							placeholder="<?php echo Text::_('COM_SOCIALADS_ADS_SEARCH'); ?>"
							value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
							title="<?php echo Text::_('JSEARCH_FILTER'); ?>" />
					</div>
					<div class="btn-group float-start">
						<button class="btn btn-md btn-outline-secondary hasTooltip" type="submit" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
							<i class="fa fa-search"></i>
						</button>
						<button class="btn btn-md btn-outline-secondary hasTooltip" id="clear-search-button" type="button" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>">
							<i class="fa fa-remove"></i>
						</button>
					</div>

					<div class="btn-group pull-right hidden-phone social-ads-filter-margin-left">
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
				</div>
			<?php
			endif; ?>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="row mt-1">
						<div class="form-inline pull-right fromTodateFields">
							<div class="col-md-6 col-sm-6 col-12 pull-right">
								<label for="to" class="hidden-xs"><?php echo Text::_("COM_SOCIALADS_STATS_TO_DATE"); ?></label>
								<?php
								echo HTMLHelper::_('calendar',$this->state->get('filter.to'), 'to', 'to', '%Y-%m-%d', array(
									'class' => 'inputbox form-control input-xs sa-dashboard-calender', 'placeholder' => Text::_('COM_SOCIALADS_STATS_TO_DATE')
								));
								?>
							</div>
							<div class="col-md-6 col-sm-6 col-12 pull-right me-2">
								<label for="from" class="hidden-xs"><?php echo Text::_('COM_SOCIALADS_STATS_FROM_DATE'); ?></label>
								<?php
								echo HTMLHelper::_('calendar', $this->state->get('filter.from'), 'from', 'from', '%Y-%m-%d', array(
									'class' => 'inputbox form-control input-xs sa-dashboard-calender', 'placeholder' => Text::_('COM_SOCIALADS_STATS_FROM_DATE')
								));
								?>
							</div>
						</div>
					</div>

					<div class="row mt-2">
						<div class="col-md-12 col-sm-12 col-12">
							<div class="btn-group pull-right hidden-phone social-ads-filter-margin-left">
								<?php echo HTMLHelper::_('select.genericlist', $this->zonesoptions, "filter_zoneslist", 'class="ad-status inputbox form-select input-medium" size="1" onchange="document.adminForm.submit();" name="zoneslist"', "value", "text", $this->state->get('filter.zoneslist')); ?>
							</div>
							<div class="btn-group pull-right hidden-phone hidden-tablet social-ads-filter-margin-left">
								<?php
									echo HTMLHelper::_('select.genericlist', $this->adstatus, "filter_adstatus", 'class="ad-status inputbox form-select input-medium" size="1"
									onchange="document.adminForm.submit();" name="adstatus"', "value", "text", $this->state->get('filter.adstatus'));
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<?php
			if (empty($this->items)):
			?>
				<div class="clearfix">&nbsp;</div>
					<div class="alert">
						<?php
						echo Text::_('COM_SOCIALADS_FILTER_SEARCH_NOT_FOUND');
						?>
					</div>
			<?php
			else:
			?>
				<div class="col-md-12">
					<div id="no-more-tables" class="table-responsive ads-list">
						<table class="table table-responsive table-condensed" id="dataList">
							<thead>
								<tr>
									<th>
										<?php echo HTMLHelper::_('grid.sort', 'COM_SOCIALADS_ADS_AD_TITLE', 'a.ad_title', $listDirn, $listOrder); ?>
									</th>
									<?php
									if (isset($this->items[0]->state)): ?>
										<th class="nowrap center">
											<?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder);  ?>
										</th>
									<?php
									endif;
									if ($ad_params->get('payment_mode') == 'wallet_mode')
									{ ?>
										<th>
											<?php echo Text::_('COM_SOCIALADS_ADS_AD_CAMPAGIN'); ?>
										</th>
									<?php
									} ?>
									<th>
										<?php echo Text::_('COM_SOCIALADS_ADS_AD_ZONE'); ?>
									</th>
									<th>
										<?php echo Text::_('COM_SOCIALADS_ADS_AD_TYPE'); ?>
									</th>
									<th class="sa-text-right">
										<?php echo Text::_('COM_SOCIALADS_ADS_AD_TYPE_IMPRS'); ?>
									</th>
									<th class="sa-text-right">
										<?php echo Text::_('COM_SOCIALADS_ADS_AD_NO_OF_CLICKS'); ?>
									</th>
							</thead>
							<tbody>
								<?php
								foreach ($this->items as $i => $item): ?>
									<tr class="row<?php echo $i % 2;?>">
										<td data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_TITLE');?>" class="">
											<?php echo $this->escape($item->ad_title); ?>
										</td>
										<td class="text-center" data-title="<?php echo Text::_('JSTATUS');?>">
											<div>
												<a class="btn btn-micro hasTooltip" href="javascript:void(0);" title="<?php echo ($item->state) ? Text::_('COM_SOCIALADS_ADS_UNPUBLISH') : Text::_('COM_SOCIALADS_ADS_PUBLISH');?>"
												onclick="document.adminForm.cb<?php echo $i; ?>.checked=1; document.adminForm.boxchecked.value=1; Joomla.submitbutton('<?php echo ($item->state) ? 'ads.unpublish' : 'ads.publish';?>');">
													<img src="<?php echo Uri::root(true); ?>/media/com_sa/images/<?php echo ($item->state) ? 'publish.png' : 'unpublish.png';?>"/>
												</a>
											</div>
										</td>
										<?php
										if ($ad_params->get('payment_mode') == 'wallet_mode')
										{?>
											<td data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_CAMPAGIN');?>">
												<?php
												if($item->campaign == "")
												{
													echo Text::_("COM_SOCIALADS_NA");
												}
												else
												{
													echo htmlspecialchars($item->campaign, ENT_COMPAT, 'UTF-8');
												} ?>
											</td>
										<?php
										}?>
										<td data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_ZONE');?>">
											<?php echo htmlspecialchars($item->zone_name, ENT_COMPAT, 'UTF-8'); ?>
										</td>
										<td data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_TYPE');?>">
											<?php
											if ($item->ad_alternative == 1)
											{
												echo Text::_('COM_SOCIALADS_ADS_AD_TYPE_ALT_AD');
											}
											elseif ($item->ad_noexpiry == 1)
											{
												echo Text::_('COM_SOCIALADS_ADS_AD_TYPE_UNLTD_AD');
											}
											else if ($item->ad_affiliate == 1)
											{
												echo Text::_('COM_SOCIALADS_AD_TYP_AFFI');
											}
											else
											{
												if ($item->ad_payment_type == 0)
												{
													echo Text::_('COM_SOCIALADS_ADS_AD_TYPE_IMPRS');
												}
												else if ($item->ad_payment_type == 1)
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
													if(($item->ad_enddate!='0000-00-00') && $item->ad_enddate)			//if not 0 then	only show end date
													{?>
														<img src="<?php echo Uri::root(true) . '/media/com_sa/images/end_date.png' ?>">
														<?php echo $item->ad_enddate;
													} ?>
												<?php
												}
											}?>
										</td>
										<?php
										if ($payment_mode == 'pay_per_ad_mode')
										{ ?>
											<td class="text-center" data-title="<?php echo Text::_('COM_SOCIALADS_PAYMENT_STATUS');?>">
												<?php
												if ($item->ad_alternative == 1 || $item->ad_noexpiry == 1 || $item->ad_affiliate == 1)
												{ ?>
													<i class="fa fa-check"></i>
												<?php
												}
												else
												{
													switch ($item->status)
													{
														case 'P': ?>
															<i class="fa fa-clock-o"> </i>
															<?php
															break;
														case 'C': ?>
															<i class="fa fa-check"></i>
															<?php
															break;
														case 'RF': ?>
															<i class="fa fa-times"></i>
															<?php
															break;
														default: ?>
																<i class="fa fa-minus-circle"></i>
															<?php
															break;
													}
												} ?>
											</td>
											<?php
										}

										// Popover for ad credits and availability
										$out_of = '';

										$from       = $this->state->get('filter.from') ? $this->state->get('filter.from') : null;
										$to       = $this->state->get('filter.to') ? $this->state->get('filter.to') : null;
										$impAndCount = SaCommonHelper::getImpressionAndClicks($item->ad_id, $from, $to, $this->thirdPartyId);
										$clicks = $impAndCount['clicks'];
										$impr = $impAndCount['imp'];

										if ($payment_mode == 'pay_per_ad_mode')
										{
											// if camp ad is there den they dont have credits..
											if ($item->camp_id!=0 && !$item->bid_value)
											{
												$out_of = '';
											}
											elseif ($item->bid_value > 0)
											{
												$out_of = $item->bid_value;
											}
											elseif ($item->ad_alternative== 1 || $item->ad_noexpiry== 1 || $item->ad_affiliate == 1)
											{
												$out_of = Text::_('COM_SOCIALADS_CREDIT_UNLIMITED');
											}
											elseif ($item->ad_payment_type == 2)
											{
												$out_of = '';
											}
											else
											{
												$out_of = $item->ad_credits_balance;
											}

											if ($out_of)
											{
												$text_to_show = Text::_('COM_SOCIALADS_CREDITS_AVAILABLE')." : " . $out_of . '<br />';

												if ($item->ad_payment_type == 0)
												{
													$text_to_show .= Text::_('COM_SOCIALADS_ADS_AD_TYPE_IMPRS')." : " . $item->impressions;
												}

												if($item->ad_payment_type == 1)
												{
													$text_to_show .= Text::_('COM_SOCIALADS_ADS_AD_NO_OF_CLICKS')." : " . $item->clicks;
												}

												$out_of_anchor = '<a class="ad_type_tootip" data-content="' . $text_to_show.'" data-placement="top" data-html="html"  data-trigger="hover" rel="popover" >';
												$out_of_anchor = ' / ' . $out_of_anchor . $out_of . '</a>';
											}
										} ?>

										<td class="sa-text-right" data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_NO_OF_IMPRESSIONS');?>">
											<?php
											if ($impr)
											{
												echo $impr;
											}
											else
											{
												echo "0";
											}

											// If ad is type is impreddions then show available credits
											if ($item->ad_payment_type == 0 && $out_of)
											{
												echo $out_of_anchor;
											} ?>
										</td>
										<td class="sa-text-right" data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_NO_OF_CLICKS');?>">
											<?php
											if ($clicks)
											{
												echo $clicks;
											}
											else
											{
												echo "0";
											}

											// If ad is type is clicks then show available credits
											if ($item->ad_payment_type == 1 && $out_of)
											{
												echo $out_of_anchor;
											}
											?>
										</td>
										<td data-title="<?php echo Text::_('COM_SOCIALADS_ADS_AD_ACTIONS');?>">
											<?php
											$itemid = SaCommonHelper::getSocialadsItemid('adsummary');

											$stats = Uri::root() . substr( Route::_('index.php?option=com_socialads&tmpl=component&view=adsummary&adid=' . (int) $item->ad_id . '&Itemid=' . (int) $itemid . '&thirdpartyid=' . $this->thirdpartyid . '&callfromadreport=1'), strlen(Uri::base(true)) + 1
											);

											$itemid = SaCommonHelper::getSocialadsItemid('preview');
											$link = Uri::root() . substr(Route::_('index.php?option=com_socialads&view=preview&tmpl=component&layout=default&id=' . (int) $item->ad_id . '&Itemid=' . (int) $itemid), strlen(Uri::base(true)) + 1
											);
											?>
											<div class="btn-group actions">
												<a rel="{handler: 'iframe', size: {x: 350, y: 350}}"title="<?php echo Text::_('COM_SOCIALADS_AD_PREVIEW'); ?>" class="sa-btn-wrapper modal btn btn-mini" href="<?php echo $link; ?>"  >
														<i class="fa fa-picture-o"></i>
												</a>
												<a rel="{handler: 'iframe', size: {x: 1100, y: 600}}" title="<?php echo Text::_('COM_SOCIALADS_AD_STATS'); ?>" href="<?php echo $stats; ?>" class="sa-btn-wrapper modal btn btn-mini saActions">
													<i class="fa fa-bar-chart"></i>
												</a>
											</div>
										</td>
									</tr>
								<?php
								endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="pull-right clearfix">
					<?php echo $this->pagination->getListFooter(); ?>
				</div>
				<div class="clearfix"></div>
				<div class="alert alert-info row">
					<div class="col-md-4 col-sm-12 sa-legends-padding">
						<div>
							<i class="fa fa-user"></i> = <?php echo Text::_('COM_SOCIALADS_GUEST_ADS'); ?>
						</div>
						<div>
							<img src="<?php echo Uri::root(true) . '/media/com_sa/images/group.png'; ?>"/> = <?php echo Text::_('COM_SOCIALADS_TARGET_ADS'); ?>
						</div>
					</div>
					<div class="col-md-4 col-sm-12 sa-legends-padding">
						<div>
							<i class="fa fa-minus-circle"></i> = <?php echo Text::_('COM_SOCIALADS_NO_ADORDER'); ?>
						</div>
						<div>
							<i class="fa fa-clock-o" ></i> = <?php echo Text::_('COM_SOCIALADS_SA_PENDIN'); ?>
						</div>
					</div>
					<div class="col-md-4 col-sm-12 sa-legends-padding">
						<div>
							<i class="fa fa-check"></i> = <?php echo Text::_('COM_SOCIALADS_SA_CONFIRM') . ' / ' . Text::_('COM_SOCIALADS_SA_APPROVE'); ?>
						</div>
						<div>
							<i class="fa fa-times"></i> = <?php echo Text::_('COM_SOCIALADS_SA_REFUND') . ' / ' . Text::_('COM_SOCIALADS_SA_REJEC'); ?>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			<?php
			endif; ?>
			<input type="hidden" id='reason' name="reason" value="" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" id='hidid' name="ad_id" value="" />
			<input type="hidden" id='hidstat' name="status" value="" />
			<input type="hidden" id='hidzone' name="zone" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
<script>
	sa.initSaJs();
	Joomla.submitbutton = function(action)
	{
		sa.ads.submitButtonAction(action)
	}
	techjoomla.jQuery(document).ready(function()
	{
		jQuery('.ad_type_tootip').popover();
		jQuery('.fromTodateFields .sa-dashboard-calender').change( function () {
			document.adminForm.submit();
		});
	});
</script>
