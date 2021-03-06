<?php

/**
 * This is the model class for table "screen".
 *
 * The followings are the available columns in table 'screen':
 * @property integer $id
 * @property string $name
 * @property integer $width
 * @property integer $height
 *
 * The followings are the available model relations:
 * @property Channel[] $channels
 */
class Screen extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'screen';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['name, width, height, user_id', 'required'],
            ['width, height', 'numerical', 'integerOnly'=>true],
            ['name', 'length', 'max'=>255],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            ['id, name, width, height', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return [
            'windows' => [self::HAS_MANY, 'Window', 'screen_id'],
            'showcases' => [self::HAS_MANY, 'Showcase', 'id_window']
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'width' => 'Width',
            'height' => 'Height',
        ];
    }

    public function getInfo() {
        if (!is_int(intval($this->width))
            || !is_int(intval($this->height))
        ) {
            throw new Error (implode('',
                [ __CLASS__, ' contains corrupted data ', $this->width, ', ', $this->height, '.']
            ));
        }

        return [
            'width' => $this->width,
            'height' => $this->height
        ];
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('width',$this->width);
        $criteria->compare('height',$this->height);

        if(Yii::app()->user->name != User::ROLE_ADMIN)
        {
            $criteria->compare('user_id',Yii::app()->user->id);
        }

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Screen the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function AppendBlockToScreen($ip, $TV)
    {

    }
}
