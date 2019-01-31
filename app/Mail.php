<?php
namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{
	public function send($to,$from,$body) {
		$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
		try {
			//Server settings
			$mail->SMTPDebug = 0;                                 // Enable verbose debug output
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = '	smtp.mailtrap.io';  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = '85dc606515ca45';                 // SMTP username
			$mail->Password = 'a0862f19651833';                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = 2525;                                    // TCP port to connect to

			//Recipients
			$mail->setFrom($to, 'Tom');
			$mail->addAddress($from, 'andrew');     // Add a recipient
			// $mail->addAddress('ellen@example.com');               // Name is optional
			// $mail->addReplyTo('info@example.com', 'Information');
			// $mail->addCC('cc@example.com');
			// $mail->addBCC('bcc@example.com');

			//Attachments
			// $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
			// $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

			//Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = 'We Mak';
			$mail->Body = $body;
			$mail->AltBody = '';

			$mail->send();

			$success = true;
			$error = false;
			$message = 'Mail Sent Successfully Please Check Your Mail!';

			return compact('success','error','message');
		} catch (Exception $e) {
			$success = false;
			$error = true;
			$message = 'Mail could not be sent. Mailer Error: ' . $mail->ErrorInfo;

			return compact('success','error','message');
		}
	}
}