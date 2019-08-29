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
                        'showCaption' => true,
                        'showBrowse' => true,
                        'showPreview' => true,
                        'showUpload' => false,
                        'showRemove' => false,
                        'uploadAsync' => false,
                        'dropZoneEnabled' => true,
                        'maxFileCount' => 10,
                        'deleteUrl' => Url::toRoute(['/fileinput/file-manager/delete-file']),
                        'initialPreview' => $this->model->filesLinks,
                        'initialPreviewAsData' => true,
                        'initialPreviewFileType' => 'image',
                        'overwriteInitial' => false,
                        'initialPreviewConfig' => $this->model->filesLinksData,
                        'maxFileSize' => 3000, // Kb
                        'allowedFileExtensions' => ["jpg", "png", "mp4", "pdf"],
                        'uploadUrl' => Url::to(['/fileinput/file-manager/file-upload']),
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
