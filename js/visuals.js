// js/visuals.js

function renderRadarChart(canvasElement, scores) { // Diubah: Terima 'canvasElement'
    // DIUBAH: Tidak lagi menggunakan getElementById, langsung gunakan parameter
    if (!canvasElement) {
        console.error('Canvas element not provided to renderRadarChart.');
        return;
    }

    // Hancurkan instance chart yang ada sebelumnya jika ada (untuk mencegah memory leak)
    if (canvasElement.chartInstance) {
        canvasElement.chartInstance.destroy();
    }

    const labels = [
        'Linguistik', 'Logis Matematis', 'Spasial', 'Kinestetik',
        'Musikal', 'Interpersonal', 'Intrapersonal', 'Naturalis'
    ];

    const dataValues = [
        scores.linguistik, scores.logis_matematis, scores.spasial,
        scores.kinestetik, scores.musikal, scores.interpersonal,
        scores.intrapersonal, scores.naturalis
    ];

    const data = {
        labels: labels,
        datasets: [{
            label: 'Skor Bakat Anda (%)',
            data: dataValues,
            fill: true,
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            borderColor: 'rgb(59, 130, 246)',
            pointBackgroundColor: 'rgb(59, 130, 246)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgb(59, 130, 246)'
        }]
    };

    // DIUBAH: Gunakan konteks dari 'canvasElement'
    const ctx = canvasElement.getContext('2d');
    const newChart = new Chart(ctx, {
        type: 'radar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                r: {
                    angleLines: { color: 'rgba(255, 255, 255, 0.3)' },
                    grid: { color: 'rgba(255, 255, 255, 0.3)' },
                    pointLabels: { color: '#FFFFFF', font: { size: 11 } },
                    ticks: { color: '#FFFFFF', backdropColor: 'rgba(0, 0, 0, 0.5)', stepSize: 20 },
                    suggestedMin: 0,
                    suggestedMax: 10
                }
            }
        }
    });

    // Simpan instance chart di elemen itu sendiri untuk referensi di masa depan
    canvasElement.chartInstance = newChart;
}