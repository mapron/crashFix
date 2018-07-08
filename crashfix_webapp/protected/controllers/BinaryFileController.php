<?php
Yii::import('application.vendors.ezcomponents.*');
Yii::import('application.vendors.SevenZipArchive');
require_once "Base/src/base.php";
Yii::registerAutoloader(array('ezcBase', 'autoload'), true);


class BinaryFileController extends Controller
{
	// Use two-column layout
	public $layout='//layouts/column2';
	public $sidebarActiveItem = 'BinaryFile';

	// @var BinaryFile directory list provider
	private $_binaryFileProvider;
	
	public function init()
	{
	    $project = Yii::app()->user->getCurProject();
	    
	    $path = Yii::app()->getBasePath()
	       . DIRECTORY_SEPARATOR . "data"
	       . DIRECTORY_SEPARATOR . "peFiles"
	       . DIRECTORY_SEPARATOR . $project->name;
	    
	    $this->_binaryFileProvider = new BinaryFile($path);
	}

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
			array('allow',  // Allow authenticated users
				'actions'=>array(
					'index',
					'delete',
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
	    $this->checkAuthorization();

		// Render view
		$this->render('index', array(
		    'dataProvider'=> $this->_binaryFileProvider,
		));
	}

	
	public function actionDelete($id)
	{
	    $this->checkAuthorization();
	    
	    $this->_binaryFileProvider->delete($id);
	    
		// Redirect to index
		$this->redirect(array('binaryFile/index', ));
	}

	/**
	 * This is the 'uploadFile' action that is invoked
	 * when a user wants to upload one or several PDB files using web GUI.
	 */
	public function actionUploadFile()
	{
		// Check if user is authorized to perform this action
		$this->checkAuthorization();

		$submitted = false;


		if(isset($_POST['version']) && !empty($_POST['version']))
		{
		    $version = $_POST['version'];
		    $submitted = true;
			$attachmentName = $_FILES['fileAttachment']['name'];
			if (preg_match('/\\.7z$/i', $attachmentName ) && preg_match('/^[0-9.]+$/', $version))
			{
				$tmpName = $_FILES['fileAttachment']['tmp_name'];
				
				$zip = new SevenZipArchive($tmpName);
				$allEntries = $zip->entries();
				$peEntries = [];
				foreach ($allEntries as $entry)
				{
				    $name = $entry['Name'];
				    if ($name[0] == '$')
				        continue;
				    if (preg_match('/\\.(exe|dll)$/i', $name ))
				        $peEntries[] = $name;
				}
				$path = $this->_binaryFileProvider->getPath($version);
				if (file_exists($path))
				    $this->_binaryFileProvider->delete($version);
				    
				mkdir($path, 0777, true);
				$zip->extractTo($path, $peEntries); 
				
				$this->_binaryFileProvider->renameFilesRecursive($version);
				
				// Redirect to index
				$this->redirect(array('binaryFile/index', ));
			}
		}

		// Display the result
		$this->render('uploadFile',
					array(
						'submitted'=>$submitted,
					)
				);
	}


	/**
	 * Checks if user is authorized to perform the action.
	 * @param string $permission Permission name.
	 * @throws CHttpException
	 * @return void
	 */
	protected function checkAuthorization($permission = 'pperm_browse_debug_info')
	{
		
		$projectId = Yii::app()->user->getCurProjectId();
		if($projectId==false)
			return;
	
		// Check if user can perform this action
		if(!Yii::app()->user->checkAccess($permission,
				array('project_id'=>$projectId)) )
		{
			throw new CHttpException(403, 'You are not authorized to perform this action.');
		}
	}
}



