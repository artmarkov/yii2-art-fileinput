<?php

namespace artsoft\fileinput\widgets;

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Description of FileInput
 *
 * @author artmarkov@mail.ru
 */
class FileInput extends \yii\base\Widget {

    public $model;
    public $id;
    
    public $disabled = false;
    public $options = [];
    public $pluginOptions = [];
    public $pluginEvents = [];

    private $rawWidgetHtml;
    
    public function run()
    {
        if (!isset($this->model)) {
            throw new \yii\base\InvalidConfigException('Model was not found.');
        }
        
        if (!isset($this->id))
        {
            $this->id = $this->getId();
        }
        
        $this->buildWidget();

        return $this->getWidgetHtml();
    }

    private function buildWidget()
    {
        $this->rawWidgetHtml = \kartik\file\FileInput::widget([
                    'id' => $this->id,
                    'name' => 'attachment[]',
                    'language' => Yii::$app->language ? Yii::$app->language : 'en',
                    'disabled' => $this->disabled,
                    'options' => $this->options,
                    'pluginOptions' => ArrayHelper::merge([
                        'theme' => '', //explorer
                        'showCaption' => true,
                        'showBrowse' => true,
                        'showPreview' => true,
                        'showUpload' => true,
                        'showRemove' => false,
                        'uploadAsync' => false,
                        'dropZoneEnabled' => false,
                        'browseOnZoneClick' => false,
                        'maxFileCount' => 10,
                        'deleteUrl' => Url::toRoute(['/fileinput/file-manager/delete-file']),
                        'initialPreview' => $this->model->filesLinks,
                        'initialPreviewAsData' => true,
                        'initialPreviewFileType' => 'image',
                        'overwriteInitial' => false,
                        'initialPreviewConfig' => $this->model->filesLinksData,
                        'maxFileSize' => 3000, // Kb
                        'allowedFileExtensions' => ['txt', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'pdf', 'jpg', 'png', 'mp4', 'mp3'],
                        'uploadUrl' => Url::to(['/fileinput/file-manager/file-upload']),
                        'hideThumbnailContent' => false,
                        'preferIconicPreview' => false,
                        'previewFileIcon' => '<i class="fa fa-files"></i>',
                        'previewFileIconSettings' => [
                            'txt'  => '<i class="fa fa-file-text-o text-default"></i>',
                            'doc'  => '<i class="fa fa-file-word-o text-primary"></i>',
                            'docx' => '<i class="fa fa-file-word-o text-primary"></i>',
                            'xls'  => '<i class="fa fa-file-excel-o text-success"></i>',
                            'xlsx' => '<i class="fa fa-file-excel-o text-success"></i>',
                            'ppt'  => '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                            'pptx' => '<i class="fa fa-file-powerpoint-o text-danger"></i>',
                            'zip'  => '<i class="fa fa-file-archive-o text-muted"></i>',
                            'rar'  => '<i class="fa fa-file-archive-o text-muted"></i>',
                            'pdf'  => '<i class="fa fa-file-pdf-o text-warning"></i>',
                            'jpg'  => '<i class="fa fa-file-image-o text-primary"></i>',
                            'png'  => '<i class="fa fa-file-image-o text-primary"></i>',
                            'mp4'  => '<i class="fa fa-film text-primary"></i>',
                            'mp3'  => '<i class="fa fa-file-audio-o text-primary"></i>',
                        ],
                        'fileActionSettings' => [
                            'showDrag' => true,
                            'showZoom' => true,
                            'showRemove' => true,
                        ],
                        'uploadExtraData' => [
                            'FileManager[class]' => $this->model->formName(),
                            'FileManager[item_id]' => $this->model->id
                        ],
                            ], $this->pluginOptions),
                    'pluginEvents' => ArrayHelper::merge([
                        'filesorted' => new \yii\web\JsExpression('function(event, params){
                                                  $.post("' . Url::toRoute(["/fileinput/file-manager/sort-file", "id" => $this->model->id]) . '", {sort: params});
                                            }'),
                        'filebatchselected' => new \yii\web\JsExpression('function(event, files) {                                               
                                                  $("#' . $this->id . '").fileinput("upload");
                                            }'),
                            ], $this->pluginEvents),
        ]);
    }

    public function getWidgetHtml()
    {
        return $this->rawWidgetHtml;
    }

}
