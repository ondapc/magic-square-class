<?php
/**
 * @link   https://github.com/zionsg/MagicSquare for repository
 * @author Zion Ng <zion@intzone.com>
 * @since  2016-06-19T20:00+08:00
 */

use DomainException;
use InvalidArgumentException;

/**
 * Magic square constructor
 *
 * @todo Implement singly even generator
 */
class MagicSquare
{
    /**
     * Supported orders of magic squares - to be init in constructor
     *
     * @var array
     */
    protected $orders = [];

    /**
     * Constructor
     *
     * Setup callbacks for supported orders of magic squares.
     */
    public function __construct()
    {
        $this->orders = [
            'odd' => [
                'test' => function ($n) { return (1 === $n % 2); },
                'computeWidth' => function ($cellCount) {
                    $min = (int) ceil(sqrt($cellCount));
                    return $min + (0 === $min % 2 ? 1 : 0);
                },
                'generator' => 'generateOdd',
            ],

            'doublyEven' => [
                'test' => function ($n) { return (0 === $n % 4); },
                'computeWidth' => function ($cellCount) {
                    $min = (int) ceil(sqrt($cellCount));
                    return (int) (ceil($min / 4) * 4);
                },
                'generator' => 'generateDoublyEven',
            ],

            
            'singlyEven' => [
                'test' => function ($n) { return (0 === ($n - 2) % 4); },
                'computeWidth' => function ($cellCount) {
                    $min = (int) ceil(sqrt($cellCount));
                    return (int) (ceil(($min - 2) / 4) * 4) + 2;
                },
                'generator' => 'generateSinglyEven',
            ],
        ];
    }

    /**
     * Compute minimum width of magic square needed to contain a number of cells
     *
     * A cell may contain a character, number, word, etc. A 4x4 magic square consists of 16 cells.
     * This goes thru all the supported orders, computes the respective widths and finds the smallest.
     *
     * @param  int $cellCount
     * @throws InvalidArgumentException if $cellCount is not a positive integer
     * @return int Returns 0 if width cannot be computed
     */
    public function computeWidth($cellCount)
    {
        $this->assertPositiveInteger($cellCount);

        $widths = [];
        foreach ($this->orders as $order) {
            $widthFn = $order['computeWidth'];
            if (is_callable($widthFn)) {
                $widths[] = $widthFn($cellCount);
            }
        }

        return ($widths ? min($widths) : 0);
    }

    /**
     * Create n x n magic square with numbers 1 to n
     *
     * Note that there is no solution for n = 2.
     * No further assertions or tests will be done in the individual generators as they are already done here.
     *
     * @param  int $n
     * @throws InvalidArgumentException if n is not a positive integer
     * @throws DomainException if n is 2
     * @throws DomainException if unable to generate for n
     * @return array
     *         [   // Example of result for 3x3 square
     *             [<row 1 col 1>, <row 1 col 2>, <row 1 col 3>],
     *             [<row 2 col 1>, <row 2 col 2>, <row 2 col 3>],
     *             [<row 3 col 1>, <row 3 col 2>, <row 3 col 3>],
     *         ]
     */
    public function generate($n)
    {
        $this->assertPositiveInteger($n);

        if (2 === $n) {
            throw new DomainException("There is no solution for n = 2");
        }

        // In general, the orders here are mutually exclusive, ie. there is no $n where 2 orders may be used,
        // hence there is no need to find the smallest width among compatible orders and use that order
        foreach ($this->orders as $name => $order) {
            $testFn = $order['test'];
            if (is_callable($testFn) && $testFn($n)) {
                $generator = $order['generator'];
                if (is_callable(array($this, $generator))) {
                    return $this->$generator($n);
                }
            }
        }

        throw new DomainException("Unable to generate {$n} x {$n} magic square");
    }

