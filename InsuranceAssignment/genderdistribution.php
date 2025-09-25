<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <title>Gender Distribution Chart</title>
</head>
<body>

<?php 
$con = new mysqli('localhost', 'root', 'AppDev@2021', 'DBInsurance');
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Query to fetch gender counts from the driverDetails table
$sql = "SELECT gender, COUNT(*) as count FROM driverDetails GROUP BY gender";
$query = $con->query($sql);
if (!$query) {
    die("Query failed: " . $con->error);
}

$genders = [];
$counts = [];
while ($data = $query->fetch_assoc()) {
    $genders[] = $data['gender'];  // Expecting gender data as 'M', 'F', etc.
    $counts[] = $data['count'];
}
?>

<div id="myPlot" style="width:100%;max-width:700px"></div>

<script>
// Convert PHP arrays to JavaScript arrays
const labelsArray = <?php echo json_encode($genders); ?>;
const valuesArray = <?php echo json_encode($counts); ?>;

// Set up the plot layout
const layout = { title: "Gender Distribution in Driver Details" };
// Configure the data for the pie chart
const data = [{ labels: labelsArray, values: valuesArray, type: "pie", hole: 0.4 }];

// Render the pie chart
Plotly.newPlot("myPlot", data, layout);
</script>

</body>
</html>
