<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <div class="columns-2">
        <x-card class="p-6" shadow separator>
            <div id="chart1"></div>
        </x-card>
        <x-card class="p-6" shadow separator>
            <div id="chart2"></div>
        </x-card>
    </div>


    <script>
        $(document).ready(function () {
            var options = {
                chart: {
                    type: 'pie'
                },
                series: [44, 55, 41, 17, 15],
                chartOptions: {
                    labels: ['Apple', 'Mango', 'Orange', 'Watermelon']
                },
            }

            var chart1 = new ApexCharts(document.querySelector("#chart1"), options);
            var chart2 = new ApexCharts(document.querySelector("#chart2"), options);

            chart1.render();
            chart2.render();
        })
    </script>
</div>
