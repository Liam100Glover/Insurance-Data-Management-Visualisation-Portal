<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>Data Visualisation - Kids Driving Frequency</title>
</head>
<body>

<?php 
  $con = new mysqli('localhost', 'root', 'AppDev@2021', 'DBInsurance');
  if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
  }

  // Adjust the SQL query to select KIDSDRIV data
  $sql = "
    SELECT
      dd.KIDSDRIV,
      COUNT(dd.KIDSDRIV) AS Frequency
    FROM
      driverDetails dd
    GROUP BY
      dd.KIDSDRIV
    ORDER BY
      Frequency DESC;
  ";

  $query = $con->query($sql);

  if (!$query) {
    die("Query failed: " . $con->error);
  }

  $kidsDriving = [];
  $frequency = [];
  while ($data = $query->fetch_assoc()) {
    $kidsDriving[] = $data['KIDSDRIV'];
    $frequency[] = $data['Frequency'];
  }
?>

<div style="width: 600px; height: 400px;">
  <canvas id="myChart"></canvas>
</div>
 
<script>
  const labels = <?php echo json_encode($kidsDriving); ?>;
  const data = {
    labels: labels,
    datasets: [{
      label: 'Frequency of Kids Driving',
      data: <?php echo json_encode($frequency); ?>,
      backgroundColor: [
        'rgba(255, 99, 132, 0.2)',
        'rgba(255, 159, 64, 0.2)',
        'rgba(255, 205, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(153, 102, 255, 0.2)',
        'rgba(201, 203, 207, 0.2)'
      ],
      borderColor: [
        'rgb(255, 99, 132)',
        'rgb(255, 159, 64)',
        'rgb(255, 205, 86)',
        'rgb(75, 192, 192)',
        'rgb(54, 162, 235)',
        'rgb(153, 102, 255)',
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
