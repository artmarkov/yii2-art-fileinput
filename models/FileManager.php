<?php

namespace artsoft\fileinput\models;

use Yii;
use artsoft\fileinput\FileInputModule;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use artsoft\models\User;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%file_manager}}".
 *
 * @property int $id
 * @property string $orig_name
 * @property string $name
 * @property string $class
 * @property int $item_id
 * @property int $sort
 * @property string $alt
 * @property string $type
 * @property string $filetype
 * @property string $size
 * @property int $created_at
 * @property int $created_by
 */
class FileManager extends \yii\db\ActiveRecord {

    /**
     * array const
     */
    const TYPE = [
            'jpg' => ['type' => 'image'],
            'png' => ['type' => 'image', 'filetype' => 'image/png'],
            'pdf' => ['type' => 'pdf'],
            'mp4' => ['type' => 'video', 'filetype' => 'video/mp4'],
        ];

      /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [         
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => NULL,
            ], 
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => NULL,
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return '{{%file_manager}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['orig_name', 'name', 'type'], 'required'],
            [['created_at'], 'safe'],
            [['item_id', 'sort', 'size'], 'integer'],
            ['sort', 'default', 'value' => function($model) {
                $count = FileManager::find()->andWhere(['class' => $model->class, 'item_id' => $model->item_id])->count();
                return ($count > 0) ? $count++ : 0;
            }],
            [['type', 'filetype'], 'safe'],
            [['orig_name', 'name', 'class', 'alt'], 'string', 'max' => 256],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('art', 'ID'),
            'orig_name' => Yii::t('art', 'Orig Name'),
            'name' => Yii::t('art', 'Name'),
            'class' => Yii::t('art', 'Class'),
            'item_id' => Yii::t('art', 'Item ID'),
            'alt' => Yii::t('art', 'Alt'),
            'type' => Yii::t('art', 'Type'),
            'filetype' => Yii::t('art', 'Filetype'),
            'size' => Yii::t('art', 'Size'),
        ];
    }
    
     /**
     * 
     * @param type model $file
     * @return model
     */
     public static function getFileAttribute($file) {
         
        $model = new FileManager();
        $name = $file->name;
        $model->name = strtotime('now') . '_' . Yii::$app->getSecurity()->generateRandomString(6) . '.' . $file->extension;
        $model->orig_name = $name;
        $model->alt = $name;
        $model->type = ArrayHelper::getValue(self::TYPE, $file->extension . '.type') ? ArrayHelper::getValue(self::TYPE, $file->extension . '.type') : 'image';
        $model->filetype = ArrayHelper::getValue(self::TYPE, $file->extension . '.filetype');
        $model->size = $file->size;

        return $model;
    }

     /**
     * 
     * @return boolean
     */
    public function beforeDelete() {
        if (parent::beforeDelete()) {
            FileManager::updateAllCounters(['sort' => -1], [
                'and', ['class' => $this->class, 'item_id' => $this->item_id], [ '>', 'sort', $this->sort]
            ]);
            //удаляем физически              
                    if (file_exists($this->getRoutes())) {
                        @unlink($this->getRoutes());
                    }
            
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $class
     * @return type string
     */
    public static function getFolder($class){
        return strtolower($class);
    } 
    /**
     * 
     * @return type string
     */
    public static function getAbsoluteDir(){
        return Yii::$app->getModule('fileinput')->absolutePath;
    } 
    /**
     * 
     * @return type string
     */
    public static function getUploadDir(){
        return Yii::$app->getModule('fileinput')->uploadPath;
    }
    /**
     * 
     * @return type string
     */
    public function getRoutes(){
        return "{$this::getAbsoluteDir()}/{$this::getFolder($this->class)}/{$this->name}";
    }
    
    /**
     * 
     * @return string
     */
    public function getFileUrl() {

        $uploadDir = Url::to('/', true) . $this->getUploadDir();

        if ($this->name && file_exists($this->getRoutes())) {

            //$path = Url::to('/', true) . $uploadDir . DIRECTORY_SEPARATOR . $this->class . DIRECTORY_SEPARATOR . $this->name;
            $path = "{$uploadDir}/{$this::getFolder($this->class)}/{$this->name}";
        } else {
            //$path = Url::to('/', true) . $uploadDir . DIRECTORY_SEPARATOR . 'nophoto.svg';
            $path = "{$uploadDir}/nofile.jpg";
        }
        return $path;
    }

}
