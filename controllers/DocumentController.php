<?php

namespace app\controllers;

use app\components\FileDeleteToS3Job;
use app\components\SendMessageJob;
use app\models\Document;
use app\models\DocumentAccess;
use app\models\DocumentEvent;
use app\models\DocumentFile;
use app\models\DocumentRead;
use app\models\DocumentSearch;
use app\models\User;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * DocumentController implements the CRUD actions for Document model.
 */
class DocumentController extends Controller
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
                        'publish' => ['POST'],
                        'cancel' => ['POST'],
                        'file-delete' => ['POST'],
                        'add-access' => ['POST'],
                        'cancel-access' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index'],
                            'roles' => ['viewDocumentList'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['view'],
                            'roles' => ['viewDocument'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['create'],
                            'roles' => ['createDocument'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['update'],
                            'roles' => ['updateDocument'],
                            'roleParams' => function() {
                                return ['document' => $this->findModel(Yii::$app->request->get('id'))];
                            },
                        ],
                        [
                            'allow' => true,
                            'actions' => ['publish'],
                            'roles' => ['publishDocument'],
                            'roleParams' => function() {
                                return ['document' => $this->findModel(Yii::$app->request->get('id'))];
                            },
                        ],
                        [
                            'allow' => true,
                            'actions' => ['cancel'],
                            'roles' => ['cancelDocument'],
                            'roleParams' => function() {
                                return ['document' => $this->findModel(Yii::$app->request->get('id'))];
                            },
                        ],

                        [
                            'allow' => true,
                            'actions' => ['file-delete'],
                            'roles' => ['fileDeleteDocument'],
                            'roleParams' => function() {
                                return ['file' => $this->findFile(Yii::$app->request->get('id'))];
                            },
                        ],
                        [
                            'allow' => true,
                            'actions' => ['upload'],
                            'roles' => ['fileUploadDocument'],
                            'roleParams' => function() {
                                return ['document' => $this->findModel(Yii::$app->request->get('id'))];
                            },
                        ],
                        [
                            'allow' => true,
                            'actions' => ['download'],
                            'roles' => ['fileDownloadDocument'],
                        ],
                        [
                            'allow' => true,
                            'actions' => ['user-list'],
                            'roles' => ['accessDocumentAdd'],
                            'roleParams' => function() {
                                return ['document' => $this->findModel(Yii::$app->request->get('id'))];
                            },
                        ],
                        [
                            'allow' => true,
                            'actions' => ['add-access'],
                            'roles' => ['accessDocumentAdd'],
                            'roleParams' => function() {
                                return ['document' => $this->findModel(Yii::$app->request->get('id'))];
                            },
                        ],
                        [
                            'allow' => true,
                            'actions' => ['cancel-access'],
                            'roles' => ['accessDocumentCancel'],
                            'roleParams' => function() {
                                return ['access' => $this->findAccess(Yii::$app->request->get('id'))];
                            },
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_ACTION, function ($event) {
            $id = Yii::$app->request->get('id');

            if (in_array($event->action->id, ['view', 'update', 'publish', 'cancel', 'upload', 'add-access'])) {
                $this->checkDocumentAccess($id);
            }
        });
    }


    /**
     * Lists all Document models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $userId = Yii::$app->user->identity->id;

        if ($model->created_by !== $userId) {
            DocumentRead::markAsRead($model->id, $userId);
            DocumentEvent::createEvent($model->id, $userId, 'просмотрел(а) документ');
        }

        $fileDataProvider = new ArrayDataProvider([
            'allModels' => DocumentFile::findAll(['document_id' => $id]),
            'pagination' => [
                'pageSize' => 10,
                'pageParam' => 'page-file',
                'params' => [
                    'id' => $id,
                    'page-file' => Yii::$app->request->get('page-file', 0),
                    '#' => 'pills-file',
                ],
            ],
        ]);

        $accessDataProvider = new ArrayDataProvider([
            'allModels' => DocumentAccess::findAll(['document_id' => $id]),
            'pagination' => [
                'pageSize' => 10,
                'pageParam' => 'page-access',
                'params' => [
                    'id' => $id,
                    'page-access' => Yii::$app->request->get('page-access', 0),
                    '#' => 'pills-access',
                ],
            ],
        ]);

        $eventDataProvider = new ArrayDataProvider([
            'allModels' => DocumentEvent::findAll(['document_id' => $id]),
            'pagination' => [
                'pageSize' => 10,
                'pageParam' => 'page-event',
                'params' => [
                    'id' => $id,
                    'page-event' => Yii::$app->request->get('page-event', 0),
                    '#' => 'pills-event',
                ],
            ],
            'sort' => [
                'attributes' => [
                    'id',
                ],
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        return $this->render('view', [
            'model' => $model,
            'fileDataProvider' => $fileDataProvider,
            'accessDataProvider' => $accessDataProvider,
            'eventDataProvider' => $eventDataProvider,
        ]);
    }

    /**
     * Creates a new Document model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Document();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                 if ($model->save()) {
                     Yii::$app->notification->success('Документ добавлен');

                     return $this->redirect(['view', 'id' => $model->id]);
                 } else {
                     Yii::$app->notification->error('Не удалось добавить документ');

                     return $this->redirect(['view', 'id' => $model->id]);
                 }
            } else {
                Yii::$app->notification->error('Не удалось добавить документ');

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Document model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->save()) {
                Yii::$app->notification->success('Изменения сохранены');
            } else {
                Yii::$app->notification->success('Не удалось сохранить изменения');
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Document model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Document the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Document::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не существует.');
    }

    /**
     * Finds the DocumentFile model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return DocumentFile the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findFile($id)
    {
        if (($model = DocumentFile::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенный файл не существует.');
    }

    /**
     * Finds the DocumentAccess model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return DocumentAccess the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findAccess($id)
    {
        if (($model = DocumentAccess::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенное разрешение не существует.');
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionPublish($id) {
        $model = $this->findModel($id);

        if ($model->status === $model::STATUS_DRAFT || $model->status === $model::STATUS_INACTIVE) {
            $model->status = $model::STATUS_ACTIVE;

            if ($model->save()) {
                if (Yii::$app->params['telegram']) {
                    $telegramChatIds = User::find()
                        ->select('telegram_chat_id')
                        ->innerJoin('document_access', 'document_access.user_id = user.id')
                        ->where(['document_access.document_id' => $model->id])
                        ->column();

                    if ($telegramChatIds) {
                        foreach ($telegramChatIds as $telegramChatId) {
                            if ($telegramChatId) {
                                Yii::$app->queue->push(new SendMessageJob([
                                    'chatId' => $telegramChatId,
                                    'messageText' => 'Новый документ доступен для просмотра - ' . $model->name,
                                ]));
                            }
                        }
                    }
                }

                Yii::$app->notification->success('Документ опубликован');

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->notification->success('Не удалось опубликовать документ');
            }
        } else {
            Yii::$app->notification->success('Не удалось опубликовать документ');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * @param $id
     * @return Response
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        if ($model->status === $model::STATUS_ACTIVE || $model->status === $model::STATUS_DRAFT) {
            $model->status = $model::STATUS_INACTIVE;

            if ($model->save()) {
                Yii::$app->notification->success('Документ отменен');
            } else {
                Yii::$app->notification->error('Не удалось отменить документ');
            }
        } else {
            Yii::$app->notification->error('Не удалось отменить документ');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionUpload($id)
    {
        $document = $this->findModel($id);
        $model = new DocumentFile();
        $model->document_id = $document->id;

        $uploadedCount = 0;
        $errorOccurred = false;

        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstances($model, 'file');

            if ($model->file) {
                $currentYearMonth = date('Y-m');
                $uploadPath = Yii::getAlias('@webroot/uploads/' . $currentYearMonth);

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                foreach ($model->file as $file) {
                    $fileName = $this->generateUniqueFileName($file->name);
                    $documentFile = new DocumentFile();
                    $documentFile->document_id = $document->id;
                    $documentFile->name = $file->name;
                    $documentFile->stored_name = $currentYearMonth . '/' . $fileName;
                    $documentFile->size = $file->size;
                    $documentFile->type = $file->type;
                    $documentFile->local_storage = 1;

                    if ($documentFile->validate() && $documentFile->save()) {
                        $file->saveAs($uploadPath . '/' . $fileName);
                        $uploadedCount++;
                    } else {
                        $errorOccurred = true;
                        Yii::$app->notification->error('Ошибка при загрузке файла: ' . $file->name);
                    }
                }

                if ($errorOccurred) {
                    Yii::$app->notification->error('Ошибка. Загружено файлов: ' . $uploadedCount);
                } else {
                    Yii::$app->notification->success('Успешно. Загружено файлов: ' . $uploadedCount);
                }

                return $this->redirect(['view', 'id' => $document->id, '#' => 'pills-file']);
            } else {
                Yii::$app->notification->error('Ошибка при загрузке файла');
            }
        }

        return $this->render('upload', [
            'model' => $model,
            'document' => $document,
        ]);
    }

    /**
     * @param $originalName
     * @return string
     */
    function generateUniqueFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $randomString = bin2hex(random_bytes(10));

        return uniqid('file_') . '_' . $randomString . '.' . $extension;
    }

    /**
     * @param $id
     * @return \yii\console\Response|Response
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionDownload($id)
    {
        $model = $this->findFile($id);

        $document = $this->findModel($model->document_id);

        $this->checkDocumentAccess($document->id);

        $userId = Yii::$app->user->identity->id;

        if ($model && Yii::$app->params['s3']) {
            $s3 = Yii::$app->s3;

            if ($s3->fileExists($model->stored_name)) {
                $fileUrl = $s3->getTemporaryUrl($model->stored_name, Yii::$app->params['s3_link_expiration']);
                if ($fileUrl) {
                    if ($document->created_by !== $userId) {
                        DocumentEvent::createEvent($document->id, $userId, 'скачал(а) файл - ' . $model->name);
                    }

                    return $this->redirect($fileUrl);
                }
            }
        }

        if ($model) {
            $filePath = Yii::getAlias('@webroot/uploads/' . $model->stored_name);

            if (file_exists($filePath)) {
                if ($document->created_by !== $userId) {
                    DocumentEvent::createEvent($document->id, $userId, 'скачал(а) файл - ' . $model->name);
                }

                return Yii::$app->response->sendFile($filePath, $model->name);
            }
        }

        Yii::$app->notification->error('Ошибка при скачивании файла');

        return $this->redirect(['view', 'id' => $model->document_id, '#' => 'pills-file']);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public function actionFileDelete($id)
    {
        $model = $this->findFile($id);

        $document = $this->findModel($model->document_id);

        $this->checkDocumentAccess($document->id);

        $filePath = Yii::getAlias('@webroot/uploads/' . $model->stored_name);

        if ($model->delete()) {
            Yii::$app->notification->success('Файл удален');
        } else {
            Yii::$app->notification->error('Не удалось удалить файл');
        }

        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }

        if (Yii::$app->params['s3']) {
            Yii::$app->queue->push(new FileDeleteToS3Job([
                's3Key' => $model->stored_name,
            ]));
        }

        return $this->redirect(['view', 'id' => $document->id, '#' => 'pills-file']);
    }

    /**
     * @param $id
     * @return true
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    protected function checkDocumentAccess($id)
    {
        $document = $this->findModel($id);

        if (!$document->userHasAccess()) {
            throw new ForbiddenHttpException('У вас нет доступа к этому документу.');
        }
        return true;
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function actionAddAccess()
    {
        $model = new DocumentAccess();

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->save()) {
                Yii::$app->notification->success('Доступ предоставлен');
            } else {
                Yii::$app->notification->success('Не удалось предоставить доступ');
            }
        } else {
            Yii::$app->notification->success('Не удалось предоставить доступ');
        }

        return $this->redirect(['document/view', 'id' => $model->document_id, '#' => 'pills-access']);
    }

    /**
     * @param $id
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public function actionCancelAccess($id)
    {
        $model = $this->findAccess($id);

        $document = $this->findModel($model->document_id);

        $this->checkDocumentAccess($document->id);

        if ($model->delete()) {
            Yii::$app->notification->success('Доступ отменен');
        } else {
            Yii::$app->notification->error('Не удалось отменить доступ');
        }

        return $this->redirect(['document/view', 'id' => $document->id, '#' => 'pills-access']);
    }

    /**
     * @param string|null $q
     * @param int|null $id
     * @param int|null $authorId
     * @return array[]
     */
    public function actionUserList(string $q = null, int $id = null, int $authorId = null) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => []];
        $userId = Yii::$app->user->getId();
        $query = new Query;

        $query->select(['user.id', 'CONCAT_WS(Char(32), employee.last_name, employee.first_name, employee.middle_name, CONCAT("(", position.name, ")")) AS text'])
            ->from('user')
            ->innerJoin('employee', 'user.id = employee.user_id')
            ->leftJoin('document_access', 'user.id = document_access.user_id AND document_access.document_id = :documentId', [':documentId' => $id])
            ->leftJoin('position', 'employee.position_id = position.id');

        if (is_string($q)) {
            $q = trim($q);

            if (strlen($q) > 255) {
                $q = substr($q, 0, 255);
            }

            $query->where(['like', 'CONCAT_WS(Char(32), employee.last_name, employee.first_name, employee.middle_name, position.name)', trim($q)])
                ->andWhere(['not', ['user.id' => [(int) $userId, (int) $authorId]]])
                ->andWhere(['document_access.user_id' => null])
                ->andWhere(['user.status' => User::STATUS_ACTIVE])
                ->andWhere(['employee.status' => User::STATUS_ACTIVE])
                ->limit(20);
        }

        $data = $query->orderBy(['last_name' => SORT_DESC])->all();
        $out['results'] = array_merge($out['results'], $data);

        return $out;
    }
}
