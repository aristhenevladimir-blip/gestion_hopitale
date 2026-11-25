<script src="<?= escape_html(url("/assets/vendor/chart.umd.min.js")) ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();
    if (!window.Chart) return;
    const actCtx = document.getElementById('activityChart');
    if (actCtx) {
        new Chart(actCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($activityLabels) ?>,
                datasets: [{
                    label: 'Consultations',
                    data: <?= json_encode($activityData) ?>,
                    borderColor: '#10a87a', backgroundColor: '#10a87a',
                    order: 1, borderWidth: 2, pointRadius: 3, pointHoverRadius: 5,
                    pointBackgroundColor: '#10a87a', tension: 0.35
                }, {
                    label: 'Prescriptions',
                    data: <?= json_encode($prescriptionActivityData) ?>,
                    borderColor: '#2f80ed', backgroundColor: '#2f80ed',
                    order: 0, borderWidth: 2, pointRadius: 3, pointHoverRadius: 5,
                    pointBackgroundColor: '#2f80ed', borderDash: [8, 6], tension: 0.35
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function(items) { return items.length ? 'Date : ' + items[0].label : ''; },
                            label: function(ctx) {
                                var unit = ctx.dataset.label === 'Consultations' ? 'consultation(s)' : 'prescription(s)';
                                return ctx.dataset.label + ' : ' + ctx.parsed.y + ' ' + unit;
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: '#475569', font: { size: 12 } }, grid: { color: '#e2e8f0' } },
                    x: { ticks: { color: '#475569', font: { size: 12 }, maxTicksLimit: 7, maxRotation: 0 }, grid: { display: false } }
                }
            }
        });
    }
    const sexeCtx = document.getElementById('sexeChart');
    if (sexeCtx) {
        new Chart(sexeCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($sexeLabels) ?>,
                datasets: [{ data: <?= json_encode($sexeData) ?>, backgroundColor: <?= json_encode($sexeColors) ?>, borderWidth: 0, hoverOffset: 10 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '75%',
                plugins: { legend: { display: false }, tooltip: { enabled: true } }
            }
        });
    }
});
</script>
