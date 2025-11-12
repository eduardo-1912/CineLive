<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['name', 'email', 'subject', 'body'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Nome',
            'email' => 'Email',
            'subject' => 'Assunto',
            'body' => 'Mensagem',
        ];
    }

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
            ->setReplyTo([$this->email => $this->name])
            ->setSubject('Novo contacto: ' . $this->subject)
            ->setHtmlBody("
            <h3>Nova mensagem de contacto</h3>
            <p><strong>Nome:</strong> {$this->name}</p>
            <p><strong>Email:</strong> {$this->email}</p>
            <p><strong>Assunto:</strong> {$this->subject}</p>
            <p><strong>Mensagem:</strong></p>
            <p>{$this->body}</p>")
            ->setTextBody("Nova mensagem de contacto:\n\nNome: {$this->name}\nEmail: {$this->email}\nAssunto: {$this->subject}\n\n{$this->body}")
            ->send();
    }

}
