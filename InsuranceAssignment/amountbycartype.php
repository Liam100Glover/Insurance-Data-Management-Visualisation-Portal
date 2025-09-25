<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>Data Visualisation - Claim Amount by Car Type</title>
</head>
<body>

<?php 
  $con = new mysqli('localhost', 'root', 'AppDev@2021', 'DBInsurance');
  if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
  }

  $sql = "
    SELECT
      cd.CAR_TYPE,
      SUM(CAST(REPLACE(REPLACE(cl.CLM_AMT, '$', ''), ',', '') AS DECIMAL(10, 2))) AS TotalClaimAmount
    FROM
      carDetails cd
    LEFT JOIN
      claimDetails cl ON cd.carID = cl.carID
    GROUP BY
      cd.CAR_TYPE
    ORDER BY
      TotalClaimAmount DESC;
  ";

  $query = $con->query($sql);

  if (!$query) {
    die("Query failed: " . $con->error);
  }

  $CarTypes = [];
  $TotalClaimAmounts = [];
  while ($data = $query->fetch_assoc()) {
    $CarTypes[] = $data['CAR_TYPE'];
    $TotalClaimAmounts[] = $data['TotalClaimAmount'];
  }
?>

<div style="width: 600px; height: 400px;">
  <canvas id="myChart"></canvas>
</div>

<script>
  const labels = <?php echo json_encode($CarTypes); ?>;
  const data = {
    labels: labels,
    datasets: [{
      label: 'Total Claim Amount by Car Type',
      data: <?php echo json_encode($TotalClaimAmounts); ?>,
      backgroundColor: [
        'rgba(54, 162, 235, 0.5)',
        'rgba(255, 206, 86, 0.5)',
        'rgba(75, 192, 192, 0.5)',
        'rgba(153, 102, 255, 0.5)',
        'rgba(255, 159, 64, 0.5)',
        'rgba(255, 99, 132, 0.5)',
        'rgba(201, 203, 207, 0.5)'
      ],
      borderColor: [
        'rgb(54, 162, 235)',
        'rgb(255, 206, 86)',
        'rgb(75, 192, 192)',
        'rgb(153, 102, 255)',
        'rgb(255, 159, 64)',
        'rgb(255, 99, 132)',
        'rgb(201, 203, 207)'
      ],
      borderWidth: 1
    }]
  };

  const config = {
    type: 'bar',
    data: data,
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    },
  };

  var myChart = new Chart(
    document.getElementById('myChart'),
    config
  );
</script>

</body>
</html>
