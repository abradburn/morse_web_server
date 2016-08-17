var map = function($target){
	this.target = $target;
	this.object = null;
	this.data = null;
}

function map_init($map){
	var styles = [{"featureType": "administrative", "stylers": [{ "visibility": "off" }]},{"featureType": "landscape", "stylers": [{ "visibility": "off" }]},{"featureType": "poi", "stylers": [
      { "visibility": "off" }]},{"featureType": "water", "stylers": [{ "visibility": "simplified" }]},{"featureType": "road", "stylers": [{ "visibility": "simplified" }]},{"featureType": "transit",
    "stylers": [{ "visibility": "off" }]}]

	var styledMap = new google.maps.StyledMapType(styles, {name: "Styled Map"});
	var $mapOptions = {
		zoom: 10,
		center: new google.maps.LatLng(28.48449950, -81.25188330), // Orange County, FL
		mapTypeControlOptions: {
			mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
		}
	}
	$map.object = new google.maps.Map(document.getElementById($map.target), $mapOptions);

	$map.object.mapTypes.set('map_style', styledMap);
 	$map.object.setMapTypeId('map_style');
}

function area_map_display($object, $data){
	$object.data = $data;

	map_draw_shapes($object);
}

function heat_map_display($object, $data){
	$object.data = $data;

	//map_draw_heatmap($object);
	map_draw_pointmap($object);
}

function map_draw_heatmap($map){
	$map.points = new Array();

	for(var prop in $map.data.points){
		$map.points.push(new google.maps.LatLng($map.data.points[prop][0], $map.data.points[prop][1]));
	}

	var pointArray = new google.maps.MVCArray($map.points);

	$map.heatmap = new google.maps.visualization.HeatmapLayer({data: pointArray});

	$map.heatmap.setMap($map.object);

	$map.heatmap.set('radius', 20);
	$map.heatmap.set('opacity', 1);
}

function map_draw_pointmap($map){
	$map.points = new Array();

	var image = $HOME + 'image/point_1px.png';

	for(var prop in $map.data.points){
		$map.points[prop] = new google.maps.Marker({position: new google.maps.LatLng($map.data.points[prop][0], $map.data.points[prop][1]), map: $map.object, icon: image});
	}


}

function create_legend($map, $target){
	var $max = Math.round($map.data.max * 10000) / 100;
	var $green = Math.round($map.data.max * .75 * 10000) / 100;
	var $yellow = Math.round($map.data.max * .5 * 10000) / 100;
	var $orange = Math.round($map.data.max * .25 * 10000) / 100;
	var $red = 0;

	$($target).html(
		'<span class="level_5">'+$max+'% - '+$green+'%</span><span class="level_4">'+$green+'% - '+$yellow+'%</span><span class="level_3">'+$yellow+'% - '+$orange+'%</span><span class="level_2">'+$orange+'% - '+$red+'%</span><span class="level_1">No circulators.</span>'
	);
}

function map_draw_shapes($map){
	for(var prop in $map.data.shapes){
		var $coords = new Array();
		var $lat = {
			max: null,
			min: null
		}
		var $lng = {
			max: null,
			min: null
		}
		var $center = null;

		for(var $i = 0; $i < $map.data.shapes[prop].length; $i++){
			$coords.push(new google.maps.LatLng($map.data.shapes[prop][$i][0], $map.data.shapes[prop][$i][1]));

			if($map.data.shapes[prop][$i][0] > $lat.max || $lat.max == null){
				$lat.max = $map.data.shapes[prop][$i][0];
			}else if($map.data.shapes[prop][$i][0] < $lat.min || $lat.min == null){
				$lat.min = $map.data.shapes[prop][$i][0];
			}

			if($map.data.shapes[prop][$i][1] > $lng.max || $lng.max == null){
				$lng.max = $map.data.shapes[prop][$i][1];
			}else if($map.data.shapes[prop][$i][1] < $lng.min || $lng.min == null){
				$lng.min = $map.data.shapes[prop][$i][1];
			}
		}

		$center = new google.maps.LatLng(($lat.max - $lat.min) / 2 + $lat.min, ($lng.max - $lng.min) / 2 + $lng.min);

		var $fill_color = '#FFFFFF';
		var $opacity = .5;
		if(typeof $map.data.style_data[prop] !== 'undefined'){
			var $percent = $map.data.style_data[prop] / $map.data.max;
			if($percent > .75){
				$fill_color = '#2ACB46';
			}else if($percent <= .75 && $percent > .5){
				$fill_color = '#F8FF2E';
			}else if($percent <= .5 && $percent > .25){
				$fill_color = '#FFB52E';
			}else if($percent <= .25){
				$fill_color = '#FF3A2E';
				$opacity = .2;
			}
		}

		$map.shapes = new Array();

		$map.shapes[prop] = new google.maps.Polygon({
			paths: $coords,
			strokeColor: '#000000',
			strokeOpacity: 0.2,
			strokeWeight: 2,
			fillColor: $fill_color,
			fillOpacity: $opacity
		});

		$map.shapes[prop].setMap($map.object);

		$map.labels = new Array();
		var image = $HOME + 'image/point_1px.png';

		$map.labels[prop] = new MapLabel({
			text: prop,
			map: $map.object,
		});

		$map.labels[prop].set('position', $center);
	}

	$map.points = new Array();
	var image = $HOME + 'image/point_1px.png';

	for(var prop in $map.data.points){
		var $latlng = new google.maps.LatLng($map.data.points[prop][0], $map.data.points[prop][1]);
		$map.points.push(new google.maps.Marker({
			position: $latlng,
			map: $map.object,
			icon:image
		}));
	}

	$map.branches = new Array();
	var image = $HOME + 'image/library_icon_20.png';

	for(var prop in $map.data.branches){
		var $latlng = new google.maps.LatLng($map.data.branches[prop]['lat'], $map.data.branches[prop]['lng']);
		$map.points.push(new google.maps.Marker({
			position: $latlng,
			map: $map.object,
			icon: image
		}));
	}
}
