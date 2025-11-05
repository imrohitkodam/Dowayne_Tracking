var circle_pop = [];
var addingWrapperCircle = 0;
var map;
var circles = [];
var drawedCircles = [];
var markers = [];
var geojson = {
	type: "FeatureCollection",
	features: [],
};
var drawingManager;
var pinDensityPerSqKm = 10; // pins per square kilometer
var city, region, country, geolocation;

function initMap() {
	techjoomla.jQuery(document).ready(function() {
		var bounds = '';
		var mapCenter, locationByCircleArray;

		// If already map service area present then display
		if (Array.isArray(mapDetails) && mapDetails.length) {
			mapCenter = {
				lat: parseFloat(mapDetails[0].lat),
				lng: parseFloat(mapDetails[0].lng)
			};

			if (mapDetails.length == 1)
			{
				map = new google.maps.Map(document.getElementById('map'), {
					center: mapCenter,
					zoom: 9,
				});
			}
			else 
			{
				bounds = new google.maps.LatLngBounds();
				map = new google.maps.Map(document.getElementById('map'), {
					mapId: "DEMO_MAP_ID",
				});
			}

		} else {
			mapCenter = {
				lat: 51.509865,
				lng: -0.118092
			};

			map = new google.maps.Map(document.getElementById('map'), {
				center: mapCenter,
				zoom: 9,
			});
		}

		// If already map service area present then display
		if (Array.isArray(mapDetails) && mapDetails.length) 
		{
			// Define places and circles
			var places = [];

			// create places array as per google maps need
			mapDetails.forEach(function(mapDetail) {
				places.push({
					name: 'Service Area',
					location: {
						lat: parseFloat(mapDetail.lat),
						lng: parseFloat(mapDetail.lng)
					},
					radius: parseFloat(mapDetail.radius) // Circle radius in meters
				});
			});

			// Create circles for each place
			places.forEach( function(place) {

				var radiusInMeters = convertMilesToMeters(place.radius);
				createCircle(place.location, radiusInMeters);

				if (mapDetails.length != 1)
				{
					bounds.extend(place.location);
				}
			});

			// Fit the map to the bounds
			if (mapDetails.length != 1)
			{
				map.fitBounds(bounds);
			}

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

				drawedCircles.push(circle);
				// Add an event listener to reposition the circle if the map is dragged
				google.maps.event.addListener(map, 'drag', function() {
					circle.setCenter(center);
				});
			}

			circles = mapDetails;
			jQuery('#selectlocationonmap_div #mapDetails').val(JSON.stringify(circles));
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

		// after circle complete check the population count and push the latest information in circle
		google.maps.event.addListener(drawingManager, 'circlecomplete', function(latestCircle) {
			drawingManager.setDrawingMode(null);

			var center = latestCircle.getCenter();
			var radius = convertMetersToMiles(latestCircle.getRadius());

			circles.push({
				lat: center.lat(),
				lng: center.lng(),
				radius: radius,
				center: center
			});

			drawedCircles.push(latestCircle);
			calculatePopulation(center, radius);

			city = region = country = geolocation = '';
			reverseGeocode(center, radius)
		});

		// Code for hide respective map targeting based on selection (like if map select hide option and)
		techjoomla.jQuery('.targetting_yes_no label').on("click", function() {
			if (jQuery(this).parent().hasClass('selectLocationOnMapDiv'))
			{
				let radio_id = techjoomla.jQuery(this).attr('for');

				techjoomla.jQuery('#' + radio_id).attr('checked', 'checked');

				techjoomla.jQuery('#' + radio_id).prop("checked", true)

				let radio_btn = techjoomla.jQuery('#' + radio_id);
				let radio_value = radio_btn.val();
				if (radio_value == 1)
				{
					jQuery('.selectLocationDiv').parent().addClass('d-none');
				}
				else
				{
					jQuery('.selectLocationDiv').parent().removeClass('d-none');
				}
			}

			if (jQuery(this).parent().hasClass('selectLocationDiv'))
			{
				let radio_id = techjoomla.jQuery(this).attr('for');

				techjoomla.jQuery('#' + radio_id).attr('checked', 'checked');

				/*for jQuery 1.9 and higher*/
				techjoomla.jQuery('#' + radio_id).prop("checked", true)

				let radio_btn = techjoomla.jQuery('#' + radio_id);
				let radio_value = radio_btn.val();
				if (radio_value == 1)
				{
					jQuery('.selectLocationOnMapDiv').parent().addClass('d-none');
				}
				else
				{
					jQuery('.selectLocationOnMapDiv').parent().removeClass('d-none');
				}
			}
		});
	});
}

