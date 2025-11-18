<?php


class Email extends CI_Model {

        private $fromemail="pedidospromoopcion@gmail.com";

        public function sendMail(
          $toemail = array(), 
          $bcc = array(),
          $subject, 
          $message,
          $from = "Tienda Web",
          $reply_to = "pedidospromoopcion@gmail.com"
          ){

          $subject = str_replace("\n","",$subject);
          $message = str_replace("\n","",$message);
          $message = str_replace("\r","",$message);

          $subject= $this->db->escape_str($subject);
          //$message= $this->db->escape_str($message);

          date_default_timezone_set("America/mexico_city");

          // Load PHPMailer library
          $this->load->library('phpmailer_lib');
          // PHPMailer object
          $mail = $this->phpmailer_lib->load();
          // SMTP configuration
          $mail->IsSMTP(); // establecemos que utilizaremos SMTP
          //$mail->SMTPDebug = 3;  // debugging: 1 = errors and messages, 2 = messages only
          //$mail->Debugoutput = 'html';
          //$mail->SMTPDebug = 2;
          /*
          $mail->Host = 'smtp.office365.com';
          $mail->Port       = 587;
          $mail->Username   = "web@promoopcion.com";  // la cuenta de correo GMail
          $mail->Password   = "Wpr0m001";            // password de la cuenta GMail
          $mail->setFrom("web@promoopcion.com", $from);  //Quien envía el correo
          $mail->SMTPSecure = 'tls';//'tls';  // establecemos el prefijo del protocolo seguro de comunicación con el servidor
          $mail->setFrom("web@promoopcion.com", $from);
          */

          $mail->IsSMTP();
          $mail->Host = 'smtp.promocionalesenlinea.org';
          $mail->Port = "465";
          $mail->Username = "web@promocionalesenlinea.org"; // la cuenta de correo GMail
          $mail->Password = "Pm46de9ec"; // password de la cuenta GMail
          $mail->setFrom("web@promocionalesenlinea.org", $from); //Quien envía el correo
          $mail->SMTPSecure = 'ssl';//'tls'; // establecemos el prefijo del protocolo seguro de comunicación con el servidor


          $mail->SMTPAuth = true; // habilitamos la autenticación SMTP
          //$mail->SMTPAutoTLS = false;

          //$mail->Helo = "outlook.office365.com"; //Muy importante para que llegue a hotmail y otros$mail->Host       = "correo.promoopcion.com";      // establecemos GMail como nuestro servidor SMTP
          // establecemos el puerto SMTP en el servidor de GMail
          $mail->AltBody = "";
          $mail->CharSet = 'UTF-8';


          foreach ($toemail as $to) {
            $mail->addAddress($to);
          }

          foreach ($bcc as $to) {
            $mail->AddBCC($to);
          }
// Para borrar //
          $mail->AddBCC('desarrolloweb@promoopcion.com');
          $mail->AddBCC('plataformasdigitales@promoopcion.com');

//  Para borrar ///

          $mail->Subject   = $subject;  //Asunto del mensaje

          $mail->isHTML(true);
          $mail->Body      = $message;
          $mail->AltBody   = "";

          $mail->SMTPOptions = array(
          	'ssl' => array(
          		'verify_peer' => false,
          		'verify_peer_name' => false,
          		'allow_self_signed' => true
          	)
          );


          if(!$mail->Send()) {
            echo "<br>Error en el envío: " . $mail->ErrorInfo;
            return FALSE;
          } else {
            return TRUE;
          }
        }

}
