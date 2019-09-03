<?php

namespace artsoft\fileinput\controllers;

use Yii;
use yii\web\UploadedFile;
use artsoft\fileinput\models\FileManager;
use yii\helpers\FileHelper;
use yii\web\Response;
use yii\web\BadRequestHttpException;
/**
 * Description of FileManagerController
 *
 * @author markov-av
 */
class FileManagerController extends \artsoft\controllers\admin\BaseController {

    public $enableOnlyActions = ['file-upload', 'sort-file', 'delete-file'];

    /**
     * 
     * @return type
     * @throws BadRequestHttpException
     */
    public function actionFileUpload() {
        
        $result = [];

        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (!Yii::$app->request->isPost) {
            throw new BadRequestHttpException('Only POST is allowed.');
        }
        $post = Yii::$app->request->post();
        
        $dir = FileManager::getAbsoluteDir() . DIRECTORY_SEPARATOR . FileManager::getFolder($post['FileManager']['class']);

        if (!file_exists($dir)) {
            FileHelper::createDirectory($dir);
        }

        $files = UploadedFile::getInstancesByName('attachment');
        foreach ($files as $file) {
            $model = FileManager::getFileAttribute($file);
            $model->load($post);
            $model->validate();
            // echo '<pre>' . print_r($model, true) . '</pre>';
            if ($model->hasErrors()) {
                $result = ['status' => false, 'message' => 'Error', 'error' => $model->getFirstError('file')];
            } else {
                if ($file->saveAs($dir . DIRECTORY_SEPARATOR . $model->name)) {

                    $result = ['status' => true, 'message' => 'Success', 'filename' => $model->name];
                } else {
                    $result = ['status' => false, 'message' => 'Error', 'filename' => $model->name];
                }
                $model->save();
            }
        }
        return $result;
    }

    /**
     * 
     * @param type $id
     * @return boolean
     * @throws MethodNotAllowedHttpException
     */
    public function actionSortFile($id) {
        if (Yii::$app->request->isAjax) {
            
            $model = FileManager::findOne(['item_id' => $id]);
            $post = Yii::$app->request->post('sort');
            
            if ($post['oldIndex'] > $post['newIndex']) {
                $param = ['and', ['>=', 'sort', $post['newIndex']], ['<', 'sort', $post['oldIndex']]];
                $counter = 1;
            } else {
                $param = ['and', ['<=', 'sort', $post['newIndex']], ['>', 'sort', $post['oldIndex']]];
                $counter = -1;
            }
            FileManager::updateAllCounters(['sort' => $counter], [
                'and', ['class' => $model->class, 'item_id' => $id], $param]);
            FileManager::updateAll(['sort' => $post['newIndex']], [
                'id' => $post['stack'][$post['newIndex']]['key']
            ]);

            return true;
        }
        throw new MethodNotAllowedHttpException();
    }

    /**
     * 
     * @return boolean
     * @throws NotFoundHttpException
     */
    public function actionDeleteFile() {
        if (($model = FileManager::findOne(Yii::$app->request->post('key'))) and $model->delete()) {

            return true;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
