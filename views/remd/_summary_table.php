<?php
use yii\helpers\Html;

/** @var array $data */
/** @var array $typedData */

$months = [
    1 => ['name' => 'Январь', 'field' => 'jan'],
    2 => ['name' => 'Февраль', 'field' => 'feb'],
    3 => ['name' => 'Март', 'field' => 'mar'],
    4 => ['name' => 'Апрель', 'field' => 'apr'],
    5 => ['name' => 'Май', 'field' => 'may'],
    6 => ['name' => 'Июнь', 'field' => 'jun'],
    7 => ['name' => 'Июль', 'field' => 'jul'],
    8 => ['name' => 'Август', 'field' => 'aug'],
    9 => ['name' => 'Сентябрь', 'field' => 'sep'],
    10 => ['name' => 'Октябрь', 'field' => 'oct'],
    11 => ['name' => 'Ноябрь', 'field' => 'nov'],
    12 => ['name' => 'Декабрь', 'field' => 'dec']
];

$quarters = [
    '1 квартал' => ['field' => 'q1', 'months' => [1, 2, 3]],
    '2 квартал' => ['field' => 'q2', 'months' => [4, 5, 6]],
    '3 квартал' => ['field' => 'q3', 'months' => [7, 8, 9]],
    '4 квартал' => ['field' => 'q4', 'months' => [10, 11, 12]]
];

function formatNumber($value) {
    return $value ? number_format($value, 0, '', ' ') : '-';
}

function calculatePercentWithColor($plan, $actual) {
    if ($plan == 0) {
        return [
            'text' => '-',
            'class' => ''
        ];
    }

    $percent = round(($actual / $plan) * 100);
    $class = ($percent >= 100) ? 'text-success' : 'text-danger';

    return [
        'text' => $percent . '%',
        'class' => $class
    ];
}
?>

<div class="table-responsive">
    <table class="table table-bordered table-hover table-striped mb-0">
        <thead>
        <tr>
            <th rowspan="2" class="align-middle">Период</th>
            <th colspan="3" class="text-center align-middle">Все документы</th>

            <?php foreach ($typedData as $item): ?>

                <th colspan="3" class="text-center align-middle"><?= Html::encode($item['type']) ?></th>

            <?php endforeach; ?>

        </tr>
        <tr>
            <th class="text-center">План</th>
            <th class="text-center">Факт</th>
            <th class="text-center">%</th>

            <?php foreach ($typedData as $item): ?>

                <th class="text-center">План</th>
                <th class="text-center">Факт</th>
                <th class="text-center">%</th>

            <?php endforeach; ?>

        </tr>
        </thead>
        <tbody>

        <?php foreach ($months as $monthNum => $monthInfo): ?>

            <?php
            $generalPlan = $plan->{$monthInfo['field']} ?? 0;
            $generalActual = $actual[$monthNum] ?? 0;
            $generalPercent = calculatePercentWithColor($generalPlan, $generalActual);

            if ($data['hideEmptyMonths'] && $generalActual == 0) continue;
            ?>

            <tr>
                <td><?= $monthInfo['name'] ?></td>
                <td class="text-center"><?= formatNumber($generalPlan) ?></td>
                <td class="text-center"><?= formatNumber($generalActual) ?></td>
                <td class="text-center <?= $generalPercent['class'] ?>"><strong><?= $generalPercent['text'] ?></strong></td>

                <?php foreach ($typedData as $item): ?>

                    <?php
                    $typePlan = $item['plan']->{$monthInfo['field']} ?? 0;
                    $typeActual = $item['actual'][$monthNum] ?? 0;
                    $typePercent = calculatePercentWithColor($typePlan, $typeActual);
                    ?>

                    <td class="text-center"><?= formatNumber($typePlan) ?></td>
                    <td class="text-center"><?= formatNumber($typeActual) ?></td>
                    <td class="text-center <?= $typePercent['class'] ?>"><strong><?= $typePercent['text'] ?></strong></td>

                <?php endforeach; ?>

            </tr>

        <?php endforeach; ?>

        <?php
        foreach ($quarters as $qName => $qData):
            $generalQPlan = $plan->{$qData['field']} ?? 0;
            $generalQActual = 0;

            $typedQPlans = [];
            $typedQActuals = [];
            $typedQPercents = [];

            foreach ($qData['months'] as $monthNum) {
                $generalQActual += $actual[$monthNum] ?? 0;
            }

            $generalQPercent = calculatePercentWithColor($generalQPlan, $generalQActual);

            if ($data['hideEmptyMonths'] && $generalQActual == 0) {
                continue;
            }

            foreach ($typedData as $item) {
                $typeQPlan = $item['plan']->{$qData['field']} ?? 0;
                $typeQActual = 0;

                foreach ($qData['months'] as $monthNum) {
                    $typeQActual += $item['actual'][$monthNum] ?? 0;
                }

                $typedQPlans[] = $typeQPlan;
                $typedQActuals[] = $typeQActual;
                $typedQPercents[] = calculatePercentWithColor($typeQPlan, $typeQActual);
            }
            ?>

            <tr class="info">
                <td class="text-nowrap"><strong><?= $qName ?></strong></td>
                <td class="text-center"><strong><?= formatNumber($generalQPlan) ?></strong></td>
                <td class="text-center"><strong><?= formatNumber($generalQActual) ?></strong></td>
                <td class="text-center <?= $generalQPercent['class'] ?>"><strong><?= $generalQPercent['text'] ?></strong></td>

                <?php foreach ($typedQPlans as $i => $typeQPlan): ?>

                    <td class="text-center"><strong><?= formatNumber($typeQPlan) ?></strong></td>
                    <td class="text-center"><strong><?= formatNumber($typedQActuals[$i]) ?></strong></td>
                    <td class="text-center <?= $typedQPercents[$i]['class'] ?>"><strong><?= $typedQPercents[$i]['text'] ?></strong></td>

                <?php endforeach; ?>

            </tr>

        <?php endforeach; ?>

        <?php
        $yearPlan = $plan->year_plan ?? 0;
        $yearActual = array_sum($actual);
        $yearPercent = calculatePercentWithColor($yearPlan, $yearActual);

        $typedYearPercents = [];
        foreach ($typedData as $item) {
            $typeYearPlan = $item['plan']->year_plan ?? 0;
            $typeYearActual = array_sum($item['actual']);
            $typedYearPercents[] = calculatePercentWithColor($typeYearPlan, $typeYearActual);
        }
        ?>

        <tr class="active">
            <td><strong>Год</strong></td>
            <td class="text-center text-nowrap"><strong><?= formatNumber($yearPlan) ?></strong></td>
            <td class="text-center text-nowrap"><strong><?= formatNumber($yearActual) ?></strong></td>
            <td class="text-center text-nowrap <?= $yearPercent['class'] ?>"><strong><?= $yearPercent['text'] ?></strong></td>

            <?php foreach ($typedData as $i => $item): ?>

                <td class="text-center text-nowrap"><strong><?= formatNumber($item['plan']->year_plan ?? 0) ?></strong></td>
                <td class="text-center text-nowrap"><strong><?= formatNumber(array_sum($item['actual'])) ?></strong></td>
                <td class="text-center text-nowrap <?= $typedYearPercents[$i]['class'] ?>"><strong><?= $typedYearPercents[$i]['text'] ?></strong></td>

            <?php endforeach; ?>
        </tr>
        </tbody>
    </table>
</div>