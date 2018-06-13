<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "room".
 *
 * @property int $id
 * @property string $room_number
 * @property int $type
 * @property int $campus
 * @property int $available
 *
 * @property Application[] $applications
 * @property RoomType $type0
 * @property Campus $campus0
 */
class Room extends \yii\db\ActiveRecord
{
    const STATUS_AVAILABLE = 1;
    const STATUS_UNAVAILABLE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['room_number', 'type', 'campus'], 'required'],
            [['type', 'campus'], 'integer'],
            ['available', 'default', 'value' => self::STATUS_AVAILABLE],
            ['available', 'in', 'range' => [self::STATUS_AVAILABLE, self::STATUS_UNAVAILABLE]],
            [['room_number'], 'string', 'max' => 10],
            [['type'], 'exist', 'skipOnError' => true, 'targetClass' => RoomType::className(), 'targetAttribute' => ['type' => 'id']],
            [['campus'], 'exist', 'skipOnError' => true, 'targetClass' => Campus::className(), 'targetAttribute' => ['campus' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_number' => '房间号',
            'type' => '房间类型',
            'campus' => '校区',
            'available' => '状态',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApplications()
    {
        return $this->hasMany(Application::className(), ['room_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType0()
    {
        return $this->hasOne(RoomType::className(), ['id' => 'type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampus0()
    {
        return $this->hasOne(Campus::className(), ['id' => 'campus']);
    }

    public static function getAllTypes()
    {
        return RoomType::find()
            ->select(['type_name', 'id'])
            ->orderBy('id')
            ->indexBy('id')
            ->column();
    }

    public static function getAllCampus()
    {
        return Campus::find()
            ->select(['campus_name', 'id'])
            ->orderBy('id')
            ->indexBy('id')
            ->column();
    }

    public function getStatusBg()
    {
        $options = [];
        switch ($this->available) {
            case self::STATUS_AVAILABLE:
                $options['class'] = 'bg-success';
                break;
            case self::STATUS_UNAVAILABLE:
                $options['class'] = 'bg-danger';
                break;
        }
        return $options;
    }

    /**
     * 获取当前房间状态字符串
     *
     * @return string|null
     */
    public function getStatusStr() {
        switch ($this->available) {
            case self::STATUS_AVAILABLE:
                return '可用';
            case self::STATUS_UNAVAILABLE:
                return '不可用';
        }
        return null;
    }

    /**
     * 获取当前房间状态字符串（上色）
     *
     * @return string|null
     */
    public function getColoredStatusStr() {
        switch ($this->available) {
            case self::STATUS_AVAILABLE:
                return '<span class="text-success">（可用）</span>';
            case self::STATUS_UNAVAILABLE:
                return '<span class="text-danger">（不可用）</span>';
        }
        return null;
    }

    public static function getAllStatus()
    {
        return [
            self::STATUS_AVAILABLE => '可用',
            self::STATUS_UNAVAILABLE => '不可用',
        ];
    }

    /**
     * 切换房间状态
     *
     * @return bool
     */
    public function changeStatus()
    {
        if ($this->available == self::STATUS_AVAILABLE) {
            $this->available = self::STATUS_UNAVAILABLE;
        } else {
            $this->available = self::STATUS_AVAILABLE;
        }

        return true;
    }

    public function getQueueCount($s_time_str, $e_time_str)
    {
        $s_time = strtotime($s_time_str);
        $e_time = strtotime($e_time_str);

        $overlap = (new Query())
            ->select('id')
            ->from('application')
            ->where("not (start_time >= $e_time or end_time <= $s_time)")
            ->andWhere(['status' => Application::STATUS_PENDDING])
            ->andWhere(['room_id' => $this->id])
            ->count();

        return $overlap;
    }

    public function getApprovalStatus($s_time_str, $e_time_str)
    {
        $s_time = strtotime($s_time_str);
        $e_time = strtotime($e_time_str);

        $overlap = (new Query())
            ->select('id')
            ->from('application')
            ->where("not (start_time >= $e_time or end_time <= $s_time)")
            ->andWhere(['status' => Application::STATUS_APPROVED])
            ->andWhere(['room_id' => $this->id])
            ->count();

        if ($overlap > 0) {
            return ['text' => '已分配', 'class' => ['class' => 'bg-danger']];
        }
        return ['text' => '未分配', 'class' => ['class' => 'bg-success']];
    }
}