function startDrawingCircle() {
	// clearCircle();
	if (event)
	{
		event.preventDefault();
	}

	// jQuery('#totalPopulationCount').html('');
	addingWrapperCircle = 0;
	drawingManager.setDrawingMode('circle');
}

function clearCircle() {
	if (event)
	{
		event.preventDefault();
	}
	
	drawedCircles.forEach(circle => circle.setMap(null));
	circles = [];
	drawedCircles = [];
	circle_pop = [];
	city = region = country = geolocation = '';
	jQuery('#totalPopulationCount').html('');
	jQuery('#selectlocationonmap_div #mapDetails').val('');
}

	// Convert miles to meters
function convertMilesToMeters(miles) {
	return miles * 1609.34; // 1 mile = 1609.34 meters
}

// Convert miles to meters
function convertMetersToMiles(meter) {
	return meter / 1609.34; // 1 mile = 1609.34 meters
}

function calculatePopulation(latlng, radius) 
{
	// Get the current bounds
	var bounds = map.getBounds();

	// Get the coordinates of the bounds
	var ne = bounds.getNorthEast(); // Northeast coordinates
	var sw = bounds.getSouthWest(); // Southwest coordinates

	var points = {
		'points' : {
			'ne' : {
				lat : ne.lat(),
				lng : ne.lng()
			},
			'sw' : {
				lat : sw.lat(),
				lng : sw.lng()
			}
		}
	}

	console.log(points)

	techjoomla.jQuery.ajax({
		type: 'GET',
		url: Joomla.getOptions('system.paths').base + '/index.php?option=com_socialads&task=thirdpartyenrollment.getTotalPopulation&format=json',
		// url: 'index.php?option=com_socialads&task=thirdpartyenrollment.getTotalPopulation&format=json',
		data: points,
		async: true,
		dataType: 'json',
		success: function (result) {
			if (result.length) {
				var totalPopulationForNewArea = 0;
				var matchingThirdPartyIds = []; // Array to store matching third_party_ids
				result.forEach(function(item, index) {
					console.log(item)
					var locationByCircle = item.location;
					var locationByCircle = locationByCircle.substring(1, locationByCircle.length-1);

					locationByCircleArray = locationByCircle.split(",");
					mapCenter = {
						lat: parseFloat(locationByCircleArray[0]),
						lng: parseFloat(locationByCircleArray[1])
					};

					let percentage = calculateCircleOverlapPercentage(latlng.lat(), latlng.lng(), radius,
						mapCenter.lat, mapCenter.lng, item.radius);

					console.log(percentage, item);
					var popu = 0;

					if (percentage) {
						// If percentage > 1%, collect the third_party_id
						if (percentage > 1) {
							matchingThirdPartyIds.push(item.third_party_id);
						}
						
						if (percentage == 100) {
							if (radius > item.radius) {
								popu = item.count;
							} else {
								let smallCircleArea = (Math.PI * radius * radius);
								let bigCircleArea = (Math.PI * item.radius * item.radius);
								let remainingAreaPercentage = smallCircleArea * 100 / bigCircleArea;
								popu = remainingAreaPercentage * item.count / 100;
							}

						} else {
							popu = (percentage * item.count) / 100;
						}
						totalPopulationForNewArea += parseInt(popu);
					}
				});
				
				// Display the matching third_party_ids visibly on the page
				if (matchingThirdPartyIds.length > 0) {
					// Store in hidden input for form submission
					var $input = jQuery('#map_third_party_ids');
					if ($input.length === 0) {
						jQuery('#selectlocationonmap_div').append('<input type="hidden" name="map[third_party_ids]" id="map_third_party_ids" value="' + matchingThirdPartyIds.join(',') + '">');
					} else {
						$input.val(matchingThirdPartyIds.join(','));
					}
					
					// Display third party IDs visibly on the page
					var $displayDiv = jQuery('#third_party_ids_display');
					if ($displayDiv.length === 0) {
						jQuery('#selectlocationonmap_div').append('<div id="third_party_ids_display" style="margin-top: 10px; padding: 10px; background-color: #f0f8ff; border: 1px solid #ccc; border-radius: 5px;"><strong>Third Party IDs:</strong> <span id="third_party_ids_list"></span></div>');
						$displayDiv = jQuery('#third_party_ids_display');
					}
					jQuery('#third_party_ids_list').html(matchingThirdPartyIds.join(', '));
					$displayDiv.show();
					
					console.log('Displayed matching third_party_ids:', matchingThirdPartyIds);
				} else {
					// Remove both hidden input and display div if no matches found
					jQuery('#map_third_party_ids').remove();
					jQuery('#third_party_ids_display').hide();
				}

				let multipopulation = 0;
				console.log('Total population for new area' + totalPopulationForNewArea)
				if (circles.length -1)
				{
					// Define given circle and other circles
					let givenCircle = { latitude: latlng.lat(), longitude: latlng.lng(), radius: radius };
					let updatedCircles = [];
					let newCirclePopulation, calculateTotalPopulation = 0;
					newCirclePopulation = totalPopulationForNewArea;
					updatedCircles = circles.slice(0, -1);
					updatedCircles.slice().forEach(function(oldCircle) {
						if (!calculateTotalPopulation)
						{
							let percentage = calculateCircleOverlapPercentage(latlng.lat(), latlng.lng(), radius,
							oldCircle.lat, oldCircle.lng, oldCircle.radius);

							if (percentage == 100) {
								if (radius < oldCircle.radius) {
									calculateTotalPopulation = 1;
									newCirclePopulation = 0;
								} else {
									newCirclePopulation = totalPopulationForNewArea - oldCircle.population;
								}

							} else if (percentage) {
								popu = (percentage * totalPopulationForNewArea) / 100;
								newCirclePopulation = totalPopulationForNewArea - popu;
							}
						}
					});

					multipopulation = parseInt(jQuery('#mapPopulationCount').val()) + parseInt(newCirclePopulation);
				}
				else 
				{
					multipopulation = totalPopulationForNewArea;
				}

				circles[circles.length - 1].population = totalPopulationForNewArea;

				jQuery("#totalPopulationCount").removeClass('d-none');
				jQuery("#totalPopulationCount").html("Total Population is: " + multipopulation);
				jQuery('#mapPopulationCount').val(multipopulation);
			} else {
				circles[circles.length - 1].population = 0;

				if(!parseInt(jQuery('#mapPopulationCount').val()))
				{
					jQuery('#mapPopulationCount').val(0);
					jQuery("#totalPopulationCount").removeClass('d-none');
					jQuery("#totalPopulationCount").html("Total Population is: 0");
				}
				else 
				{
					multipopulation = parseInt(jQuery('#mapPopulationCount').val());
					jQuery("#totalPopulationCount").html("Total Population is: " + multipopulation);
				}
				jQuery("#totalPopulationCount").removeClass('d-none');
				alert('No third party present in given location');
			}
		}
	});

	return true;
}

