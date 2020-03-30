<?php

namespace Hcode;

// invoca a classe Tpl
use Rain\Tpl;

// invoca a classe PHPMailer
use PHPMailer\PHPMailer\PHPMailer;

class Mailer {

    const USERNAME = "seuEmailVerdadeiro";
    const PASSWORD = "senhaDoEmail";
    const NAME_FROM = "Assunto do e-mail";

    // o atribudo $mail foi criado para ser chamado só quando for necessário
    private $mail;

    // a estrutura inicial até o foreach é a do Tpl, a mesma usada na classe Page
    public function __construct($toAdress, $toName, $subject, $tplName, $data = array())
    {

        // o caminho do tpl_dir foi alterado para /views/email/ onde está arquivo forgot.html
        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/email/", // endereço das páginas HTML
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/", // endereço da página montada
            "debug"         => false // configurado com false melhora a velocidade
        );

        Tpl::configure( $config );

        $tpl = new Tpl;

        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);

        }

        $html = $tpl->draw($tplName, true);

        $this->mail = new PHPMailer;

        //Tell PHPMailer to use SMTP
        $this->mail->isSMTP();

        //Enable SMTP debugging
        // SMTP::DEBUG_OFF = off (for production use)
        // SMTP::DEBUG_CLIENT = client messages
        // SMTP::DEBUG_SERVER = client and server messages
        $this->mail->SMTPDebug = 0;
        $this->Debugoutput = 'html';

        //Set the hostname of the mail server
        // $this->mail->Host = 'smtp.gmail.com';
        $this->mail->Host = 'seuSMTP';
        // use
        // $this->mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->mail->Port = 465;
        // $this->mail->Port = 993;

        //Set the encryption mechanism to use - STARTTLS or SMTPS
        // $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        // O método PHPMailer::ENCRYPTION_STARTTL não existe nesta versão do phpmailer e foi alterado
        $this->mail->SMTPSecure = 'ssl';

        //Whether to use SMTP authentication
        $this->mail->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        $this->mail->Username = Mailer::USERNAME;

        //Password to use for SMTP authentication
        $this->mail->Password = Mailer::PASSWORD;

        //Set who the message is to be sent from
        $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

        //Set an alternative reply-to address
        // $this->mail->addReplyTo('replyto@example.com', 'First Last');

        //Set who the message is to be sent to
        $this->mail->addAddress($toAdress, $toName);

        //Set the subject line
        $this->mail->Subject = $subject;

        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $this->mail->msgHTML($html);

        //Replace the plain text body with one created manually
        $this->mail->AltBody = 'This is a plain-text message body';

        //Attach an image file
        // $this->mail->addAttachment('images/phpmailer_mini.png');

    }

    // envia o e-mail com o código de reset para o usuário
    public function send()
    {

        return $this->mail->send();

    }
    
}