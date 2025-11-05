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

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

if (JVERSION < '4.0')
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

HTMLHelper::_('bootstrap.renderModal', 'a.modal');
$versionObj = new SaVersion;
$options = array("version" => $versionObj->getMediaVersion());
HTMLHelper::stylesheet('media/com_sa/vendors/font-awesome/css/font-awesome.min.css', $options);

$versionObj = new SaVersion;
$options = array("version" => $versionObj->getMediaVersion());
HTMLHelper::stylesheet('media/com_sa/vendors/font-awesome/css/font-awesome.min.css', $options);

$user	= Factory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_socialads');
$saveOrder	= $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_socialads&task=thirdpartyenrollments.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'couponList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();

if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>
<div class="<?php echo SA_WRAPPER_CLASS;?> sa-thirdpartyenrollments">
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
	endif;?>
	<form action="<?php echo Route::_('index.php?option=com_socialads&view=thirdpartyenrollments'); ?>" method="post" name="adminForm" id="adminForm">
		<div id="filter-bar" class="btn-toolbar">
			<div class="col-md-12 mt-2">
				<div class="filter-search btn-group float-start">
					<input type="text" name="filter_search" id="filter_search" class="form-control" placeholder="<?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_FILTER_SEARCH'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_FILTER_SEARCH'); ?>" />
				</div>

				<div class="btn-group float-start">
					<button class="btn hasTooltip btn-outline-secondary" type="submit" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="fa fa-search"></i></button>
					<button class="btn hasTooltip btn-outline-secondary" id="clear-search-button" type="button" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="fa fa-remove"></i></button>
				</div>

				<?php
				if (JVERSION >= '3.0') : ?>
					<div class="btn-group float-end hidden-phone">
						<label for="limit" class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
				<?php
				endif; ?>

				<div class="btn-group float-end hidden-phone">
					<?php
					echo HTMLHelper::_('select.genericlist', $this->publish_states, "filter_published", 'class="input-medium form-select" size="1" onchange="document.adminForm.submit();" name="filter_published"', "value", "text", $this->state->get('filter.state'));
					?>
				</div>
			</div>
		</div>
		<div class="clearfix"> </div>
		<?php
		if (empty($this->items)) : ?>
			<div class="clearfix">&nbsp;</div>
			<div class="alert alert-no-items">
				<?php echo Text::_('COM_SOCIALADS_FILTER_SEARCH_NOT_FOUND'); ?>
			</div>
		<?php
		else : ?>
			<div id = "no-more-tables">
				<table class="table table-responsive mt-2" id="couponList">
					<thead>
						<tr>
						<?php
						if (isset($this->items[0]->ordering)): ?>
							<th width="1%" class="nowrap text-center hidden-phone">
								<?php echo HTMLHelper::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
							</th>
						<?php
						endif; ?>
							<th width="1%" class="hidden-phone">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>
						<?php
						if (isset($this->items[0]->state)): ?>
							<th width="1%" class="nowrap text-center">
								<?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
						<?php
						endif; ?>
						<th class="left">
							<?php echo HTMLHelper::_('grid.sort',  'COM_SOCIALADS_THIRD_PARTY_BUSINESS_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th class="left">
							<?php echo HTMLHelper::_('grid.sort',  'Created By', 'creator_name', $listDirn, $listOrder); ?>
						</th>
						<th class="left">
							<?php echo HTMLHelper::_('grid.sort',  'COM_SOCIALADS_THIRD_PARTY_LOCATION', 'a.code', $listDirn, $listOrder); ?>
						</th>
						<th class="left">
							<?php echo HTMLHelper::_('grid.sort',  'COM_SOCIALADS_THIRD_PARTY_ENROLLMENT_PROFILE', $listDirn, $listOrder); ?>
						</th>
						<th class="sa-text-right">
							<?php echo HTMLHelper::_('grid.sort',  'COM_SOCIALADS_THIRD_PARTY_COUNT', 'a.value', $listDirn, $listOrder); ?>
						</th>
						</tr>
					</thead>
					<tfoot>
						<?php
						if(isset($this->items[0]))
						{
							$colspan = count(get_object_vars($this->items[0]));
						}
						else
						{
							$colspan = 10;
						}
						?>
						<tr>
							<td colspan="<?php echo $colspan ?>">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<?php
						foreach ($this->items as $i => $item) :
							$ordering   = ($listOrder == 'a.ordering');
							$canCreate	= $user->authorise('core.create',		'com_socialads');
							$canEdit	= $user->authorise('core.edit',			'com_socialads');
							$canCheckin	= $user->authorise('core.manage',		'com_socialads');
							$canChange	= $user->authorise('core.edit.state',	'com_socialads');
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<?php
								if (isset($this->items[0]->ordering)): ?>
									<td class="order nowrap text-center hidden-phone">
										<?php
										if ($canChange) :
											$disableClassName = '';
											$disabledLabel = '';
											if (!$saveOrder) :
												$disabledLabel    = Text::_('JORDERINGDISABLED');
												$disableClassName = 'inactive tip-top';
											endif; ?>
											<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
												<i class="icon-menu"></i>
											</span>
											<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
										<?php
										else : ?>
											<span class="sortable-handler inactive" >
												<i class="icon-menu"></i>
											</span>
										<?php
										endif; ?>
									</td>
								<?php
								endif; ?>
								<td class="hidden-phone">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<?php
								if (isset($this->items[0]->state)): ?>
									<td class="text-center" data-title="<?php echo Text::_('JSTATUS');?>">
										<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'thirdpartyenrollments.', $canChange, 'cb'); ?>
									</td>
								<?php
								endif; ?>

								<td data-title="<?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_BUSINESS_NAME');?>">
									<?php
										echo $this->escape($item->business_name);
									?>
								</td>

								<td data-title="<?php echo Text::_('COM_SOCIALADS_CREATED_BY');?>">
									<?php
									// Display the creator's name, fallback to username if name not available
									$creatorName = '';
									if (isset($item->creator_name) && !empty($item->creator_name)) {
										$creatorName = $this->escape($item->creator_name);
									} elseif (isset($item->creator_username) && !empty($item->creator_username)) {
										$creatorName = $this->escape($item->creator_username);
									} elseif (isset($item->created_by) && !empty($item->created_by)) {
										// If only user ID is available, you might want to load the user object
										$creatorUser = Factory::getUser($item->created_by);
										$creatorName = $creatorUser->name ? $this->escape($creatorUser->username) : $this->escape($creatorUser->name);
										//print_r($creatorUser);
									} else {
										$creatorName = Text::_('COM_SOCIALADS_UNKNOWN_USER');
									}
									echo $creatorName;
									?>
								</td>

								<td data-title="<?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_LOCATION');?>">
									<?php 
									$link = Route::_('index.php?option=com_socialads&view=thirdpartylocation&tmpl=component&layout=default&id=' . $item->id);

									echo HTMLHelper::_(
										'bootstrap.renderModal',
										'locationView' . $item->id . 'Modal',
										array(
												'title'       => $this->escape($item->business_name),
												'backdrop'    => 'static',
												'url'         => $link,
												'height'      => '100px',
												'width'       => '100px',
												'bodyHeight'  => 70,
												'modalWidth'  => 65,
											)
									);
									?>

									<a data-bs-toggle="modal" data-bs-target="#locationView<?php echo $item->id ?>Modal" class="sa-btn-wrapper btn btn-mini saActions">
										<span class="editlinktip hasTip" title="<?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_LOCATION'); ?>" >
											<img src="<?php echo Uri::root() . '/media/com_sa/images/map.png'?>">
										</span>
									</a>
								</td>

								<td data-title="<?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_ENROLLMENT_PROFILE');?>">
									<?php 
									$link = Route::_('index.php?option=com_socialads&view=thirdpartyenrollmentprofile&tmpl=component&layout=default&id=' . $item->id);

									echo HTMLHelper::_(
										'bootstrap.renderModal',
										'enrollmentProfileView' . $item->id . 'Modal',
										array(
												'title'       => $this->escape($item->business_name),
												'backdrop'    => 'static',
												'url'         => $link,
												'height'      => '100px',
												'width'       => '100px',
												'bodyHeight'  => 70,
												'modalWidth'  => 65,
											)
									);
									?>

									<a data-bs-toggle="modal" data-bs-target="#enrollmentProfileView<?php echo $item->id ?>Modal" class="sa-btn-wrapper btn btn-mini saActions">
										<span class="editlinktip hasTip" title="<?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_ENROLLMENT_PROFILE'); ?>" >
											<img src="<?php echo Uri::root() . 'media/com_sa/images/map.png'?>">
										</span>
									</a>
								</td>
								<td data-title="<?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_COUNT');?>" class="sa-text-right">
									<?php echo $item->population_count; ?>
								</td>
							</tr>
						<?php
						endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php
		endif; ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>

<script type="text/javascript">
	saAdmin.initSaJs();
	var tjListOrderingColumn = "<?php echo $listOrder; ?>";
	Joomla.submitbutton = function(action){saAdmin.thirdpartyenrollments.submitButtonAction(action);}
</script>
