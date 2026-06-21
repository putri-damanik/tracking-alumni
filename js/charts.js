/**
 * js/charts.js
 * Logika eksternal Chart.js untuk merender grafik di dashboard.
 * Data (sektorLabels, sektorData, statusLabels, statusData) dikirim
 * dari dashboard.php melalui variabel global yang sudah di-json_encode.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // 1. BAR CHART - Distribusi Sektor Pekerjaan
    // ==========================================
    const ctxSektor = document.getElementById('chartSektor');

    if (ctxSektor) {
        new Chart(ctxSektor, {
            type: 'bar',
            data: {
                labels: sektorLabels,
                datasets: [{
                    label: 'Jumlah Alumni',
                    data: sektorData,
                    backgroundColor: 'rgba(79, 70, 229, 0.7)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.parsed.y + ' alumni';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // ==========================================
    // 2. DONUT CHART - Persentase Status Alumni
    // ==========================================
    const ctxStatus = document.getElementById('chartStatus');

    if (ctxStatus) {
        const statusColors = {
            'Bekerja': '#10b981',
            'Wirausaha': '#f59e0b',
            'Kuliah': '#3b82f6',
            'Mencari Kerja': '#f43f5e'
        };

        const backgroundColors = statusLabels.map(function (label) {
            return statusColors[label] || '#9ca3af';
        });

        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Status Alumni',
                    data: statusData,
                    backgroundColor: backgroundColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            padding: 16,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return ' ' + context.label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

});