function calculateCircleOverlapPercentage(lat1, lon1, radius1, lat2, lon2, radius2) {
	const distance = calculateDistance(lat1, lon1, lat2, lon2);

	// Check if the circles are completely separate
	if (distance >= radius1 + radius2) {
		return 0; // No overlap, return 0%
	}

	// Check if one circle is completely within the other
	if (distance <= Math.abs(radius1 - radius2)) {
		const smallerRadius = Math.min(radius1, radius2);
		const overlapPercentage = (Math.PI * smallerRadius * smallerRadius) / (Math.PI * smallerRadius * smallerRadius) * 100;
		return overlapPercentage;
	}

	// Calculate the intersection area using the Law of Cosines
	const angle1 = Math.acos((radius1 * radius1 + distance * distance - radius2 * radius2) / (2 * radius1 * distance));
	const angle2 = Math.acos((radius2 * radius2 + distance * distance - radius1 * radius1) / (2 * radius2 * distance));

	const intersectionArea =
		(angle1 * radius1 * radius1) -
		(0.5 * radius1 * radius1 * Math.sin(2 * angle1)) +
		(angle2 * radius2 * radius2) -
		(0.5 * radius2 * radius2 * Math.sin(2 * angle2));

	const overlapPercentage = (intersectionArea / (Math.PI * radius1 * radius1)) * 100;
	return overlapPercentage;
}

function calculateDistance(lat1, lon1, lat2, lon2) {
	const radius = 3959; // Earth's radius in kilometers
	const dLat = toRadians(lat2 - lat1);
	const dLon = toRadians(lon2 - lon1);

	const a =
		Math.sin(dLat / 2) * Math.sin(dLat / 2) +
		Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);

	const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
	const distance = radius * c;

	return distance;
}

