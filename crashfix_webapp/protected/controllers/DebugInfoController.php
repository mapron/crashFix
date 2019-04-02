<?php
Yii::import('application.vendors.ezcomponents.*');
Yii::import('application.vendors.SevenZipArchive');
require_once "Base/src/base.php";
Yii::registerAutoloader(array('ezcBase', 'autoload'), true);


class DebugInfoController extends Controller
{
	// Use two-column layout
	public $layout='//layouts/column2';
	public $sidebarActiveItem = 'DebugInfo';

	// Action constants used by actionUpload() method.
	const ACTION_CHECK = 1;  // We should only check file presence in the database.
	const ACTION_UPLOAD = 2; // We should upload file and import it into the database.

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
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
			array('allow',  // Allow not authenticated users
				'actions'=>array('uploadExternal'),
				'users'=>array('?'),
			),
			array('allow',  // Allow authenticated users
				'actions'=>array(
					'index',
					'view',
					'download',
					'uploadStat',
					'delete',
					'deleteMultiple',
				    'deleteAll',
					'uploadFile'
				),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array();
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// Check if user is authorized to perform this action
		$this->checkAuthorization(null);

		// Create new model that will contain search options.
		$model = new DebugInfo('search');

		// Fill model fields
		if(isset($_GET['q']))
		{
			$model->isAdvancedSearch = false;
			$model->filter = $_GET['q'];
		}
		else if(isset($_POST['DebugInfo']))
		{
			$model->isAdvancedSearch = true;
			$model->attributes = $_POST['DebugInfo'];
		}

		// Search
		$dataProvider=$model->search();

		// Render view
		$this->render('index', array(
			'dataProvider'=>$dataProvider,
			'model'=>$model,
		));
	}

	/**
	 * This is the 'view' action that is invoked
	 * when a user wants to display detailed information on a given debug info file.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$model = $this->loadModel($id);

		// Check if user is authorized to perform this action
		$this->checkAuthorization($model);

		$this->render('view', array(
			'model'=>$model,
		));
	}

	/**
	 * This is the 'download' action that is invoked
	 * when a user wants to download the given PDB file.
	 * @param integer $id the ID of the model to be downloaded
	 */
	public function actionDownload($id)
	{
		$model = $this->loadModel($id);

		// Check if user is authorized to perform this action
		$this->checkAuthorization($model);

		// Dump file content to stdout
		$model->dumpFileAttachmentContent();
	}

	/**
	 * This is the 'delete' action that is invoked
	 * when a user wants to delete the given PDB file.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$model = $this->loadModel($id);

		// Check if user is authorized to perform this action
		$this->checkAuthorization($model, 'pperm_manage_debug_info');

		// Mark row from database table for deletion
		if(!$model->markForDeletion())
		{
			throw new CHttpException(404, 'The specified record doesn\'t exist in the database');
		}

		// Redirect to index
		$this->redirect(array('debugInfo/index', ));
	}

	/**
	 * This is the 'deleteMultiple' action that is invoked
	 * when a user wants to delete several PDB files at once.
	 */
	public function actionDeleteMultiple()
	{
		if(!isset($_POST['DeleteRows']))
		{
			throw new CHttpException(404, 'The parameter is invalid');
		}

		// Get data from user
		$deleteRows=$_POST['DeleteRows'];

		foreach($deleteRows as $id)
		{
			$model = $this->loadModel($id);

			// Check if user is authorized to perform this action
			$this->checkAuthorization($model, 'pperm_manage_debug_info');

			// Mark row from database table to be deleted
			if(!$model->markForDeletion())
			{
				throw new CHttpException(404, 'The specified record doesn\'t exist in the database or could not be deleted.');
			}
		}

		// Redirect to index
		$this->redirect(array('debugInfo/index', ));
	}
	
	public function actionDeleteAll()
	{	    
	    // Check if user is authorized to perform this action
	    $this->checkAuthorization(null);
	    
	    $model = new DebugInfo('search');
    
	    $dataProvider = $model->search();
	    
	    $criteria = $dataProvider->getCriteria();
	    
	    DebugInfo::model()->updateAll(array('status' => DebugInfo::STATUS_PENDING_DELETE), $criteria);    
	  	    
	    // Redirect to index
	    $this->redirect(array('debugInfo/index'));
	}
	
	/**
	 *  This action generates an image with debug info upload statistics
	 *  for the current project.
	 */
	public function actionUploadStat($w, $h, $period)
	{
		// Check if user is authorized to perform this action
		$this->checkAuthorization(null);

		DebugInfo::generateDebugInfoUploadStat($w, $h, $period);
	}

