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
                'password',
                'required',
                'on' => 'backendCreate',
                'message' => 'Password cannot be blank.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'telemovel' => 'Telemóvel',
            'password' => 'Password',
            'role' => 'Função',
            'roleFormatted' => 'Função',
            'status' => 'Estado',
            'cinema_id' => 'Cinema',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public function isEditable(): bool { return true; }

    public function isDeletable(): bool { return true; }

    // GUARDAR A PASSWORD E AUTH_KEY/TOKEN (SE NÃO EXISTIR)
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

    // OBTER ROLE DO USER
    public function getRoleName()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        return empty($roles) ? null : array_key_first($roles);
    }

    // OBTER ROLE DO USER FORMATADO
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
    public function isStaff(): bool
    {
        if ($this->roleName == 'gerente' || $this->roleName == 'funcionario'){
            return true;
        }
        return false;
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

    // OBTER COMPRAS DO USER
    public function getCompras()
    {
        return $this->hasMany(Compra::class, ['cliente_id' => 'id']);
    }

    // OBTER ALUGUERES DO USER
    public function getAlugueres()
    {
        return $this->hasMany(AluguerSala::class, ['cliente_id' => 'id']);
    }

    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsRoles()
    {
        return [
            'cliente' => 'Cliente',
            'funcionario' => 'Funcionário',
            'gerente' => 'Gerente',
            'admin' => 'Administrador',
        ];
    }

    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_INACTIVE => 'Inativo',
            self::STATUS_DELETED => 'Eliminado',
        ];
    }

    /**
     * @return string
     */
        public function displayRole()
    {
        return self::optsRoles()[$this->roleName];
    }

    /**
     * @return bool
     */
    public function isRoleAdmin()
    {
        return $this->roleName === 'admin';
    }

    /**
     * @return bool
     */
    public function isRoleGerente()
    {
        return $this->roleName === 'gerente';
    }

    /**
     * @return bool
     */
    public function isRoleFuncionario()
    {
        return $this->roleName === 'funcionario';
    }

    /**
     * @return bool
     */
    public function isRoleCliente()
    {
        return $this->roleName === 'cliente';
    }


    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function setStatusToActive()
    {
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isStatusInactive()
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function setStatusToInactive()
    {
        $this->status = self::STATUS_INACTIVE;
    }

    /**
     * @return bool
     */
    public function isStatusDeleted()
    {
        return $this->status === self::STATUS_DELETED;
    }

    public function setStatusToDeleted()
    {
        $this->status = self::STATUS_DELETED;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token, 'status' => self::STATUS_ACTIVE]);
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
