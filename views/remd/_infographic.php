<?php

/** @var array $data */
/** @var string $chartType */

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

$monthLabels = [];
$monthPlanData = [];
$monthActualData = [];
$monthPercentData = [];

foreach ($months as $monthNum => $monthInfo) {
    $monthPlan = $plan->{$monthInfo['field']} ?? 0;
    $monthActual = $actual[$monthNum] ?? 0;

    if ($data['hideEmptyMonths'] && $monthActual == 0) {
        continue;
    }

    $percent = $monthPlan ? round(($monthActual / $monthPlan) * 100) : 0;

    $monthLabels[] = $monthInfo['name'] . ' ' . $percent . '%';
    $monthPlanData[] = $monthPlan;
    $monthActualData[] = $monthActual;
    $monthPercentData[] = $percent;
}

$periodLabels = [];
$periodPlanData = [];
$periodActualData = [];
$periodPercentData = [];

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

    $periodLabels[] = $qName . ' ' . $percent . '%';
    $periodPlanData[] = $qPlan;
    $periodActualData[] = $qActual;
    $periodPercentData[] = $percent;
}

$yearPlan = $plan->year_plan ?? 0;
$yearActual = array_sum($actual);
$yearPercent = $yearPlan ? round(($yearActual / $yearPlan) * 100) : 0;

$periodLabels[] = 'Год ' . $yearPercent . '%';
$periodPlanData[] = $yearPlan;
$periodActualData[] = $yearActual;
$periodPercentData[] = $yearPercent;
$chartType = $chartType ? $chartType : 'linear';

$this->registerJsFile('@web/js/chart.js', ['position' => View::POS_END]);
$this->registerJsFile('@web/js/chartjs-plugin-datalabels.js', ['position' => View::POS_END]);
?>

<div class="row">
    <div class="col-xl-8">
        <div class="chart-container" style="position: relative; height:400px;">
            <canvas id="chart-month-<?= $plan ? $plan->id : 'general' ?>"></canvas>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="chart-container" style="position: relative; height:400px;">
            <canvas id="chart-period-<?= $plan ? $plan->id : 'general' ?>"></canvas>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
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
                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
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

                            var percentData = [];
                            if (context.chart.canvas.id.includes('month')) {
                                percentData = <?= Json::encode($monthPercentData) ?>;
                            } else {
                                percentData = <?= Json::encode($periodPercentData) ?>;
                            }

                            if (context.datasetIndex === 1 && percentData[context.dataIndex] !== 0) {
                                label += ' (' + percentData[context.dataIndex] + '%)';
                            }

                            return label;
                        }
                    }
                }
            }
        };

        new Chart(
            document.getElementById('chart-month-<?= $plan ? $plan->id : 'general' ?>').getContext('2d'),
            {
                type: 'bar',
                data: {
                    labels: <?= Json::encode($monthLabels) ?>,
                    datasets: [
                        {
                            label: 'План',
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1,
                            data: <?= Json::encode($monthPlanData) ?>,
                            order: 2
                        },
                        {
                            label: 'Факт',
                            backgroundColor: 'rgba(59, 125, 221, 0.7)',
                            borderColor: 'rgba(59, 125, 221, 1)',
                            borderWidth: 1,
                            data: <?= Json::encode($monthActualData) ?>,
                            order: 1
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            type: <?= Json::encode($chartType) ?>,
                            suggestedMax: Math.max(
                                ...<?= Json::encode($monthPlanData) ?>,
                                ...<?= Json::encode($monthActualData) ?>
                            ) * 1.10,
                            ticks: {
                                callback: function(value) {
                                    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                                }
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            }
        );

        new Chart(
            document.getElementById('chart-period-<?= $plan ? $plan->id : 'general' ?>').getContext('2d'),
            {
                type: 'bar',
                data: {
                    labels: <?= Json::encode($periodLabels) ?>,
                    datasets: [
                        {
                            label: 'План',
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1,
                            data: <?= Json::encode($periodPlanData) ?>,
                            order: 2
                        },
                        {
                            label: 'Факт',
                            backgroundColor: 'rgba(59, 125, 221, 0.7)',
                            borderColor: 'rgba(59, 125, 221, 1)',
                            borderWidth: 1,
                            data: <?= Json::encode($periodActualData) ?>,
                            order: 1
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            type: <?= Json::encode($chartType) ?>,
                            suggestedMax: Math.max(
                                ...<?= Json::encode($periodPlanData) ?>,
                                ...<?= Json::encode($periodActualData) ?>
                            ) * 1.10,
                            ticks: {
                                callback: function(value) {
                                    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                                }
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            }
        );
    });
</script>