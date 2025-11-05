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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('script', 'media/com_sa/js/markerclusterer.min.js');

if (JVERSION < '4.0') {
	HTMLHelper::_('formbehavior.chosen', 'select');
}

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_socialads', JPATH_ADMINISTRATOR);
// echo "<pre>";print_r($this->item);die("heyui");

if ($this->item->state == 1) {
	$state_string = Text::_("COM_SOCIALADS_COUPONS_PUBLISHED");
	$state_value  = 1;
} else {
	$state_string = Text::_("COM_SOCIALADS_COUPONS_UNPUBLISHED");
	$state_value  = 0;
}

$canState = Factory::getUser()->authorise('core.edit.state', 'com_socialads');
$root = Uri::root();
?>
<style>
	#map {
		height: 400px;
		width: 100%;
	}
</style>

<div class="<?php echo SA_WRAPPER_CLASS; ?> thirdparty front-end-edit">
	<div class="page-header">
		<h1>
			<?php
			if (!empty($this->item->id)) {
				echo Text::_('COM_SOCIALADS_THIRD_PARTY_PROFILE');
			} else {
				echo Text::_('COM_SOCIALADS_THIRD_PARTY_REGISTER');
			}
			?>
		</h1>
	</div>
	<form id="thirdparty-form" action="<?php echo Route::_('index.php?option=com_socialads&task=thirdparty.edit'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

		<ul class="nav nav-tabs">
			<li class="nav-item">
				<a class="nav-link active" data-bs-toggle="tab" href="#profile">Profile</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" data-bs-toggle="tab" href="#widgetCode">Get widget code</a>
			</li>
		</ul>

		<div class="tab-content">
			<div class="tab-pane container active" id="profile">

				
					<div class="form-group row">
						<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 form-label">
							<?php echo $this->form->getLabel('created_by'); ?>
						</div>
						<div class="col-lg-8 col-md-8 col-sm-9 col-xs-12">
							<?php echo $this->form->getInput('created_by'); ?>
						</div>
					</div>
			

				<div class="form-group row">
					<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('business_name'); ?></div>
					<div class="col-lg-8 col-md-8 col-sm-9 col-xs-12"><?php echo $this->form->getInput('business_name'); ?></div>
				</div>

				<div class="form-group row">
					<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 form-label"><?php echo $this->form->getLabel('description'); ?></div>
					<div class="col-lg-8 col-md-8 col-sm-9 col-xs-12"><?php echo $this->form->getInput('description'); ?></div>
				</div>
				<!---->
				<?php
				if (!empty($this->item->id)) {
				?>
					<div class="form-group row">
						<?php $canState = false; ?>
						<?php $canState = $canState = Factory::getUser()->authorise('core.edit.own', 'com_socialads'); ?>

						<div class=" col-lg-2 col-md-2 col-sm-2 col-xs-12 form-label ">
							<?php echo $this->form->getLabel('state'); ?>
						</div>
						<div class="col-lg-8 col-md-8 col-sm-9 col-xs-12">
							<input type="hidden" name="jform[is_enabled]" value="<?php echo $this->form->getValue('state'); ?>">

							<?php
							if ($this->form->getValue('state')) {
							?>
								<span class="badge bg-primary"><?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_PUBLISHED'); ?></span>
							<?php
							} else {
							?>
								<span class="badge bg-secondary"><?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_UNPUBLISHED'); ?></span>
							<?php
							}
							?>
						</div>

					</div>
				<?php
				} ?>

				<div class="form-group row countDisplayFormRow <?php echo empty($this->item->id) ? 'd-none' : ''; ?>">
					<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 form-label">
						<label>Total <?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_POPULATION_COUNT'); ?> </label> 
					</div>
					<div class="col-lg-8 col-md-8 col-sm-9 col-xs-12">
						<input type="text" readonly class="form-control countDisplayInTextbox" value="<?php echo (!empty($this->item->id)) ? $this->form->getValue('count') : ''; ?>">
					</div>
				</div>

				<div class="form-group row">
					<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 form-label">
						<label>
							<?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_SELECT_LOCATION'); ?>
						</label>
					</div>
					<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
						<div class="mt-3 mb-3 alert alert-primary">
							Add the service areas where you want to display the ad
						</div>

						<button id="newCampaignCircle" onclick="startDrawingCircle()"><?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_ADD_SERVICE_AREA'); ?></button>
						<!-- Button trigger modal -->
						<button id="addTextModalOpenButton" data-bs-toggle="modal" data-bs-target="#addTextModal">
							Add Pins From Text 
						</button>

						<button id="addCSVModalOpenButton" data-bs-toggle="modal" data-bs-target="#addCSVModal">
							Add Pins From CSV 
						</button>

						<button id="clearCircles" onclick="clearCircle()">Clear service area</button>
						<?php echo $this->loadTemplate('csv_modal_bs5'); ?>
						<?php echo $this->loadTemplate('text_modal_bs5'); ?>
						<br />
						<div id="map" class="mt-3"></div>
						<br />
					</div>
				</div>

				<div class="form-group row">
					<div class="col-lg-12 col-md-12 col-sm-9 col-xs-12">
						<button type="submit" class="validate btn float-end ms-2 btn-success"><?php echo Text::_('JSUBMIT'); ?></button>
						<a class="btn float-end ms-2 btn-danger" href="<?php echo Route::_('index.php?option=com_socialads&task=thirdparty.cancel'); ?>" title="<?php echo Text::_('JCANCEL'); ?>">
							<?php echo Text::_('JCANCEL'); ?>
						</a>
					</div>
				</div>
			</div>

			<div class="tab-pane container" id="widgetCode">
				<?php
				if (!empty($this->item->id)) {
				?>

					<div class="form-group row">
						<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 form-label">
							<?php echo $this->form->getLabel('adzone'); ?>
						</div>
						<div class="col-lg-8 col-md-8 col-sm-9 col-xs-12">
							<?php echo $this->form->getInput('adzone'); ?>
						</div>
					</div>

					<div class="form-group row widgetDisplayFormRow d-none">
						<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 form-label">
							<label for="html-widget-textarea">HTML Code</label>
						</div>
						<div class="col-lg-8 col-md-8 col-sm-9 col-xs-12 appendCloneHTMLCode">
						</div>

						<div class="col-lg-10 col-md-10 col-sm-12 col-xs-12">
							<button class="btn btn-primary mt-2 copyToClipboard float-end">
								Copy to Clipboard
							</button>
						</div>

						<div class="htmlCodeArea" style="display:none">
							<textarea id="html-widget-textarea-clone" readonly disabled rows="6" class="form-control">
								<div id="displayThirdPartyWidget" style="display:none">
									<input type="hidden" id="thirdPartyID">
									<input type="hidden" id="thirdPartyZoneID">
									<div id="displayThirdPartyAd">
									</div>
								</div>

								<noscript>
									<a href="<?php echo $root; ?>index.php?option=com_socialads&task=thirdparty.getUrlById&id=thirdPartyUpdatedID&zoneid=thirdPartyUpdatedZoneID" target="_blank"> 
										<img src="<?php echo $root; ?>index.php?option=com_socialads&task=thirdparty.getImageById&id=thirdPartyUpdatedID&zoneid=thirdPartyUpdatedZoneID" height="zoneHeight" width="zoneWidth" /> 
									</a>
								</noscript>

								<script type="text/javascript">
									var ad_html = '';
									var id = document.getElementById("thirdPartyID").value;;
									var zoneid = document.getElementById("thirdPartyZoneID").value;;

									if (window.XMLHttpRequest) {
										xhttp = new XMLHttpRequest();
									}
									else {
										xhttp = new ActiveXObject("Microsoft.XMLHTTP");
									}

									xhttp.open("GET", "<?php echo $root; ?>index.php?option=com_socialads&task=thirdparty.getThirdPartyAdHtml&id=" + id + "&zoneid=" + zoneid, false);
									xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
									xhttp.send("id=" + id + "&zoneid=" + zoneid);
									ad_html = xhttp.responseText;
									document . getElementById('displayThirdPartyAd').innerHTML = ad_html;
									document . getElementById('displayThirdPartyWidget') . style.display = '';

								</script>
							</textarea>
						</div>
					</div>

					<div class="form-group row widgetDisplayFormRow d-none mt-2">
						<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 form-label">
							<label for="imageLinkURLField">Image Link </label>
						</div>
						<div class="col-lg-8 col-md-8 col-sm-9 col-xs-12">
							<input type="text" id="imageLinkURLField" class="form-control imageLinkURL" disabled >
						</div>

						<div class="col-lg-10 col-md-10 col-sm-12 col-xs-12">
							<button class="btn btn-primary mt-2 copyToClipboardImageLink float-end">
								Copy to Clipboard
							</button>
						</div>
					</div>

					<div class="form-group row widgetDisplayFormRow d-none mt-2">
						<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 form-label">
							<label for="urlLinkURLField">URL Link</label>
						</div>
						<div class="col-lg-8 col-md-8 col-sm-9 col-xs-12">
							<input type="text" id="urlLinkURLField" class="form-control urlLinkURL" disabled >
						</div>

						<div class="col-lg-10 col-md-10 col-sm-12 col-xs-12">
							<button class="btn btn-primary mt-2 copyToClipboardURL float-end">
								Copy to Clipboard
							</button>
						</div>
					</div>
				<?php
				} ?>
			</div>


			<input type="hidden" name="jform[populationAreas]" id="populationAreas">
			<input type="hidden" name="option" value="com_socialads" />
			<input type="hidden" name="task" value="thirdparty.save" />
			<input type="hidden" id="third-party-id-field" name="cid" value="<?php echo $this->item->id; ?>" />
			<?php
			echo $this->form->renderField('map_zoom_size');
			echo HTMLHelper::_('form.token');
			?>
	</form>
