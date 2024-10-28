<?php

/** @var object $role */
/** @var object $permissions */
/** @var object $assignedPermissions */
/** @var array $permissionCategories */
/** @var array $permissionData */

$this->title = 'Настройки доступа: ' . $role->description;
$this->params['breadcrumbs'][] = ['label' => 'Роли', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $role->description, 'url' => ['view', 'name' => $role->name]];
$this->params['breadcrumbs'][] = 'Настройки доступа';
?>

<div class="card">
    <div class="card-body">

        <?= $this->render('_form-permission', [
            'role' => $role,
            'permissions' => $permissions,
            'assignedPermissions' => $assignedPermissions,
            'permissionCategories' => $permissionCategories,
            'permissionData' => $permissionData,
        ]) ?>

    </div>
</div>
