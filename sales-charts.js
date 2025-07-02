// Orders per day
new Chart(document.getElementById('dailyOrders'), {
  type: 'bar',
  data: {
    labels: days,
    datasets: [{
      label: 'Orders per Day',
      data: dailyData,
      backgroundColor: '#007BFF'
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false }
    }
  }
});

// Product performance by day
const productDatasets = productLabels.map((label, i) => ({
  label: label,
  data: Object.values(productData[i]),
  backgroundColor: 'hsl(' + (i * 40 % 360) + ', 70%, 50%)'
}));

new Chart(document.getElementById('productPerformance'), {
  type: 'bar',
  data: {
    labels: days,
    datasets: productDatasets
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});

// Payment method trends
new Chart(document.getElementById('paymentLineChart'), {
  type: 'line',
  data: {
    labels: days,
    datasets: [
      {
        label: 'Cash',
        data: dailyCash,
        borderColor: '#28a745',
        backgroundColor: 'rgba(40,167,69,0.2)',
        fill: true
      },
      {
        label: 'Mpesa',
        data: dailyMpesa,
        borderColor: '#ffc107',
        backgroundColor: 'rgba(255,193,7,0.2)',
        fill: true
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 1 }
      }
    }
  }
});

// Unpaid orders
new Chart(document.getElementById('unpaidOrders'), {
  type: 'bar',
  data: {
    labels: days,
    datasets: [{
      label: 'Unpaid Orders',
      data: unpaidData,
      backgroundColor: '#dc3545'
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false }
    }
  }
});

// Revenue by product
new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: productRevenueLabels,
    datasets: [{
      label: 'Revenue (KES)',
      data: productRevenueData,
      backgroundColor: '#17a2b8'
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});

// Best products per day
new Chart(document.getElementById('bestProductChart'), {
  type: 'bar',
  data: {
    labels: bestLabels,
    datasets: [{
      label: 'Best Product Revenue',
      data: bestRevenueData,
      backgroundColor: '#6f42c1'
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});

// Stock activity
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
