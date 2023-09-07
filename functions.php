<?php
// FLATTENS ARRAY
function array_flatten($a) { 
    $ab = array(); 
    
    if(!is_array($a)) return $ab;
    foreach($a as $value){
        if(is_array($value)){
            $ab = array_merge($ab,array_flatten($value));
        } else {
            array_push($ab,$value);
        }
    }
    return $ab;
}

// INTEGER VALID SET
function string_integer($int) {
	if(preg_match('/^\d+$/',$int)) {
		$int = $int;
	} else {
		$int = 5;
	}
	return (int) $int;
}


// MAP COLORS PASSED
function color_gradient($HexFrom, $HexTo, $ColorSteps) {

    $FromRGB['r'] = hexdec(substr($HexFrom, 0, 2));
    $FromRGB['g'] = hexdec(substr($HexFrom, 2, 2));
    $FromRGB['b'] = hexdec(substr($HexFrom, 4, 2));

    $ToRGB['r'] = hexdec(substr($HexTo, 0, 2));
    $ToRGB['g'] = hexdec(substr($HexTo, 2, 2));
    $ToRGB['b'] = hexdec(substr($HexTo, 4, 2));

    $StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps - 1);
    $StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps - 1);
    $StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps - 1);

    $GradientColors = array();

    for($i = 0; $i <= $ColorSteps; $i++) {
        $RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
        $RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
        $RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));

        $HexRGB['r'] = sprintf('%02x', ($RGB['r']));
        $HexRGB['g'] = sprintf('%02x', ($RGB['g']));
        $HexRGB['b'] = sprintf('%02x', ($RGB['b']));

        $GradientColors[] = implode(NULL, $HexRGB);
    }
    $GradientColors = array_filter($GradientColors, function($val){
        return (strlen($val) == 6 ? true : false );
    });
    return $GradientColors;
}
// GENERATE GRADIENT COLOR FROM FUNCTION
function color_from_gradient($n, $min, $max, $colors) {
    $tablecolors = [];
    $prevcolor = array_shift($colors);
    foreach ($colors as $color) {
        $tablecolors = array_merge($tablecolors, color_gradient($prevcolor, $color, 10));
        $prevcolor = $color;
    }
    $max = $max-$min;
    if ( $max == 0 ) {
		$max = 1;
	} else {
		$max = $max;
	}
    $n-= $min;
    if ($n > $max) $n = $max;
    
    $ncolor = round(count($tablecolors)/$max * $n)-1;
    
    if ( strlen($tablecolors[$ncolor]) < 3 ) {
		$tablecolors[$ncolor] = $tablecolors['0'];
	} else {
		$tablecolors[$ncolor] = $tablecolors[$ncolor];
	}
    
    return '#' . $tablecolors[$ncolor];
}


// GET THE NEATMAP MIN / MAX FROM ARRAY VALUES
function table_grid_heatmap_print($array, $grid) {
	$array_flip = $array;
	$min = min($array_flip);
	$max = max($array_flip);
	$columns = $grid;
	$rows = $columns * $columns;	
	$html[] = '<div class="div_table">';	
	$html[] = '<table class="tables">';
	$html[] = '<tr>';
	foreach ($array as $key => $value) {
		$html[] = '<td class="td_gradient" style="background-color: '.color_from_gradient($value, $min, $max, ['FFFFFF', 'FFFF00', 'FF0000']).';"><b>' . $key . '</b> <sup>'.$value.'</sup></td>';
		// check if we need a new row
		if($key % $columns === 0) {
			$html[] = "</tr><tr>";
		}
	}
	$html[] = '</tr>';
	$html[] = '</table>';
	$html[] = '<h5>N<sup>X</sup> WHERE N = NUMBER AND X = OCCURRENCE RATE</h5>';
	$html[] = '</div>';	
	return join("\n", $html);
}