function toRadians(degrees) {
	return degrees * (Math.PI / 180);
}

function reverseGeocode(latlng, radius, population) 
{
	var geocoder = new google.maps.Geocoder();
	// var latlng = new google.maps.LatLng(latitude, longitude);

	geocoder.geocode({
		'latLng': latlng
	}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			if (results[0]) {
				var addressComponents = results[0].address_components;
				let cityUpdated = 0;
				let regionUpdated = 0;
				let countryUpdated = 0;

				for (var i = 0; i < addressComponents.length; i++) {
					var types = addressComponents[i].types;
					if (types.includes('locality')) {
						city = addressComponents[i].long_name;
						if (city)
						{
							cityUpdated = 1;
							// jQuery('#selectlocationonmap_div #circleCity').val(city)
							circles[circles.length-1].city = city;
						}
					} else if (types.includes('administrative_area_level_1')) {
						region = addressComponents[i].long_name;
						regionUpdated = 1;
						if (region)
						{
							// jQuery('#selectlocationonmap_div #circleRegion').val(region)
							circles[circles.length-1].region = region;
						}
					} else if (types.includes('country')) {
						country = addressComponents[i].long_name;
						countryUpdated = 1;
						if (country)
						{
							circles[circles.length-1].country = country;
							// jQuery('#selectlocationonmap_div #circleCountry').val(country)
						}
					}
				}

				if (!cityUpdated)
				{
					circles[circles.length-1].city = ' ';
				}

				if (!regionUpdated)
				{
					circles[circles.length-1].region = ' ';
				}

				if (!countryUpdated)
				{
					circles[circles.length-1].country = ' ';
				}
				jQuery('#selectlocationonmap_div #mapDetails').val(JSON.stringify(circles));

			} else {
				alert('Selected location city, state, Country not exist');
				clearCircle();
			}
		} else {
			console.log("Geocoder failed due to: " + status);
		}
	});

	return true;
}

