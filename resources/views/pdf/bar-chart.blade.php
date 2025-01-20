<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApexCharts Bar Chart Example</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
    <div id="chart"></div>

    <script>
        var options = {
            chart: {
                type: 'bar',
                height: 350
            },
            series: [{
                data: @json($data), // Pass the data array from the controller
            }],
            xaxis: {
                categories: @json($labels), // Pass the labels array from the controller
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);

        chart.render().then(() => {
            window.setTimeout(function() {
                chart.dataURI().then(({
                    imgURI,
                }) => { //Here shows error
                    console.log(imgURI);
                })
            }, 1000)
        });
    </script>
</body>

</html>
