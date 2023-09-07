<?php
require_once( 'config.php' );
require_once( 'class/MagicSquare.php' );

$grid  = (isset($_REQUEST['grid']) && $_REQUEST['grid']) ? $_REQUEST['grid'] : '5';
$grid = (int) string_integer($grid);

$description = '<h3>Magic square has a grid '.$grid.'<b>x</b>'.$grid.' and all diagonals / horizontals / vertical values sum up to '.(($grid*$grid+1)*$grid/2).'</h3>';

$magicSquare = new MagicSquare();
try {
    $result = $magicSquare->generate($grid);
    $matrix = $magicSquare->render($result);
} catch (Exception $e) {
    echo $e->getMessage();
}

if (magic_square_valid($matrix)) {
	$magic_square_valid = "<h3>This is a valid magic square</h3>";
} else {
	$magic_square_valid = "<h3>This is not a valid magic Square</h3>";
}

$table = magic_square_table($grid, $matrix) ;
$array = magic_square_array($grid, $matrix);
$array_magic_sq = array_merge_recursive($array["X"], $array["Y"]);
$array_magic_diagonal = magic_square_diagonal($array);
$array_magic_sq[] = $array_magic_diagonal["D1"];
$array_magic_sq[] = $array_magic_diagonal["D2"];	

for( $i=0; $i<count($array_magic_sq); $i++ ) {
	$html_table[] = table_grid_print($grid, $i, $array_magic_sq);
}

// FOR THE HEATMAP TABLE
$numbers = array_flatten($array_magic_sq);
$count_occurrence = array_count_values($numbers);
ksort($count_occurrence);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="format-detection" content="address=no,email=no,telephone=no">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title><?php echo trim(strip_tags($description)); ?></title>
<link rel="stylesheet" href="/style.css?v=3B"/>
</head>
<body>
<br/>
<?php echo $description; ?>
<?php echo $magic_square_valid; ?>
<form action="?" method="GET">	
<input type="number" value="<?php echo $grid; ?>" name="grid" /><input type="submit" value="GO" />
</form>
<h3>Magic Square <?php echo $grid.'<b>x</b>'.$grid; ?></h3>
<?php echo $table; ?>
<h3>Heatmap and Permutations</h3>
<?php echo table_grid_heatmap_print($count_occurrence, $grid); ?>
<?php echo join("\n", $html_table);?>
<div style="clear:both;"></div>
</body>
</html>
