<?php
/* An implementation of the classic hungarian algorithm for the assignment
 * problem.
 * Copyright 2007 Gary Baker (GPL v3)
 * Java to PHP adaptation by James Nolan (aug. 2014)
 *
 * Project: j-p
 * Date: August 2014
 * Author: James Nolan */

require_once('Cell.class.php');

class HungarianAlgorithm {

    public function /* int[][] */ computeAssignments(/*float[][]*/ $matrix) {

        // subtract minumum value from rows and columns to create lots of zeroes
        $this->reduceMatrix($matrix);


        // non negative values are the index of the starred or primed zero in the row or column
        /* int[] */ $starsByRow = array_fill(0, count($matrix), -1);
        /* int[] */ $starsByCol = array_fill(0, count($matrix), -1);
        /* int[] */ $primesByRow = array_fill(0, count($matrix), -1);

        // 1s mean covered, 0s mean not covered
        /* int[] */ $coveredRows = array_fill(0, count($matrix[0]), 0);
        /* int[] */ $coveredCols = array_fill(0, count($matrix[0]), 0);

        // star any zero that has no other starred zero in the same row or column
        $this->initStars($matrix, $starsByRow, $starsByCol);
        $this->coverColumnsOfStarredZeroes($starsByCol, $coveredCols);

        while (!$this->allAreCovered($coveredCols)) {

            /* int[] */ $primedZero = $this->primeSomeUncoveredZero($matrix, $primesByRow, $coveredRows, $coveredCols);
            $i = 0;
            while (is_null($primedZero)) {
                // keep making more zeroes until we find something that we can prime (i.e. a zero that is uncovered)
                $this->makeMoreZeroes($matrix, $coveredRows, $coveredCols);
                $primedZero = $this->primeSomeUncoveredZero($matrix, $primesByRow, $coveredRows, $coveredCols);
            }

            // check if there is a starred zero in the primed zero's row
            /* int */ $columnIndex = $starsByRow[$primedZero[0]];
            if (-1 == $columnIndex){

                // if not, then we need to increment the zeroes and start over
                $this->incrementSetOfStarredZeroes($primedZero, $starsByRow, $starsByCol, $primesByRow);
                $primesByRow = array_fill(0, count($primesByRow), -1);
                $coveredRows = array_fill(0, count($coveredRows), 0);
                $coveredCols = array_fill(0, count($coveredCols), 0);
                $this->coverColumnsOfStarredZeroes($starsByCol, $coveredCols);
            } else {

                // cover the row of the primed zero and uncover the column of the starred zero in the same row
                $coveredRows[$primedZero[0]] = 1;
                $coveredCols[$columnIndex] = 0;
            }
        }

        // ok now we should have assigned everything
        // take the starred zeroes in each column as the correct assignments

        /* int[][] */ $retval = array();
        for (/* int */ $i = 0; $i < count($starsByCol);  $i++) {
            $retval[$i] = array($starsByCol[$i], $i);
        }
        return $retval;





    }

    private function /* boolean */ allAreCovered(/* int[] */ &$coveredCols) {
        foreach (/* int */ $coveredCols as $covered) {
            if (0 == $covered) return false;
        }
        return true;
    }


    /**
     * the first step of the hungarian algorithm
     * is to find the smallest element in each row
     * and subtract it's values from all elements
     * in that row
     *
     * @return the next step to perform
     */
    private function /* void */ reduceMatrix(/* float[][] */ &$matrix) {

        for (/* int */ $i = 0; $i < count($matrix); $i++) {

            // find the min value in the row
            /* float */ $minValInRow = Cell::MAX_VALUE;
            for (/* int */ $j = 0; $j < count($matrix[$i]); $j++) {
                if ($minValInRow > $matrix[$i][$j]->getValue()) {
                    $minValInRow = $matrix[$i][$j]->getValue();
                }
            }

            // subtract it from all values in the row
            for (/* int */ $j = 0; $j < count($matrix[$i]); $j++) {
                $matrix[$i][$j]->setValue($matrix[$i][$j]->getValue() - $minValInRow);
            }
        }

        for (/* int */ $i = 0; $i < count($matrix[0]); $i++) {
            /* float */ $minValInCol = Cell::MAX_VALUE;
            for (/* int */ $j = 0; $j < count($matrix); $j++) {
                if ($minValInCol > $matrix[$j][$i]->getValue()) {
                    $minValInCol = $matrix[$j][$i]->getValue();
                }
            }

            for (/* int */ $j = 0; $j < count($matrix); $j++) {
                $matrix[$j][$i]->setValue($matrix[$j][$i]->getValue() - $minValInCol);
            }
        }

    }

    /**
     * init starred zeroes
     *
     * for each column find the first zero
     * if there is no other starred zero in that row
     * then star the zero, cover the column and row and
     * go onto the next column
     *
     * @param costMatrix
     * @param starredZeroes
     * @param coveredRows
     * @param coveredCols
     * @return the next step to perform
     */
    private function /* void */ initStars(/* float[][] */ &$costMatrix, /* int[] */ &$starsByRow, /* int[] */ &$starsByCol) {


        /* int[] */ $rowHasStarredZero = array_fill(0, count($costMatrix), 0);
        /* int[] */ $colHasStarredZero = array_fill(0, count($costMatrix[0]), 0);

        for (/* int */ $i = 0; $i < count($costMatrix); $i++) {
            for (/* int */ $j = 0; $j < count($costMatrix[$i]); $j++) {
                if (0 == $costMatrix[$i][$j]->getValue() && 0 == $rowHasStarredZero[$i] && 0 == $colHasStarredZero[$j]) {
                    $starsByRow[$i] = $j;
                    $starsByCol[$j] = $i;
                    $rowHasStarredZero[$i] = 1;
                    $colHasStarredZero[$j] = 1;
                    break; // move onto the next row
                }
            }
        }
    }


