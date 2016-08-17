google.load('visualization', '1.0', {'packages':['corechart']});
google.load('visualization', '1.0', {'packages':['table']});

function barChart($data, $options, $target){
	var $chart = new google.visualization.BarChart(document.getElementById($target));
	$chart.draw($data, $options);
}

function columnChart($data, $options, $target){
	var $chart = new google.visualization.ColumnChart(document.getElementById($target));
	$chart.draw($data, $options);
}

function pieChart($data, $options, $target){
	var $chart = new google.visualization.PieChart(document.getElementById($target));
	$chart.draw($data, $options);
}

function lineChart($data, $options, $target){
	var $chart = new google.visualization.LineChart(document.getElementById($target));
	$chart.draw($data, $options);
}

function dataTable($data, $options, $target){
	var $chart = new google.visualization.Table(document.getElementById($target));
	$chart.draw($data, $options);
}

function draw_barchart($data, $target){
	if(typeof $data == 'undefined' || $data['data'] == null || typeof $target == 'undefined'){
		return false;
	}

	var $data_table = new google.visualization.DataTable();

	  // Build table layout from list of included columns.
	for(var $column in $data['columns']){
		if($data['columns'][$column]['role'] !== false){
			$data_table.addColumn({id:$data['columns'][$column]['display_name'], type:$data['columns'][$column]['data_type'], role:$data['columns'][$column]['role']});
		}else{
			$data_table.addColumn($data['columns'][$column]['data_type'], $data['columns'][$column]['display_name']);
		}
	}
	$data_table.addRows($data['data']);

	barChart($data_table, $data['options'], $target);
}

function draw_columnchart($data, $target){
	if(typeof $data == 'undefined' || $data['data'] == null || typeof $target == 'undefined'){
		return false;
	}

	var $data_table = new google.visualization.DataTable();

	  // Build table layout from list of included columns.
	for(var $column in $data['columns']){
		if($data['columns'][$column]['role'] !== false){
			$data_table.addColumn({id:$data['columns'][$column]['display_name'], type:$data['columns'][$column]['data_type'], role:$data['columns'][$column]['role']});
		}else{
			$data_table.addColumn($data['columns'][$column]['data_type'], $data['columns'][$column]['display_name']);
		}
	}
	$data_table.addRows($data['data']);

	columnChart($data_table, $data['options'], $target);
}

function draw_piechart($data, $target){
	if(typeof $data == 'undefined' || $data['data'] == null || typeof $target == 'undefined'){
		return false;
	}

	var $data_table = new google.visualization.DataTable();

	  // Build table layout from list of included columns.
	for(var $column in $data['columns']){
		if($data['columns'][$column]['role'] !== false){
			$data_table.addColumn({id:$data['columns'][$column]['display_name'], type:$data['columns'][$column]['data_type'], role:$data['columns'][$column]['role']});
		}else{
			$data_table.addColumn($data['columns'][$column]['data_type'], $data['columns'][$column]['display_name']);
		}
	}
	$data_table.addRows($data['data']);

	pieChart($data_table, $data['options'], $target);
}

function draw_interval($data, $target){
	if(typeof $data == 'undefined' || $data['data'] == null || typeof $target == 'undefined'){
		console.log('Parameters passed to this function do not match the pattern expected.');
		return false;
	}

	var $interval = new google.visualization.DataTable();

	for(var $column in $data['columns']){
		if($data['columns'][$column]['role'] !== false){
			$interval.addColumn({id:$data['columns'][$column]['display_name'], type:$data['columns'][$column]['data_type'], role:$data['columns'][$column]['role']});
		}else{
			$interval.addColumn($data['columns'][$column]['data_type'], $data['columns'][$column]['display_name']);
		}

		if($data['columns'][$column]['data_type'] == 'date'){
			for(var $row in $data['data']){
				$data['data'][$row][$column] = new Date($data['data'][$row][$column]);
			}

		}
	}
	$interval.addRows($data['data']);

	var $options = {
		title: 'Title',
		curveType:'function',
		lineWidth: 2,
		intervals: {'style':'area'},
	}

	if(typeof $data['options'] !== 'undefined'){
		for(var $option in $data['options']){
			$options[$option] = $data['options'][$option];
		}
	}

	lineChart($interval, $options, $target);
}

function draw_datatable($data, $target){
	if(typeof $data == 'undefined' || $data['data'] == null || typeof $target == 'undefined'){
		return false;
	}

	var $format = new Array();

	var $data_table = new google.visualization.DataTable();

	  // Build table layout from list of included columns.
	for(var $column in $data['columns']){
		if($data['columns'][$column]['role'] !== false){
			$data_table.addColumn({id:$data['columns'][$column]['display_name'], type:$data['columns'][$column]['data_type'], role:$data['columns'][$column]['role']});
		}else{
			$data_table.addColumn($data['columns'][$column]['data_type'], $data['columns'][$column]['display_name']);
		}

		if(typeof $data['columns'][$column]['formatting'] !== 'undefined'){
			if($data['columns'][$column]['formatting'][0] == 'decimal'){
				$format.push(new Array(new google.visualization.NumberFormat(
					$data['columns'][$column]['formatting'][1]
				), parseInt($column)));

			}
		}
	}

	$data_table.addRows($data['data']);

	for(var $formatter in $format){
		$format[$formatter][0].format($data_table, $format[$formatter][1]);
	}

	dataTable($data_table, $data['options'], $target);
}