// CHECKS TO SEE IF THE MAGIC SQUARE ARRAY COMPUTES > HORIZONTAL, VERTICAL, AND DIAGONAL SUMS
function magic_square_valid($matrix) {

    // sumd1 and sumd2 are the sum of the two diagonals
    $sumd1 = 0; $sumd2 = 0;
    $n= count($matrix);
    for($i = 0; $i < $n; $i++) {
        // (i, i) is the diagonal from top-left -> bottom-right
        $sumd1 = $sumd1 + $matrix[$i][$i];
        // (i, N - i - 1) is the diagonal from top-right -> bottom-left
        $sumd2 = $sumd2 + $matrix[$i][$n-$i-1];
    }
    // if the two diagonal sums are unequal then it is not a magic square
    if( $sumd1 != $sumd2) {
        return false;
	}
	// checks the horizontal and vertical sums
    for($i = 0; $i < $n; $i++) {
        $rowSum = 0;    $colSum = 0;
        for ($j = 0; $j < $n; $j++) {
			$rowSum += $matrix[$i][$j];
			$colSum += $matrix[$j][$i];
        }
        if ($rowSum != $colSum || $colSum != $sumd1) {
            return false;
        }
    }
    return true;
}








// GENERATE A MULTI-DIMENSIONAL ARRAY OF THE MAGIC SQUARE
function magic_square_array($grid, $matrix) {
	$i = 0;
	$j = 0;
	$sum = 0;
	$arr = array();
	for ( $i=0 ; $i < $grid ; $i++ ) {
		$sum = $sum + $matrix[$i][$grid-$i-1];
	}
	for ( $i=0 ; $i < $grid ; $i++ ) {
		for ( $j=0 ; $j<$grid ; $j++ ) {
			$arr["X"][$j][] = $matrix[$i][$j];
			$arr["Y"][$j][] = $matrix[$j][$i];
		}
	}
	return $arr;
}
// GENERATE DIAGONAL COUNTERPARTS OF THE MAGIC SQUARE
function magic_square_diagonal($matrix) {
	$m = count($matrix["X"]);
	$arr = array();	
	for ($i = 0; $i < $m; $i++) {
		$arr["D1"][] = $matrix["X"][$i][$i];
	}	
	for ($i = 0; $i < $m; $i++) {
		$arr["D2"][] = $matrix["Y"][$i][$m-$i-1];
	}	
	return $arr;
}



// HTML TABLE OF WITH MAGIC SQ. SUM PERMUTATIONS
function table_grid_print($grid, $x, $grid_arrays) {
	$columns = $grid;
	$rows = $grid * $grid;
	$html[] = '<div class="div_table">';	
	$html[] = '<table class="tables">';
	$html[] = '<tr>';
	for($i=1; $i<=$rows; $i++) {
		$d = $grid_arrays;
		$style = style_numbers($i, $d[$x]);
		$html[] = "<td ".$style.">" . $i . "</td>";
		// check if we need a new row
		if($i % $columns === 0) {
			$html[] = "</tr><tr>";
		}
	}
	$html[] = '</tr>';
	$html[] = '</table>';
	$html[] = '</div>';	

	return join("", $html);
}

// STYLE CELLS ( HIGHLIGHT SUMS PERMUTATIONS )
function style_numbers($i, $diagonals) {
	$style = "";
	if (in_array($i, $diagonals)) {
		$style = 'class="td_magic"';
	} else {
		$style = 'class="td_normal"';
	}
return $style;	
}

// PRINTS THE TABLE WITH THE NUMBERS OF THE MAGIC GRID
function magic_square_table($grid, $matrix) {
	$i = 0;
	$j = 0;
	$sum = 0 ;
	for ( $i=0; $i < $grid; $i++ ) {
		$sum = $sum + $matrix[$i][$grid-$i-1];
	}
	$html[] = '<table width="100%" border="1" cellpadding="3" cellspacing="3">';
	for ( $i=0; $i < $grid; $i++ ) {
		$html[] = '<tr>';
		for ( $j=0 ; $j < $grid ; $j++ ) {
			$html[] = '<td align="center">'.$matrix[$i][$j].'</td>';
		}
		$html[] = '</tr>';
	}
	$html[] = '</table>';
	return join("\n", $html);
}






