<?php

namespace app\controllers;

use app\models\AuthItem;
use app\models\AuthItemSearch;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\rbac\Item;
use yii\rbac\Role;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * AuthItemController implements the CRUD actions for AuthItem model.
 */
class AuthItemController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'block' => ['POST'],
                        'unlock' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index'],
                            'roles' => ['viewRoleList'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['view'],
                            'roles' => ['viewRole'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['create'],
                            'roles' => ['createRole'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['update'],
                            'roles' => ['updateRole'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['block'],
                            'roles' => ['blockRole'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['unlock'],
                            'roles' => ['unlockRole'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['permission-update'],
                            'roles' => ['updateRole'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all AuthItem models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AuthItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AuthItem model.
     * @param string $name Name
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($name)
    {
        return $this->render('view', [
            'model' => $this->findModel($name),
        ]);
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AuthItem();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if ($model->setRoleName($model->description) && $model->save()) {
                    Yii::$app->notification->success('Роль добавлена');

                    return $this->redirect(['view', 'name' => $model->name]);
                } else {
                    Yii::$app->notification->error('Не удалось добавить роль');
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $name Name
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($name)
    {
        $model = $this->findModel($name);

        $this->checkAdminRole($name);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->setRoleName($model->description) && $model->save()) {
                Yii::$app->notification->success('Роль обновлена');

                return $this->redirect(['view', 'name' => $model->name]);
            } else {
                Yii::$app->notification->error('Не удалось обновить роль');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param $name
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionBlock($name)
    {
        $model = $this->findModel($name);

        $this->checkAdminRole($name);

        if ($model->status !== $model::STATUS_ACTIVE) {
            Yii::$app->notification->error('Роль уже заблокирована');

            return $this->redirect(['view', 'name' => $model->name]);
        }

        $model->status = $model::STATUS_INACTIVE;

        if ($model->save()) {
            Yii::$app->notification->success('Роль заблокирована');
        } else {
            Yii::$app->notification->error('Не удалось заблокировать роль');
        }

        return $this->redirect(['view', 'name' => $model->name]);
    }

    /**
     * @param $name
     * @return Response
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionUnlock($name)
    {
        $model = $this->findModel($name);

        $this->checkAdminRole($name);

        if ($model->status !== $model::STATUS_INACTIVE) {
            Yii::$app->notification->error('Роль уже разблокирована');

            return $this->redirect(['view', 'name' => $model->name]);
        }

        $model->status = $model::STATUS_ACTIVE;

        if ($model->save()) {
            Yii::$app->notification->success('Роль разблокирована');
        } else {
            Yii::$app->notification->error('Не удалось разблокировать роль');
        }

        return $this->redirect(['view', 'name' => $model->name]);
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $name Name
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name)
    {
        if (($model = AuthItem::findOne(['name' => $name])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * @param $name
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionPermissionUpdate($name)
    {
        $auth = Yii::$app->authManager;
        $role = $this->getRole($name);

        $permissionCategories = $this->getPermissionCategories();
        $assignedPermissions = $this->getAssignedPermissions($role);

        if (Yii::$app->request->isPost) {
            Yii::$app->notification->success('Настройки доступа обновлены');

            return $this->handlePostRequest($auth, $role);
        }

        $permissionData = $this->preparePermissionData($permissionCategories, $auth->getPermissions(), $assignedPermissions);

        return $this->render('permission-update', [
            'role' => $role,
            'permissions' => $auth->getPermissions(),
            'assignedPermissions' => $assignedPermissions,
            'permissionCategories' => $permissionCategories,
            'permissionData' => $permissionData,
        ]);
    }

    /**
     * @param $name
     * @return Item|Role
     * @throws NotFoundHttpException
     */
    private function getRole($name)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);

        if ($role === null) {
            throw new NotFoundHttpException('Роль не найдена.');
        }

        $this->checkAdminRole($name);

        return $role;
    }

    /**
     * @return array[]
     */
    private function getPermissionCategories()
    {
        return [
            'Документы' => [
                'viewDocumentMenu',
                'viewDocumentList',
                'viewDocument',
                'createDocument',
                'updateOwnDocument',
                'updateDocument',
                'publishOwnDocument',
                'publishDocument',
                'cancelOwnDocument',
                'cancelDocument'
            ],
            'Доступ к документам' => [
                'accessOwnDocumentList',
                'accessDocumentList',
                'accessOwnDocumentAdd',
                'accessDocumentAdd',
                'accessOwnDocumentCancel',
                'accessDocumentCancel'
            ],
            'Файлы документа' => [
                'fileDownloadDocument',
                'fileUploadOwnDocument',
                'fileUploadDocument',
                'fileDeleteOwnDocument',
                'fileDeleteDocument'
            ],
            'Пользователи' => [
                'viewUserMenu',
                'viewUserList',
                'viewUser',
                'createUser',
                'updateUser',
                'changePasswordUser',
                'blockUser',
                'unlockUser'
            ],
            'Роли' => [
                'viewRoleMenu',
                'viewRoleList',
                'viewRole',
                'createRole',
                'updateRole',
                'permissionUpdateRole',
                'blockRole',
                'unlockRole'
            ],
            'Должности' => [
                'viewPositionMenu',
                'viewPositionList',
                'viewPosition',
                'createPosition',
                'updatePosition'
            ],
            'Сотрудники' => [
                'viewEmployeeMenu',
                'viewEmployeeList',
                'viewEmployee',
                'createEmployee',
                'updateEmployee'
            ],
            'События документа' => [
                'eventOwnDocumentView',
                'eventDocumentView'
            ],
            'Сертификаты' => [
                'viewCertificateMenu',
                'viewCertificateList',
                'viewCertificate',
                'createCertificate',
                'updateCertificate'
            ],
            'Удостоверяющие центры' => [
                'viewIssuerMenu',
                'viewIssuerList',
                'viewIssuer',
                'createIssuer',
                'updateIssuer'
            ],
        ];
    }

    /**
     * @param $role
     * @return array
     */
    private function getAssignedPermissions($role)
    {
        $auth = Yii::$app->authManager;

        return ArrayHelper::getColumn($auth->getChildren($role->name), 'name');
    }

    /**
     * @param $auth
     * @param $role
     * @return Response
     */
    private function handlePostRequest($auth, $role)
    {
        $postedPermissions = Yii::$app->request->post('permissions', []);
        $auth->removeChildren($role);

        foreach ($postedPermissions as $permissionName) {
            $permission = $auth->getPermission($permissionName);

            if ($permission) {
                $auth->addChild($role, $permission);
            }
        }

        return $this->redirect(['view', 'name' => $role->name]);
    }

    /**
     * @param $categories
     * @param $permissions
     * @param $assignedPermissions
     * @return array
     * @throws \Exception
     */
    private function preparePermissionData($categories, $permissions, $assignedPermissions)
    {
        $permissionData = [];

        foreach ($categories as $category => $permissionsInCategory) {
            foreach ($permissionsInCategory as $permissionName) {
                $permission = ArrayHelper::getValue($permissions, $permissionName);

                if ($permission) {
                    $permissionData[$category][$permissionName] = [
                        'checked' => in_array($permission->name, $assignedPermissions),
                        'description' => $permission->description ?: 'Описание отсутствует.',
                    ];
                }
            }
        }

        return $permissionData;
    }

    /**
     * @param $name
     * @return void
     * @throws NotFoundHttpException
     */
    private function checkAdminRole($name)
    {
        if ($name == 'administrator') {
            throw new NotFoundHttpException('Роль «Администратор» нельзя изменять.');
        }
    }
}