    /**
     * Compute sum of n x n magic square
     *
     * @param  int $n
     * @return int
     */
    public function computeSum($n)
    {
        $this->assertPositiveInteger($n);

        return ($n / 2) * (pow($n, 2) + 1);
    }

    /**
     * Check if magic square is valid
     *
     * All rows must yield the same sum.
     * All columns must yield the same sum.
     * Both diagonals must yield the same sum.
     *
     * @param  array $magicSquare @see result for generate()
     * @return bool
     */
    public function isValid(array $magicSquare)
    {
        $n = count($magicSquare);
        if (0 === $n) {
            return false;
        }

        // Sums
        $sum = $this->computeSum($n);
        $sums = array_fill(0, $n, $sum);
        $rowSums = array_map('array_sum', $magicSquare);
        $columnSums = [];
        $diagonals = [[], []]; // 1st diagonal from top-left to bottom-right, 2nd from bottom-left to top-right
        $diagonalSums = [];

        for ($col = 0; $col < $n; $col++) {
            // array_column can be used if PHP >= 5.5
            $columnSums[] = array_sum(array_map(function ($row) use ($col) { return $row[$col]; }, $magicSquare));

            $diagonals[0][] = $magicSquare[$col][$col];
            $diagonals[1][] = $magicSquare[$n - $col - 1][$col];
        }
        $diagonalSums = array_map('array_sum', $diagonals);

        $result = ($rowSums === $sums) && ($columnSums === $sums)
               && ($sum === $diagonalSums[0]) && ($sum === $diagonalSums[1]);

        return $result;
    }

    /**
     * Render magic square as HTML table
     *
     * @param  array  $magicSquare     @see result for generate()
     * @param  bool   $useDefaultStyle Whether to use default CSS styling for table
     * @param  string $tableClass      Optional CSS class for HTML table
     * @return string Empty string returned if magic square is empty
     */
    public function render(array $magicSquare)
    {
        $n = count($magicSquare);
        if (0 === $n) {
            return '';
        }


        for ($row = 0; $row < $n; $row++) {
            for ($col = 0; $col < $n; $col++) {
                    $array[$row][] = $magicSquare[$row][$col];
            }
        }
        return $array;
    }

    /**
     * Assert that a variable is a positive integer
     *
     * @param  mixed $var
     * @throws InvalidArgumentException if assertion fails
     * @return bool
     */
    protected function assertPositiveInteger($var)
    {
        $result = (is_int($var) && $var > 0);

        if (!$result) {
            throw new InvalidArgumentException("{$var} is not a positive integer");
        }

        return $result;
    }

    /**
     * Create n x n magic square where n is odd, ie. n = 2p + 1, p being a positive integer
     *
     * Uses Siamese method.
     *
     * @link   https://en.wikipedia.org/wiki/Siamese_method
     * @param  int $n
     * @return array @see result for generate()
     */
    protected function generateOdd($n)
    {
        $result = array_fill(0, $n, array_fill(0, $n, ''));

        $i = 0;
        $max = $n * $n;
        $row = 0;
        $col = (int) floor($n / 2);

        do {
            $i++;
            $result[$row][$col] = $i;

            $nextRow = ($row - 1 + $n) % $n;
            $nextCol = ($col + 1) % $n;
            $isNextCellEmpty = ('' === $result[$nextRow][$nextCol]);

            $row = $isNextCellEmpty ? $nextRow : (($row + 1) % $n);
            $col = $isNextCellEmpty ? $nextCol : $col;
        } while ($i < $max);

        return $result;
    }

