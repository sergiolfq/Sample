<?php/*
* @description this controller handles functionalities of the recipes in the system CRUD, only logged in user can have access to this controller.
* @author Sergio Fuenmayor
**/

class RecipeController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

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
		return array(
		/*	array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('@'),
			),*/
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('ver','create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
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


	public function actionVer($id)
	{
            /*   $pdf = new Pdf();
               $pdf->mostrarConstancia();
               $pdf->output();
               yii::app()->end();*/

	$this->render('ver',array(
			'model'=>$this->loadModel($id),
		));
	}


	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Recipe;
             
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);
                if(isset(yii::app()->user->ultimaHistoria))                          // check if a medical record has been visited before
		{
		if(isset($_POST['Recipe']))
		{
			$model->attributes=$_POST['Recipe'];
                        $model->id_his=yii::app()->user->ultimaHistoria;
                        $model->id_u=yii::app()->user->id_u;
                        $model->fecha_rec=date('Y-m-d');
						$model->tratamiento=utf8_encode($model->tratamiento);  threatment
						$model->indicaciones=utf8_encode($model->indicaciones); orders
			
			if($model->save()){
                                
				$bit= new Bitacora;					// adds to the log tables 
               	$bit->id_his=yii::app()->user->ultimaHistoria;
				$bit->id_u=yii::app()->user->id_u;
				$bit->descripcion_bit="se creo recipe ";
           		$bit->fecha_bit=date('Y-m-d');
           		$bit->save();
			
                Yii::app()->user->setFlash('success', "Recipe creado con exito ");
				$this->redirect(array('update','id'=>$model->id_rec));
							}
		}

		$this->render('create',array(
			'model'=>$model,
		));
                } 
                 else 
                 throw new CHttpException(403,'Por favor consulte una historia.');               
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);



		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Recipe']))
		{
			$model->attributes=$_POST['Recipe'];
			if($model->save()){

				$bit= new Bitacora;					// se agrega a la bitacora 
                		$bit->id_his=yii::app()->user->ultimaHistoria;
				$bit->id_u=yii::app()->user->id_u;
				$bit->descripcion_bit="se actualizo recipe ";
                		$bit->fecha_bit=date('Y-m-d');
                		$bit->save();



                             Yii::app()->user->setFlash('success', "Cambios en recipe guardados con Exito ");
			     $this->redirect(array('update','id'=>$model->id_rec));
                                          }
                                 else{
                                 Yii::app()->user->setFlash('error', "Error al guardar recipe ");
                                     }      
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

			$bit= new Bitacora;					// adds to the log table 
            $bit->id_his=yii::app()->user->ultimaHistoria;  // last visited record
			$bit->id_u=yii::app()->user->id_u;
			$bit->descripcion_bit="se elimino recipe ";
			$bit->fecha_bit=date('Y-m-d');
            $bit->save();
		

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Recipe');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
               if(yii::app()->user->ultimaHistoria){
				$model=new Recipe('search');
				$model->unsetAttributes();  // clear any default values
				if(isset($_GET['Recipe']))
				$model->attributes=$_GET['Recipe'];

				$this->render('admin',array(
				'model'=>$model,
				));
                } 
                else 
                      throw new CHttpException(403,'Por favor consulte una historia.');               
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Recipe the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Recipe::model()->findByPk($id);
                if(isset(yii::app()->user->ultimaHistoria)){           // si ha consultado una historia 
		if($model===null)                                      // si el recipe no existe 
			throw new CHttpException(404,'The requested page does not exist.');  
                 else if($model->id_his!=yii::app()->user->ultimaHistoria)                        // si este recipe no corresponde a la historia consultada 
                        throw new CHttpException(403,'Por favor consulte una historia.');  
                 }
                 else 
                    throw new CHttpException(403,'Por favor consulte una historia.');  
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Recipe $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='recipe-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
