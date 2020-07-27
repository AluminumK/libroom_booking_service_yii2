<?php

namespace backend\models;

use yii\base\Model;
use common\models\Admin;

class ResetPasswordForm extends Model
{
    public $password;
    public $password2;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password', 'password2'], 'required'],
            ['password', 'string', 'min' => 6],
            ['password2', 'compare', 'compareAttribute' => 'password', 'message' => '两次输入密码不一致。'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'password' => '密码',
            'password2' => '再次输入密码',
        ];
    }

    /**
     * 修改密码
     *
     * @param integer $id
     * @return bool
     * @throws \Exception
     */
    public function resetPassword($id)
    {
        if (!$this->validate()) {
            return null;
        }

        $admin = Admin::findOne($id);
        $admin->setPassword($this->password);

        return $admin->save();
    }
}
