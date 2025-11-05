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

$canState = Factory::getUser()->authorise('core.edit.state', 'com_socialads');
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
			 	echo Text::_('COM_SOCIALADS_THIRD_PARTY_PROFILE');
			?>
		</h1>
	</div>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
					<div id="map"></div>
					<br />
					
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script type="text/javascript">
	var circle_pop = [];
	var addingWrapperCircle = 0;
	var map;
	var circles = [];
	var drawableCircles = [];
	var clusterMarkers = [];
	var markers = [];
	var allMarkerClusters = '';
	var geojson = {
		type: "FeatureCollection",
		features: [],
	};
	var alreadyPresentCirCles = JSON.parse(JSON.stringify(<?php echo (isset($this->locationDetails) ? json_encode($this->locationDetails) : ''); ?>));
	console.log(alreadyPresentCirCles)
	var drawingManager;
	var city, region, country, geolocation;

	async function initMap() {

		const { Map, InfoWindow } = await google.maps.importLibrary("maps");
		const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary(
			"marker",
		);

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
						zoom: "<?php echo $this->zoomSize; ?>" ? Number("<?php echo $this->zoomSize; ?>") : 10,
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


						var marker = new google.maps.marker.AdvancedMarkerElement({
							position: mapCenter,
							map: map
						});
						
						bounds.extend(marker.position);
					});

					// Fit the map to the bounds
					map.fitBounds(bounds);

					var infoWindow = new google.maps.InfoWindow({
						content: "",
						disableAutoPan: true,
					});
				}


				// Add some markers to the map.
				var markers = locationsArray.map((position, i) => {
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

					clusterMarkers.push(marker);

					// markers can only be keyboard focusable when they have click listeners
					// open info window when marker is clicked
					marker.addListener("click", () => {
						infoWindow.setContent(position.lat + ", " + position.lng);
						infoWindow.open(map, marker);
					});
					
					return marker;
				});

				// Add a marker clusterer to manage the markers.
				allMarkerClusters = new markerClusterer.MarkerClusterer({ markers, map });


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
						country != null) {
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
				textAreaValue = textAreaValue.replace(new RegExp('thirdPartyUpdatedID', 'g'), id);
				textAreaValue = textAreaValue.replace(new RegExp('thirdPartyUpdatedZoneID', 'g'), zoneid);

				oldHtmlCodeClone.find('#html-widget-textarea-clone').val(textAreaValue);
				oldHtmlCodeClone.find('#html-widget-textarea-clone').attr('id', 'html-widget-textarea');
				oldHtmlCodeClone.attr('style', 'display:"block"');
				oldHtmlCodeClone.attr('class', '');

				jQuery('#thirdparty-form .appendCloneHTMLCode').html(oldHtmlCodeClone);
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
	});
</script>


<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->googleMapApiKey; ?>&libraries=drawing,geometry&loading=async&callback=initMap" async defer></script>
