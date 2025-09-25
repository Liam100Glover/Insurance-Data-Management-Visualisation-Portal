<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <title>Red vs. Non-Red Cars</title>
</head>
<body>

<?php 
$con = new mysqli('localhost', 'root', 'AppDev@2021', 'DBInsurance');
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Query to fetch count of red and non-red cars
$sql = "
    SELECT 
        CASE 
            WHEN RED_CAR = 1 THEN 'Red' 
            ELSE 'Non-Red' 
        END as car_color, 
        COUNT(*) as count
    FROM carDetails
    GROUP BY car_color";

$query = $con->query($sql);
if (!$query) {
    die("Query failed: " . $con->error);
}

$carColors = [];
$counts = [];
while ($data = $query->fetch_assoc()) {
    $carColors[] = $data['car_color'];
    $counts[] = $data['count'];
}
?>

<div id="carColorPlot" style="width:100%;max-width:700px"></div>

<script>
// Convert PHP arrays to JavaScript arrays
const carColorsArray = <?php echo json_encode($carColors); ?>;
const countsArray = <?php echo json_encode($counts); ?>;

// Set up the plot layout
const layout = { title: "Red vs. Non-Red Cars" };
// Configure the data for the pie chart
const data = [{
    labels: carColorsArray,
    values: countsArray,
    type: "pie"
}];

// Render the pie chart
Plotly.newPlot("carColorPlot", data, layout);
</script>

</body>
</html>
