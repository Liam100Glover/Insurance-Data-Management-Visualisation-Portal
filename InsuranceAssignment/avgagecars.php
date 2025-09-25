<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <title>Average Age of Cars by Type</title>
</head>
<body>

<?php 
$con = new mysqli('localhost', 'root', 'AppDev@2021', 'DBInsurance');
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Query to fetch average car age by car type
$sql = "
    SELECT 
        CAR_TYPE, 
        AVG(CAR_AGE) as average_age
    FROM carDetails
    GROUP BY CAR_TYPE";

$query = $con->query($sql);
if (!$query) {
    die("Query failed: " . $con->error);
}

$carTypes = [];
$averageAges = [];
while ($data = $query->fetch_assoc()) {
    $carTypes[] = $data['CAR_TYPE'];
    $averageAges[] = $data['average_age'];
}
?>

<div id="averageCarAgePlot" style="width:100%;max-width:700px"></div>

<script>
// Convert PHP arrays to JavaScript arrays
const carTypesArray = <?php echo json_encode($carTypes); ?>;
const averageAgesArray = <?php echo json_encode($averageAges); ?>;

// Set up the plot layout
const layout = { title: "Average Age of Cars by Type" };
// Configure the data for the bar chart
const data = [{
    x: carTypesArray,
    y: averageAgesArray,
    type: "bar"
}];

// Render the bar chart
Plotly.newPlot("averageCarAgePlot", data, layout);
</script>

</body>
</html>
