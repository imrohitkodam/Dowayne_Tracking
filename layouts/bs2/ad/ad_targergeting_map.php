<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    SocialAds
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('formbehavior.chosen', '#locationSelect');

$versionObj = new SaVersion;
$options    = array("version" => $versionObj->getMediaVersion());
HTMLHelper::script('media/com_sa/js/map.js', $options);
$displayData->map_target = isset($displayData->map_target) ? $displayData->map_target : [];
if (1)
{
	// If edit ad from adsummary then prefill targeting for geo targeting...else placeholder
	$check_radio_region = $check_radio_city = '';
	$everywhere         = $country         = $region         = $city         = '';

	if ($displayData->edit_ad_id)
	{
		if (isset($displayData->map_target['country']))
		{
			$country = $displayData->map_target['country'];
		}

		// For region field to prefilled...
		if (!empty($displayData->map_target['region']))
		{
			$check_radio_region = 1;
			$region             = $displayData->map_target['region'];
		}

		// For city field to prefilled...
		if (!empty($displayData->map_target['city']))
		{
			$check_radio_city = 1;

			if (isset($displayData->map_target['city']))
			{
				$city = $displayData->map_target['city'];
			}
		}

		if (empty($displayData->map_target['region']) && empty($displayData->map_target['city']))
		{
			$everywhere = 1;
		}
	}

	$publish1 = $publish2 = $publish1_label = $publish2_label = '';

	if (isset($displayData->map_target) && $displayData->map_target)
	{
		if ($displayData->map_target)
		{
			$publish1       = 'checked="checked"';
			$publish1_label = 'btn-success';
		}
		else
		{
			$publish2       = 'checked="checked"';
			$publish2_label = 'btn-danger';
		}
	}
	else
	{
		$publish2       = 'checked="checked"';
		$publish2_label = 'btn-danger';
	} ?>

<style>
	#map {
		height: 400px;
		width: 100%;
	}
</style>

