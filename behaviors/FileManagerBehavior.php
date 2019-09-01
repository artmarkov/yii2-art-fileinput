<?php

namespace artsoft\fileinput\behaviors;

use artsoft\fileinput\models\FileManager;
use yii\helpers\ArrayHelper;
use yii\db\ActiveRecord;

/**
 * Class FileManagerBehavior
 * @package artsoft\fileinput\behaviors
 *
 * Usage:
 * 1. In your model, add the behavior and configure it:
 * owner_id - primary key owner your model (default - id)
 * 
 * public function behaviors()
 * {
 *     return [
 *             'fileManager' => [
 *                  'class' => \artsoft\fileinput\behaviors\FileManagerBehavior::className(),
 *               // 'owner_id' => 'id',
 *],
 *     ];
 * }
 */
class FileManagerBehavior extends \yii\base\Behavior {

    public $owner_id = 'id';

    public function events() {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete'
        ];
    }

    public function getFiles() {
        return $this->owner->hasMany(FileManager::className(), ['item_id' => $this->owner_id])->orderBy('sort');
    }

    public function getFilesLinks() {
        return ArrayHelper::getColumn($this->owner->files, 'fileUrl');
    }

    public function getFilesLinksData() {
        return ArrayHelper::toArray($this->owner->files, [
                    FileManager::className() => [
                        'type' => 'type',
                        'filetype' => 'filetype',
                        'downloadUrl' => 'fileUrl',
                        'caption' => 'name',
                        'size' => 'size',
                        'key' => 'id',
                        'frameAttr' => [
                            'title' => 'orig_name',
                        ]
                    ]]
        );
    }

    public function getFilesCount() {
        $data = ArrayHelper::getColumn($this->owner->files, 'id');
        return count($data);
    }

    public function getFileColumn() {
        return FileManager::find()
                        ->andWhere(['class' => $this->owner->formName()])
                        ->andWhere(['item_id' => $this->owner->id])
                        ->asArray()->column();
    }

    public function beforeDelete() {
        foreach ($this->getFileColumn() as $id) {
            FileManager::findOne($id)->delete();
        }
    }

}
