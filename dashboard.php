<?php
session_start();
require_once __DIR__ . '/middleware/auth.php'; // Ensure user is logged in
handleAuth();
require_once __DIR__ . '/stats/summary.php'; // This provides $summary array
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold text-dark"><i class="fas fa-chart-line me-2 text-danger"></i>Dashboard Thống Kê</h2>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-5">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                <div class="display-5 fw-bold text-primary mb-2"><?php echo $summary['total_students']; ?></div>
                <div class="text-muted text-uppercase tracking-wider small fw-bold">Tổng số sinh viên</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                <div class="display-5 fw-bold text-success mb-2"><?php echo $summary['average_gpa']; ?></div>
                <div class="text-muted text-uppercase tracking-wider small fw-bold">GPA Trung Bình</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">
                <div class="display-5 fw-bold text-danger mb-2"><?php echo $summary['pass_rate']; ?>%</div>
                <div class="text-muted text-uppercase tracking-wider small fw-bold">Tỷ lệ Đạt</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4">
        <!-- Pass/Fail Chart -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <h5 class="fw-bold mb-4 text-dark text-center">Tỷ lệ Đạt / Trượt</h5>
                <div style="height: 300px;">
                    <canvas id="passFailChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Grade Distribution Chart -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <h5 class="fw-bold mb-4 text-dark text-center">Phân bố điểm chữ</h5>
                <div style="height: 300px;">
                    <canvas id="gradeDistChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch data for charts
    fetch('stats/chart_data.php')
        .then(response => response.json())
        .then(data => {
            // Pass/Fail Chart
            const ctxPF = document.getElementById('passFailChart').getContext('2d');
            new Chart(ctxPF, {
                type: 'doughnut',
                data: {
                    labels: data.pass_fail.labels,
                    datasets: [{
                        data: data.pass_fail.data,
                        backgroundColor: ['#28a745', '#dc3545'],
                        hoverOffset: 10,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '70%'
                }
            });

            // Grade Distribution Chart
            const ctxGD = document.getElementById('gradeDistChart').getContext('2d');
            new Chart(ctxGD, {
                type: 'bar',
                data: {
                    labels: data.grade_distribution.labels,
                    datasets: [{
                        label: 'Số lượng sinh viên',
                        data: data.grade_distribution.data,
                        backgroundColor: '#dc3545',
                        borderRadius: 8,
                        barThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching chart data:', error));
});
</script>

<style>
.rounded-4 { border-radius: 1rem !important; }
.tracking-wider { letter-spacing: 0.05em; }
.display-5 { font-size: 3rem; }
.card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
