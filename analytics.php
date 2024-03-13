<?php
// analytics.php
session_start();
// Ensure session is started only once
require_once('DBConnection.php');

function renderCharts($conn, $dfrom, $dto, $user_where)
{
    $sql = "SELECT DATE(date_added) as transaction_date, SUM(total) as total_amount, COUNT(transaction_id) as num_items 
            FROM  `transaction_list` 
            WHERE date(date_added) BETWEEN '{$dfrom}' AND '{$dto}' {$user_where} 
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
    ?>

    <!DOCTYPE HTML>
    <html>

    <head>
        <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <style>
            .filter-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }

            .filter-container label {
                margin-bottom: 0;
                font-weight: bold;
                color: white; /* Set the text color to white */
            }

            .chart-container {
                margin-top: 20px;
            }
        </style>
    </head>

    <body style="background-color: black;"> <!-- Set the background color here -->

        <div class="filter-container">
            <div>
                <label for="date_from" class="control-label">Date From</label>
                <input type="date" name="date_from" id="date_from" value="<?php echo $dfrom ?>" class="form-control rounded-0">
            </div>
            <div>
                <label for="date_to" class="control-label">Date To</label>
                <input type="date" name="date_to" id="date_to" value="<?php echo $dto ?>" class="form-control rounded-0">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary rounded-0" id="filter" type="button"><i class="fa fa-filter"></i> Filter</button>
            </div>
        </div>

        <div class="chart-container">
            <div id="chartContainerAmount" style="height: 370px; width: 100%;"></div>
            <div id="chartContainerNumItems" style="height: 370px; width: 100%; margin-top: 20px;"></div>
        </div>

        <script>
            window.onload = function () {

                var chartAmount = new CanvasJS.Chart("chartContainerAmount", {
                    animationEnabled: true,
                    theme: "light2",
                    title: {
                        text: "Total Amount Sold"
                    },
                    axisX: {
                        title: "Date",
                        interval: 1,
                        valueFormatString: "YYYY-MM-DD"
                    },
                    axisY: {
                        title: "Amount",
                        includeZero: true,
                        prefix: "$"
                    },
                    toolTip: {
                        shared: true
                    },
                    legend: {
                        cursor: "pointer",
                        verticalAlign: "center",
                        horizontalAlign: "right",
                        itemclick: toggleDataSeriesAmount
                    },
                    data: [{
                        type: "column",
                        name: "Total Amount",
                        indexLabel: "{y}",
                        yValueFormatString: "$#0.##",
                        showInLegend: true,
                        dataPoints: <?php echo json_encode($dataPointsAmount, JSON_NUMERIC_CHECK); ?>
                    }]
                });
                chartAmount.render();

                var chartNumItems = new CanvasJS.Chart("chartContainerNumItems", {
                    animationEnabled: true,
                    theme: "light2",
                    title: {
                        text: "Number of Items Sold"
                    },
                    axisX: {
                        title: "Date",
                        interval: 1,
                        valueFormatString: "YYYY-MM-DD"
                    },
                    axisY: {
                        title: "Number of Items",
                        includeZero: true
                    },
                    toolTip: {
                        shared: true
                    },
                    legend: {
                        cursor: "pointer",
                        verticalAlign: "center",
                        horizontalAlign: "right",
                        itemclick: toggleDataSeriesNumItems
                    },
                    data: [{
                        type: "column",
                        name: "Number of Items",
                        indexLabel: "{y}",
                        showInLegend: true,
                        dataPoints: <?php echo json_encode($dataPointsNumItems, JSON_NUMERIC_CHECK); ?>
                    }]
                });
                chartNumItems.render();

                function toggleDataSeriesAmount(e) {
                    if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                        e.dataSeries.visible = false;
                    } else {
                        e.dataSeries.visible = true;
                    }
                    chartAmount.render();
                }

                function toggleDataSeriesNumItems(e) {
                    if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                        e.dataSeries.visible = false;
                    } else {
                        e.dataSeries.visible = true;
                    }
                    chartNumItems.render();
                }

                $('#filter').click(function () {
                    location.href = "analytics.php?date_from=" + $('#date_from').val() + "&date_to=" + $('#date_to').val();
                });
            }
        </script>
    </body>

    </html>
    <?php
}

// Set default values if not provided in the URL
$dfrom = isset($_GET['date_from']) ? $_GET['date_from'] : date("Y-m-d", strtotime(date("Y-m-d") . " -1 week"));
$dto = isset($_GET['date_to']) ? $_GET['date_to'] : date("Y-m-d");
$user_where = "";
if ($_SESSION['type'] != 1) {
    $user_where = " and user_id = '{$_SESSION['user_id']}' ";
}

// Include the following function call to render the charts with the current date filter
renderCharts($conn, $dfrom, $dto, $user_where);
?>
