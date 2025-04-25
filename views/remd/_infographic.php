<?php

/** @var array $data */

use yii\helpers\Json;
use yii\web\View;

$months = [
    1 => ['name' => 'Янв', 'field' => 'jan'],
    2 => ['name' => 'Фев', 'field' => 'feb'],
    3 => ['name' => 'Мар', 'field' => 'mar'],
    4 => ['name' => 'Апр', 'field' => 'apr'],
    5 => ['name' => 'Май', 'field' => 'may'],
    6 => ['name' => 'Июн', 'field' => 'jun'],
    7 => ['name' => 'Июл', 'field' => 'jul'],
    8 => ['name' => 'Авг', 'field' => 'aug'],
    9 => ['name' => 'Сен', 'field' => 'sep'],
    10 => ['name' => 'Окт', 'field' => 'oct'],
    11 => ['name' => 'Ноя', 'field' => 'nov'],
    12 => ['name' => 'Дек', 'field' => 'dec']
];

$quarters = [
    '1 кв' => ['field' => 'q1', 'months' => [1, 2, 3]],
    '2 кв' => ['field' => 'q2', 'months' => [4, 5, 6]],
    '3 кв' => ['field' => 'q3', 'months' => [7, 8, 9]],
    '4 кв' => ['field' => 'q4', 'months' => [10, 11, 12]]
];

$labels = [];
$planData = [];
$actualData = [];
$percentData = [];
$quarterLabels = [];
$quarterPlanData = [];
$quarterActualData = [];
$quarterPercentData = [];

foreach ($months as $monthNum => $monthInfo) {
    $monthPlan = $plan->{$monthInfo['field']} ?? 0;
    $monthActual = $actual[$monthNum] ?? 0;

    if ($data['hideEmptyMonths'] && $monthActual == 0) {
        continue;
    }

    $percent = $monthPlan ? round(($monthActual / $monthPlan) * 100) : 0;

    $labels[] = $monthInfo['name'] . ' ' . $percent . '%';
    $planData[] = $monthPlan;
    $actualData[] = $monthActual;
    $percentData[] = $percent;
}

foreach ($quarters as $qName => $qData) {
    $qPlan = $plan->{$qData['field']} ?? 0;
    $qActual = 0;

    foreach ($qData['months'] as $monthNum) {
        $qActual += $actual[$monthNum] ?? 0;
    }

    if ($data['hideEmptyMonths'] && $qActual == 0) {
        continue;
    }

    $percent = $qPlan ? round(($qActual / $qPlan) * 100) : 0;

    $quarterLabels[] = $qName . ' ' . $percent . '%';
    $quarterPlanData[] = $qPlan;
    $quarterActualData[] = $qActual;
    $quarterPercentData[] = $percent;
}

$yearPlan = $plan->year_plan ?? 0;
$yearActual = array_sum($actual);
$yearPercent = $yearPlan ? round(($yearActual / $yearPlan) * 100) : 0;

$allLabels = array_merge($labels, $quarterLabels, ['Год ' . $yearPercent . '%']);
$allPlanData = array_merge($planData, $quarterPlanData, [$yearPlan]);
$allActualData = array_merge($actualData, $quarterActualData, [$yearActual]);
$allPercentData = array_merge($percentData, $quarterPercentData, [$yearPercent]);
?>

<div class="chart-container" style="position: relative; height:400px; margin-top:20px;">
    <canvas id="chart-<?= $plan ? $plan->id : 'general' ?>"></canvas>
</div>

<?php
$this->registerJsFile('@web/js/chart.js', ['position' => View::POS_END]);
$this->registerJsFile('@web/js/chartjs-plugin-datalabels.js', ['position' => View::POS_END]);
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('chart-<?= $plan ? $plan->id : 'general' ?>').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= Json::encode($allLabels) ?>,
                datasets: [
                    {
                        label: 'План',
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1,
                        data: <?= Json::encode($allPlanData) ?>,
                        order: 2
                    },
                    {
                        label: 'Факт',
                        backgroundColor: 'rgba(59, 125, 221, 0.7)',
                        borderColor: 'rgba(59, 125, 221, 1)',
                        borderWidth: 1,
                        data: <?= Json::encode($allActualData) ?>,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'logarithmic',
                        ticks: {
                            callback: function(value) {
                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                            }
                        }
                    }
                },
                plugins: {
                    datalabels: {
                        display: function(context) {
                            return window.innerWidth > 700;
                        },
                        anchor: 'end',
                        align: 'end',
                        color: 'black',
                        font: {
                            size: '10'
                        },
                        formatter: function(value) {
                            return value;
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw.toLocaleString();

                                if (context.datasetIndex === 1 && <?= Json::encode($allPercentData) ?>[context.dataIndex] !== 0) {
                                    label += ' (' + <?= Json::encode($allPercentData) ?>[context.dataIndex] + '%)';
                                }

                                return label;
                            }
                        }
                    }
                }
            }
            ,plugins: [ChartDataLabels]
        });
    });
</script>
