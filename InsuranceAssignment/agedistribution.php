<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>Data Visualisation - Age Distribution</title>
</head>
<body>

<?php 
  $con = new mysqli('localhost', 'root', 'AppDev@2021', 'DBInsurance');
  if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
  }

  // SQL query to categorize AGE data
  $sql = "
    SELECT
      CASE 
        WHEN AGE BETWEEN 16 AND 24 THEN '16-24'
        WHEN AGE BETWEEN 25 AND 35 THEN '25-35'
        WHEN AGE BETWEEN 36 AND 45 THEN '36-45'
        ELSE '46+'
      END AS AgeGroup,
      COUNT(*) AS Frequency
    FROM
      driverDetails
    GROUP BY
      AgeGroup
    ORDER BY
      AgeGroup;
  ";

  $query = $con->query($sql);

  if (!$query) {
    die("Query failed: " . $con->error);
  }

  $ageGroups = [];
  $frequencies = [];
  while ($data = $query->fetch_assoc()) {
    $ageGroups[] = $data['AgeGroup'];
    $frequencies[] = $data['Frequency'];
  }
?>

<div style="width: 600px; height: 400px;">
  <canvas id="myChart"></canvas>
</div>
 
<script>
  const labels = <?php echo json_encode($ageGroups); ?>;
  const data = {
    labels: labels,
    datasets: [{
      label: 'Age Group Distribution',
      data: <?php echo json_encode($frequencies); ?>,
      backgroundColor: [
        'rgba(255, 99, 132, 0.2)',
        'rgba(255, 159, 64, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(153, 102, 255, 0.2)'
      ],
      borderColor: [
        'rgb(255, 99, 132)',
        'rgb(255, 159, 64)',
        'rgb(75, 192, 192)',
        'rgb(54, 162, 235)',
        'rgb(153, 102, 255)'
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
