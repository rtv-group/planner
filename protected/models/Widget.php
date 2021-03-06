<?php

/**
 * This is the model class for table "widget".
 *
 * The followings are the available columns in table 'widget':
 * @property integer $id
 * @property string $name
 * @property string $content
 * @property integer $user_id
 * @property string $created_dt
 * @property string $updated_dt
 *
 * The followings are the available model relations:
 * @property User $user
 * @property WidgetToChannel[] $widgetToChannels
 */
class Widget extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'widget';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['name, description, show_duration, periodicity', 'required'],
            ['show_duration, periodicity, offset', 'numerical', 'integerOnly'=>true],
            ['name', 'length', 'max'=>255],
            ['created_dt, config', 'safe'],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            ['name, created_dt', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'widgetToChannels' => [self::HAS_MANY, 'WidgetToChannel', 'widget_id'],
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
            'description' => 'Description',
            'show_duration' => 'Show duration',
            'offset' => 'Offset',
            'periodicity' => 'Periodicity',
            'config' => 'Config',
            'created_dt' => 'Created Date',
        ];
    }

    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('name',$this->name,true);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }

    public function getInfo()
    {
        if (!is_int(intval($this->show_duration))
            || !is_int(intval($this->periodicity))
            || !is_int(intval($this->offset))
        ) {
            throw new Error (implode('',
                [ __CLASS__, ' contains corrupted data ', $this->show_duration, ', ', $this->periodicity, '.']
            ));
        }

        return [
            'show_duration' => $this->show_duration,
            'periodicity' => $this->periodicity,
            'offset' => $this->offset
        ];
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Widget the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
