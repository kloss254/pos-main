<?php
$conn = new mysqli("localhost", "root", "", "pos");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$dailyOrders = [];
$productsPerformance = [];
$productRevenue = [];
$dailyBestProduct = [];
$totalRevenue = 0;
$paymentMethods = ['Cash' => 0, 'Mpesa' => 0];
$dailyPayments = ['Cash' => [], 'Mpesa' => []];
$unpaidOrders = 0;
$dailyUnpaid = [];

$weekDays = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
foreach ($weekDays as $day) {
    $dailyOrders[$day] = 0;
    $dailyPayments['Cash'][$day] = 0;
    $dailyPayments['Mpesa'][$day] = 0;
    $dailyUnpaid[$day] = 0;
    $dailyBestProduct[$day] = ["product" => "", "revenue" => 0];
}

$sql = "SELECT o.*, p.product_name, p.price FROM orders o JOIN products p ON o.product_id = p.id";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $day = date('l', strtotime($row['created_at']));
    $dailyOrders[$day]++;
    $product = $row['product_name'];
    $revenue = $row['price'] * $row['quantity'];
    $totalRevenue += $revenue;

    if (!isset($productsPerformance[$product])) {
        $productsPerformance[$product] = array_fill_keys($weekDays, 0);
        $productRevenue[$product] = 0;
    }
    $productsPerformance[$product][$day] += $row['quantity'];
    $productRevenue[$product] += $revenue;

    if ($revenue > $dailyBestProduct[$day]['revenue']) {
        $dailyBestProduct[$day] = ["product" => $product, "revenue" => $revenue];
    }

    if ($row['payment_method'] === 'Mpesa') {
        $paymentMethods['Mpesa']++;
        $dailyPayments['Mpesa'][$day]++;
    } elseif ($row['payment_method'] === 'Cash') {
        $paymentMethods['Cash']++;
        $dailyPayments['Cash'][$day]++;
    }

    if ($row['payment_method'] === 'Unpaid') {
        $unpaidOrders++;
        $dailyUnpaid[$day]++;
    }
}


  
// Fetch latest inventory logs (limit 10)
$logs_result = $conn->query("
  SELECT il.*, p.product_name 
  FROM inventory_logs il 
  JOIN products p ON il.product_id = p.id 
  ORDER BY il.timestamp DESC 
  LIMIT 10
");

$stockActivity = [];
$activityQuery = "
    SELECT p.product_name, COUNT(*) AS activity_count
    FROM inventory_logs il
    JOIN products p ON il.product_id = p.id
    GROUP BY il.product_id
    ORDER BY activity_count DESC
    LIMIT 10
";
$result = $conn->query($activityQuery);
while ($row = $result->fetch_assoc()) {
    $stockActivity[$row['product_name']] = $row['activity_count'];
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Weekly Sales Report</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      padding: 20px;
    }
    h1, h2 {
      color: #333;
    }
    .button-group {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 20px;
    }
    .button-group button {
      padding: 10px 20px;
      font-size: 14px;
      margin-right: 10px;
      background: #2c3e50;
      border: none;
      color: #fff;
      cursor: pointer;
      border-radius: 5px;
    }
    .button-group button:last-child {
     : background: #6c757d;
    }
    .chart-section {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 40px;
      background: #fff;
      border: 1px solid #ccc;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .chart {
      width: 60%;
    }
    .stats {
      width: 35%;
      padding-left: 20px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 10px;
    }
    .stat-box {
      background: #f8f9fa;
      border-left: 4px solid #007bff;
      padding: 12px 16px;
      border-radius: 5px;
    }
    .highlight-box {
      background: #8ca3a7;
      border-left: 4px solid #2c3e50;
      font-weight: bold;
    }
    .stat-box h4 {
      margin: 0;
      font-size: 16px;
      color: #333;
    }
    .stat-box p {
      margin: 4px 0 0;
      font-size: 14px;
      color: #555;
    }
    canvas {
      max-height: 260px;
      background: #fff;
    }
    
  </style>
</head>
<body>



<div id="reportContent">
  <h1><bold>Weekly Sales Analysis</bold></h1>
    
  <div class="button-group">
    <button onclick="downloadPDF()"> Download PDF Report</button>
    <button onclick="window.location.href='admin-dashboard.php'">⬅️ Back to Dashboard</button>
  </div>

  <!-- Orders -->
  <div class="chart-section">
    <div class="chart">
      <h2>Orders Made Daily</h2>
      <canvas id="dailyOrders"></canvas>
    </div>
    <div class="stats">
      <div class="stat-box highlight-box"><h4>Total Orders</h4><p><?= array_sum($dailyOrders) ?></p></div>
      <?php foreach ($dailyOrders as $day => $count): ?>
        <div class="stat-box"><h4><?= $day ?></h4><p><?= $count ?> orders</p></div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Product Performance -->
  <div class="chart-section">
    <div class="chart">
      <h2>Product Performance</h2>
      <canvas id="productPerformance"></canvas>
    </div>
    <div class="stats">
      <?php foreach ($productsPerformance as $product => $days): ?>
        <div class="stat-box"><h4><?= $product ?></h4><p>Total: <?= array_sum($days) ?> sold</p></div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Payment Methods -->
  <div class="chart-section">
    <div class="chart">
      <h2>Payment Methods (Daily Trend)</h2>
      <canvas id="paymentLineChart"></canvas>
    </div>
    <div class="stats">
      <div class="stat-box highlight-box"><h4>Total Cash</h4><p><?= $paymentMethods['Cash'] ?> transactions</p></div>
      <div class="stat-box highlight-box"><h4>Total Mpesa</h4><p><?= $paymentMethods['Mpesa'] ?> transactions</p></div>
      <?php foreach ($weekDays as $day): ?>
        <div class="stat-box">
          <h4><?= $day ?></h4>
          <p>Mpesa: <?= $dailyPayments['Mpesa'][$day] ?> transactions</p>
          <p>Cash: <?= $dailyPayments['Cash'][$day] ?> transactions</p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Unpaid -->
  <div class="chart-section">
    <div class="chart">
      <h2>Unpaid Orders (Daily)</h2>
      <canvas id="unpaidOrders"></canvas>
    </div>
    <div class="stats">
      <div class="stat-box highlight-box"><h4>Total Unpaid</h4><p><?= $unpaidOrders ?> orders</p></div>
      <?php foreach ($weekDays as $day): ?>
        <div class="stat-box"><h4><?= $day ?></h4><p><?= $dailyUnpaid[$day] ?> unpaid</p></div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Revenue Summary -->
  <div class="chart-section">
    <div class="chart">
      <h2>Revenue Overview</h2>
      <canvas id="revenueChart"></canvas>
    </div>
    <div class="stats">
      <div class="stat-box"><h4>Total Revenue</h4><p><?= number_format($totalRevenue, 2) ?> KES</p></div>
      <?php foreach ($productRevenue as $product => $revenue): ?>
        <div class="stat-box"><h4><?= $product ?> Revenue</h4><p><?= number_format($revenue, 2) ?> KES</p></div>
      <?php endforeach; ?>
    </div>
  </div>


    <!-- Stock Activity -->
  <div class="chart-section">
    <div class="chart">
      <h2>Stock Activity by Product</h2>
      <canvas id="stockActivityChart"></canvas>
    </div>
    <div class="stats">
      <?php foreach ($stockActivity as $product => $count): ?>
        <div class="stat-box">
          <h4><?= $product ?></h4>
          <p><?= $count ?> updates</p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>


  <!-- Inventory Logs Section -->
  <div class="chart-section">
    <div class="chart" style="width:100%">
      <h2>Recent Inventory Logs</h2>
      <table style="width:100%; border-collapse: collapse; font-size: 14px;">
        <thead style="background: #e9ecef;">
          <tr>
            <th style="padding: 10px;">Date</th>
            <th style="padding: 10px;">Product</th>
            <th style="padding: 10px;">Action</th>
            <th style="padding: 10px;">Qty</th>
            <th style="padding: 10px;">Old Stock</th>
            <th style="padding: 10px;">New Stock</th>
            <th style="padding: 10px;">User</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($log = $logs_result->fetch_assoc()): ?>
            <tr style="border-bottom: 1px solid #ccc;">
              <td style="padding: 8px;"><?= htmlspecialchars($log['timestamp']) ?></td>
              <td style="padding: 8px;"><?= htmlspecialchars($log['product_name']) ?></td>
              <td style="padding: 8px;"><?= ucfirst(htmlspecialchars($log['action'])) ?></td>
              <td style="padding: 8px;"><?= $log['quantity'] ?></td>
              <td style="padding: 8px;"><?= $log['old_stock'] ?></td>
              <td style="padding: 8px;"><?= $log['new_stock'] ?></td>
              <td style="padding: 8px;"><?= $log['user'] ?: 'Admin' ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>


  <!-- Best Product by Day -->
  <div class="chart-section">
    <div class="chart">
      <h2>Best Products by Day</h2>
      <canvas id="bestProductChart"></canvas>
    </div>
    <div class="stats">
      <?php foreach ($dailyBestProduct as $day => $data): ?>
        <div class="stat-box"><h4><?= $day ?></h4><p><?= $data['product'] ?>: <?= number_format($data['revenue'], 2) ?> KES</p></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- JavaScript for PDF -->
<script>
function downloadPDF() {
  const element = document.getElementById('reportContent');

  const opt = {
    margin:       0.3,
    filename:     'weekly_sales_report.pdf',
    image:        { type: 'jpeg', quality: 0.98 },
    html2canvas:  { scale: 2 },
    jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
  };

  html2pdf().set(opt).from(element).save();
}
</script>


<!-- Chart Scripts -->
<script>
  const days = <?= json_encode(array_keys($dailyOrders)) ?>;
  const dailyData = <?= json_encode(array_values($dailyOrders)) ?>;

  new Chart(document.getElementById('dailyOrders'), {
    type: 'bar',
    data: { labels: days, datasets: [{ label: 'Orders per Day', data: dailyData, backgroundColor: '#007BFF' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });

  const productLabels = <?= json_encode(array_keys($productsPerformance)) ?>;
  const productData = <?= json_encode(array_values($productsPerformance)) ?>;
  const productDatasets = productLabels.map((label, i) => ({
    label: label,
    data: Object.values(productData[i]),
    backgroundColor: 'hsl(' + (i * 40 % 360) + ', 70%, 50%)'
  }));

  new Chart(document.getElementById('productPerformance'), {
    type: 'bar',
    data: { labels: days, datasets: productDatasets },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });

  const dailyCash = <?= json_encode(array_values($dailyPayments['Cash'])) ?>;
  const dailyMpesa = <?= json_encode(array_values($dailyPayments['Mpesa'])) ?>;

  new Chart(document.getElementById('paymentLineChart'), {
    type: 'line',
    data: {
      labels: days,
      datasets: [
        { label: 'Cash', data: dailyCash, borderColor: '#28a745', backgroundColor: 'rgba(40,167,69,0.2)', fill: true },
        { label: 'Mpesa', data: dailyMpesa, borderColor: '#ffc107', backgroundColor: 'rgba(255,193,7,0.2)', fill: true }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      }
    }
  });

  const unpaidData = <?= json_encode(array_values($dailyUnpaid)) ?>;

  new Chart(document.getElementById('unpaidOrders'), {
    type: 'bar',
    data: { labels: days, datasets: [{ label: 'Unpaid Orders', data: unpaidData, backgroundColor: '#dc3545' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });

  const productRevenueLabels = <?= json_encode(array_keys($productRevenue)) ?>;
  const productRevenueData = <?= json_encode(array_values($productRevenue)) ?>;

  new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: { labels: productRevenueLabels, datasets: [{ label: 'Revenue (KES)', data: productRevenueData, backgroundColor: '#17a2b8' }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });

  const bestLabels = <?= json_encode(array_keys($dailyBestProduct)) ?>;
  const bestRevenueData = <?= json_encode(array_column($dailyBestProduct, 'revenue')) ?>;

  new Chart(document.getElementById('bestProductChart'), {
    type: 'bar',
    data: { labels: bestLabels, datasets: [{ label: 'Best Product Revenue', data: bestRevenueData, backgroundColor: '#6f42c1' }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });
  const stockLabels = <?= json_encode(array_keys($stockActivity)) ?>;
const stockCounts = <?= json_encode(array_values($stockActivity)) ?>;

new Chart(document.getElementById('stockActivityChart'), {
  type: 'bar',
  data: {
    labels: stockLabels,
    datasets: [{
      label: 'Stock Update Count',
      data: stockCounts,
      backgroundColor: '#20c997'
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      title: {
        display: true,
        text: 'Most Frequently Updated Products'
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1 }
      }
    }
  }
});

</script>
</body>
</html>