    /**
     * just marke the columns covered for any coluimn containing a starred zero
     * @param starsByCol
     * @param coveredCols
     */
    private function /* void */ coverColumnsOfStarredZeroes(/* int[] */ &$starsByCol, /* int[] */ &$coveredCols) {
        for (/* int */ $i = 0; $i < count($starsByCol); $i++) {
            $coveredCols[$i] = -1 == $starsByCol[$i] ? 0 : 1;
        }
    }


    /**
     * finds some uncovered zero and primes it
     * @param matrix
     * @param primesByRow
     * @param coveredRows
     * @param coveredCols
     * @return
     */
    private function /* int[] */ primeSomeUncoveredZero(/* float[][] */ &$matrix, /* int[] */ &$primesByRow,
                                       /* int[] */ &$coveredRows, /* int[] */ &$coveredCols) {


        // find an uncovered zero and prime it
        for (/* int */ $i = 0; $i < count($matrix); $i++) {
            if (1 == $coveredRows[$i]) continue;
            for (/* int */ $j = 0; $j < count($matrix[$i]); $j++) {
                // if it's a zero and the column is not covered
                if (0 == $matrix[$i][$j]->getValue() && 0 == $coveredCols[$j]) {

                    // ok this is an unstarred zero
                    // prime it
                    $primesByRow[$i] = $j;
                    return array($i, $j);
                }
            }
        }
        return null;

    }

    /**
     *
     * @param unpairedZeroPrime
     * @param starsByRow
     * @param starsByCol
     * @param primesByRow
     */
    private function /* void */ incrementSetOfStarredZeroes(/* int[] */ &$unpairedZeroPrime, /* int[] */ &$starsByRow, /* int[] */ &$starsByCol, /* int[] */ &$primesByRow) {

        // build the alternating zero sequence (prime, star, prime, star, etc)
        /* int */ $i = 0;
        /* int */ $j = $unpairedZeroPrime[1];

        /* Set<int[]>  */ $zeroSequence = array();
        //$zeroSequence.add($unpairedZeroPrime);
        $zeroSequence[] = $unpairedZeroPrime;
        /* boolean */ $paired = false;
        do {
            $i = $starsByCol[$j];
            $paired = -1 != $i && $zeroSequence[] = array($i, $j);
            if (!$paired) break;

            $j = $primesByRow[$i];
            $paired = -1 != $j && $zeroSequence[] = array($i, $j);

        } while ($paired);


        // unstar each starred zero of the sequence
        // and star each primed zero of the sequence
        foreach (/* int[] */ $zeroSequence as $zero) {
            if ($starsByCol[$zero[1]] == $zero[0]) {
                $starsByCol[$zero[1]] = -1;
                $starsByRow[$zero[0]] = -1;
            }
            if ($primesByRow[$zero[0]] == $zero[1]) {
                $starsByRow[$zero[0]] = $zero[1];
                $starsByCol[$zero[1]] = $zero[0];
            }
        }

    }


    private function /* void */ makeMoreZeroes(/* float[][] */ &$matrix, /* int[] */ &$coveredRows, /* int[] */ &$coveredCols) {

        // find the minimum uncovered value
        /* float */ $minUncoveredValue = Cell::MAX_VALUE;
        for (/* int */ $i = 0; $i < count($matrix); $i++) {
            if (0 == $coveredRows[$i]) {
                for (/* int */ $j = 0; $j < count($matrix[$i]); $j++) {
                    if (0 == $coveredCols[$j] && $matrix[$i][$j]->getValue() < $minUncoveredValue) {
                        $minUncoveredValue = $matrix[$i][$j]->getValue();
                    }
                }
            }
        }

        // add the min value to all covered rows
        for (/* int */ $i = 0; $i < count($coveredRows); $i++) {
            if (1 == $coveredRows[$i]) {
                for (/* int */ $j = 0; $j < count($matrix[$i]); $j++) {
                    try {
                        $matrix[$i][$j]->setValue($matrix[$i][$j]->getValue() + $minUncoveredValue);
                    } catch (Exception $e) {
                        //echo 'Error with ' . $matrix[$j][$i]->getWorker()->fullname . '<br />';
                        //die();
                    }
                }
            }
        }

        // subtract the min value from all uncovered columns
        for (/* int */ $i = 0; $i < count($coveredCols); $i++) {
            if (0 == $coveredCols[$i]) {
                for (/* int */ $j = 0; $j < count($matrix); $j++) {
                    try {
                        $matrix[$j][$i]->setValue($matrix[$j][$i]->getValue() - $minUncoveredValue);
                    } catch (Exception $e) {
                        //echo 'Error with ' . $matrix[$j][$i]->getWorker()->fullname . '<br />';
                        //die();
                    }
                }
            }
        }
    }




}
?>