</div>

<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script type="text/javascript">
	var circle_pop = [];
	var rootUrl = "<?php echo Uri::root(); ?>";
	var addingWrapperCircle = 0;
	var map;
	var circles = [];
	var drawableCircles = [];
	var clusterMarkers = [];
	var alreadyCityPresent = [];
	var failedCities = [];
	var markers = [];
	var allMarkerClusters = '';
	var geojson = {
		type: "FeatureCollection",
		features: [],
	};

	let failCount = 0;
	let duplicateCount = 0;
	let addressesProcessed = {};

	var alreadyPresentCirCles = JSON.parse(JSON.stringify(<?php echo (isset($this->locationDetails) ? json_encode($this->locationDetails) : ''); ?>));
	console.log(alreadyPresentCirCles)
	var drawingManager;
	var city, region, country, geolocation, geocoder;

	async function initMap() {

		const { Map, InfoWindow } = await google.maps.importLibrary("maps");
		const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary(
			"marker",
		);
		geocoder = new google.maps.Geocoder();

		techjoomla.jQuery(document).ready(function() {
			var defaultZoom = jQuery('#jform_map_zoom_size').val() ? parseInt(jQuery('#jform_map_zoom_size').val()) : 10;
			var mapCenter, locationByCircleArray, locationsArray = [], cityNameArray = [];

			if (Array.isArray(alreadyPresentCirCles) && alreadyPresentCirCles.length) {

				var locationByCircle = alreadyPresentCirCles[0].location;
				var locationByCircle = locationByCircle.substring(1, locationByCircle.length - 1);

				locationByCircleArray = locationByCircle.split(",");
				mapCenter = {
					lat: parseFloat(locationByCircleArray[0]),
					lng: parseFloat(locationByCircleArray[1])
				};			

				var bounds = new google.maps.LatLngBounds();

				if (alreadyPresentCirCles.length == 1)
				{
					map = new google.maps.Map(document.getElementById('map'), {
						center: mapCenter,
						zoom: "<?php echo $this->item->map_zoom_size; ?>" ? Number("<?php echo $this->item->map_zoom_size; ?>") : 10,
						mapId: "DEMO_MAP_ID",
					});
					locationsArray.push(mapCenter)
					cityNameArray.push(alreadyPresentCirCles[0].city)
				}
				else 
				{
					map = new google.maps.Map(document.getElementById('map'), {
						mapId: "DEMO_MAP_ID",
					});

					// Add markers and extend bounds for each location
					alreadyPresentCirCles.forEach(function(locationAddress) {
						var location = locationAddress.location;
						var locationByCircle = location.substring(1, location.length - 1);

						locationArray = locationByCircle.split(",");
						mapCenter = {
							lat: parseFloat(locationArray[0]),
							lng: parseFloat(locationArray[1])
						};
						locationsArray.push(mapCenter)
						cityNameArray.push(locationAddress.city)
					});

					var infoWindow = new google.maps.InfoWindow({
						content: "",
						disableAutoPan: true,
					});
				}


				// Add some markers to the map.
				{
					const markers = locationsArray.map((position, i) => {
						var label = alreadyPresentCirCles[i].count.toString();
						var name = 'Radius: ' + alreadyPresentCirCles[i].radius + ' Mile  ' +
									'Count: ' + alreadyPresentCirCles[i].count + '  ' +
									'City: ' + alreadyPresentCirCles[i].city + '  ' +
									'Region: ' + alreadyPresentCirCles[i].region + '  ' +
									'Country: ' + alreadyPresentCirCles[i].country;
						// var label = cityNameArray[i];
						var pinGlyph = new google.maps.marker.PinElement({
							glyph: label,
							glyphColor: "white",
							title: name
						});
						var marker = new google.maps.marker.AdvancedMarkerElement({
							position,
							content: pinGlyph.element,
							title: name
						});

						bounds.extend(marker.position);

						clusterMarkers.push(marker);

						// markers can only be keyboard focusable when they have click listeners
						// open info window when marker is clicked
						marker.addListener("click", () => {
							infoWindow.setContent(position.lat + ", " + position.lng);
							infoWindow.open(map, marker);
						});
						
						return marker;
					});
					
					if (alreadyPresentCirCles.length != 1)
					{
						// Fit the map to the bounds
						map.fitBounds(bounds);
					}

					// Add a marker clusterer to manage the markers.
					allMarkerClusters = new markerClusterer.MarkerClusterer({ markers, map });
				}


			} else {
				mapCenter = {
					lat: 51.509865,
					lng: -0.118092
				};

				map = new google.maps.Map(document.getElementById('map'), {
					center: mapCenter,
					zoom: defaultZoom,
				});
			}

			google.maps.event.addListener(map, 'zoom_changed', function() {
				var zoom = map.getZoom();
				jQuery('#jform_map_zoom_size').val(zoom);
			});

			if (Array.isArray(alreadyPresentCirCles) && alreadyPresentCirCles.length) {
				// Define places and circles
				var places = [];
				alreadyPresentCirCles.forEach(function(alreadyPresentCirCleLocation) {
					locationByCircle = alreadyPresentCirCleLocation.location;
					locationByCircle = locationByCircle.substring(1, locationByCircle.length - 1);

					locationByCircleArray = locationByCircle.split(",");
					mapCenter = {
						lat: parseFloat(locationByCircleArray[0]),
						lng: parseFloat(locationByCircleArray[1])
					};
					places.push({
						location: mapCenter,
						radius: alreadyPresentCirCleLocation.radius,
						name: 'Radius: ' + alreadyPresentCirCleLocation.radius + ' Mile  ' +
							'Count: ' + alreadyPresentCirCleLocation.count + '  ' +
							'City: ' + alreadyPresentCirCleLocation.city + '  ' +
							'Region: ' + alreadyPresentCirCleLocation.region + '  ' +
							'Country: ' + alreadyPresentCirCleLocation.country
					});

					circles.push({
						'location': mapCenter,
						'radius': alreadyPresentCirCleLocation.radius,
						'count': alreadyPresentCirCleLocation.count,
						'city': alreadyPresentCirCleLocation.city,
						'region': alreadyPresentCirCleLocation.region,
						'country': alreadyPresentCirCleLocation.country,
						'id': alreadyPresentCirCleLocation.id,
					});
					var totalPopulationCount = jQuery('.countDisplayInTextbox').val() ? jQuery('.countDisplayInTextbox').val() : 0;
					jQuery('.countDisplayInTextbox').val(parseInt(totalPopulationCount) + parseInt(alreadyPresentCirCleLocation.count));
					jQuery('#thirdparty-form #populationAreas').val(JSON.stringify(circles));
				});

				// Create circles for each place
				places.forEach(function(place) {

					var radiusInMeters = convertMilesToMeters(place.radius);
					createCircle(place.location, radiusInMeters);

					// Add place name as a label to the circle
					// var marker = new google.maps.Marker({
					// 	position: place.location,
					// 	map: map,
					// 	title: place.name
					// });

					// markers.push(marker);
				});

				// Create a circle on the map
				function createCircle(center, radius) {
					circle = new google.maps.Circle({
						strokeColor: '#FF0000',
						strokeOpacity: 0.8,
						strokeWeight: 2,
						fillColor: '#FF0000',
						fillOpacity: 0.35,
						map: map,
						center: center,
						radius: radius
					});
					drawableCircles.push(circle);

					// Add an event listener to reposition the circle if the map is dragged
					google.maps.event.addListener(map, 'drag', function() {
						circle.setCenter(center);
					});
				}
			}

			drawingManager = new google.maps.drawing.DrawingManager({
				drawingMode: null,
				drawingControl: false,
				circleOptions: {
					fillColor: 'blue',
					fillOpacity: 0.3,
					strokeWeight: 2,
					clickable: false,
					editable: true,
					zIndex: 1
				}
			});
			drawingManager.setMap(map);

			google.maps.event.addListener(drawingManager, 'circlecomplete', function(latestCircle) {
				drawingManager.setDrawingMode(null);

				var center = latestCircle.getCenter();
				var radius = convertMetersToMiles(latestCircle.getRadius());

				let population = prompt("Population in circle Service area", 0);
				if (population == null || population == "") {
					latestCircle.setMap(null);
				} else {
					city = region = country = geolocation = '';
					var locationResult = reverseGeocode(center, radius, population, latestCircle);
				}

			});
		});
	}

	function startDrawingCircle() {
		event.preventDefault();
		drawingManager.setDrawingMode('circle');
	}

	function startDrawingWrapperCircle() {
		addingWrapperCircle = 1;
		drawingManager.setDrawingMode('circle');
		document.getElementById("new3rdPartyCircle").attr('disabled');
		document.getElementById("newCampaignCircle").attr('disabled');
	}

	function clearCircle() {
		event.preventDefault();

		drawableCircles.forEach(circle => circle.setMap(null));
		markers.forEach(marker => marker.setMap(null));
		if (allMarkerClusters)
		{
			allMarkerClusters.clearMarkers();		
		}
		clusterMarkers.forEach(marker => marker.setMap(null));

		markers = [];
		clusterpinGlyphs = [];
		clusterMarkers = [];
		drawableCircles = [];
		circles = [];
		circle_pop = [];
		city = region = country = geolocation = '';
		jQuery('#thirdparty-form #populationAreas').val('');
		jQuery('.countDisplayInTextbox').val(0);
	}

	// Convert miles to meters
	function convertMilesToMeters(miles) {
		return miles * 1609.34; // 1 mile = 1609.34 meters
	}

	// Convert miles to meters
	function convertMetersToMiles(meter) {
		return meter / 1609.34; // 1 mile = 1609.34 meters
	}

	function reverseGeocode(latlng, radius, population, latestCircle) {
		var geocoder = new google.maps.Geocoder();
		// var latlng = new google.maps.LatLng(latitude, longitude);

		geocoder.geocode({
			'latLng': latlng
		}, async function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[0]) {
					var addressComponents = results[0].address_components;
					var city = null;
					var region = null;
					var country = null;

					for (var i = 0; i < addressComponents.length; i++) {
						var types = addressComponents[i].types;
						//console.log('type '+i+ 'is :'+types+' value is : '+addressComponents[i].long_name);
						//console.log('type1 '+i+ 'is :'+types_1+' value is : '+addressComponents_1[i].long_name);
						if ((types.includes('postal_town') || types.includes('locality') || types.includes('administrative_area_level_1')) && city == null) {
							city = addressComponents[i].long_name;
							console.log('city found :' + addressComponents[i].long_name);
						} else if ((types.includes('administrative_area_level_2') || types.includes('administrative_area_level_1')) && region == null) {
							region = addressComponents[i].long_name;
							console.log('region found :' + addressComponents[i].long_name);
						} else if (types.includes('country') && country == null) {
							country = addressComponents[i].long_name;
							console.log('country found :' + addressComponents[i].long_name);
						}
					}

					if (city != null &&
						region != null &&
						country != null) 
					{
						console.log('city val is: ' + city + ' region val is: ' + region + ' and country val is :' + country);
						circles.push({
							'location': latlng,
							'radius': radius,
							'count': population,
							'city': city,
							'region': region,
							'country': country,
						});
						jQuery('#thirdparty-form #populationAreas').val(JSON.stringify(circles));
						drawableCircles.push(latestCircle);

						var totalPopulationCount = jQuery('.countDisplayInTextbox').val() ? jQuery('.countDisplayInTextbox').val() : 0;
						jQuery('.countDisplayInTextbox').val(parseInt(totalPopulationCount) + parseInt(population));
						jQuery('#thirdparty-form .countDisplayFormRow').removeClass('d-none')
					} else {
						alert('Could not detect the city for the selected service area. Please re-try to select the service area.')
						latestCircle.setMap(null);
					}
				} else {
					alert('Could not detect the city for the selected service area. Please re-try to select the service area.')
					latestCircle.setMap(null);
				}
			} else {
				console.log("Geocoder failed due to: " + status);
				latestCircle.setMap(null);
			}
		});

		return true;
	}

	// We get the all the address related information using geocode API
	// Also check the if address is present on the MAP if present then add else not
	function geocodeAddress(geocoder, address) {
		if (addressesProcessed[address.toLowerCase()]) {
			duplicateCount++;
			return;
		}
		addressesProcessed[address.toLowerCase()] = true;

		geocoder.geocode({ address: address }, (results, status) => {
			if (status === 'OK') {
				if (results[0]) {
					var addressComponents = results[0].address_components;
					var city = null;
					var region = null;
					var country = null;

					for (var i = 0; i < addressComponents.length; i++) {
						var types = addressComponents[i].types;
						//console.log('type '+i+ 'is :'+types+' value is : '+addressComponents[i].long_name);
						//console.log('type1 '+i+ 'is :'+types_1+' value is : '+addressComponents_1[i].long_name);
						if ((types.includes('postal_town') || types.includes('locality') || types.includes('administrative_area_level_1')) && city == null) {
							city = addressComponents[i].long_name;
							console.log('city found :' + addressComponents[i].long_name);
						} else if ((types.includes('administrative_area_level_2') || types.includes('administrative_area_level_1')) && region == null) {
							region = addressComponents[i].long_name;
							console.log('region found :' + addressComponents[i].long_name);
						} else if (types.includes('country') && country == null) {
							country = addressComponents[i].long_name;
							console.log('country found :' + addressComponents[i].long_name);
						}
					}

					if (city != null &&
						region != null &&
						country != null) 
					{
						const addressIsPresent = circles.find(circle => ((circle.location.lat == results[0].geometry.location.lat().toFixed(7)) && (circle.location.lng == results[0].geometry.location.lng().toFixed(7))));

						if (!addressIsPresent)
						{
							console.log('addressIsPresent')
							console.log(addressIsPresent)
							circles.push({
								'location': results[0].geometry.location,
								'radius': 4,
								'count': 1,
								'city': city,
								'region': region,
								'country': country,
							});
							jQuery('#thirdparty-form #populationAreas').val(JSON.stringify(circles));

							var totalPopulationCount = jQuery('.countDisplayInTextbox').val() ? jQuery('.countDisplayInTextbox').val() : 0;
							jQuery('.countDisplayInTextbox').val(parseInt(totalPopulationCount) + parseInt(1));
							jQuery('#thirdparty-form .countDisplayFormRow').removeClass('d-none')
							addMarker(results[0].geometry.location);
						}
						else 
						{
							alreadyCityPresent.push(address.toLowerCase());
						}
					}
				}
			} else {
				failedCities.push(address);
			}
		});
	}

	// Add the marker for given location. 
	// Used for add location from CSV or Text
	function addMarker(location) {
		var marker = new google.maps.marker.AdvancedMarkerElement({
			position: location,
			map: map
		});
		
		markers.push(marker);
		adjustViewport();
	}

	// If muliple locations added from CSV or Text options then display all the locations
	function adjustViewport() {
		let bounds = new google.maps.LatLngBounds();
		markers.forEach(marker => bounds.extend(marker.position));
		if (markers.length > 0) {
			map.fitBounds(bounds);
		}
	}

	jQuery('#thirdparty-form').on('submit', function() {

		if (!jQuery("#thirdparty-form #jform_business_name").val()) {
			var error_html = '';
			error_html += "<br />Please Enter Business Name";
			Joomla.renderMessages({
				"warning": [error_html]
			});

			jQuery('#thirdparty-form #jform_business_name').focus();
			return false;
		}

		return true;
	});

	techjoomla.jQuery(document).ready(function() {
		jQuery('#thirdparty-form #jform_adzone').on('change', function() {
			if (jQuery('#thirdparty-form #jform_adzone').val() != 0) {
				var oldHtmlCodeClone = jQuery('.htmlCodeArea').clone();
				jQuery('.widgetDisplayFormRow').removeClass('d-none');
				var id = jQuery('#thirdparty-form #third-party-id-field').val();
				var zoneid = jQuery('#thirdparty-form #jform_adzone').val();

				var textAreaValue = oldHtmlCodeClone.find('#html-widget-textarea-clone').val();
				textAreaValue = textAreaValue.replace('id="thirdPartyID"', ' id="thirdPartyID" value="' + id + '"');
				textAreaValue = textAreaValue.replace('id="thirdPartyZoneID"', ' id="thirdPartyZoneID" value="' + zoneid + '"');

				// To get zone information for diplay image (Get height and width based on the zone)
				techjoomla.jQuery.ajax({
					type: 'GET',
					url: Joomla.getOptions('system.paths').base + '/index.php?option=com_sa&task=create.getZonesData&zone_id=' + zoneid,
					dataType: 'json',
					success: function (data) 
					{
						if (data[0].is_responsive)
						{
							textAreaValue = textAreaValue.replace('zoneHeight', 'auto');
							textAreaValue = textAreaValue.replace('zoneWidth', '100%');
						}
						else
						{
							if (data[0].use_image_ratio && data[0].img_width_ratio)
							{
								textAreaValue = textAreaValue.replace('zoneWidth', data[0].img_width_ratio * 100);
							}
							else if (data[0].img_width)
							{
								textAreaValue = textAreaValue.replace('zoneWidth', data[0].img_width);
							} 
							else 
							{
								textAreaValue = textAreaValue.replace('zoneWidth', '100');
							}

							if (data[0].use_image_ratio && data[0].img_height_ratio)
							{
								textAreaValue = textAreaValue.replace('zoneHeight', data[0].img_height_ratio * 100);
							}
							else if (data[0].img_height)
							{
								textAreaValue = textAreaValue.replace('zoneHeight', data[0].img_height);
							} 
							else 
							{
								textAreaValue = textAreaValue.replace('zoneHeight', '10');
							}
						}

						textAreaValue = textAreaValue.replace(new RegExp('thirdPartyUpdatedID', 'g'), id);
						textAreaValue = textAreaValue.replace(new RegExp('thirdPartyUpdatedZoneID', 'g'), zoneid);

						oldHtmlCodeClone.find('#html-widget-textarea-clone').val(textAreaValue);
						oldHtmlCodeClone.find('#html-widget-textarea-clone').attr('id', 'html-widget-textarea');
						oldHtmlCodeClone.attr('style', 'display:"block"');
						oldHtmlCodeClone.attr('class', '');

						jQuery('#thirdparty-form .appendCloneHTMLCode').html(oldHtmlCodeClone);
						jQuery('.imageLinkURL').val(Joomla.getOptions('system.paths').baseFull + 'index.php?option=com_socialads&task=thirdparty.getImageById&id='+ id +'&zoneid=' + zoneid);
						jQuery('.urlLinkURL').val(Joomla.getOptions('system.paths').baseFull + 'index.php?option=com_socialads&task=thirdparty.getUrlById&id='+ id +'&zoneid=' + zoneid);
					}
				});
			} else {
				jQuery('.widgetDisplayFormRow').addClass('d-none');
			}
		});

		jQuery(".copyToClipboard").click(function() {
			event.preventDefault();

			// Create a new textarea element to hold the text to copy
			var textarea = document.createElement("textarea");
			textarea.value = jQuery('#html-widget-textarea').val();

			// Append the textarea to the document
			document.body.appendChild(textarea);

			// Select the text in the textarea
			textarea.select();

			// Copy the selected text to the clipboard
			document.execCommand("copy");

			// Remove the textarea
			document.body.removeChild(textarea);

			// Provide feedback to the user
			alert("Copied to clipboard succesfully");
		});

		jQuery(".copyToClipboardImageLink").click(function() {
			event.preventDefault();

			// Create a new textarea element to hold the text to copy
			var textarea = document.createElement("textarea");
			textarea.value = jQuery('input.imageLinkURL').val();

			// Append the textarea to the document
			document.body.appendChild(textarea);

			// Select the text in the textarea
			textarea.select();

			// Copy the selected text to the clipboard
			document.execCommand("copy");

			// Remove the textarea
			document.body.removeChild(textarea);

			// Provide feedback to the user
			alert("Copied to clipboard succesfully");
		});

		jQuery(".copyToClipboardURL").click(function() {
			event.preventDefault();

			// Create a new textarea element to hold the text to copy
			var textarea = document.createElement("textarea");
			textarea.value = jQuery('input.urlLinkURL').val();

			// Append the textarea to the document
			document.body.appendChild(textarea);

			// Select the text in the textarea
			textarea.select();

			// Copy the selected text to the clipboard
			document.execCommand("copy");

			// Remove the textarea
			document.body.removeChild(textarea);

			// Provide feedback to the user
			alert("Copied to clipboard succesfully");
		});

		// While open addTextModalOpenButton, and addCSVModalOpenButton module prevent event for submit the form
		jQuery("#addTextModalOpenButton, #addCSVModalOpenButton").click(function() {
			event.preventDefault();
		});

		jQuery("#addTextModal").on('hide.bs.modal', function(){
			jQuery('#addTextModal #addPinsTextArea').val('');
		});

		jQuery("#addCSVModal").on('hide.bs.modal', function(){
			jQuery('#addCSVModal #csvFormFile').val('');
		});

		jQuery("#addTextModal #addPinsFromTextSubmit").click(function() {
			console.log("show");
			event.preventDefault();
			alreadyCityPresent = [];
			failedCities = [];

			if (!jQuery('#addTextModal #addPinsTextArea').val())
			{
				alert('Please enter the addresses in the text box')
				return;
			}

			jQuery('#addTextModal #addPinsTextArea').val().split(/\r?\n/).forEach(address => {
				if (address.trim() !== "") geocodeAddress(geocoder, address.trim());
			});

			setTimeout( function() {
				if (alreadyCityPresent.length)
				{
					alert('The following addresses are already present:-  ' + alreadyCityPresent.toString());
				}

				if (failedCities.length)
				{
					alert('The following addresses failed to insert:-  ' + failedCities.toString());
				}
			}, 3500);

			jQuery('#addTextModal').modal('hide');
		});

		jQuery("#addCSVModal #addPinsFromCSVSubmit").click(function() {
			event.preventDefault();
			alreadyCityPresent = [];
			failedCities = [];

			if (jQuery('#addCSVModal #csvFormFile')[0].files.length === 0) 
			{
            	alert("Please select CSV file to add addreses");
				return;
            }
			else
			{
				const file = jQuery('#addCSVModal #csvFormFile')[0].files[0];
				if (!file) return;

				const reader = new FileReader();
				reader.onload = function(e) {
					const text = e.target.result;
					text.split(/\r?\n/).forEach(address => {
						if (address.trim() !== "") geocodeAddress(geocoder, address.trim());
					});
				};
				reader.readAsText(file);
			}

			setTimeout( function() {
				if (alreadyCityPresent.length)
				{
					alert('Following city already present:-  ' + alreadyCityPresent.toString());
				}

				if (failedCities.length)
				{
					alert('Following cities failed to insert:-  ' + failedCities.toString());
				}
			}, 3500);

			jQuery('#addCSVModal').modal('hide');
		});
	});
</script>


<script async src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->googleMapApiKey; ?>&libraries=drawing,geometry&loading=async&callback=initMap" async defer></script>
