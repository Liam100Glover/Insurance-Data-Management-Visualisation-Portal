<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <title>Marital Status Distribution</title>
</head>
<body>

<?php 
$con = new mysqli('localhost', 'root', 'AppDev@2021', 'DBInsurance');
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Query to fetch marital status (represented as 0 or 1 in the database)
$sql = "SELECT MSTATUS, COUNT(*) AS count FROM driverDetails GROUP BY MSTATUS";
$query = $con->query($sql);
if (!$query) {
    die("Query failed: " . $con->error);
}

$maritalStatusLabels = [];
$counts = [];
while ($data = $query->fetch_assoc()) {
    $label = ($data['MSTATUS'] == 1) ? 'Married' : 'Single';
    $maritalStatusLabels[] = $label;
    $counts[] = $data['count'];
}
?>

<div id="myPlot" style="width:100%;max-width:700px"></div>

<script>
// Convert PHP arrays to JavaScript arrays
const labelsArray = <?php echo json_encode($maritalStatusLabels); ?>;
const valuesArray = <?php echo json_encode($counts); ?>;

// Set up the plot layout
const layout = { title: "Marital Status Distribution" };
// Configure the data for the pie chart
const data = [{ 
    labels: labelsArray, 
    values: valuesArray, 
    type: "pie", 
    hole: 0.4, 
    marker: {
        colors: ['rgba(75, 192, 192, 0.8)', 'rgba(54, 162, 235, 0.8)'], // colors for Single and Married
        line: {
            color: '#000',
            width: 1
        }
    },
    hoverinfo: 'label+percent',
    textinfo: 'value'
}];

// Render the pie chart
Plotly.newPlot("myPlot", data, layout);
</script>

</body>
</html>
