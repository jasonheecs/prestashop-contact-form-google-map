{*	Strip whitespace and carriage return from infobox for use in js	*}
{capture name="map_infobox_content"}{$map_infobox}{/capture}
{assign var="map_infobox_content" value=$smarty.capture.map_infobox_content|escape:"javascript"|replace:"\\r":""|replace:"\\n":""|replace:"\\/":"/"}
<div id="gmap"></div>

<script type="text/javascript">
{literal}
// <![CDATA[
var GMap = (function() {
	"use strict";

	var mapCenter;
	var map;
	var markers;
	var mapOptions;
	var infoWindow;

	function initMarkers(markersData) {
		var markers = [];

		markersData.forEach(function(markerData) {
			var marker = new google.maps.Marker({
				position: markerData.position,
				title: markerData.title,
				icon: markerData.icon,
				animation: google.maps.Animation.DROP
			});
			marker.setMap(map);
			setBounce(marker);
			if (markerData.infoWindowContent) {
				setInfoWindow(marker, markerData.infoWindowContent);
			}

			setTimeout(function() {
				new google.maps.event.trigger(marker, 'click');
				marker.setAnimation(google.maps.Animation.BOUNCE);
			}, 900);

			markers.push(marker);
		});

		return markers;

		function setBounce(marker) {
			google.maps.event.addListener(marker, "click", function() {
				if (marker.getAnimation() != null) {
					marker.setAnimation(null);
				} else {
					marker.setAnimation(google.maps.Animation.BOUNCE);
				}
			});					
		}

		function setInfoWindow(marker, infoWindowContent) {
			google.maps.event.addListener(marker, "click", function() {
				if (infoWindow) {
					infoWindow.close();
					infoWindow=null;
				} else {
					infoWindow = initInfoWindow(infoWindowContent);
					infoWindow.open(map, marker);
				}
			});
		}
	}

	function initInfoWindow(infoWindowContent) {
		var content = "<div id='infoContent'>" +  infoWindowContent +"</div>";

		return new google.maps.InfoWindow({
			content: content
		});
	}

	function setDirectionsLink(linkEl, destCoords) {
		var coords = destCoords["A"] + "," + destCoords["F"];
		var link = "https://maps.google.com?daddr=" + coords;
		linkEl.setAttribute("href", link);
	}

	function initMap() {
		mapCenter = new google.maps.LatLng({/literal}{$map_center_lat}{literal},{/literal}{$map_center_long}{literal});
		mapOptions = {
			center: mapCenter,
			zoom: 18,
			zoomControl: true,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle.DEFAULT,
				position: google.maps.ControlPosition.LEFT_TOP
			},
			panControl: false,
			mapTypeControl: false,
			scaleControl: false,
			streetViewControl: false,
			overviewMapControl: false,
			scrollwheel: false
		};
		map = new google.maps.Map(document.getElementById("gmap"), mapOptions);

		var markerSettings = Object.create(null);

		{/literal}
			{if isset($map_marker_lat) && isset($map_marker_long)}
				{literal}
				markerSettings["position"] = new google.maps.LatLng({/literal}{$map_marker_lat}{literal},{/literal}{$map_marker_long}{literal});
				{/literal}
			{/if}
			{if isset($map_marker)}
				{literal}
				markerSettings["icon"] = "{/literal}{$map_marker}{literal}";
				{/literal}
			{/if}
			{if isset($map_marker_title)}
				{literal}
				markerSettings["title"] = "{/literal}{$map_marker_title}{literal}";
				{/literal}
			{/if}
			{if isset($map_infobox)}
				{literal}
				markerSettings["infoWindowContent"] = "{/literal}{$map_infobox_content}{literal}";
				{/literal}
			{/if}
		{literal}

		if (markerSettings["position"]) {
			markers = initMarkers([markerSettings]);
		}

		// setDirectionsLink(document.getElementById("getdirections_link"), mapCenter);
	}

	function init() {				
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = "https://maps.googleapis.com/maps/api/js?key={/literal}$map_api{literal}&v=3" + "&callback=GMap.initMap";
		document.body.appendChild(script);
	}

	return {
		init: init,
		initMap: initMap
	};
	})();
	window.addEventListener("load", GMap.init);
// ]]>
{/literal}
</script>
