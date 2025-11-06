<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    public $nome;
    public $email;
    public $mensagem;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['nome', 'email', 'mensagem'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @param string $email the target email address
     * @return bool whether the email was sent
     */
    public function sendEmail($email)
    {
        return Yii::$app->mailer->compose()
            ->setTo($email)
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setReplyTo([$this->email => $this->nome])
            ->setSubject($this->mensagem)
            ->setTextBody($this->mensagem)
            ->send();
    }
}
