<?php

class AdminLinkController extends Controller {

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/column2';

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // admin only
                'actions' => array('admin', 'delete', 'index', 'view', 'create', 'update'),
                'roles' => array('admin'),
            ),
            array('allow',
                'actions' => array('create1', 'delete1'),
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new Link;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Link'])) {
            $model->attributes = $_POST['Link'];
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    public function storeLink(&$model, &$id) {


        if (isset($_SESSION['dataset_id'])) {
            $dataset_id = $_SESSION['dataset_id'];


            $model->dataset_id = $dataset_id;
            if (!$model->save()) {
                $model->addError('keyword', 'Error: Link is not stored!');
                return false;
            }
        
            $id = $model->id;
            return true;
        }

        return false;
    }

    public function actionCreate1() {
        $model = new Link;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);


        $model->dataset_id = 1;


        $link_database = array();
        //retrieve the database
        if (!isset($_SESSION['link_database'])) {
            $models = Link::model()->findAll();
            $database = array();
            foreach ($models as $m) {
                $value = $m->getDatabase();
                if (!in_array($value, $database))
                    array_push($database, $value);
            }
            sort($database);
            $_SESSION['link_database'] = $database;
        }
        $link_database = $_SESSION['link_database'];

        //update 
        if (!isset($_SESSION['links']))
            $_SESSION['links'] = array();

        $links = $_SESSION['links'];

        if (isset($_POST['Link'])) {

            $link = $link_database[$_POST['Link']['database']] . ":" . $_POST['Link']['acc_num'];

            $is_primary = 1;
            
            $model->attributes = $_POST['Link'];
            $model->link = $link;
            $model->is_primary = $is_primary;
            $id = 0;
            if ($this->storeLink($model, $id)) {
                $link_type = "ext_acc_mirror";
                if ($is_primary == 0)
                    $link_type = "ext_acc_link";

                $newItem = array('id' => $id, 'link' => $link, 'link_type' => $link_type);


                array_push($links, $newItem);

                $_SESSION['links'] = $links;
                    $vars = array('links');
                    Dataset::storeSession($vars);
                $model = new Link;
            }
        }

        $link_model = new CArrayDataProvider($links);

        $this->render('create1', array(
            'model' => $model,
            'link_model' => $link_model,
            'link_database' => $link_database
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Link'])) {
            $model->attributes = $_POST['Link'];
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        if (Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
        else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
    }

    public function actionDelete1($id) {
        if (isset($_SESSION['links'])) {
            $info = $_SESSION['links'];
            foreach ($info as $key => $value) {
                if ($value['id'] == $id) {
                    unset($info[$key]);
                    $_SESSION['links'] = $info;
                    $vars = array('links');
                    Dataset::storeSession($vars);
                    $condition = 'id='.$id;
                    Link::model()->deleteAll($condition);
                    
                    $this->redirect("/adminLink/create1");
                }
            }
        }
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('Link');
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new Link('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['Link']))
            $model->attributes = $_GET['Link'];

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id) {
        $model = Link::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'link-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}
