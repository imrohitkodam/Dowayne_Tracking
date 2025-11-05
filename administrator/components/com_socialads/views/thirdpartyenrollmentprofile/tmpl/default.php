    <?php
    /**
     * @version    SVN:<SVN_ID>
     * @package    Com_Socialads
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

    $versionObj = new SaVersion;
    $options = array("version" => $versionObj->getMediaVersion());
    HTMLHelper::stylesheet('media/com_sa/vendors/font-awesome/css/font-awesome.min.css', $options);

    $root = Uri::root();
    ?>
    <style>
        .profile-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .profile-section h4 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #007cba;
            padding-bottom: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .form-control[readonly] {
            background-color: #f8f9fa;
            opacity: 1;
        }
        .copy-btn {
            margin-top: 5px;
        }
        #map {
            height: 300px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h2><?php echo Text::_('COM_SOCIALADS_THIRD_PARTY_ENROLLMENT_PROFILE'); ?></h2>
            </div>
        </div>

        <div class="row">
            <!-- Widget Code Section -->
            <div class="col-md-6">
                <div class="profile-section">
                    <h4><?php echo Text::_('COM_SOCIALADS_WIDGET_CODE'); ?></h4>
                    
                    <div class="form-group">
                        <label for="zone-select"><?php echo Text::_('COM_SOCIALADS_CHOOSE_ZONE'); ?></label>
                        <select id="zone-select" class="form-control">
                            <option value=""><?php echo Text::_('COM_SOCIALADS_SELECT_ZONE'); ?></option>
                            <?php foreach ($this->zones as $zone): ?>
                                <option value="<?php echo $zone->id; ?>" 
                                    data-width="<?php echo $zone->img_width; ?>"
                                    data-height="<?php echo $zone->img_height; ?>"
                                    data-responsive="<?php echo $zone->is_responsive; ?>"
                                    data-use-ratio="<?php echo $zone->use_image_ratio; ?>"
                                    data-width-ratio="<?php echo $zone->img_width_ratio; ?>"
                                    data-height-ratio="<?php echo $zone->img_height_ratio; ?>">
                                    <?php echo $this->escape($zone->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="widget-details">
                        <div class="form-group">
                            <label><?php echo Text::_('COM_SOCIALADS_HTML_CODE'); ?></label>
                            <textarea id="html-code" class="form-control" rows="8" readonly><?php 
                            // Show default widget code for first available zone
                            if (!empty($this->zones)) {
                                $firstZone = reset($this->zones);
                                $model = $this->getModel();
                                echo $this->escape($model->getWidgetCode($this->item->id, $firstZone->id));
                            }
                            ?></textarea>
                            <button type="button" class="btn btn-primary copy-btn" onclick="copyToClipboard('html-code')">
                                <?php echo Text::_('COM_SOCIALADS_COPY_TO_CLIPBOARD'); ?>
                            </button>
                        </div>

                        <div class="form-group">
                            <label><?php echo Text::_('COM_SOCIALADS_IMAGE_LINK'); ?></label>
                            <input type="text" id="image-link" class="form-control" readonly value="<?php 
                            // Show default image link for first available zone
                            if (!empty($this->zones)) {
                                $firstZone = reset($this->zones);
                                $model = $this->getModel();
                                echo $this->escape($model->getImageLink($this->item->id, $firstZone->id));
                            }
                            ?>">
                            <button type="button" class="btn btn-primary copy-btn" onclick="copyToClipboard('image-link')">
                                <?php echo Text::_('COM_SOCIALADS_COPY_TO_CLIPBOARD'); ?>
                            </button>
                        </div>

                        <div class="form-group">
                            <label><?php echo Text::_('COM_SOCIALADS_URL_LINK'); ?></label>
                            <input type="text" id="url-link" class="form-control" readonly value="<?php 
                            // Show default URL link for first available zone
                            if (!empty($this->zones)) {
                                $firstZone = reset($this->zones);
                                $model = $this->getModel();
                                echo $this->escape($model->getUrlLink($this->item->id, $firstZone->id));
                            }
                            ?>">
                            <button type="button" class="btn btn-primary copy-btn" onclick="copyToClipboard('url-link')">
                                <?php echo Text::_('COM_SOCIALADS_COPY_TO_CLIPBOARD'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        var thirdPartyId = <?php echo $this->item->id; ?>;
        var rootUrl = "<?php echo $root; ?>";
        var locationDetails = <?php echo json_encode($this->locationDetails); ?>;

        // Zone select change event
        document.getElementById('zone-select').addEventListener('change', function() {
            var zoneId = this.value;
            var widgetDetails = document.getElementById('widget-details');

            if (zoneId) {
                // Generate HTML code
                var htmlCode = generateHtmlCode(thirdPartyId, zoneId, this.options[this.selectedIndex]);
                document.getElementById('html-code').value = htmlCode;

                // Generate image and URL links
                document.getElementById('image-link').value = rootUrl + 'index.php?option=com_socialads&task=thirdparty.getImageById&id=' + thirdPartyId + '&zoneid=' + zoneId;
                document.getElementById('url-link').value = rootUrl + 'index.php?option=com_socialads&task=thirdparty.getUrlById&id=' + thirdPartyId + '&zoneid=' + zoneId;

                widgetDetails.style.display = 'block';
            } else {
                widgetDetails.style.display = 'none';
            }
        });

        // Generate Embed HTML code
        function generateHtmlCode(thirdPartyId, zoneId, zoneOption) {
            var width = '100px';
            var height = '100px';

            if (zoneOption.dataset.responsive == '1') {
                width = '100%';
                height = 'auto';
            } else {
                if (zoneOption.dataset.useRatio == '1' && zoneOption.dataset.widthRatio) {
                    width = (parseFloat(zoneOption.dataset.widthRatio) * 100) + 'px';
                } else if (zoneOption.dataset.width) {
                    width = zoneOption.dataset.width + 'px';
                }

                if (zoneOption.dataset.useRatio == '1' && zoneOption.dataset.heightRatio) {
                    height = (parseFloat(zoneOption.dataset.heightRatio) * 100) + 'px';
                } else if (zoneOption.dataset.height) {
                    height = zoneOption.dataset.height + 'px';
                }
            }

            return `
    <div id="displayThirdPartyWidget" style="display:none;">
        <div id="displayThirdPartyAd"></div>
    </div>

    <input type="hidden" id="thirdPartyID" value="${thirdPartyId}">
    <input type="hidden" id="thirdPartyZoneID" value="${zoneId}">

    <noscript>
        <a href="${rootUrl}index.php?option=com_socialads&task=thirdparty.getUrlById&id=${thirdPartyId}&zoneid=${zoneId}" target="_blank"> 
            <img src="${rootUrl}index.php?option=com_socialads&task=thirdparty.getImageById&id=${thirdPartyId}&zoneid=${zoneId}" height="${height}" width="${width}" /> 
        </a>
    </noscript>`;
        }

        // Load Ad dynamically
        function loadAdHtml() {
            var id = document.getElementById("thirdPartyID").value;
            var zoneid = document.getElementById("thirdPartyZoneID").value;

            var xhttp = new XMLHttpRequest();
            xhttp.open("GET", rootUrl + "index.php?option=com_socialads&task=thirdparty.getThirdPartyAdHtml&id=" + id + "&zoneid=" + zoneid, false);
            xhttp.send();

            document.getElementById('displayThirdPartyAd').innerHTML = xhttp.responseText;
            document.getElementById('displayThirdPartyWidget').style.display = '';
        }

        // Copy to Clipboard function
        function copyToClipboard(elementId) {
            var element = document.getElementById(elementId);
            element.select();
            element.setSelectionRange(0, 99999);

            try {
                document.execCommand('copy');
                alert('Copied to clipboard successfully!');
            } catch (err) {
                console.error('Failed to copy: ', err);
                alert('Failed to copy to clipboard');
            }
        }

        // Initialize Google Map if locations exist
        <?php if (!empty($this->locationDetails)): ?>
        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 10,
                center: {lat: 0, lng: 0}
            });

            var bounds = new google.maps.LatLngBounds();
            var locations = [];

            <?php foreach ($this->locationDetails as $location): ?>
                <?php
                $locationCoords = trim($location->location, '()');
                $coords = explode(',', $locationCoords);
                if (count($coords) == 2):
                ?>
                var location = {
                    lat: parseFloat(<?php echo trim($coords[0]); ?>),
                    lng: parseFloat(<?php echo trim($coords[1]); ?>)
                };
                locations.push(location);
                bounds.extend(location);

                var marker = new google.maps.Marker({
                    position: location,
                    map: map,
                    title: '<?php echo $this->escape($location->city . ', ' . $location->region . ', ' . $location->country); ?>'
                });

                var circle = new google.maps.Circle({
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35,
                    map: map,
                    center: location,
                    radius: <?php echo $location->radius * 1609.34; ?> // Convert miles to meters
                });
                <?php endif; ?>
            <?php endforeach; ?>

            if (locations.length > 0) {
                map.fitBounds(bounds);
            }
        }

        // Load Google Maps API
        var script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=<?php echo $this->params->get('google_map_api_key', ''); ?>&callback=initMap';
        script.async = true;
        document.head.appendChild(script);
        <?php endif; ?>
    </script>

