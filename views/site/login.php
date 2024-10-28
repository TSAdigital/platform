<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use kartik\form\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Вход';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="d-table-cell align-middle">
    <div class="text-center mt-4">
        <h1 class="h2"><?= Yii::$app->params['siteName']; ?><sup><small><?= Yii::$app->params['siteDescription']; ?></small></sup></h1>
        <p class="lead">
            Войдите в свою учетную запись, чтобы продолжить
        </p>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="m-sm-3 login-form">

                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <div class="mb-3">

                    <?= $form->field($model, 'username')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Введите имя пользователя',
                        'autofocus' => true
                    ])->label($model->getAttributeLabel('username'), ['class' => 'form-label']) ?>

                </div>

                <div class="mb-3">

                    <?= $form->field($model, 'password')->passwordInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Введите пароль',
                    ])->label($model->getAttributeLabel('password'), ['class' => 'form-label']) ?>

                </div>

                <div>
                    <div class="form-check align-items-center">

                        <?= $form->field($model, 'rememberMe')->checkbox([
                            'id' => 'customControlInline',
                            'class' => 'form-check-input'
                        ])->label('Запомнить меня', ['class' => 'form-check-label text-small']) ?>

                    </div>
                </div>

                <div class="d-grid gap-2 mt-3">

                    <?= Html::submitButton('Войти', ['class' => 'btn btn-lg btn-primary', 'name' => 'login-button']) ?>

                </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>
