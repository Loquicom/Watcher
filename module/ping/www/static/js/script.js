/* --- Variables --- */

// Ping
let pingChart = null;

/* --- Listeners --- */

function changePingDate() {
    // Show loader
    $('#ping-data tr:not(.loader)').remove();
    $('#ping-data .loader td').show();
    // Get and change date
    const date = $(this).val();
    $('#ping-date').html(date);
    // Ajax
    $.ajax('./ajax/ping/date.php?date=' + encodeURIComponent(date))
        .done((data) => {
            $('#ping-data').html(data);
        })
        .fail((data) => {
            $('#ping-data').html('<tr class="error"><td colspan="5">' + data.responseText + '</td></tr>');
        });
}

function changePingDetailWebsite() {
    // Check limit is valid
    const limit = $('#ping-detail-limit').val();
    if (limit <= 0) {
        return;
    }
    // Show loader
    $('#ping-detail-data tr:not(.loader)').remove();
    $('#ping-detail-data .loader td').show();
    // Ajax
    const website = $(this).val();
    $.ajax('./ajax/ping/detail.php?website=' + encodeURIComponent(website) + '&limit=' + limit)
        .done((data) => {
            $('#ping-detail-data').html(data);
        })
        .fail((data) => {
            $('#ping-detail-data').html('<tr class="error"><td colspan="5">' + data.responseText + '</td></tr>');
        });
}

function changePingDetailLimit() {
    changePingDetailWebsite.call($('#ping-detail-website')[0]);
}

function changePingChartWebsite() {
    const website = $('#ping-chart-website').val();
    const date = $('#ping-chart-selected-date').val();
    // Ajax
    $.ajax('./ajax/ping/chart.php?website=' + encodeURIComponent(website) + '&date=' + encodeURIComponent(date))
        .done((data) => {
            loadPingChart(data.bar, data.line);
        })
        .fail((data) => {
            console.error(data);
        }, 'json');
}

function changePingChartDate() {
    changePingChartWebsite.call($('#ping-chart-website')[0]);
}

/* --- Loader --- */

function loadPingChart(line, bar) {
    // Si le graph existe déjà
    if (pingChart !== null) {
        // Mise à jour des données
        pingChart.data.datasets.forEach(dataset => {
            switch(dataset.id) {
                case 'percent':
                    dataset.data = bar;
                    break;
                case 'total':
                    dataset.data = line;
                    break;
            }
        });
        pingChart.update();
    } 
    // Création du graph
    else {
        // Canvas
        const ctx = document.getElementById('ping-chart').getContext('2d');
        // Label et données
        const labels = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        const data = {
            labels: labels,
            datasets: [
                {
                    id: 'percent',
                    label: 'Successful ping (%)',
                    data: bar,
                    borderColor: 'rgb(255, 179, 0)',
                    backgroundColor: 'rgba(255, 179, 0, 0.5)',
                    stack: 'combined',
                    type: 'bar'
                },
                {
                    id: 'total',
                    label: 'Total ping',
                    data: line,
                    borderColor: 'rgb(30, 136, 229)',
                    backgroundColor: 'rgba(30, 136, 229, 0.5)',
                    stack: 'combined'
                }
            ]
        };
        // Plot
        pingChart = new Chart(ctx,{
            type: 'line',
            data: data,
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            },
        });
    }
}

/* --- Ready --- */

$(() => {
    $('#ping-selected-date').on('change', changePingDate).change();
    $('#ping-detail-website').on('change', changePingDetailWebsite);
    $('#ping-detail-limit').on('change', changePingDetailLimit);
    $('#ping-chart-website').on('change', changePingChartWebsite).change();
    $('#ping-chart-selected-date').on('change', changePingChartDate);
});