	/**
	 * This is the 'uploadFile' action that is invoked
	 * when a user wants to upload one or several PDB files using web GUI.
	 */
	public function actionUploadFile()
	{
		// Check if user is authorized to perform this action
		$this->checkAuthorization(null);

		$model = new DebugInfo('create');
		$submitted = false;


		if(isset($_POST['DebugInfo']))
		{
			$attachmentName = $_FILES['DebugInfo']['name']['fileAttachment'];
			if (preg_match('/\\.7z$/', $attachmentName ))
			{
				$tmpName = $_FILES['DebugInfo']['tmp_name']['fileAttachment'];
				
				$zip = new SevenZipArchive($tmpName);

				$tmpDir = sys_get_temp_dir() . "/upload_tmp";
				if (!file_exists($tmpDir))
				mkdir($tmpDir);
				$files = glob($tmpDir . '/*'); // get all file names
				foreach($files as $file){ // iterate files
					if(is_file($file))
					unlink($file); // delete file
				}
				$zip->extractTo($tmpDir);
				$files = glob($tmpDir . '/*');
				foreach($files as $file)
				{
					if(!is_file($file))
						continue;
					
					$file_md5 = md5_file($file);
					$model3 = DebugInfo::model()->find(array(
										'condition'=>'md5=:md5',
										'params'=>array(':md5'=>$file_md5),
										));
										
					if($model3!==Null) // skip existing files with same hash
						continue;

					$model2 = new DebugInfo('create');
					$model2->fileAttachmentIsUploaded = false;
					$model2->attributes = $_POST['DebugInfo'];
					$model2->guid = 'tmp_'.MiscHelpers::GUID();

					// Get uploaded file
					$model2->fileAttachment = new CUploadedFile(pathinfo($file, PATHINFO_BASENAME ), $file, 'pdb', filesize($file), 0);
					// This will create a new record in the {{debuginfo}} db table
					// and move the uploaded file to its persistent location.
					$model2->save();
				}
			}
			else
			{
				$submitted = true;
				$model->attributes = $_POST['DebugInfo'];
				$model->guid = 'tmp_'.MiscHelpers::GUID();

				if($model->validate())
				{

					// Get uploaded file
					$model->fileAttachment =
							CUploadedFile::getInstance($model, 'fileAttachment');

					// This will create a new record in the {{debuginfo}} db table
					// and move the uploaded file to its persistent location.
					$model->save();

				}
			}
			
			$project = Yii::app()->user->getCurProject();
			$project->cleanupOldDebugInfo();
		}

		// Display the result
		$this->render('uploadFile',
					array(
						'model'=>$model,
						'submitted'=>$submitted,
					)
				);
	}

	/**
	 * Provides a way to an external application to upload PDB files by POST.
	 */
	public function actionUploadExternal()
	{
		// Determine what action to perform -
		// to check file presence or to do actual upload.
		$action = self::ACTION_UPLOAD; // Assume upload action by default
		if(isset($_POST['action']))
		{
			if($_POST['action']=='Check')
				$action = self::ACTION_CHECK;
			else if($_POST['action']=='UploadFile')
				$action = self::ACTION_UPLOAD;
		}

		// Create new model instance
		$model = new DebugInfo('create');

		// Fill model attributes
		if(isset($_POST['DebugInfo']))
			$model->attributes = $_POST['DebugInfo'];

		// Check if file with such a GUID exists
		$fileNotFound = $model->checkFileGUIDExists();

		// Determine what to do based on the "action" attribute
		if($action==self::ACTION_CHECK )
		{
			// Just render the result of checking GUID presence.
			$this->renderPartial('_upload', array('model'=>$model));
		}
		else
		{
			// The following code is executed when action is self::ACTION_UPLOAD

			// Check if we have found such file in the database. If yes, we do not need
			// to upload another one.
			if($fileNotFound && $model->validate())
			{
				// Get uploaded file
				$model->fileAttachment = CUploadedFile::getInstance($model, 'fileAttachment');

				// This will create a new record in the {{debuginfo}} db table
				$model->save();
			}

			// Display the result
			$this->renderPartial('_upload', array('model'=>$model));
		}
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=DebugInfo::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Checks if user is authorized to perform the action.
	 * @param DebugInfo $model Authorization object. Can be null.
	 * @param string $permission Permission name.
	 * @throws CHttpException
	 * @return void
	 */
	protected function checkAuthorization($model, $permission = 'pperm_browse_debug_info')
	{
		if($model==null)
		{
			$projectId = Yii::app()->user->getCurProjectId();
			if($projectId==false)
				return;
		}
		else
			$projectId = $model->project_id;

		// Check if user can perform this action
		if(!Yii::app()->user->checkAccess($permission,
				array('project_id'=>$projectId)) )
		{
			throw new CHttpException(403, 'You are not authorized to perform this action.');
		}
	}
}