<div class="form-horizontal">
	<div id="map_target_space" class="target_space well">
		<div class="control-group">
			<label class="control-label span2" for="">
				Map Targetting
			</label>
			<div class="controls span8 input-append targetting_yes_no">
				<input type="radio" name="map_targett" id="maplocationcheckboxyes" value="1"  <?php echo $publish1;?>>
				<label class="first btn <?php echo $publish1_label;?>" type="button" for="maplocationcheckboxyes">
					<?php echo Text::_('JYES'); ?>
				</label>

				<input type="radio" name="map_targett" id="maplocationcheckboxno" value="0" <?php echo $publish2;?>>
				<label class="btn <?php echo $publish2_label;?>" type="button" for="maplocationcheckboxno">
					<?php echo Text::_('JNO');?>
				</label>
			</div>
		</div>

		<div id="map_targett_div" class="targetting" style="<?php echo (isset($displayData->map_target) && $displayData->map_target) ? '' : 'display:none;'; ?>">
			<input type="hidden" name="selectLocationOn" id="selectLocationOn" value="<?php echo $displayData->map_target ? $displayData->map_target['location_on'] : ''; ?>">

			<div id="mapping-field-table">
				<?php
					$publish1 = $publish2 = $publish1_label = $publish2_label = '';

					if (isset($displayData->map_target) && $displayData->map_target && !$displayData->map_target['location_on'] &&  $displayData->map_target['location'])
					{
						if ($displayData->map_target)
						{
							$publish1       = 'checked="checked"';
							$publish1_label = 'btn-success';
						}
						else
						{
							$publish2       = 'checked="checked"';
							$publish2_label = 'btn-danger';
						}
					}
					else
					{
						$publish2       = 'checked="checked"';
						$publish2_label = 'btn-danger';
					} 
				?>

				<div class="control-group">
					<label class="control-label span2" for="">
						Select Location On Map
					</label>
					<div class="controls span8 input-append targetting_yes_no selectLocationOnMapDiv">
						<input type="radio" name="selectlocationonmap" id="selectlocationonmapcheckboxyes" value="1" <?php echo $publish1;?> >
						<label class="first btn <?php echo $publish1_label;?>" type="button" for="selectlocationonmapcheckboxyes">
							<?php echo Text::_('JYES'); ?>
						</label>

						<input type="radio" name="selectlocationonmap" id="selectlocationonmapcheckboxno" value="0" <?php echo $publish2;?> >
						<label class="last btn <?php echo $publish2_label;?>" type="button" for="selectlocationonmapcheckboxno">
							<?php echo Text::_('JNO');?>
						</label>
					</div>
				</div>

				<div id="selectlocationonmap_div" class="targetting" style="<?php echo (isset($displayData->map_target) && $displayData->map_target && !$displayData->map_target['location_on'] && $displayData->map_target['location']) ? '' : 'display:none;'; ?>">
					<input type="hidden" name="map[Center]" id="circleCenter" value="<?php echo ($displayData->map_target && !$displayData->map_target['location_on']) ? $displayData->map_target['location'] : ''; ?>">
					<input type="hidden" name="map[Radious]" id="circleRadius" value="<?php echo ($displayData->map_target && !$displayData->map_target['location_on']) ? $displayData->map_target['radius'] : ''; ?>">
					<input type="hidden" name="map[City]" id="circleCity" value="<?php echo ($displayData->map_target && !$displayData->map_target['location_on']) ? $displayData->map_target['city'] : ''; ?>">
					<input type="hidden" name="map[Region]" id="circleRegion" value="<?php echo ($displayData->map_target && !$displayData->map_target['location_on']) ? $displayData->map_target['region'] : ''; ?>">
					<input type="hidden" name="map[Country]" id="circleCountry" value="<?php echo ($displayData->map_target && !$displayData->map_target['location_on']) ? $displayData->map_target['country'] : ''; ?>">
					<input type="hidden" name="map[population]" id="mapPopulationCount" value="<?php echo ($displayData->map_target && !$displayData->map_target['location_on']) ? $displayData->map_target['count'] : ''; ?>">

					<div class="sa-mt-2">
						<button id="newCampaignCircle" onclick="startDrawingCircle()">Add Ad Area</button>
						<button id="clearCircles" onclick="clearCircle()">Clear Circle</button>
					</div>

					<div class="alert alert-info mt-2">
						<i>Select the Map location</i>
					</div>
					<div id="map"></div>
					<br />

					<h2>
						<div id="totalPopulationCount" class="<?php echo ($displayData->map_target && !$displayData->map_target['location_on']) ? '' : 'd-none'; ?>">
							Total Population is: <?php echo ($displayData->map_target && !$displayData->map_target['location_on']) ? $displayData->map_target['count'] : ''; ?>
						</div>
					</h2>
				</div>



				<?php
					$publish1 = $publish2 = $publish1_label = $publish2_label = '';

					if (isset($displayData->map_target) && $displayData->map_target && $displayData->map_target['location_on'] == 1)
					{
						if ($displayData->map_target)
						{
							$publish1       = 'checked="checked"';
							$publish1_label = 'btn-success';
						}
						else
						{
							$publish2       = 'checked="checked"';
							$publish2_label = 'btn-danger';
						}
					}
					else
					{
						$publish2       = 'checked="checked"';
						$publish2_label = 'btn-danger';
					} 
				?>

				<div class="control-group">
					<label class="control-label span2" for="">
						Select Location from options
					</label>
					<div class="controls span8 input-append targetting_yes_no selectLocationDiv">
						<input type="radio" name="selectlocation" id="selectlocationcheckboxyes" value="1" <?php echo $publish1;?> >
						<label class="first btn <?php echo $publish1_label;?>" type="button" for="selectlocationcheckboxyes">
							<?php echo Text::_('JYES'); ?>
						</label>

						<input type="radio" name="selectlocation" id="selectlocationcheckboxno" value="0" <?php echo $publish2;?> >
						<label class="last btn <?php echo $publish2_label;?>" type="button" for="selectlocationcheckboxno">
							<?php echo Text::_('JNO');?>
						</label>
					</div>
				</div>

				<div id="selectlocation_div" class="targetting" style="<?php echo (isset($displayData->map_target) && $displayData->map_target && $displayData->map_target['location_on']) ? '' : 'display:none;'; ?>">
					<div class="col-xs-12">
						<div class="control-group">
							<label class="control-label span2" for="locationSelect" title="Location">
								Select Location
							</label>
							<div class="span8">
								<?php
								$default = ($displayData->map_target && $displayData->map_target['location_on']) ? $displayData->map_target['location'] : null;

								$options   = array();
								$options[] = HTMLHelper::_('select.option', "", Text::_('Select Location'));

								foreach ($displayData->thirdPartyLocations as $key => $value)
								{
									$options[] = HTMLHelper::_('select.option', $value, $value);
								}

								$taxval = 0;

								echo $displayData->dropdown = HTMLHelper::_(
									'select.genericlist', $options, 'location[location]',
									'class=" form-select required"  required="true" aria-invalid="false" size="1"
									onchange=\'sa.create.getStatesList(this.id,"","' . Text::_('COM_SOCIALADS_BILLING_SELECT_STATE') . '", "locationState") \' ',
									'value', 'text', $default, 'locationSelect'
									); ?>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label span2" for="locationCity" title="City">
							</label>
							<div class="span8">
								<input type="hidden" name="location[count]" id="locationInputCount" value="<?php echo ($displayData->map_target && $displayData->map_target['location_on']) ? $displayData->map_target['count'] : ''; ?>">
								<h4 class="mt-3">
									<div id="totalPopulationCountByCityName" class="<?php echo ($displayData->map_target && $displayData->map_target['location_on']) ? '' : 'd-none'; ?>">
										Total Population is: <?php echo ($displayData->map_target && $displayData->map_target['location_on']) ? $displayData->map_target['count'] : ''; ?>
									</div>
								</h4>
								<div class="alert alert-info mt-2 selectlocationmapmsg <?php echo ($displayData->map_target && $displayData->map_target['location_on']) ? '' : 'd-none'; ?>">
									<i>For simplicity you can select the location on Map</i>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
		<!-- map_target_div end here -->
		<div style="clear:both;"></div>
	</div>
</div>
	<?php
}
?>
