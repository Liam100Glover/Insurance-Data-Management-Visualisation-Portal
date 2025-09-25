<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <title>Red Cars Claims Distribution Chart</title>
</head>
<body>

<?php 
$con = new mysqli('localhost', 'root', 'AppDev@2021', 'DBInsurance');
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Query to fetch red car claims status counts from the carDetails and claimDetails tables
$sql = "
    SELECT 
        CASE 
            WHEN c.CLAIM_FLAG = 1 THEN 'Claim Made' 
            ELSE 'No Claim Made' 
        END as claim_status, 
        COUNT(*) as count 
    FROM carDetails cd
    LEFT JOIN claimDetails c ON cd.ID = c.ID
    WHERE cd.RED_CAR = 1 
    GROUP BY claim_status";

$query = $con->query($sql);
if (!$query) {
    die("Query failed: " . $con->error);
}

$claimStatuses = [];
$counts = [];
while ($data = $query->fetch_assoc()) {
    $claimStatuses[] = $data['claim_status'];  // Expecting claim status as 'Claim Made', 'No Claim Made'
    $counts[] = $data['count'];
}
?>

<div id="myPlot" style="width:100%;max-width:700px"></div>

<script>
// Convert PHP arrays to JavaScript arrays
const labelsArray = <?php echo json_encode($claimStatuses); ?>;
const valuesArray = <?php echo json_encode($counts); ?>;

// Set up the plot layout
const layout = { title: "Red Cars Claims Distribution" };
// Configure the data for the pie chart
const data = [{ labels: labelsArray, values: valuesArray, type: "pie", hole: 0.4 }];

// Render the pie chart
Plotly.newPlot("myPlot", data, layout);
</script>

</body>
</html>
