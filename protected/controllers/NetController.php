<?php

class NetController extends Controller
{
  /**
   * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
   * using two-column layout. See 'protected/views/layouts/column2.php'.
   */
  public $layout='//layouts/column1';

  /**
   * @return array action filters
   */
  public function filters()
  {
    return array(
      'accessControl', // perform access control for CRUD operations
      'postOnly + delete', // we only allow deletion via POST request
    );
  }

  /**
   * Specifies the access control rules.
   * This method is used by the 'accessControl' filter.
   * @return array access control rules
   */
  public function accessRules()
  {
    return [
      [
        'allow',
        'actions'=>['index','view'],
        'users'=>['@'],
        'roles'=>['netViewUser'],
      ],
      [
        'allow',
        'actions'=>['create','update', 'changePoints', 'individualUpdate'],
        'users'=>['@'],
        'roles'=>['netEditUser'],
      ],
      [
        'allow',
        'actions'=>['delete'],
        'users'=>['@'],
        'roles'=>['netUser'],
      ],
      [
        'deny',
        'users'=>['*'],
      ],
    ];
  }

  /**
   * Displays a particular model.
   * @param integer $id the ID of the model to be displayed
   */
  public function actionView($id)
  {
    $this->render('view',array(
      'model'=>$this->loadModel($id),
    ));
  }

  /**
   * Creates a new model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   */
  public function actionCreate()
  {
    $model=new Net;

    $this->performAjaxValidation($model);

    if(isset($_POST['Net']))
    {

        $attributes = array_merge($_POST['Net'], ['id_user' => Yii::app()->user->id]);
        $model->attributes = $attributes;
        if ($model->save()) {
            $attachedPoints = [];
            if(isset($attributes['attachedPoints'])
              && is_array($attributes['attachedPoints'])
            ) {
               $attachedPoints = $attributes['attachedPoints'];
            }

            $pointToNet = new PointToNet;
            $pointToNet->saveArray($model->id, $attachedPoints);

            $this->redirect(['update', 'id' => $model->id]);
        }
    }

    $availablePoints = [];
    if (Yii::app()->user->role === User::ROLE_ADMIN) {
        $availablePoints = Point::Model()->findAll();
    } else {
        $availablePoints = Point::Model()->findAll('id_user=:u',
            [':u'=> Yii::app()->user->id]
        );
    }

    $model->availablePoints = CHtml::listData($availablePoints, 'id', 'name');

    $this->render('create', [
        'model' => $model
    ]);
  }

  /**
   * Updates a particular model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id the ID of the model to be updated
   */
  public function actionUpdate($id)
  {
    $model=$this->loadModel($id);

    if (isset($_POST['Net'])) {
        $attributes = $_POST['Net'];
        $model->attributes = $attributes;

        if ($model->save()) {
            if (isset($_POST['NetApplications'])) {
                $pointApplications = $_POST['NetApplications'];
                foreach ($model->points as $point) {
                    Yii::app()->pointService->updateRelations([
                        'id' => intval($point->id),
                        'tvScheduleFrom' => isset($pointApplications["tvScheduleFrom"]) ? $pointApplications["tvScheduleFrom"] : [],
                        'tvScheduleTo' => isset($pointApplications["tvScheduleTo"]) ? $pointApplications["tvScheduleTo"] : [],
                        'showcases' => isset($pointApplications["showcases"]) ? $pointApplications["showcases"] : [],
                        'channels' => isset($pointApplications["channels"]) ? $pointApplications["channels"] : [],
                        'ip' => $point->ip
                    ]);
                }

            }

            $this->redirect(['individualUpdate', 'id'=>$model->id]);
        }
    }

    $playlists = [];
    $allPlaylists = Playlists::getUserPlaylists();
    foreach ($allPlaylists as $playlist) {
        $playlists[$playlist['type']][] = $playlist;
    }

    $widgets = Widget::model()->findAll();

    $screens = Screen::model()->findAllByAttributes(
      ['user_id' => Yii::app()->user->id]
    );

    $this->render('update', [
        'model' => $model,
        'playlists' => $playlists,
        'screens' => $screens,
        'widgets' => $widgets
    ]);
  }

  public function actionChangePoints($id)
  {
    $model=$this->loadModel($id);

    if (isset($_POST['Net'])) {
        $attributes = $_POST['Net'];
        $model->attributes = $attributes;

        if ($model->save()) {
            $attachedPoints = [];

            if(isset($_POST['Net']['attachedPoints'])
              && is_array($_POST['Net']['attachedPoints'])
            ) {
               $attachedPoints = $_POST['Net']['attachedPoints'];
            }

            $pointToNet = new PointToNet;
            $pointToNet->saveArray($model->id, $attachedPoints);
        }

        $this->redirect(['update', 'id'=>$model->id]);
    }

    $availablePoints = [];
    if (Yii::app()->user->role === User::ROLE_ADMIN) {
        $availablePoints = Point::Model()->findAll();
    } else {
        $availablePoints = Point::Model()->findAll('id_user=:u',
            [':u'=> Yii::app()->user->id]
        );
    }

    $model->availablePoints = CHtml::listData($availablePoints, 'id', 'name');

    $attachedPoints = [];
    foreach ($model->pointsToNet as $pointToNet) {
        $attachedPoints[strval($pointToNet->id_point)] = ['selected' => 'selected'];
    }

    $this->render('changePoints', [
        'model' => $model,
        'attachedPoints' => $attachedPoints
    ]);
  }

