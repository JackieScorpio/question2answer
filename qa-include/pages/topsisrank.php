<?php
// Define the input matrix as a 2D array with n rows and m columns
//$input_matrix = array(
//    array(4, 5, 8, 20),
//    array(7, 6, 2, 0),
//    array(5, 7, 4, 10),
//    array(6, 10, 0, 15),
//    array(8, 2, 5, 5),
//    // add more rows as needed
//);
$input_matrix = array(
    array(0.1, 5, 5000, 4.7),
    array(0.2, 6, 6000, 5.6),
    array(0.4, 7, 7000, 6.7),
    array(0.9, 10, 10000, 2.3),

    array(1.2, 2, 400, 1.8),
    // add more rows as needed
);

// Define the weight matrix as a 1D array with m elements
$wweight_matrix = array(0.2, 0.3, 0.4, 0.1);

// Define the impact matrix as a 1D array with m elements
$iimpact_matrix = array(1, 1, 1, 1);


echo var_dump(topsis($input_matrix, $wweight_matrix, $iimpact_matrix));


function topsis($input_matrix, $weight_matrix, $impact_matrix) {
    // 定义标准化矩阵为具有n行m列的2D阵列
    $normalized_matrix = array();

    // 每列的平方和
    $sum_of_squares = array();

    // 每列平方和的平方根
    $sqrt_sum_of_squares = array();

    // 构建标准化矩阵
    for ($i = 0; $i < count($input_matrix); $i++) {
        $row = array();
        for ($j = 0; $j < count($input_matrix[$i]); $j++) {
            $row[] = $input_matrix[$i][$j];
            $sum_of_squares[$j] += pow($input_matrix[$i][$j], 2);
        }
        $normalized_matrix[] = $row;
    }

    for ($i = 0; $i < count($sum_of_squares); $i++) {
        $sqrt_sum_of_squares[$i] = sqrt($sum_of_squares[$i]);
    }

    $weighted_normalized_matrix = array();
    for ($i = 0; $i < count($normalized_matrix); $i++) {
        $row = array();
        for ($j = 0; $j < count($normalized_matrix[$i]); $j++) {
            $row[] = $normalized_matrix[$i][$j] / $sqrt_sum_of_squares[$j];
        }
        $weighted_normalized_matrix[] = $row;
    }
    echo var_dump($weighted_normalized_matrix);

    // 计算最优解和最劣解
    $ideal_solution = array();
    $negative_ideal_solution = array();
    for ($i = 0; $i < count($weighted_normalized_matrix[0]); $i++) {
        $column = array_column($weighted_normalized_matrix, $i);
        if ($impact_matrix[$i] == 1) {
            $ideal_solution[] = max($column);
            $negative_ideal_solution[] = min($column);
        } else {
            $ideal_solution[] = min($column);
            $negative_ideal_solution[] = max($column);
        }
    }

    // 计算每个备选方案到最优解和最劣解的距离
    $distance_to_ideal = array();
    $distance_to_negative_ideal = array();
    for ($i = 0; $i < count($weighted_normalized_matrix); $i++) {
        $row = $weighted_normalized_matrix[$i];
        $d_plus = 0;
        $d_minus = 0;
        for ($j = 0; $j < count($row); $j++) {
            $d_plus += $weight_matrix[$j] * pow($row[$j] - $ideal_solution[$j], 2);
            $d_minus += $weight_matrix[$j] * pow($row[$j] - $negative_ideal_solution[$j], 2);
        }
        $distance_to_ideal[] = sqrt($d_plus);
        $distance_to_negative_ideal[] = sqrt($d_minus);
    }
    echo var_dump($distance_to_ideal);
    echo var_dump($distance_to_negative_ideal);

    // 计算每个备选方案的得分
    $performance_score = array();
    for ($i = 0; $i < count($distance_to_negative_ideal); $i++) {
        $performance_score[] = $distance_to_negative_ideal[$i] / ($distance_to_ideal[$i] + $distance_to_negative_ideal[$i]);
    }

    return $performance_score;
}