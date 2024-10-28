<?php

use kartik\file\FileInput;
use yii\bootstrap5\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\DocumentFile */
/* @var $document app\models\Document */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Добавить файлы';
$this->params['breadcrumbs'][] = ['label' => 'Документы', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $document->name, 'url' => ['view', 'id' => $document->id]];
$this->params['breadcrumbs'][] = $this->title;
$modal = <<<JS
    $(document).on('click', '.btn-kv-close', function() {
        $('#kvFileinputModal').modal('hide');
    });
JS;
$button= <<<JS
    $(document).on('beforeSubmit', 'form', function () {
        var button = $(this).find('button[type="submit"]');
        button.prop('disabled', true).text('Загрузка...');
        
        setTimeout(function() {
            button.prop('disabled', false).text('Загрузить');
        }, 10000);
        
        return true;
    });
JS;
$this->registerJs($modal, View::POS_END);
$this->registerJs($button, View::POS_END);
?>

<div class="card">
    <div class="card-body">
        <div class="document-file-form">

            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

            <?= $form->field($model, 'file[]')->widget(FileInput::classname(),
                [
                    'options' => ['multiple' => true],
                    'pluginOptions' => [
                        'showRemove' => false,
                        'showCancel' => false,
                        'showUpload' => false,
                        'maxFileCount' => $model->maxFiles,
                        'maxFileSize' => $model->fileSize * 1024,
                    ],
                ]
            )->label(false); ?>

            <div class="form-group mt-3 d-grid">

                <?= Html::submitButton('Загрузить', ['class' => 'btn btn-success']) ?>

            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>