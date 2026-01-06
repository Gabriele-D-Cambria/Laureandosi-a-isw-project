<?php

declare(strict_types=1);

require_once  implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'includes',"definitions.php"]);
require_once joinPath(__DIR__, "CalcoloReportistica.php");
require_once joinPath(__DIR__, "..", "..", "vendor", "autoload.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailSender{
	private MailInfo $mailInfo;


	public function __construct(string $cdl) {
		$this->mailInfo = CalcoloReportistica::getInstance()->getMailInfo($cdl);
	}

	public function inviaMail(string $mailTo, string $attachmentFile) : bool{
		$mail = new PHPMailer();
		
		$mail->IsSMTP();

		$mail->Host = $this->mailInfo->host;
		$mail->SMTPSecure = "tls";
		$mail->SMTPAuth = false;
		$mail->Port = 25;

		$mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
		
		$mail->setFrom( $this->mailInfo->fromMail, $this->mailInfo->fromName);
		$mail->AddAddress($mailTo);

		$mail->Subject = $this->mailInfo->subject;
		$mail->Body = $this->mailInfo->body;

		if(file_exists($attachmentFile)){
			$mail->addAttachment($attachmentFile);
			$ret = $mail->Send();
		
			$mail->SmtpClose();
			return $ret;
		}
		
		return false;		
	}
}