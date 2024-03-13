<?php
// update_charts.php

require_once('DBConnection.php');

$dateFrom = $_GET['date_from'];
$dateTo = $_GET['date_to'];

$user_where = "";
if ($_SESSION['type'] != 1) {
    $user_where = " and user_id = '{$_SESSION['user_id']}' ";
}

$sql = "SELECT DATE(date_added) as transaction_date, SUM(total) as total_amount, COUNT(transaction_id) as num_items 
        FROM  `transaction_list` 
        WHERE date(date_added) BETWEEN '{$dateFrom}' AND '{$dateTo}' {$user_where} 
        GROUP BY transaction_date  
        ORDER BY transaction_date ASC";

$qry = $conn->query($sql);

$dataPointsAmount = array();
$dataPointsNumItems = array();

while ($row = $qry->fetch_assoc()) {
    $dataPointsAmount[] = array(
        "label" => date("Y-m-d", strtotime($row['transaction_date'])),
        "y" => $row['total_amount']
    );

    $dataPointsNumItems[] = array(
        "label" => date("Y-m-d", strtotime($row['transaction_date'])),
        "y" => $row['num_items']
    );
}

echo json_encode(array(
    "dataPointsAmount" => $dataPointsAmount,
    "dataPointsNumItems" => $dataPointsNumItems
));
?>
