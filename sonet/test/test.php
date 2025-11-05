<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
									<div id="displayThirdPartyWidget" style="display:none">
									<input type="hidden"  id="thirdPartyID" value="19">
									<input type="hidden"  id="thirdPartyZoneID" value="18">
									<div id="displayThirdPartyAd">
									</div>
								</div>

								<noscript>
							
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
								
									// xhttp.open("GET", "https://ss4all.uk/index.php?option=com_socialads&task=thirdparty.getThirdPartyAdHtml&id=" + id + "&zoneid=" + 24, false);
									xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
									xhttp.send("id=" + id + "&zoneid=" + zoneid);
								
									ad_html = xhttp.responseText;
									document . getElementById('displayThirdPartyAd').innerHTML = ad_html;
									document . getElementById('displayThirdPartyWidget') . style.display = '';

								</script>
								<h1>Host Pune</h1>
								<a href="http://ttpl-rt-234-php83.local/dowayne_last_testing/index.php?option=com_socialads&task=thirdparty.getUrlById&id=1&zoneid=18"><img src="http://ttpl-rt-234-php83.local/dowayne_last_testing/index.php?option=com_socialads&task=thirdparty.getImageById&id=1&zoneid=18"></a>
								<h1>Host London</h1>
								<a href="http://ttpl-rt-234-php83.local/dowayne_last_testing/index.php?option=com_socialads&task=thirdparty.getUrlById&id=2&zoneid=18"><img src="http://ttpl-rt-234-php83.local/dowayne_last_testing/index.php?option=com_socialads&task=thirdparty.getImageById&id=2&zoneid=18"></a>
		
</body>
</html>