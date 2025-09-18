document.addEventListener('DOMContentLoaded', function() {
    // Graphique pour la page des opérateurs
    const operatorChart = document.getElementById('operatorChart');
    
    if (operatorChart) {
        const ctx = operatorChart.getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Couverture réseau', 'Qualité Internet', 'Service client', 'Rapport qualité-prix'],
                datasets: [
                    {
                        label: 'MTN Cameroun',
                        data: [3.8, 3.0, 2.5, 3.3],
                        backgroundColor: '#ffce00',
                        borderColor: '#ffce00',
                        borderWidth: 1
                    },
                    {
                        label: 'Orange Cameroun',
                        data: [3.5, 3.8, 3.0, 2.8],
                        backgroundColor: '#ff7900',
                        borderColor: '#ff7900',
                        borderWidth: 1
                    },
                    {
                        label: 'Nexttel',
                        data: [2.5, 2.0, 1.5, 2.3],
                        backgroundColor: '#063970',
                        borderColor: '#063970',
                        borderWidth: 1
                    },
                    {
                        label: 'Camtel',
                        data: [2.3, 2.8, 1.8, 2.5],
                        backgroundColor: '#3a86ff',
                        borderColor: '#3a86ff',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        title: {
                            display: true,
                            text: 'Évaluation moyenne (sur 5)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Catégories'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Comparaison des opérateurs télécom au Cameroun'
                    }
                }
            }
        });
    }
});