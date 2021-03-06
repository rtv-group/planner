<?php

/**
 * This is the model class for table "point".
 *
 * The followings are the available columns in table 'point':
 * @property integer $id
 * @property string $name
 * @property string $username
 * @property string $password
 * @property string $sync_time
 * @property string $update_time
 * @property integer $volume
 * @property integer $TV
 * @property integer $channels
 * @property integer $id_user
 */
class Point extends CActiveRecord
{
    public $content;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'point';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['name, username, ip, screen_id', 'required'],
            ['volume, TV, screen_id, id_user', 'numerical', 'integerOnly'=>true],
            ['name, username', 'length', 'max'=>255],
            ['sync', 'boolean'],
            ['sync', 'default',
                    'value'=> false,
                    'setOnEmpty' => true, 'on'=>'insert'],
            ['id_user', 'default',
                    'value'=> Yii::app()->user->id,
                    'setOnEmpty' => true, 'on'=>'insert'],
            ['update_time', 'default',
                    'value' => new CDbExpression('NOW()'),
                    'setOnEmpty'=>false,'on'=>'insert'],
            ['update_time', 'default',
                    'value' => new CDbExpression('NOW()'),
                    'setOnEmpty' => false, 'on'=>'update'],
            ['content', 'file', 'types'=>'zip', 'allowEmpty'=>true],
            ['name', 'safe', 'on'=>'search'],
        ];
    }


    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'showcases'=>[self::HAS_MANY, 'Showcase', 'id_point'],
            'widgets'=>[self::HAS_MANY, 'Widget', ['id_widget'=>'id'],'through'=>'showcases'],
            'playlistToPoint'=>[self::HAS_MANY,'PlaylistToPoint', ['id_point' => 'id']],
            'playlists'=>[self::HAS_MANY,'Playlists', ['id_playlist'=>'id'],'through'=>'playlistToPoint'],
            'pointToNet'=>[self::HAS_MANY, 'PointToNet', 'id_point'],
            'net'=>[self::HAS_MANY, 'Net', ['id_net'=>'id'],'through'=>'pointToNet'],
            'screen'=>[self::BELONGS_TO, 'Screen', 'screen_id'],
            'tv'=>[self::HAS_MANY, 'TvSchedule', 'id_point']
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
            'username' => 'Username',
            'password' => 'Password',
            'ip' => 'Point IP Address',
            'sync_time' => 'Last Success Sync Time',
            'update_time' => 'Last Update Time',
            'volume' => 'Volume',
            'free_space' => 'Free Space',
            'TV' => 'TV hardware turning',
            'TVschedule' => 'TV turn on Schedule',
            'channels' => 'Channels',
            'sync' => 'Syncronized',
            'status' => 'Status',
            'screen' => 'Screen',
            'screen_id' => "Screen",
            'ctrl' => "Controls"
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
        $criteria=new CDbCriteria;
        $criteria->compare('name',$this->name,true);
        $criteria->compare('ip',$this->ip,true);

        if (Yii::app()->user->role != User::ROLE_ADMIN) {
            $criteria->compare('id_user', Yii::app()->user->id);
        }

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }

    public function searchWithoutContent()
    {
        $pointCriteria=new CDbCriteria;

        if (Yii::app()->user->role != User::ROLE_ADMIN) {
            $pointCriteria->compare('id_user', Yii::app()->user->id);
        }

        $allPoints = Point::model()->findAll($pointCriteria);
        $points = [];

        foreach ($allPoints as $point) {
            $playlists = $point->playlists;

            $count = count($playlists);
            $expired = 0;
            foreach ($playlists as $playlist) {
                if (date_create($playlist->toDatetime) < new DateTime('now')) {
                    $expired++;
                }
            }

            if ($count === $expired) {
                $points[] = $point;
            }
        }

        return new CArrayDataProvider($points, [
            'keyField' => 'id',
            'sort' => [
                'attributes' => [
                    'id' => [
                        'asc'=>'id',
                        'desc'=>'id DESC',
                        'label' => 'ID'
                    ],
                    'name' => [
                        'asc'=>'name',
                        'desc'=>'name DESC',
                        'label' => 'Name'
                    ],
                    'ip' => [
                        'asc'=>'ip',
                        'desc'=>'ip DESC',
                        'label' => 'IP'
                    ],
                ],
            ]
        ]);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Point the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
