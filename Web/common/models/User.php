<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    // CAMPOS TEMPORÁRIOS
    public $password;
    public $role;
    public $cinema_id;

    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            [['username', 'email'], 'trim'],
            [['username', 'email'], 'required'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            [['username', 'email'], 'unique'],
            ['role', 'safe'],
            ['password', 'string', 'min' => 8],
            [
                'password', 'required',
                'when' => fn($model) => $model->isNewRecord,
                'whenClient' => "function() { return !$('#user-id').val(); }",
                'message' => 'Password cannot be blank.'
            ],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    // GUARDAR A PASSWORD E AUTH_KEY/TOKEN
    public function beforeSave($insert)
    {
        if ($this->password) {
            $this->setPassword($this->password);
        }

        if ($insert) {
            if (empty($this->auth_key)) {
                $this->generateAuthKey();
            }
            if (empty($this->verification_token)) {
                $this->generateEmailVerificationToken();
            }
        }

        return parent::beforeSave($insert);
    }

    // OBTER USER_PROFILE DO USER
    public function getProfile()
    {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }

    // OBTER CINEMA DO USER
    public function getCinema()
    {
        return $this->hasOne(Cinema::class, ['id' => 'cinema_id'])->via('profile');
    }

    // OBTER ROLE DO USER
    public function getRoleName()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        return empty($roles) ? null : array_key_first($roles);
    }

    // OBTER ROLE DO USER FORMATO (PARA MOSTRAR NAS VIEWS)
    public function getRoleFormatted()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        if (empty($roles)) return '-';
        $labels = [
            'admin' => 'Administrador',
            'gerente' => 'Gerente',
            'funcionario' => 'Funcionário',
            'cliente' => 'Cliente',
        ];
        $key = array_key_first($roles);
        return $labels[$key] ?? ucfirst($key);
    }

    // VERIFICAR SE É GERENTE OU FUNCIONÁRIO
    public function isStaff()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        return (isset($roles['gerente']) || isset($roles['funcionario'])) && !isset($roles['admin']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
}
