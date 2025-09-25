<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <title>Claims Amount vs. Income</title>
</head>
<body>

<?php 
$con = new mysqli('localhost', 'root', 'AppDev@2021', 'DBInsurance');
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Query to fetch claim amounts and driver incomes
$sql = "
    SELECT 
        dd.INCOME, 
        cl.CLM_AMT 
    FROM driverDetails dd
    INNER JOIN claimDetails cl ON dd.ID = cl.ID
    WHERE cl.CLAIM_FLAG = 1";

$query = $con->query($sql);
if (!$query) {
    die("Query failed: " . $con->error);
}

$incomes = [];
$claimAmounts = [];
while ($data = $query->fetch_assoc()) {
    // Removing dollar signs and commas from income and claim amount to convert to numeric values
    $income = str_replace(['$', ','], '', $data['INCOME']);
    $claimAmount = str_replace(['$', ','], '', $data['CLM_AMT']);
    
    $incomes[] = floatval($income);
    $claimAmounts[] = floatval($claimAmount);
}
?>

<div id="myScatterPlot" style="width:100%;max-width:700px"></div>

<script>
// Convert PHP arrays to JavaScript arrays
const incomesArray = <?php echo json_encode($incomes); ?>;
const claimAmountsArray = <?php echo json_encode($claimAmounts); ?>;

// Set up the plot layout
const layout = { 
    title: "Claims Amount vs. Income",
    xaxis: { title: "Income ($)" },
    yaxis: { title: "Claim Amount ($)" }
};

// Configure the data for the scatter plot
const data = [{
    x: incomesArray,
    y: claimAmountsArray,
    mode: 'markers',
    type: 'scatter'
}];

// Render the scatter plot
Plotly.newPlot("myScatterPlot", data, layout);
</script>

</body>
</html>
