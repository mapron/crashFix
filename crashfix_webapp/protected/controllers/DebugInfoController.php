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

	private $_debugInfoProvider;

	public function init()
	{
	    $project = Yii::app()->user->getCurProject();

	    if (empty($project))
	        return;

	   $selVer = null; // stupid getCurProjectVersions API.
	   $this->_debugInfoProvider = new DebugInfo(Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "debugInfo",
	       $project->name,
	       Yii::app()->user->getCurProjectVersions($selVer));
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
					'uploadFile',
				    'addVersion'
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
	        'dataProvider'=> $this->_debugInfoProvider,
	    ));
	}


	public function actionDelete($project, $version, $arch)
	{
	    $this->checkAuthorization();

	    $this->_debugInfoProvider->delete($project, $version, $arch);

		// Redirect to index
		$this->redirect(array('debugInfo/index'));
	}

	/**
	 * This is the 'uploadFile' action that is invoked
	 * when a user wants to upload one or several PDB files using web GUI.
	 */
	public function actionUploadFile($project, $version, $arch)
	{
		// Check if user is authorized to perform this action
		$this->checkAuthorization();

		if ($this->processUploadedFile($project, $version, $arch))
		{
		    // Redirect to index
		    $this->redirect(array('debugInfo/index'));
		}

		// Display the result
		$this->render('uploadFile', [
		    'data' => [
                'project' => $project,
                'version' => $version,
                'arch'    => $arch,
        	]
        ]);
	}

	public function actionAddVersion()
	{
	    $version = Yii::app()->request->getPost('version');
	    if ($version)
	    {
	        $version = trim($version);
	        $project = Yii::app()->user->getCurProject();
	        AppVersion::createIfNotExists($version, $project->id);
	    }

	    $this->redirect(array('debugInfo/index'));
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

	protected function processUploadedFile($project, $version, $arch)
	{
	    if(!isset($_FILES['fileAttachment']))
	       return false;

        $attachmentName = $_FILES['fileAttachment']['name'];
        if (!preg_match('/\\.7z$/i', $attachmentName ))
            return false;

        $tmpName = $_FILES['fileAttachment']['tmp_name'];

        $zip = new SevenZipArchive($tmpName);

        $path = $this->_debugInfoProvider->getPath($project, $version, $arch);

        mkdir($path, 0777, true);

        return $zip->extractTo($path);
	}
}