  public function actionIndividualUpdate($id)
  {
    $model=$this->loadModel($id);

    if (isset($_POST['Net'])) {
        $attributes = $_POST['Net'];
        $model->attributes = $attributes;

        if ($model->save()) {
            if (isset($_POST['NetApplications'])
                && isset($_POST['NetApplications']['Points'])
            ) {
                $applications = $_POST['NetApplications']['Points'];
                foreach ($applications as $pointId => $pointApplications) {
                    $point = Point::model()->findByPk($pointId);
                    Yii::app()->pointService->updateRelations([
                        'id' => intval($point->id),
                        'tvScheduleFrom' => isset($pointApplications["tvScheduleFrom"]) ? $pointApplications["tvScheduleFrom"] : [],
                        'tvScheduleTo' => isset($pointApplications["tvScheduleTo"]) ? $pointApplications["tvScheduleTo"] : [],
                        'showcases' => isset($pointApplications["showcases"]) ? $pointApplications["showcases"] : [],
                        'channels' => isset($pointApplications["channels"]) ? $pointApplications["channels"] : [],
                        'ip' => $point->ip
                    ]);
                }

            }

            $this->redirect(['view', 'id'=>$model->id]);
        }
    }

    $playlists = [];
    $allPlaylists = Playlists::getUserPlaylists();
    foreach ($allPlaylists as $playlist) {
        $playlists[$playlist['type']][] = $playlist;
    }

    $widgets = Widget::model()->findAll();

    $screens = Screen::model()->findAllByAttributes(
      ['user_id' => Yii::app()->user->id]
    );

    $this->render('individualUpdate', [
        'model' => $model,
        'playlists' => $playlists,
        'screens' => $screens,
        'widgets' => $widgets,
        'points' => $model->points
    ]);
  }

  /**
   * Deletes a particular model.
   * If deletion is successful, the browser will be redirected to the 'admin' page.
   * @param integer $id the ID of the model to be deleted
   */
  public function actionDelete($id)
  {
    $this->loadModel($id)->delete();

    // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
    if(!isset($_GET['ajax']))
      $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
  }

  /**
   * Lists all models.
   */
  public function actionIndex()
  {
    $model=new Net('search');
    $model->unsetAttributes();  // clear any default values
    if(isset($_GET['Net']))
        $model->attributes = $_GET['Net'];


    $this->render('index',array(
        'model'=>$model,
    ));
  }

  /**
   * Returns the data model based on the primary key given in the GET variable.
   * If the data model is not found, an HTTP exception will be raised.
   * @param integer $id the ID of the model to be loaded
   * @return Net the loaded model
   * @throws CHttpException
   */
  public function loadModel($id)
  {
    $model=Net::model()->findByPk($id);
    if($model===null)
      throw new CHttpException(404,'The requested page does not exist.');
    return $model;
  }

  /**
   * Performs the AJAX validation.
   * @param Net $model the model to be validated
   */
  protected function performAjaxValidation($model)
  {
    if(isset($_POST['ajax']) && $_POST['ajax']==='net-form')
    {
      echo CActiveForm::validate($model);
      Yii::app()->end();
    }
  }

  public function beforeAction($action) {
    if( parent::beforeAction($action) ) {
      /* @var $cs CClientScript */
      $cs = Yii::app()->clientScript;


      $cs->registerScriptFile( Yii::app()->getBaseUrl() . '/js/lib/jquery-ui-1.10.4.min.js' );
      $cs->registerScriptFile( Yii::app()->getBaseUrl() . '/js/lib/jquery.datetimepicker.js' );
      $cs->registerScriptFile( Yii::app()->getBaseUrl() . '/js/bootstrap/bootstrap.min.js' );

      $cs->registerScriptFile( Yii::app()->getBaseUrl() . '/js/menuDecorator.js' );

      $cs->registerScriptFile( Yii::app()->getBaseUrl() . '/js/pages/net/network.js' );

      $cs->registerCssFile(Yii::app()->baseUrl.'/css/bootstrap.css.map');
      $cs->registerCssFile(Yii::app()->baseUrl.'/css/custom-theme/jquery-ui-1.10.4.custom.css');
      $cs->registerCssFile(Yii::app()->baseUrl.'/css/jquery.datetimepicker.css');
      $cs->registerCssFile(Yii::app()->baseUrl.'/css/bootstrap/bootstrap.min.css');

      $cs->registerCssFile(Yii::app()->baseUrl.'/css/pages/net.css');

      return true;
    }
    return false;
  }
}
