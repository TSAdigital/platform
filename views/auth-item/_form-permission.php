<?php

/** @var object $role */
/** @var object $permissions */
/** @var array $assignedPermissions */
/** @var array $permissionCategories */
/** @var array $permissionData */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin(); ?>

<div class="row">

    <?php foreach ($permissionCategories as $category => $permissionsInCategory): ?>

        <div class="col-md-4 mb-3">
            <h3 class="my-2"><?= Html::encode($category) ?></h3>

            <?php foreach ($permissionData[$category] as $permissionName => $data): ?>

                <div class="checkbox">
                    <label>
                        <?= Html::checkbox("permissions[]", $data['checked'], ['value' => $permissionName]) ?>
                        <?= Html::encode($data['description']) ?>
                    </label>
                </div>

            <?php endforeach; ?>

        </div>

    <?php endforeach; ?>

</div>
<div class="form-group">
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
