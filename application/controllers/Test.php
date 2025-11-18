

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

  public function index()
	{
    $id_app = 1;

    $file_url = "";
    switch ($id_app) {
      case '1':
        $file_url = base_url()."file/aviso-de-privacidad/2";
        break;
      case '2':
        $file_url = base_url()."file/aviso-de-privacidad/3";
        break;
      case '3':
        $file_url = base_url()."file/aviso-de-privacidad/5";
        break;
    }

    $data_msg=array();
    $data_msg['store_path'] = '';
    $data_msg['site_url'] = 'https://www.promocionalesenlinea.com/';
    $data_msg['file_url'] = $file_url;
    $data_msg['color1'] = '#000000';
    $data_msg['color1_text'] = '#FFFFFF';
    $data_msg['color2'] = '#000000';
    $data_msg['welcome_msg'] = 'test';

    $body_html = $this->load->view('email_master/template/header',$data_msg,TRUE);
    $body_html .= $this->load->view('email_master/welcome',$data_msg,TRUE);
    $body_html .= $this->load->view('email_master/template/footer',$data_msg,TRUE);

    $title = "TU TIENDA HA SIDO CREADA";

    $this->load->model('Email');
    $this->Email->sendMail(
      array('bmdz.acos@gmail.com'),array(),$title,$body_html
    );

    echo "ENVIADO";

	}
}

?>
