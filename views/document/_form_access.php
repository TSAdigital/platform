<?php

use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\Document */
/* @var $documentAccess app\models\DocumentAccess */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $form = ActiveForm::begin(['id' => 'access-form', 'action' => ['document/add-access', 'id' => $model->id]]); ?>

    <?= $form->field($documentAccess, 'user_id')->widget(Select2::class, [
        'options' => ['placeholder' => 'Выберите пользователя...'],
        'pluginOptions' => [
            'dropdownParent' => '#access',
            'minimumInputLength' => 3,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Ожидаем результата...'; }"),
            ],
            'ajax' => [
                'url' => Url::to(['user-list']),
                'dataType' => 'json',
                'delay' => 250,
                'data' => new JsExpression('function(params) { return {q:params.term, id: ' . $model->id . ', authorId: ' . $model->created_by . '}; }')
            ],
        ],
    ]); ?>

    <?= $form->field($documentAccess, 'document_id')->hiddenInput(['value' => $model->id])->label(false) ?>

    <div class="form-group mt-3 d-grid">

        <?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>

    </div>

<?php ActiveForm::end(); ?>