techjoomla.jQuery(document).ready(function() {
	if (jQuery('#locationCountry').length && jQuery('#locationCountry').val())
	{
		var stateValue = jQuery('.oldStateValue').val() ? jQuery('.oldStateValue').val() : '';
		var cityValue = jQuery('.oldCityValue').val() ? jQuery('.oldCityValue').val() : '';
		sa.create.getStatesList("locationCountry", stateValue, "Select State", "locationState");
		getCityList("locationCountry", cityValue, "Select City", "locationCity");
	}

	techjoomla.jQuery('.selectLocationOnMapDiv label').on("click", function() {
		var radio_id = techjoomla.jQuery(this).attr('for');

		techjoomla.jQuery('.selectLocationDiv #selectlocationcheckboxyes').removeAttr('checked');
		techjoomla.jQuery('#' + radio_id).attr('checked', 'checked');

		var radio_btn = techjoomla.jQuery('#' + radio_id);
		var radio_value = radio_btn.val();

		if (radio_value)
		{
			techjoomla.jQuery('.selectLocationDiv #selectlocationcheckboxno').attr('checked', 'checked');
			techjoomla.jQuery('.selectLocationDiv .last').addClass('btn-danger');
			techjoomla.jQuery('.selectLocationDiv .first').removeClass('btn-success');
			techjoomla.jQuery('#selectlocation_div').hide();
			jQuery('#selectLocationOn').val(0);
		}
	});

	techjoomla.jQuery('.selectLocationDiv label').on("click", function() {
		var radio_id = techjoomla.jQuery(this).attr('for');

		techjoomla.jQuery('.selectLocationOnMapDiv #selectlocationonmapcheckboxyes').removeAttr('checked');
		techjoomla.jQuery('#' + radio_id).attr('checked', 'checked');

		var radio_btn = techjoomla.jQuery('#' + radio_id);
		var radio_value = radio_btn.val();

		if (radio_value)
		{
			techjoomla.jQuery('.selectLocationOnMapDiv #selectlocationonmapcheckboxno').attr('checked', 'checked');
			techjoomla.jQuery('.selectLocationOnMapDiv .last').addClass('btn-danger');
			techjoomla.jQuery('.selectLocationOnMapDiv .first').removeClass('btn-success');
			techjoomla.jQuery('#selectlocationonmap_div').hide();
			jQuery('#selectLocationOn').val(1);
		}
	});

	techjoomla.jQuery('#selectlocation_div #locationCountry').on("change", function() {
		var cityValue = jQuery('#selectlocation_div .cityValue').val();
		getCityList("locationCountry", cityValue, "Select City", "locationCity");
	});

	function getCityList(countryId, Dbvalue, selOptionMsg, id = "city") 
	{
		var country = techjoomla.jQuery('#' + countryId).val();
		if (country == undefined) {
			return (false);
		}
		techjoomla.jQuery.ajax({
			url: Joomla.getOptions('system.paths').base + '/index.php?option=com_sa&task=checkout.loadCity&country=' + country + '&tmpl=component&callfor=getPopulation',
			type: 'GET',
			dataType: 'json',
			success: function (data) {
				if (countryId == 'country') {
					statesListBackup = data;
				}
				generateCitiesOptions(data, countryId, Dbvalue, selOptionMsg, id);
			}
		});
	}

	function generateCitiesOptions(data, countryId, Dbvalue, selOptionMsg, id = "city") 
	{
		var country = techjoomla.jQuery('#' + countryId).val();
		var options, index, select, option;

		// add empty option according to billing or shipping
		select = techjoomla.jQuery('#' + id);
		default_opt = selOptionMsg; //"<?php echo JText::_('COM_SOCIALADS_BILLING_SELECT_STATE')?>";

		// REMOVE ALL STATE OPTIONS
		select.find('option').remove().end();

		// To give msg TASK  "please select country START"
		selected = "selected=\"selected\"";
		var op = '<option ' + selected + ' value="">' + default_opt + '</option>';
		techjoomla.jQuery('#' + id).append(op);
		// END OF msg TASK

		if (data) {
			options = data.options;
			for (index = 0; index < data.length; ++index) {
				var opObj = data[index];
				selected = "";

				if (opObj.id == Dbvalue) {
					selected = "selected=\"selected\"";
				}
				var op = '<option ' + selected + ' value=\"' + opObj.id + '\">' + opObj.city + '</option>';

				techjoomla.jQuery('#' + id).append(op);
				techjoomla.jQuery("#" + id).trigger("liszt:updated"); /* IMP : to update to chz-done selects*/
				techjoomla.jQuery("#" + id).trigger("chosen:updated");
			} // end of for
		}
	}

	techjoomla.jQuery('#selectlocation_div #locationSelect').on("change", function() {
		if (jQuery(this).val())
		{
			if (jQuery(this).val().length)
			{
				var populationCount, totalPopulationCount = 0;
				var selectedLocations = jQuery(this).val();
				selectedLocations.forEach(function (selectedLocation, index)
				{
					populationCount = selectedLocation.split('(')[1];
					populationCount = populationCount.replace(')', '');
					populationCount = parseInt(populationCount ? populationCount : '0');
					totalPopulationCount += populationCount;
				});

				if (totalPopulationCount) {
					jQuery("#totalPopulationCountByCityName").removeClass('d-none');
					jQuery("#totalPopulationCountByCityName").html("Total Population is: " + totalPopulationCount);
					jQuery('#locationInputCount').val(totalPopulationCount);
				} else {
					jQuery('#locationInputCount').val(0);
					jQuery("#totalPopulationCountByCityName").addClass('d-none');
					alert('No third party present in given location');
				}
			} else {
				jQuery('#locationInputCount').val(0);
				jQuery("#totalPopulationCountByCityName").addClass('d-none');
				alert('No third party present in given location');
			}
		}
	});

	function calculatePopulationByCityName(cityName, countryName) 
	{
		var data = {
			city: cityName,
			country: countryName
		};

		techjoomla.jQuery.ajax({
			type: 'GET',
			url: 'index.php?option=com_socialads&task=thirdpartyenrollment.getTotalPopulationByCityName&format=json',
			data: data,
			async: true,
			dataType: 'json',
			success: function (result) {
				if (result) {
					jQuery("#totalPopulationCountByCityName").removeClass('d-none');
					jQuery("#totalPopulationCountByCityName").html("Total Population is: " + result);
					jQuery('#locationInputCount').val(result);
				} else {
					jQuery('#locationInputCount').val(0);
					jQuery("#totalPopulationCountByCityName").addClass('d-none');
					alert('No third party present in given location');
				}
			}
		});

		return true;
	}

});
	