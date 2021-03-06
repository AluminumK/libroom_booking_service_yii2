<?php

namespace backend\models;

use yii\base\Model;
use common\models\Admin;

class SignupForm extends Model
{
    public $admin_id;
    public $admin_name;
    public $email;
    public $password;
    public $password2;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['admin_id', 'trim'],
            ['admin_id', 'required'],
            ['admin_id', 'unique', 'targetClass' => '\common\models\Admin', 'message' => '该工号已注册。'],
            ['admin_id', 'string', 'length' => 7],

            ['admin_name', 'trim'],
            ['admin_name', 'required'],
            ['admin_name', 'string', 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\Admin', 'message' => '该电子邮箱已注册。'],

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
            'admin_id' => '工号',
            'admin_name' => '姓名',
            'email' => '电子邮箱',
            'password' => '密码',
            'password2' => '再次输入密码',
        ];
    }

    /**
     * 注册管理员
     *
     * @return Admin|null
     * @throws \Exception
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $admin = new Admin();
        $admin->admin_id = $this->admin_id;
        $admin->admin_name = $this->admin_name;
        $admin->email = $this->email;
        $admin->setPassword($this->password);
        $admin->generateAuthKey();

        return $admin->save() ? $admin : null;
    }
}