    /**
     * Create n x n magic square where n is doubly even, ie. n = 4p, p being a positive integer
     *
     * Uses Albrecht Dürer's method.
     *
     * @link   https://en.wikipedia.org/wiki/Magic_square
     * @param  int $n
     * @return array @see result for generate()
     */
    protected function generateDoublyEven($n)
    {
		// Base grid for 4 x 4
		/*
        $baseGrid = [
            [0, 1, 1, 0],
            [1, 0, 0, 1],
            [1, 0, 0, 1],
            [0, 1, 1, 0],
        ];
		*/
		// Base grid for 4 x 4 ( inverse )
        $baseGrid = [
            [1, 0, 0, 1],
            [0, 1, 1, 0],
            [0, 1, 1, 0],
            [1, 0, 0, 1],
        ];
		
        // Step 1: Expand base grid to n x n
        $grid = [];
        for ($row = 0; $row < $n; $row++) {
            for ($col = 0; $col < $n; $col++) {
                $grid[$row][$col] = $baseGrid[$row % 4][$col % 4];
            }
        }

        $result = [];

        // Step 2: Counting from 1 to n, go from FIRST cell (topmost left), left to right, top to bottom and fill up the cells where grid indicates '1'.
        $i = 0;
        for ($row = 0; $row < $n; $row++) {
            for ($col = 0; $col < $n; $col++) {
                $i++;
                if (0 === $grid[$row][$col]) {
                    continue;
                }
                $result[$row][$col] = $i;
            }
        }

        // Step 3: Counting from 1 to n, go from LAST cell (bottommost right), right to left, bottom to top and fill up the cells where grid indicates '0'.
        $i = 0;
        for ($row = ($n - 1); $row >= 0; $row--) {
            for ($col = ($n - 1); $col >= 0; $col--) {
                $i++;
                if (1 === $grid[$row][$col]) {
                    continue;
                }
                $result[$row][$col] = $i;
            }
        }
        return $result;
    }

    /**
     * Create n x n magic square where n is singly even, ie. n = 4p + 2, p being a positive integer
     *
     * n is not defined as n = 2p as there is no solution for n = 2 with p = 1
     * Uses Conway's LUX method.
     *
     * @link   https://en.wikipedia.org/wiki/Conway%27s_LUX_method_for_magic_squares
     * @param  int $n
     * @return array @see result for generate()
     */
    protected function generateSinglyEven($n)
    {
		$k = 1 ;
		for ( $i=0 ; $i < $n ;$i++ ) {
			for ( $j=($n-1) ; $j >= 0 ; $j-- ) {
				if (($i == $j ) or ($i+$j+1 == $n)) {
					$result[$i][$j] = $k ;
				} elseif (((($i+$j)%2 == 0) and ((($i+$j >= $n) and ($j < $n/2)) or (($i-$j > 0) and ($i < $n/2) and ($i > 2)) or (($j-$i > 0) and ($i >= $n/2)) or (($i+$j < $n) and ($j >= $n/2) and ($i > 1)))) or ((($i+$j)%2 == 1) and ((($i+$j < $n) and ($i >= $n/2)) or (($j-$i > 0) and ($j < $n/2) and ($i > 1)) or (($i-$j > 0) and ($j >= $n/2)) or (($i+$j > $n) and ($i < $n/2) and ($i > 2))))) {
					$result[$i][$j] = $n*$n-$k+$n-$j*2;
				} elseif (((($i+$j)%2 == 0) and ((($j-$i > 0) and ($j < $n/2)) or (($i+$j >= $n) and ($i < $n/2) and ($j < $n-2)) or (($i+$j < $n) and ($i >= $n/2)) or (($i-$j > 0) and ($j >= $n/2) and ($j < $n-3)))) or ((($i+$j)%2 == 1) and ((($i-$j > 0) and ($i < $n/2)) or (($i+$j < $n) and ($j >= $n/2) and ($j < $n-3)) or (($i+$j >= $n) and ($j < $n/2)) or (($j-$i > 0) and ($i >= $n/2) and ($j < $n-2))))) {
					$result[$i][$j] = ($i*2+1)*$n-$k+1 ;
				} else {
					$result[$i][$j] = $n*$n-$k+1 ;
				}
				$k++ ;
			}
		}
		return $result;

    }
}
