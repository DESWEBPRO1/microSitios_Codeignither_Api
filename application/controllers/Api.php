<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {


  public $media_path = 'media/';
  public $media_url = 'https://api.promocionalesenlinea.com/media/';

  public $min_tb_number = 0;
  public $max_tb_number = 14;

  public $_temp = FALSE;

  public function index()
	{
	}

  public function get_labels(){
    $labels = $this->db->query("SELECT text_name, text_value from sys_texts order by text_name ASC")->result_array();

    $result = array();
    foreach ($labels as $label) {
      $result[$label['text_name']] = $label['text_value'];
    }
    return $result;
  }

  public function image ($image_name){

      $query = "SELECT encode(img, 'base64') as image FROM sys_images where image_name = '".$image_name."' LIMIT 1";

      $result = $this->db->query($query)->result_array();
      $data = "";
      if (count($result)>0 && $result[0]['image'] != ""){
        $data = explode(';base64,',base64_decode($result[0]['image']))[1];
      }

      header("Content-Type: image/png");
      if ($data == ""){
  			$data = $default_img;
  		}

		  echo base64_decode($data);
  }

  public function brand ($brand_name){

      $query = "SELECT encode(img, 'base64') as image FROM sys_brand where id = '".$brand_name."' LIMIT 1";

      $result = $this->db->query($query)->result_array();
      $data = "";
      if (count($result)>0 && $result[0]['image'] != ""){
        $data = explode(';base64,',base64_decode($result[0]['image']))[1];
      }

      header("Content-Type: image/png");
      if ($data == ""){
  			$data = $default_img;
  		}

		  echo base64_decode($data);
  }

  public function file($file_name, $id = ""){

        $query = "";
        $query_2 = "";
        if ($id != ""){
          $query = "SELECT encode(file, 'base64') as file FROM sys_files where name = '".$file_name."' and id = '".$id."' LIMIT 1";
        } elseif (isset($_GET['t'])) {
          $query = "SELECT encode(file, 'base64') as file FROM sys_files where name = '".$file_name."' and id_app = (SELECT id_app FROM sys_user_customer where id = (SELECT id_user_customer FROM sys_session WHERE md5(concat(id,'-',id_user_customer)) = '".$_GET['t']."' LIMIT 1) limit 1 ) LIMIT 1";
        } elseif (isset($_GET['c'])) {
          $query = "SELECT encode(file, 'base64') as file FROM sys_files where name = '".$file_name."' and id_app = (SELECT id FROM sys_app where LOWER(code) = lower('".$_GET['c']."') LIMIT 1) LIMIT 1";
        } elseif (isset($_GET['sub']) && isset($_GET['s'])) {
          //Obtener archivo de sub-micrositio
          $query_2 = "SELECT encode(file, 'base64') as file FROM sys_files where name = '".$file_name."' and id_app = (SELECT id_app FROM sys_user_customer where LOWER(store_path) = LOWER('".$_GET['s']."') LIMIT 1) LIMIT 1";
          if ($file_name == "aviso-de-privacidad-micrositio"){
            $query = "SELECT encode(aviso, 'base64') as file FROM sys_customer_sites where aviso is not null and aviso <> '' and id_customer = (SELECT id FROM sys_user_customer where LOWER(store_path) = LOWER('".$_GET['s']."') LIMIT 1) and id = '".$_GET['sub']."' LIMIT 1";
          }
        } elseif (isset($_GET['s'])) {
          //Obtener archivo de micrositio
          $query_2 = "SELECT encode(file, 'base64') as file FROM sys_files where name = '".$file_name."' and id_app = (SELECT id_app FROM sys_user_customer where LOWER(store_path) = LOWER('".$_GET['s']."') LIMIT 1) LIMIT 1";
          if ($file_name == "aviso-de-privacidad-micrositio"){
            $query = "SELECT encode(aviso, 'base64') as file FROM sys_user_customer where aviso is not null and aviso <> '' and LOWER(store_path) = LOWER('".$_GET['s']."') LIMIT 1";
          }
        }

        $result = array();
        if ($query != ""){
          $result = $this->db->query($query)->result_array();
        }

        if (count ($result) == 0 && $query_2 != ""){
          $result = $this->db->query($query_2)->result_array();
        }

        $data = "";
        if (count($result)>0 && $result[0]['file'] != ""){
          $data = explode(';base64,',base64_decode($result[0]['file']))[1];
          header("Content-Type: application/pdf");
    		  echo base64_decode($data);
        } else {
          header("HTTP/1.0 404 Not Found");
        }

  }

  public function media($table_name, $primary_key, $field_name){

      $default_img = "/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAoKCgoKCgsMDAsPEA4QDxYUExMUFiIYGhgaGCIzICUgICUgMy03LCksNy1RQDg4QFFeT0pPXnFlZXGPiI+7u/sBCgoKCgoKCwwMCw8QDhAPFhQTExQWIhgaGBoYIjMgJSAgJSAzLTcsKSw3LVFAODhAUV5PSk9ecWVlcY+Ij7u7+//CABEIATwBPAMBIgACEQEDEQH/xAAVAAEBAAAAAAAAAAAAAAAAAAAAB//aAAgBAQAAAACtgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/9oACAECEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/8QAFAEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP/EABQQAQAAAAAAAAAAAAAAAAAAAKD/2gAIAQEAAT8ASB//xAAUEQEAAAAAAAAAAAAAAAAAAACA/9oACAECAQE/AEh//8QAFBEBAAAAAAAAAAAAAAAAAAAAgP/aAAgBAwEBPwBIf//Z";

      $table = $this->db->query("SELECT view_name,primary_key from sys_table_tables where table_name = '".$table_name."' limit 1")->result_array();

      $data = "";
      if (count($table)>0){
        $query = "SELECT field_name, html_format FROM sys_table_fields where field_name = '".$field_name."' and table_name = '".$table_name."'";

        $fields = $this->db->query($query)->result_array();

        if (count($fields)>0){

          switch ($fields[0]['html_format']) {
            case 'img':
              $result = $this->db->query("SELECT encode(".$fields[0]['field_name']. ", 'base64') AS image FROM ".$table[0]['view_name']. " WHERE md5(".$table[0]['primary_key']."::text) = '".$primary_key."'")->result_array();
              if (count($result)>0 && $result[0]['image'] != ""){
          			$data = explode(';base64,',base64_decode($result[0]['image']))[1];
              }
              break;
          }
        }
      }

      header("Content-Type: image/png");
      if ($data == ""){
  			$data = $default_img;
  		}

		   $data = base64_decode($data);
       echo $data;
       die();

        $size = 150;  // new image width
        $src = imagecreatefromstring($data);
        $width = imagesx($src);
        $height = imagesy($src);
        $aspect_ratio = $height/$width;

        if ($width <= $size) {
         $new_w = $width;
         $new_h = $height;
        } else {
         $new_w = $size;
         $new_h = abs($new_w * $aspect_ratio);
        }

        $img = imagecreatetruecolor($new_w,$new_h);
        imagecopyresized($img,$src,0,0,0,0,$new_w,$new_h,$width,$height);
        //echo base64_decode($data);
        imagejpeg($img);

  }

  public function media_store ($primary_key, $store_name){

      $default_img = "/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAoKCgoKCgsMDAsPEA4QDxYUExMUFiIYGhgaGCIzICUgICUgMy03LCksNy1RQDg4QFFeT0pPXnFlZXGPiI+7u/sBCgoKCgoKCwwMCw8QDhAPFhQTExQWIhgaGBoYIjMgJSAgJSAzLTcsKSw3LVFAODhAUV5PSk9ecWVlcY+Ij7u7+//CABEIATwBPAMBIgACEQEDEQH/xAAVAAEBAAAAAAAAAAAAAAAAAAAAB//aAAgBAQAAAACtgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/9oACAECEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/8QAFAEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAxAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP/EABQQAQAAAAAAAAAAAAAAAAAAAKD/2gAIAQEAAT8ASB//xAAUEQEAAAAAAAAAAAAAAAAAAACA/9oACAECAQE/AEh//8QAFBEBAAAAAAAAAAAAAAAAAAAAgP/aAAgBAwEBPwBIf//Z";

      $result = $this->db->query("SELECT encode(logo, 'base64') AS image FROM sys_customer_sites WHERE id_customer = '".$primary_key."' and lower(store_path) = '". strtolower($store_name)."'")->result_array();
      if (count($result)>0 && $result[0]['image'] != ""){
        $data = explode(';base64,',base64_decode($result[0]['image']))[1];
      }

      header("Content-Type: image/png");
      if ($data == ""){
  			$data = $default_img;
  		}

		   $data = base64_decode($data);
       echo $data;
       die();

        $size = 150;  // new image width
        $src = imagecreatefromstring($data);
        $width = imagesx($src);
        $height = imagesy($src);
        $aspect_ratio = $height/$width;

        if ($width <= $size) {
         $new_w = $width;
         $new_h = $height;
        } else {
         $new_w = $size;
         $new_h = abs($new_w * $aspect_ratio);
        }

        $img = imagecreatetruecolor($new_w,$new_h);
        imagecopyresized($img,$src,0,0,0,0,$new_w,$new_h,$width,$height);
        //echo base64_decode($data);
        imagejpeg($img);

  }

  public function format_money ($number, $currency){
    $number = floatval($number);
    return $currency['sign_before'].number_format($number, $currency['decimal_places'],  $currency['decimal_separator'],  $currency['thousand_separator']). $currency['sign_after'];
  }

  public function place_order(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $response = array("success"=>0);

      $data = json_decode(base64_decode(file_get_contents('php://input')), true);


      //¿Se hace desde un micrositio o un submicrositio?



      if (isset($data['store_name']) && $data['store_name'] != ""){
        //TIENDA DE CLIENTE FINAL
        $store = $this->db->query("SELECT suc.id,scs.id as id_site, scs.color_primary,scs.color_secundary, scs.public_name, suc.number_phone,suc.store_path,
          suc.number_whatsapp, scs.display_stock, scs.display_price, scs.create_budget,scs.email_budget,scs.number_whatsapp_display,
          (SELECT lower(sales_org) from sys_app where id = suc.id_app limit 1) as app,
          scs.pct_iva,
          suc.number_whatsapp_countrycode as whatsapp_code,
          suc.id_app,
          suc.customer_currency, suc.text_budget_confirm
          from sys_customer_sites scs, sys_user_customer suc where scs.id_customer = suc.id
          AND suc.customer_currency is not null and suc.customer_level is not null
          AND LOWER(suc.store_path) = '".strtolower($data['store_path'])."'
          AND LOWER(scs.store_path) = '".strtolower($data['store_name'])."'
          and suc.active = true
          and scs.active = true
          and suc.visible = true LIMIT 1")->result_array();

      } else {
        //MICROSITIO
        $store = $this->db->query("SELECT id, color_primary,color_secundary, public_name, number_phone,store_path,
          number_whatsapp, pct_iva, display_stock, display_price, create_budget,public_name,email_budget,number_whatsapp_display,
          (SELECT lower(sales_org) from sys_app where id = sys_user_customer.id_app limit 1) as app,
          number_whatsapp_countrycode as whatsapp_code,
          id_app, customer_currency,text_budget_confirm
          from sys_user_customer where store_path = '".$data['store_path']."' and active = true and visible = true LIMIT 1")->result_array();

      }


      if (count($store) >0){
        $store = $store[0];

        $currency = $this->db->query("select sign_before, sign_after, decimal_places, '.' as decimal_separator, ',' as thousand_separator from sys_currency where currency = '".$store['customer_currency']."'")->result_array();
        if (count($currency)>0){
          $currency = $currency[0];

            $id_customer_site = "NULL";
            $id_customer_site_user = "NULL";
            if ($data['store_name'] != "" && $data['token'] != ""){
              $session = $this->db->query("select id_customer_site, id_customer_site_user from sys_session_sites where active = TRUE AND md5(CONCAT(id::text,'-',id_customer_site_user)) = '".$data['token']."' LIMIT 1")->result_array();
              if (count($session)>0){
                  $id_customer_site = "'".$session[0]['id_customer_site']."'";
                  $id_customer_site_user = "'".$session[0]['id_customer_site_user']."'";
              }
            }



            $type_budget = "";
            if ($store['create_budget'] == 'whatsapp' && $store['number_whatsapp'] != ""){
              $type_budget = 'whatsapp';
            } elseif ($store['create_budget'] == 'email' && $store['email_budget'] != ""){
              $type_budget = 'email';
            }

            $insert_id = $this->db->query("select getnextid_tbbudget(".$store['id'].") AS id;")->result_array()[0]['id'];

            $this->db->query("INSERT INTO tb_budget (id, id_app, origin,currency, id_customer,id_customer_site,id_customer_site_user,
              client,
              subtotal, taxes, total,
              product_count, product_detail, comments)
            VALUES ('".$insert_id."','".$store['id_app']."','".$type_budget."', '".$store['customer_currency']."','".$store['id']."',".$id_customer_site.",".$id_customer_site_user.",
              '".$data['cliente']."',
              '".$data['subtotal']."', '".$data['iva']."', '".$data['total']."',
              '".$data['product_count']."', '".$data['product_detail']."', '".$data['comments']."')");


            if ($currency['sign_before'] != ""){
              $currency['sign_before'] .= " ";
            }
            if ($currency['sign_after'] != ""){
              $currency['sign_after'] = " ".$currency['sign_after'];
            }

            switch ($type_budget) {
              case 'whatsapp':
                //Cotización por whatsapp
                $cod_salto = '%0D%0A';

                $message = urldecode("*¡Hola ".$store['public_name']."!*").$cod_salto.$cod_salto;

                $message .= urldecode("Estoy interesado en cotizar los siguientes artículos:").$cod_salto.$cod_salto;

                foreach ($data['info']['products'] as $product) {
                  $message .= urldecode("*".$product['cantidad']."* x ".$product['codigo']. " - ". strtoupper($product['producto']) ." " . strtoupper($product['color']) ." " . strtoupper($product['talla'])) .$cod_salto;
                }

                $message .= $cod_salto.urldecode("Mis datos de contacto son:").$cod_salto;

                $message .= $cod_salto.urldecode("*Cotización:* _". str_pad($insert_id, 6, "0", STR_PAD_LEFT) ."_").$cod_salto;

                $message .= $cod_salto.urldecode("*Nombre:* ". $data['info']['client']['firstName'] ."").$cod_salto;
                $message .= urldecode("*Empresa:* ". $data['info']['client']['company'] ."").$cod_salto;
                $message .= urldecode("*Correo electrónico:* ". $data['info']['client']['email'] ."").$cod_salto;
                $message .= urldecode("*Estado:* ". $data['info']['client']['state'] ."").$cod_salto;
                $message .= urldecode("*Ciudad:* ". $data['info']['client']['city'] ."").$cod_salto;
                $message .= urldecode("*Comentario:* ". $data['comments'] ."").$cod_salto;

                $response['url'] = 'https://wa.me/'.$store['whatsapp_code'].$store['number_whatsapp'].'/?text='. $message .'&source=&data=';

                $response['success'] = 1;
                $response['text_budget_confirm'] = $store['text_budget_confirm'];
                break;
              case 'email':
                //Cotización por email

                $this->load->model('Email');

                $title = 'Cotización recibida #'.str_pad($insert_id, 6, "0", STR_PAD_LEFT);

                $data['insert_id'] = str_pad($insert_id, 6, "0", STR_PAD_LEFT);
                $data['site_url'] = 'https://www.promocionalesenlinea.com/'.$store['store_path'].'/';

                if ($data['store_name'] != ""){
                  $data['site_url'] .= 'tienda/'.$data['store_name'];
                  $data['store_logo'] = base_url() . "media/tcustomer_sites/".md5($store['id_site'])."/logo";

                }
                $data['store'] = $store;
                $data['color1'] = $store['color_primary'];
                $data['color1_text'] = $this->getContrastColor($store['color_primary']);
                $data['color2'] = $store['color_secundary'];
                $data['pct_iva'] =intval($store['pct_iva']);
                $data['currency'] = $currency;

                /*
                $data['subtotal'] = $this->format_money($data['subtotal'],$currency);
                $data['iva'] = $this->format_money($data['iva'],$currency);
                $data['total'] = $this->format_money($data['total'],$currency);
                */

                $body_html = $this->load->view('email/template/header',$data,TRUE);
                $body_html .= $this->load->view('email/budget',$data,TRUE);
                $body_html .= $this->load->view('email/template/footer',$data,TRUE);

                $this->Email->sendMail(
                  array($store["email_budget"]),array(),$title,$body_html
                );

                $response['success'] = 1;
                $response['text_budget_confirm'] = $store['text_budget_confirm'];
                break;
            }
          }


      }

      echo json_encode($response);
  }



  public function place_purchase(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $response = array("success"=>0);

      $data = json_decode(base64_decode(file_get_contents('php://input')), true);

      //Obtener ID de usuario con $data['token']
      $session = $this->db->query("select id_customer_site, id_customer_site_user from sys_session_sites where active = TRUE AND md5(CONCAT(id::text,'-',id_customer_site_user)) = '".$data['token']."' LIMIT 1")->result_array();
      if (count($session)>0){
        $id_customer_site = $session[0]['id_customer_site'];
        $id_customer_site_user = $session[0]['id_customer_site_user'];

        $credit_ammount = $this->db->query("SELECT credit_ammount from sys_customer_sites_users where id_customer_site = '".$id_customer_site."' and id = '".$id_customer_site_user."' LIMIT 1 ")->result_array()[0]['credit_ammount'];

        if ($credit_ammount >= $data['total_number']){
          /*
          $store = $this->db->query("SELECT id, color_primary,color_secundary, public_name, number_phone,store_path,
            number_whatsapp, display_stock, display_price, create_budget,public_name,email_budget,number_whatsapp_display,
            (SELECT lower(sales_org) from sys_app where id = sys_user_customer.id_app limit 1) as app,
            number_whatsapp_countrycode as whatsapp_code,
            id_app, customer_currency,text_budget_confirm
            from sys_user_customer where store_path = '".$data['store_path']."' and active = true and visible = true LIMIT 1")->result_array();
          */
          $store = $this->db->query("SELECT suc.id,scs.id as id_site, scs.color_primary,scs.color_secundary, scs.public_name, suc.number_phone,suc.store_path,
            suc.number_whatsapp, scs.display_stock, scs.display_price, scs.create_budget,scs.email_budget,scs.number_whatsapp_display,
            (SELECT lower(sales_org) from sys_app where id = suc.id_app limit 1) as app,
            suc.number_whatsapp_countrycode as whatsapp_code,
            suc.id_app,
            scs.pct_iva,
            suc.customer_currency, suc.text_budget_confirm
            from sys_customer_sites scs, sys_user_customer suc where scs.id_customer = suc.id
            AND suc.customer_currency is not null and suc.customer_level is not null
            AND LOWER(suc.store_path) = '".strtolower($data['store_path'])."'
            AND LOWER(scs.store_path) = '".strtolower($data['store_name'])."'
            and suc.active = true
            and scs.active = true
            and suc.visible = true LIMIT 1")->result_array();

          if (count($store) >0){
            $store = $store[0];

            $currency = $this->db->query("select sign_before, sign_after, decimal_places, '.' as decimal_separator, ',' as thousand_separator from sys_currency where currency = '".$store['customer_currency']."'")->result_array();
            if (count($currency)>0){
              $currency = $currency[0];
              $type_budget = "";
              if ($store['create_budget'] == 'whatsapp' && $store['number_whatsapp'] != ""){
                $type_budget = 'whatsapp';
              } elseif ($store['create_budget'] == 'email' && $store['email_budget'] != ""){
                $type_budget = 'email';
              }

              $insert_id = $this->db->query("select getnextid_tbpurchase(".$store['id'].") AS id;")->result_array()[0]['id'];

              $this->db->query("INSERT INTO tb_purchase (id, id_app, origin,currency, id_customer,id_customer_site,id_customer_site_user,
                client,
                subtotal, taxes, total,
                product_count, product_detail, comments)
              VALUES ('".$insert_id."','".$store['id_app']."','".$type_budget."', '".$store['customer_currency']."','".$store['id']."','".$id_customer_site."','".$id_customer_site_user."',
                '".$data['cliente']."',
                '".$data['subtotal']."', '".$data['iva']."', '".$data['total']."',
                '".$data['product_count']."', '".$data['product_detail']."', '".$data['comments']."')");

              //Si hay algun producto "adicional" descontar cantidad de "stock"
              if (isset($data['product_detail_discount']) && is_array($data['product_detail_discount'])){
                foreach ($data['product_detail_discount'] as $product_stock_discount) {
                  $this->db->query("UPDATE tb_products_customer set stock = stock - ".intval($product_stock_discount['quantity'])."
                      where id_customer = '".$store['id']."' and item_code = '".$product_stock_discount['itemcode']."' ");
                }
              }

              $this->db->query("UPDATE sys_customer_sites_users set credit_ammount = (credit_ammount - ".$data['total_number'].")
              where id_customer = '".$store['id']."' and id_customer_site = '".$id_customer_site."' and id = '".$id_customer_site_user."'");

              $response['credit_ammount'] = $this->db->query("SELECT credit_ammount from sys_customer_sites_users where id_customer = '".$store['id']."' and id_customer_site = '".$id_customer_site."' and id = '".$id_customer_site_user."' LIMIT 1 ")->result_array()[0]['credit_ammount'];

              if ($currency['sign_before'] != ""){
                $currency['sign_before'] .= " ";
              }
              if ($currency['sign_after'] != ""){
                $currency['sign_after'] = " ".$currency['sign_after'];
              }

              switch ($type_budget) {
                case 'whatsapp':
                  //Cotización por whatsapp
                  $cod_salto = '%0D%0A';

                  $message = urldecode("*¡Hola ".$store['public_name']."!*").$cod_salto.$cod_salto;

                  $message .= urldecode("Realicé un pedido por los siguientes artículos:").$cod_salto.$cod_salto;

                  foreach ($data['info']['products'] as $product) {
                    $message .= urldecode("*".$product['cantidad']."* x ".$product['codigo']. " - ". strtoupper($product['producto']) ." " . strtoupper($product['color']) ." " . strtoupper($product['talla'])) .$cod_salto;
                  }

                  $message .= $cod_salto.urldecode("Mis datos de contacto son:").$cod_salto;

                  $message .= $cod_salto.urldecode("*Pedido:* _". str_pad($insert_id, 6, "0", STR_PAD_LEFT) ."_").$cod_salto;

                  $message .= $cod_salto.urldecode("*Nombre:* ". $data['info']['client']['firstName'] ."").$cod_salto;

                  $message .= urldecode("*Empresa:* ". $data['info']['client']['company'] ."").$cod_salto;

                  $message .= urldecode("*Correo electrónico:* ". $data['info']['client']['email'] ."").$cod_salto;

                  $message .= urldecode("*Estado:* ". $data['info']['client']['state'] ."").$cod_salto;

                  $message .= urldecode("*Ciudad:* ". $data['info']['client']['city'] ."").$cod_salto;

                  $message .= urldecode("*Comentario:* ". $data['comments'] ."").$cod_salto;

                  $response['url'] = 'https://wa.me/'.$store['whatsapp_code'].$store['number_whatsapp'].'/?text='. $message .'&source=&data=';

                  $response['success'] = 1;
                  $response['text_budget_confirm'] = $store['text_budget_confirm'];
                  break;
                case 'email':
                  //Cotización por email

                  $this->load->model('Email');

                  $title = 'Pedido recibido #'.str_pad($insert_id, 6, "0", STR_PAD_LEFT);

                  $data['insert_id'] = str_pad($insert_id, 6, "0", STR_PAD_LEFT);
                  $data['store_logo'] = base_url() . "media/tcustomer_sites/".md5($store['id_site'])."/logo";
                  $data['site_url'] = 'https://www.promocionalesenlinea.com/'.$store['store_path'].'/tienda/'.$data['store_name'];
                  $data['store'] = $store;
                  $data['color1'] = $store['color_primary'];
                  $data['color1_text'] = $this->getContrastColor($store['color_primary']);
                  $data['color2'] = $store['color_secundary'];
                  $data['pct_iva'] =intval($store['pct_iva']);
                  $data['currency'] = $currency;

                  /*
                  $data['subtotal'] = $this->format_money($data['subtotal'],$currency);
                  $data['iva'] = $this->format_money($data['iva'],$currency);
                  $data['total'] = $this->format_money($data['total'],$currency);
                  */

                  $body_html = $this->load->view('email/template/header',$data,TRUE);
                  $body_html .= $this->load->view('email/purchase',$data,TRUE);
                  $body_html .= $this->load->view('email/template/footer',$data,TRUE);
                  /*
                  $this->Email->sendMail(
                    array($store["email_budget"]),array(),$title,$body_html
                  );
                  */

                  //Correo tienda (distribuidor) //Flujo normal
                  $this->Email->sendMail(
                    array($store["email_budget"]),array(),$title,$body_html
                  );

                  //Correo de administrador de tienda (micrositio) //Flujo agregado
                  $store_email = $this->db->query("select email from sys_customer_sites_users
                  where IS_ADMIN = true
                  and id_customer_site = (select id from sys_customer_sites where id_customer = '".$store['id']."' and LOWER(store_path) = lower('".$data['store_name']."') LIMIT 1) limit 1")->result_array();
                  if (count($store_email) >0){
                    $store_email = $store_email[0]['email'];
                    $this->Email->sendMail(
                      array($store_email),array(),$title,$body_html
                    );
                  }

                  $response['success'] = 1;
                  $response['text_budget_confirm'] = $store['text_budget_confirm'];
                  break;
              }


              }


          }
        } else {
          $response['error'] = 'Crédito insuficiente';
        }



      }


      echo json_encode($response);
  }

  function getContrastColor($hexColor)
  {
          // hexColor RGB
          $R1 = hexdec(substr($hexColor, 1, 2));
          $G1 = hexdec(substr($hexColor, 3, 2));
          $B1 = hexdec(substr($hexColor, 5, 2));

          // Black RGB
          $blackColor = "#000000";
          $R2BlackColor = hexdec(substr($blackColor, 1, 2));
          $G2BlackColor = hexdec(substr($blackColor, 3, 2));
          $B2BlackColor = hexdec(substr($blackColor, 5, 2));

           // Calc contrast ratio
           $L1 = 0.2126 * pow($R1 / 255, 2.2) +
                 0.7152 * pow($G1 / 255, 2.2) +
                 0.0722 * pow($B1 / 255, 2.2);

          $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
                0.7152 * pow($G2BlackColor / 255, 2.2) +
                0.0722 * pow($B2BlackColor / 255, 2.2);

          $contrastRatio = 0;
          if ($L1 > $L2) {
              $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
          } else {
              $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
          }

          // If contrast is more than 5, return black color
          if ($contrastRatio > 5) {
              return '#000000';
          } else {
              // if not, return white color.
              return '#FFFFFF';
          }
  }

  public function get_store(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $response = array("success"=>0);

      $data = json_decode(file_get_contents('php://input'), true);

      if (isset($data['store_name']) && $data['store_name'] != ""){
        //TIENDA DE CLIENTE FINAL
        $store = $this->db->query("SELECT suc.id, suc.store_path,scs.color_primary,scs.color_secundary, scs.public_name, suc.number_phone,
          suc.number_whatsapp, scs.display_stock, scs.display_price, scs.create_budget,scs.email_budget,scs.number_whatsapp_display,
          (SELECT lower(sales_org) from sys_app where id = suc.id_app limit 1) as app,
          scs.pct_iva,
          scs.texto_iva,
          suc.number_whatsapp_countrycode as whatsapp_code,
          suc.id_app,
          suc.customer_currency, suc.key, scs.id_categories, scs.id as id_store, suc.active_sites,
          case when (suc.active_2_1_products AND scs.display_by_sku) THEN TRUE ELSE FALSE END AS display_by_sku
          from sys_customer_sites scs, sys_user_customer suc where scs.id_customer = suc.id
          AND suc.customer_currency is not null and suc.customer_level is not null
          AND LOWER(suc.store_path) = '".strtolower($data['store_path'])."'
          AND LOWER(scs.store_path) = '".strtolower($data['store_name'])."'
          and suc.active = true
          and scs.active = true
          and suc.visible = true LIMIT 1")->result_array();

          $id_store = $store[0]['id_store'];

      } else {
        //MICROSITIO
        $store = $this->db->query("SELECT id, store_path,color_primary,color_secundary, public_name, number_phone,
          number_whatsapp, display_stock, display_price, create_budget,public_name,email_budget,number_whatsapp_display,
          (SELECT lower(sales_org) from sys_app where id = sys_user_customer.id_app limit 1) as app,
          pct_iva,
          texto_iva,
          number_whatsapp_countrycode as whatsapp_code,
          id_app,
          customer_currency, key, active_sites,
          case when (active_2_1_products AND display_by_sku) THEN TRUE ELSE FALSE END AS display_by_sku
          from sys_user_customer where customer_currency is not null and customer_level is not null and
          LOWER(store_path) = '".strtolower($data['store_path'])."' and active = true and visible = true LIMIT 1")->result_array();

        $id_store = "";
      }


      if (count($store) >0){

        $response['success'] = 1;
        $response['store'] = $store[0];

        if ($response['store']['color_primary'] == ""){
          $response['store']['color_primary'] = '#8A8A8A';
        }
        if ($response['store']['color_secundary'] == ""){
          $response['store']['color_secundary'] = '#BABABA';
        }


        $response['labels']= $this->get_labels();
        $response['brands']= $this->db->query("SELECT id, brand from sys_brand order by brand ASC")->result_array();

        if (isset($response['store']['id_store']) && $response['store']['id_store'] != ""){
          //TIENDA DE CLIENTE FINAL
          $response['store']['banners'] = $this->db->query("SELECT id FROM sys_customer_sites_banners where id_customer = '".$store[0]['id']."' AND  id_customer_site = '".$store[0]['id_store']."' ORDER BY order_pos ASC")->result_array();
        } else {
          $response['store']['banners'] = $this->db->query("SELECT id FROM tb_banners where id_customer = '".$store[0]['id']."' AND id_customer_store IS NULL ORDER BY order_pos ASC")->result_array();
        }

        $useragent=$_SERVER['HTTP_USER_AGENT'];
        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
          //MOBILE
          $response['whatsapp_url'] = 'whatsapp://send';
        } else {
          //WEB
          $response['whatsapp_url'] = 'https://api.whatsapp.com/send';
        }


        $response['currency'] = array();
        $currency = $this->db->query("select sign_before, sign_after, decimal_places,  '.' as decimal_separator, ',' as thousand_separator from sys_currency
        where currency = '".$store[0]['customer_currency']."'")->result_array();
        if (count($currency)>0){
          $response['currency'] = $currency[0];
        }

        $response['store']['categories'] = array();

        array_push($response['store']['categories'], array(
          "id"=> 0,
          "name"=> "TODAS LAS CATEGORÍAS",
          "hassubcategory"=> FALSE,
          "parentId"=> 0,
          "href"=>""
        ));

        $where_categories = "";
        $where_categories_esp = "";
        if ($store[0]['display_by_sku']){
          if ($id_store != ""){
            //TIENDA DE CLIENTE FINAL
            $where_categories .= " AND (select count(item_code) from tb_products_search where id_category = tb.id::character varying and (item_code IN(SELECT sku FROM tb_products_customer_display where id_customer = '".$store[0]['id']."' AND  id_customer_site = '".$id_store."') OR parent_code IN(SELECT sku FROM tb_products_customer_display where id_customer = '".$store[0]['id']."' AND  id_customer_site = '".$id_store."')) ) > 0 ";
            //$where_categories_esp .= " AND (select count(item_code) from tb_products_search where id_category = CONCAT(".$store[0]['id'].",'-',tb.id::character varying) and (item_code IN(SELECT sku FROM tb_products_customer_display where id_customer = '".$store[0]['id']."' AND id_customer_site = '".$id_store."') OR parent_code IN(SELECT sku FROM tb_products_customer_display where id_customer='".$store[0]['id']."' AND id_customer_site = '".$id_store."')) ) > 0 ";
          }else{
            $where_categories .= " AND (select count(item_code) from tb_products_search where id_category = tb.id::character varying and (item_code IN(SELECT sku FROM tb_products_customer_display where id_customer = '".$store[0]['id']."' AND id_customer_site is null) OR parent_code IN(SELECT sku FROM tb_products_customer_display where id_customer='".$store[0]['id']."' AND id_customer_site is null)) ) > 0 ";
            //$where_categories_esp .= " AND (select count(item_code) from tb_products_search where id_category = CONCAT(".$store[0]['id'].",'-',tb.id::character varying) and (item_code IN(SELECT sku FROM tb_products_customer_display where id_customer = '".$store[0]['id']."' AND id_customer_site is null) OR parent_code IN(SELECT sku FROM tb_products_customer_display where id_customer='".$store[0]['id']."' AND id_customer_site is null)) ) > 0 ";
          }
        }



        $mclist = $this->db->query("select id, category, href_path as href from tb_categories tb WHERE id_app = '".$store[0]['id_app']."' and id_parent is null")->result_array();
        foreach ($mclist as $mc) {
          $where_extra = "";
          if (!$store[0]['display_by_sku'] && isset($response['store']['id_categories'])){
            //TIENDA DE CLIENTE FINAL

            if ($response['store']['id_categories'] != ""){
              $where_extra .= " AND id in (".$response['store']['id_categories'].") ";
            } else {
              $where_extra .= " AND id in (".$response['store']['id_categories'].") ";
            }
          }

          $catlist = $this->db->query("select id, category, href_path as href
                                from tb_categories tb WHERE
                                id_parent = '".$mc['id']."' ". $where_extra. " ".$where_categories)->result_array();
          if (count($catlist)>0){

              array_push($response['store']['categories'],array(
                "id"=> $mc['id'],
                "name"=> $mc['category'],
                "hassubcategory"=> TRUE,
                "parentId"=> 0,
                "href"=>$mc['href']
              ));


              foreach ($catlist as $cat) {
                array_push($response['store']['categories'],array(
                  "id"=> $cat['id'],
                  "name"=> $cat['category'],
                  "hassubcategory"=> FALSE,
                  "parentId"=> $mc['id'],
                  "href"=>$cat['href']
                ));
              }
          }
        }

        $response['store']['categories_esp'] = array();

        if ($response['store']['active_sites'] == "1"){
          $response['store']['categories_esp'] = $this->db->query("SELECT CONCAT(id_customer,'-', id) as id, id as href, category as name FROM tb_categories_customer tb where id_customer = '".$store[0]['id']."' and active = TRUE ".$where_categories_esp)->result_array();
        }

      }

      echo base64_encode(json_encode($response));
  }

  public function get_price_key($store_currency, $customer_level = ''){
      //$price_key = 'price_'.strtolower($store_currency)."_".strtolower($customer_level);
      $price_key = 'price_'.strtolower($store_currency)."_base";

      return $price_key;
  }

  public function get_price_function($price_key, $store,$decimals = 2,  $min = FALSE, $max = FALSE, $det = FALSE){


      if ($max){
        $fn =  "MAX";
      } elseif ($min){
        $fn =  "MIN";
      } else {
        $fn = "";
      }

      //$pct_function_outlet = "0";
      if (isset($store['id_customer']) && $store['id_customer'] != ""){
        //Micrositio cliente final
        //$pct_function = "COALESCE((SELECT CASE WHEN MAX(pct) > 0 THEN MAX(pct) ELSE NULL END FROM tb_categories_margin tm where tm.id_customer = '".$store['id_customer']."' and  tm.id_customer_site = '".$store['id']."' and tm.id_category::character varying = tp.id_category::character varying LIMIT 1),su.pct)";
        $pct_function = "COALESCE(get_product_pct(".$store['id_customer'].",".$store['id'].", id_category ), su.pct)";
        $pct_function_outlet = "COALESCE(get_product_pct_outlet(".$store['id_customer'].",".$store['id'].", id_category ), su.pct_outlet)";

      } else {
        //$pct_function = "COALESCE((SELECT CASE WHEN MAX(pct) > 0 THEN MAX(pct) ELSE NULL END FROM tb_categories_margin tm where tm.id_customer = '".$store['id']."' and  tm.id_customer_site is null and tm.id_category::character varying = tp.id_category::character varying LIMIT 1), su.pct)";
        $pct_function = "COALESCE(get_product_pct(".$store['id'].",NULL, id_category ), su.pct)";
        $pct_function_outlet = "COALESCE(get_product_pct_outlet(".$store['id'].",NULL, id_category ), su.pct_outlet)";
      }

      $active_sites = $store['active_sites'];
      if ($active_sites == ""){
        $active_sites = "0";
      }

      //$pct_function = 20;

      //$attr_outlet_cond = "MAX(attr_outlet) = 0 AND MAX(attr_preciounico) = 0";
      if ($det){
        $attr_outlet_cond = "attr_outlet = 0 AND attr_preciounico = 0 AND attr_promocion = 0";
      } else {
        $attr_outlet_cond = "MAX(attr_outlet) = 0 AND MAX(attr_preciounico) = 0 AND MAX(attr_promocion) = 0";
      }

      $pct_gral_cond = "(
          CASE
          WHEN (su.pct = 0) THEN round( ((".$fn."(".$price_key.")*(1))) ,".$decimals.")
          WHEN (su.pct = 100) THEN round( ((".$fn."(".$price_key.")*(2))) ,".$decimals.")
          WHEN (su.pct > 0 AND su.pct < 100) THEN round( ((".$fn."(".$price_key.")/((100-(su.pct))*0.01))) ,".$decimals.")
          END
        )";


      //Su.pct
      $pct_gral_cond_outlet_part = "(
          CASE
          WHEN ((".$pct_function_outlet.") = 0) THEN round( ((".$fn."(".$price_key.")*(1))) ,".$decimals.")
          WHEN ((".$pct_function_outlet.") = 100) THEN round( ((".$fn."(".$price_key.")*(2))) ,".$decimals.")
          WHEN ((".$pct_function_outlet.") > 0 AND (".$pct_function_outlet.") < 100) THEN round( ((".$fn."(".$price_key.")/((100-(".$pct_function_outlet."))*0.01))) ,".$decimals.")
          END
        )";
      $pct_gral_cond_outlet = "(
          CASE
          WHEN (su.pct = 0) THEN round( (((".$pct_gral_cond_outlet_part.")*(1))) ,".$decimals.")
          WHEN (su.pct = 100) THEN round( (((".$pct_gral_cond_outlet_part.")*(2))) ,".$decimals.")
          WHEN (su.pct > 0 AND su.pct < 100) THEN round( (((".$pct_gral_cond_outlet_part.")/((100-(su.pct))*0.01))) ,".$decimals.")
          END
        )";

      $pct_function_cond = "(
          CASE
          WHEN (".$pct_function." = 0) THEN round( ((".$fn."(".$price_key.")*(1))) ,".$decimals.")
          WHEN (".$pct_function." = 100) THEN round( ((".$fn."(".$price_key.")*(2))) ,".$decimals.")
          WHEN (".$pct_function." > 0 AND ".$pct_function." < 100) THEN round( ((".$fn."(".$price_key.")/((100-(".$pct_function."))*0.01))) ,".$decimals.")
          END
        )";

      //10%+50% = calcular 1 vez por 60% = 115.89 > 289.73
      //INICIO
      /*
      $pct_function_cond_outlet = "(
          CASE
          WHEN ((".$pct_function."+".$pct_function_outlet.") = 0) THEN round( ((".$fn."(".$price_key.")*(1))) ,".$decimals.")
          WHEN ((".$pct_function."+".$pct_function_outlet.") = 100) THEN round( ((".$fn."(".$price_key.")*(2))) ,".$decimals.")
          WHEN ((".$pct_function."+".$pct_function_outlet.") > 0 AND (".$pct_function."+".$pct_function_outlet.") < 100) THEN round( ((".$fn."(".$price_key.")/((100-(".$pct_function."+".$pct_function_outlet."))*0.01))) ,".$decimals.")
          END
        )";
      */
      //FIN

      //10%+50% = calcular 1 vez por 10% y otra vez por 50% = 115.89 > 257.54
      //INICIO
      $pct_function_cond_outlet_part = "(
          CASE
          WHEN ((".$pct_function_outlet.") = 0) THEN round( ((".$fn."(".$price_key.")*(1))) ,".$decimals.")
          WHEN ((".$pct_function_outlet.") = 100) THEN round( ((".$fn."(".$price_key.")*(2))) ,".$decimals.")
          WHEN ((".$pct_function_outlet.") > 0 AND (".$pct_function_outlet.") < 100) THEN round( ((".$fn."(".$price_key.")/((100-(".$pct_function_outlet."))*0.01))) ,".$decimals.")
          END
        )";
      $pct_function_cond_outlet = "(
          CASE
          WHEN ((".$pct_function."+".$pct_function_outlet.") = 0) THEN round( (((".$pct_function_cond_outlet_part.")*(1))) ,".$decimals.")
          WHEN ((".$pct_function."+".$pct_function_outlet.") = 100) THEN round( (((".$pct_function_cond_outlet_part.")*(2))) ,".$decimals.")
          WHEN ((".$pct_function."+".$pct_function_outlet.") > 0 AND (".$pct_function."+".$pct_function_outlet.") < 100) THEN round( (((".$pct_function_cond_outlet_part.")/((100-(".$pct_function."))*0.01))) ,".$decimals.")
          END
        )";
        //FIN


        /*
        //1 - cuando no este disponible el margen por categoría (no outlet)
        //2 - cuando no este disponible el margen por categoría
        //3 - producto normal (no outlet)
        //4 - outlet
        */

        if (isset($store['id_customer']) && $store['id_customer'] != ""){
          //Micrositio cliente final

          $function = "((
            CASE
            WHEN (".$attr_outlet_cond.") AND (is_esp = true OR ((".$active_sites." = 0 OR pct_general = true)) ) THEN ".$pct_gral_cond."
            WHEN (is_esp = true OR ((".$active_sites." = 0 OR pct_general = true))) THEN ".$pct_gral_cond_outlet."
            WHEN (".$attr_outlet_cond.") AND pct_general = false THEN ".$pct_function_cond."
            WHEN (pct_general = false) THEN ".$pct_function_cond_outlet."
            END
            )+COALESCE(get_product_costo_adicional(".$store['id_customer'].",".$store['id'].", id_category),0))
          ";

        } else {

          //if ($store['id'] == 2){
          $function = "((
            CASE
            WHEN (".$attr_outlet_cond.") AND (is_esp = true OR ((".$active_sites." = 0 OR pct_general = true)) ) THEN ".$pct_gral_cond."
            WHEN (is_esp = true OR ((".$active_sites." = 0 OR pct_general = true))) THEN ".$pct_gral_cond_outlet."
            WHEN (".$attr_outlet_cond.") AND pct_general = false THEN ".$pct_function_cond."
            WHEN (pct_general = false) THEN ".$pct_function_cond_outlet."
            END
            )+COALESCE(get_product_costo_adicional(".$store['id'].",NULL, id_category),0))
          ";

        }


      return $function;
  }


  public function get_price_function_esp($price_key, $store,$decimals = 2,  $min = FALSE, $max = FALSE){


      if ($max){
        $fn =  "MAX";
      } elseif ($min){
        $fn =  "MIN";
      } else {
        $fn = "";
      }

      if (isset($store['id_customer']) && $store['id_customer'] != ""){
        $pct_function = "COALESCE((SELECT CASE WHEN MAX(pct) > 0 THEN MAX(pct) ELSE NULL END FROM tb_categories_margin tm where tm.id_customer = '".$store['id_customer']."' and  tm.id_customer_site = '".$store['id']."' and tm.id_category::character varying = tp.id_category::character varying LIMIT 1),su.pct)";
      } else {
        $pct_function = "COALESCE((SELECT CASE WHEN MAX(pct) > 0 THEN MAX(pct) ELSE NULL END FROM tb_categories_margin tm where tm.id_customer = '".$store['id']."' and  tm.id_customer_site is null and tm.id_category::character varying = tp.id_category::character varying LIMIT 1), su.pct)";
      }

      $active_sites = $store['active_sites'];
      if ($active_sites == ""){
        $active_sites = "0";
      }

      $function = "(
        CASE
        WHEN is_esp = true OR ((".$active_sites." = 0 OR pct_general = true) AND su.pct = 0) THEN round( ((".$fn."(".$price_key.")*(1))) ,".$decimals.")
        WHEN (".$active_sites." = 0 OR pct_general = true) AND su.pct = 100 THEN round( ((".$fn."(".$price_key.")*(2))) ,".$decimals.")
        WHEN (".$active_sites." = 0 OR pct_general = true) AND su.pct < 100 AND su.pct > 0 THEN round( ((".$fn."(".$price_key.")/((100-su.pct)*0.01))) ,".$decimals.")
        WHEN pct_general = false AND (".$pct_function.") = 0 THEN round( ((".$fn."(".$price_key.")*(1))) ,".$decimals.")
        WHEN pct_general = false AND (".$pct_function.") = 100 THEN round( ((".$fn."(".$price_key.")*(2))) ,".$decimals.")
        WHEN pct_general = false AND (".$pct_function.") < 100 AND (".$pct_function.") > 0 THEN round( ((".$fn."(".$price_key.")/((100-(".$pct_function."))*0.01))) ,".$decimals.") END
        )
      ";

      return $function;
  }

  public function get_products(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $response = array("success"=>0);

      $data = json_decode(file_get_contents('php://input'), true);

      $where_extra_store = "";

      $tb_number = rand($this->min_tb_number,$this->max_tb_number);

      if (isset($data['store_name']) && $data['store_name'] != ""){
        //TIENDA DE CLIENTE FINAL
        $store = $this->db->query("SELECT scs.id,suc.id as id_customer, suc.customer_level,suc.customer_currency,
          scs.display_stock, scs.display_price, scs.create_budget,
          scs.pct,scs.pct_general, COALESCE(scs.id_category_default, (select id_category_default from sys_app where id = suc.id_app LIMIT 1) ) AS id_category_default,
          (SELECT lower(code) from sys_app where id = suc.id_app limit 1) as app, suc.id_app, scs.id_categories,
          suc.active_sites,
          COALESCE(scs.id_category_default, (select id_category_default FROM sys_app where id = suc.id_app LIMIT 1)) AS id_category_default,
          case when (suc.active_2_1_products AND scs.display_by_sku) THEN TRUE ELSE FALSE END AS display_by_sku
          from sys_customer_sites scs, sys_user_customer suc where scs.id_customer = suc.id
          AND suc.store_path = '".$data['store_path']."'
          AND LOWER(scs.store_path) = '".strtolower($data['store_name'])."'
          and suc.active = true and suc.visible = true
          and scs.active = true and scs.visible = true LIMIT 1")->result_array();

          $store_table = "sys_customer_sites";

          $id_customer = $store[0]['id_customer'];
          $id_store = $store[0]['id'];

          $id_categories_ = "";


          foreach (explode(",",$store[0]['id_categories']) as $idcat) {
            if ($id_categories_ != ""){
              $id_categories_ .= ",";
            }
            $id_categories_ .= "'".$idcat."'";
          }

          if (!$store[0]['display_by_sku']){
            $where_categories = " AND ((is_esp = false AND id_category IN (". $id_categories_ .")) OR (is_esp = TRUE)) ";
          }
      } else {
        //MICROSITIO
        $store = $this->db->query("SELECT id, customer_level,customer_currency, display_stock, display_price, create_budget,
          pct,pct_general, COALESCE(id_category_default, (select id_category_default from sys_app where id = sys_user_customer.id_app LIMIT 1)) AS id_category_default,
          active_sites,
          COALESCE(id_category_default, (select id_category_default FROM sys_app where id = id_app LIMIT 1)) AS id_category_default,
          (SELECT lower(code) from sys_app where id = sys_user_customer.id_app limit 1) as app, id_app,
          case when (active_2_1_products AND display_by_sku) THEN TRUE ELSE FALSE END AS display_by_sku
          from sys_user_customer where store_path = '".$data['store_path']."' and active = true and visible = true LIMIT 1")->result_array();

          $store_table = "sys_user_customer";

          $id_customer = $store[0]['id'];
          $id_store = "";
      }

      if (count($store) >0){

        if ($store[0]['id_category_default'] == ""){
          $store[0]['id_category_default'] = "NULL";
        }

        $response['success'] = 1;

        $price_key = $this->get_price_key($store[0]['customer_currency']);

        $currency_decimals = $this->db->query("select decimal_places from sys_currency where currency = '".$store[0]['customer_currency']."' LIMIT 1")->result_array();
        $decimals = 2;
        if (count($currency_decimals)>0){
          $decimals = $currency_decimals[0]['decimal_places'];
        }
        $price_function = $this->get_price_function($price_key, $store[0], $decimals, FALSE, FALSE);
        $price_function_filter = $this->get_price_function($price_key, $store[0], $decimals, FALSE, FALSE, TRUE);


        $order = " CASE WHEN id_category = '".$store[0]['id_category_default']."' THEN 1 ELSE category_priority END DESC ";
        switch ($data['sort']) {
          case 'EXISTENCIAS':
            $order = " stock DESC, parent_code ASC, nombre ASC ";
            break;
          case 'MENOR PRECIO':
            $order = " price ASC, parent_code ASC, nombre ASC ";
            break;
          case 'MAYOR PRECIO':
            $order = " price DESC,
            parent_code ASC, nombre ASC ";
            break;
        }

        /*
        if ($store[0]['id'] == 2){
          //echo $order;
        }
        */

        $where = "";
        $where_filter = "";
        if (isset($data['category_path']) && $data['category_path'] != ""){
          $where .= " AND (href_path = '".$data['category_path']."' OR href_path like '".$data['category_path']."_%') ";
          $where_filter .= " AND (href_path = '".$data['category_path']."' OR href_path like '".$data['category_path']."_%') ";
        }
        if (isset($data['categories_selected']) && count($data['categories_selected'])>0){
          $id_categories_ = "";

          foreach ($data['categories_selected'] as $idcat) {
            if ($id_categories_ != ""){
              $id_categories_ .= ",";
            }
            $id_categories_ .= "'".$idcat."'";
          }
          $where .= " AND (id_category IN (".$id_categories_.") OR id_category_esp IN (".$id_categories_.") )";
        }
        if (isset($data['selected_colors']) && $data['selected_colors'] != ""){
          $where .= " AND LOWER(color_name) in (".strtolower($data['selected_colors']).") ";
        }

        $ignore = array(
          "de"
        );
        //OR unaccent_string(LOWER(material)) LIKE unaccent_string('%".strtolower($text)."%')

        $where_text = "";
        if (isset($data['filter_text']) && $data['filter_text'] != ""){
          foreach (explode(" ",$data['filter_text']) as $text) {
            if (!in_array($text, $ignore)){
              if ($where_text != ""){
                $where_text .= " AND ";//OR AND
              }
              $where_text .= " (
                REPLACE(unaccent_string(LOWER(item_code)),' ','') LIKE REPLACE(unaccent_string('%".strtolower($text)."%'),' ','')
                OR unaccent_string(LOWER(nombre)) LIKE unaccent_string('%".strtolower($text)."%')
                OR unaccent_string(LOWER(category)) LIKE unaccent_string('%".strtolower($text)."%')
                OR unaccent_string(LOWER(descripcion)) LIKE unaccent_string('%".strtolower($text)."%')
                OR unaccent_string(LOWER(keywords)) LIKE unaccent_string('%".strtolower($text)."%')
              ) ";
            }
          }
        }
        if ($where_text != ""){
            $where .= " AND (".$where_text.") ";
        }

        if (isset($data['filter_stock']) && $data['filter_stock'] != ""){
          $where .= " AND stock >= '".intval($data['filter_stock'])."' ";
        }

        if (isset($data['filter_price_min']) && $data['filter_price_min'] == "1"){
          if (isset($data['price_from']) && $data['price_from'] != ""){
            $where .= " AND ".$price_function_filter." >= '".floatval($data['price_from'])."' ";
          }
        }
        if (isset($data['filter_price_max']) && $data['filter_price_max'] == "1"){
          if (isset($data['price_to']) && $data['price_to'] != ""){
            $where .= " AND ".$price_function_filter." <= '".floatval($data['price_to'])."' ";
          }
        }

        $where_active_sites = "";
        if ($store[0]['active_sites'] == "1"){
          $where_active_sites = " OR (tp.id_customer = '".$id_customer."') ";
        }


        if ($store[0]['display_by_sku']){
          //Mostrar solo productos indicados en admin
          if ($id_store != ""){
            //TIENDA DE CLIENTE FINAL
            $where .= " AND (  (item_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site = '".$id_store."' ) OR parent_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site = '".$id_store."' ))) ";
            $where_filter .= " AND (  (item_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site = '".$id_store."'  ) OR parent_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site = '".$id_store."' ))) ";
          } else {
            $where .= " AND (  (item_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site is null ) OR parent_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site is null ))) ";
            $where_filter .= " AND (  (item_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site is null ) OR parent_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site is null ))) ";
          }
        }

        $response['products_count'] = count($this->db->query("select count(parent_code)
              from tb_products_search_".$tb_number." tp, ".$store_table." su
              WHERE tp.id_app = ".$store[0]['id_app']."
              AND su.id = '". $store[0]['id']."'
              AND ( tp.id_customer is null ".$where_active_sites.") ".$where. " ".$where_extra_store.
              " GROUP BY tp.id_app, parent_code, nombre, descripcion, tp.is_esp,tp.category_priority")->result_array());


        if (isset($data['load_filter']) && $data['load_filter'] == "1"){

          $_filter = $this->db->query("select MAX(stock) as stock,
                    ".$this->get_price_function($price_key, $store[0], $decimals, TRUE, FALSE)." AS min_price,
                    ".$this->get_price_function($price_key, $store[0], $decimals, FALSE, TRUE)." as max_price,
                    string_agg(DISTINCT(concat(';',color_name)),'|') as color_hex
                  from tb_products_search_".$tb_number." tp, ".$store_table." su
                  WHERE tp.id_app = ".$store[0]['id_app']."
                  AND ".$price_key." > 0
                  AND su.id = '". $store[0]['id']."'
                  AND ( tp.id_customer is null ".$where_active_sites.") ".$where_filter. " ".$where_extra_store."
                  GROUP BY su.pct_general , su.pct, su.pct_outlet, tp.id_category, tp.is_esp,tp.category_priority
                  ORDER BY color_hex ASC ")->result_array();


          $filter_result = array(
            "stock"=>0,
            "min_price"=>"",
            "max_price"=>"",
            "color_hex"=>""
          );

          $colorhex_list = array();

          foreach ($_filter as $fil) {

            foreach (explode("|",$fil['color_hex']) as $colorhex) {
              $colorhex = strtolower($colorhex);
              if (!in_array($colorhex,$colorhex_list)){
                array_push($colorhex_list, $colorhex);
                if ($filter_result['color_hex']  != ""){
                  $filter_result['color_hex']  .= "|";
                }
                $filter_result['color_hex'] .= $colorhex;
              }
            }

            if ($fil['stock'] > $filter_result['stock']){
              $filter_result['stock'] = $fil['stock'];
            }

            if ($filter_result['min_price'] == "" || $filter_result['min_price'] > $fil['min_price']){
              $filter_result['min_price'] = $fil['min_price'];
            }

            if ($filter_result['max_price'] == "" || $filter_result['max_price'] < $fil['max_price']){
              $filter_result['max_price'] = $fil['max_price'];
            }
          }
          $response['filter'] = $filter_result;
        }

        //price_".strtolower($store[0]['customer_currency'])."_base
        $query_products = "select parent_code,MAX(item_code) AS itemcode,
              MAX(nombre) AS nombre, MAX(descripcion) AS descripcion,
              string_agg(DISTINCT(color_hex),'|') as color_hex ,
              string_agg(DISTINCT(color_name),'|') as color ,
              string_agg(DISTINCT(id_category::varchar),'|') as id_category ,
              string_agg(DISTINCT(material::varchar),'|') as material ,
              string_agg(DISTINCT(talla),'|') as talla_list,
              CASE WHEN (starts_with(parent_code, 'PRODUCTO_'))
              THEN MAX(price_".strtolower($store[0]['customer_currency'])."_base)
              ELSE ".$this->get_price_function($price_key, $store[0],$decimals, FALSE, TRUE)." END AS price,
              CASE
                WHEN (is_esp = true) THEN CONCAT('".$this->media_url."',split_part(MAX(tp.images),'///',1))
                WHEN ('".$store[0]['display_by_sku']."'= '1' AND (SELECT custom_images FROM tb_products_customer_display where id_customer = '".$id_customer."' and (sku = max(tp.item_code) or sku = max(tp.parent_code)) limit 1) = true ) THEN  CONCAT('".$this->media_url."',split_part((SELECT images FROM tb_products_customer_display where id_customer = '".$id_customer."' and (sku = max(tp.item_code) or sku = max(tp.parent_code)) limit 1),'///',1))
                WHEN (count(parent_code)>1) THEN COALESCE((SELECT url from tb_products_images where id_app = tp.id_app and parent_code = tp.parent_code and type = 'padre' limit 1),(SELECT url from tb_products_images where id_app = tp.id_app and item_code = MAX(tp.item_code) and type = 'color' ORDER BY name ASC LIMIT 1))
                ELSE COALESCE((SELECT url from tb_products_images where id_app = tp.id_app and item_code = MAX(tp.item_code) and type = 'color' ORDER BY name ASC limit 1),(SELECT url from tb_products_images where id_app = tp.id_app and parent_code = MAX(tp.parent_code) and type = 'padre' ORDER BY name ASC limit 1))
              END as image,
              CASE WHEN (count(parent_code)>1) THEN 0 ELSE MAX(stock) END AS stock
              from tb_products_search_".$tb_number." tp, ".$store_table." su
              WHERE tp.id_app = ".$store[0]['id_app']." AND ".$price_key." > 0
              AND su.id = '". $store[0]['id']."'
              AND ( tp.id_customer is null ".$where_active_sites.") ".$where." ".$where_extra_store."
              GROUP BY tp.id_app, parent_code,su.pct_general, su.pct,su.pct_outlet, tp.id_category, tp.is_esp,tp.category_priority
              ORDER BY ".$order."
              limit ".$data['limit']. " offset ". (($data['page']-1)*$data['limit']);

        $response['products'] = $this->db->query($query_products)->result_array();
        $response['query'] = $query_products;
      }

      echo json_encode($response);
  }

  public function get_product(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $response = array("success"=>0);

      $data = json_decode(file_get_contents('php://input'), true);



      $tb_number = rand($this->min_tb_number,$this->max_tb_number);


      if (isset($data['store_name']) && $data['store_name'] != ""){
        //TIENDA DE CLIENTE FINAL
        $store = $this->db->query("SELECT  scs.id,suc.id as id_customer, suc.customer_level,suc.customer_currency,
          scs.pct, scs.pct_general, scs.id_category_default, scs.display_stock, scs.display_price, scs.create_budget,
          (SELECT lower(code) from sys_app where id = suc.id_app limit 1) as app, suc.id_app,
          suc.active_sites,
          case when (suc.active_2_1_products AND scs.display_by_sku) THEN TRUE ELSE FALSE END AS display_by_sku
          from sys_customer_sites scs, sys_user_customer suc where scs.id_customer = suc.id
          AND suc.store_path = '".$data['store_path']."'
          AND LOWER(scs.store_path) = '".strtolower($data['store_name'])."'
          and suc.active = true and suc.visible = true
          and scs.active = true and scs.visible = true LIMIT 1")->result_array();
          $store_table = "sys_customer_sites";

          $id_customer = $store[0]['id_customer'];
          $id_store = $store[0]['id'];

      } else {
        //MICROSITIO
        $store = $this->db->query("SELECT id, customer_level,customer_currency, pct, display_stock, display_price, create_budget,
          (SELECT lower(code) from sys_app where id = sys_user_customer.id_app limit 1) as app, id_app , active_sites,
          case when (active_2_1_products AND display_by_sku) THEN TRUE ELSE FALSE END AS display_by_sku
           from sys_user_customer
          where store_path = '".$data['store_path']."' and active = true and visible = true LIMIT 1")->result_array();
          $store_table = "sys_user_customer";

          $id_customer = $store[0]['id'];
          $id_store = "";
      }

      if (count($store) >0){

        $response['success'] = 1;

        $currency_decimals = $this->db->query("select decimal_places from sys_currency where currency = '".$store[0]['customer_currency']."' LIMIT 1")->result_array();
        $decimals = 2;
        if (count($currency_decimals)>0){
          $decimals = $currency_decimals[0]['decimal_places'];
        }

        $price_key = $this->get_price_key($store[0]['customer_currency']);


        if (strlen($data['parentcode']) >= 9 && strpos($data['parentcode'], 'PRODUCTO_') === 0) {
           // It starts with 'http'


            //INCORRECTO

            $product = $this->db->query("select parent_code,
                  max(nombre) as nombre, max(descripcion) as descripcion,
                  string_agg(DISTINCT(concat('',';',color_name)),'|') as color_hex,
                  string_agg(DISTINCT(concat(color_hex,';',item_code,';',stock,';',';',color_name)),'|') as stock_list,
                  string_agg(DISTINCT(talla),'|') as talla_list,
                  '' as talla,
                  string_agg(DISTINCT(printing_tech),'|') as printing_tech,
                  string_agg(DISTINCT(printing_area),'|') as printing_area,
                  string_agg(DISTINCT(material),'|') as material,
                  '' as capacidad,
                  string_agg(DISTINCT(tamano),'|') as tamano,
                  string_agg(DISTINCT(empaque_cantidad),'|') as empaque_cantidad,
                  string_agg(DISTINCT(producto_peso),'|') as producto_peso,
                  string_agg(DISTINCT(producto_altura),'|') as producto_altura,
                  string_agg(DISTINCT(producto_profundidad),'|') as producto_profundidad,
                  string_agg(DISTINCT(producto_base),'|') as producto_base,
                  MAX(price_".strtolower($store[0]['customer_currency'])."_base) as price,
                string_agg(DISTINCT(images),'///') as images_text
                  from tb_products_search_".$tb_number." tp, ".$store_table." su
                  WHERE tp.id_app = ".$store[0]['id_app']." AND parent_code = '".$data['parentcode']."'
                  AND su.id = '". $store[0]['id']."'
                  AND is_esp = true
                  AND ".$price_key." > 0
                  GROUP BY tp.id_app, parent_code, su.pct_general,su.pct, su.pct_outlet,tp.id_category, is_esp
                  limit 1")->result_array();




            if (count($product)>0){
              $response['product'] = $product[0];
              $response['product']['images'] = array();

              foreach (explode('///',$response['product']['images_text']) as $image) {
                if ($image != ""){
                  array_push($response['product']['images'],$this->media_url.$image);
                }
              }
              /*
              $images = $this->db->query("SELECT url from tb_products_images WHERE id_app = '".$store[0]['id_app']."' and parent_code = '".$data['parentcode']."' and type IN ('padre','color') ORDER BY type desc")->result_array();
              foreach ($images as $img) {
                array_push($response['product']['images'], $img['url']);
              }
              */
            }


        } else {


          $where = "";
          if ($store[0]['display_by_sku']){
            //Mostrar solo productos indicados en admin
            if($id_store != ""){
              $where .= " AND (item_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site = '".$id_store."') OR parent_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site = '".$id_store."')) ";
            } else {
              $where .= " AND (item_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $store[0]['id']."' and id_customer_site is null) OR parent_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $store[0]['id']."' and id_customer_site is null)) ";
            }
          }

          $product = $this->db->query("select parent_code,
                nombre, descripcion,
                string_agg(DISTINCT(concat('',';',color_name)),'|') as color_hex,
                string_agg(DISTINCT(concat(color_hex,';',item_code,';',stock,';',';',color_name)),'|') as stock_list,
                string_agg(DISTINCT(talla),'|') as talla_list,
                '' as talla,
                string_agg(DISTINCT(printing_tech),'|') as printing_tech,
                string_agg(DISTINCT(printing_area),'|') as printing_area,
                string_agg(DISTINCT(material),'|') as material,
                MAX(capacidad) as capacidad,
                string_agg(DISTINCT(tamano),'|') as tamano,
                string_agg(DISTINCT(empaque_cantidad),'|') as empaque_cantidad,
                string_agg(DISTINCT(producto_peso),'|') as producto_peso,
                string_agg(DISTINCT(producto_altura),'|') as producto_altura,
                string_agg(DISTINCT(producto_profundidad),'|') as producto_profundidad,
                string_agg(DISTINCT(producto_base),'|') as producto_base,
                ".$this->get_price_function($price_key, $store[0],$decimals, FALSE, TRUE)." as price,
                CASE WHEN ('".$store[0]['display_by_sku']."' = '1')
                THEN (SELECT string_agg(CONCAT('''',sku,''''),',') FROM tb_products_customer_display where id_customer = '".$id_customer."' and ( string_agg(CONCAT(';',tp.item_code,';'),',') LIKE CONCAT('%;',sku,';%') or sku = tp.parent_code) and custom_images = FALSE )
                ELSE '' END AS custom_images_sku,
                CASE
                  WHEN ('".$store[0]['display_by_sku']."' = '1' AND (SELECT MAX(custom_images::text) FROM tb_products_customer_display where id_customer = '".$id_customer."' and ( string_agg(CONCAT(';',tp.item_code,';'),',') LIKE CONCAT('%;',sku,';%') or sku = tp.parent_code) ) = 'true' )
                  THEN  (SELECT string_agg(images,'///') FROM tb_products_customer_display where id_customer = '".$id_customer."' and ( string_agg(CONCAT(';',tp.item_code,';'),',') LIKE CONCAT('%;',sku,';%') or sku = tp.parent_code) AND custom_images )
                  ELSE ''
                END as images_text
                from tb_products_search_".$tb_number." tp, ".$store_table." su
                WHERE tp.id_app = ".$store[0]['id_app']." AND parent_code = '".$data['parentcode']."'
                AND su.id = '". $store[0]['id']."'
                AND is_esp = FALSE
                AND ".$price_key." > 0 ". $where."
                GROUP BY tp.id_app, parent_code, nombre, descripcion,su.pct_general,su.pct, su.pct_outlet,tp.id_category, is_esp
                limit 1")->result_array();


           if (count($product)>0){
             $response['product'] = $product[0];
             $response['product']['images'] = array();

             if ($store[0]['display_by_sku'] == "1"){
               if ($response['product']['custom_images_sku'] != ''){
                 $images = $this->db->query("SELECT url from tb_products_images WHERE id_app = '".$store[0]['id_app']."' and parent_code = '".$data['parentcode']."' and type IN ('padre') ORDER BY type desc")->result_array();
                 foreach ($images as $img) {
                   array_push($response['product']['images'], $img['url']);
                 }
                 $images = $this->db->query("SELECT url from tb_products_images WHERE id_app = '".$store[0]['id_app']."' and parent_code = '".$data['parentcode']."' AND item_code IN (".$response['product']['custom_images_sku'] .") and type IN ('color') ORDER BY type desc")->result_array();
                 foreach ($images as $img) {
                   array_push($response['product']['images'], $img['url']);
                 }
               }

               foreach (explode('///',$response['product']['images_text']) as $image) {
                 if ($image != ""){
                   array_push($response['product']['images'],$this->media_url.$image);
                 }
               }
             } else {
               $images = $this->db->query("SELECT url from tb_products_images WHERE id_app = '".$store[0]['id_app']."' and parent_code = '".$data['parentcode']."' and type IN ('padre','color') ORDER BY type desc")->result_array();
               foreach ($images as $img) {
                 array_push($response['product']['images'], $img['url']);
               }
             }

           }
        }
      }

      echo json_encode($response);
  }

  public function get_product_det(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $response = array("success"=>0);

      $data = json_decode(file_get_contents('php://input'), true);

      $tb_number = rand($this->min_tb_number,$this->max_tb_number);

      if (isset($data['store_name']) && $data['store_name'] != ""){
        //TIENDA DE CLIENTE FINAL
        $store = $this->db->query("SELECT  scs.id,suc.id as id_customer,suc.active_min, suc.customer_level,suc.customer_currency,
          scs.pct,scs.pct_general, scs.id_category_default, scs.display_stock, scs.display_price, scs.create_budget,
          (SELECT lower(code) from sys_app where id = suc.id_app limit 1) as app, id_app,
          suc.active_sites,
          case when (suc.active_2_1_products AND scs.display_by_sku) THEN TRUE ELSE FALSE END AS display_by_sku
          from sys_customer_sites scs, sys_user_customer suc where scs.id_customer = suc.id
          AND suc.store_path = '".$data['store_path']."'
          AND LOWER(scs.store_path) = '".strtolower($data['store_name'])."'
          and suc.active = true and suc.visible = true
          and scs.active = true and scs.visible = true LIMIT 1")->result_array();
          $store_table = "sys_customer_sites";

          $id_customer = $store[0]['id_customer'];
          $id_store = $store[0]['id'];

      } else {
        //MICROSITIO
        $store = $this->db->query("SELECT id,'' as id_customer, active_min, customer_level,customer_currency, pct, display_stock, display_price, create_budget,
          (SELECT lower(code) from sys_app where id = sys_user_customer.id_app limit 1) as app, id_app,
          active_sites,
          case when (active_2_1_products AND display_by_sku) THEN TRUE ELSE FALSE END AS display_by_sku
          from sys_user_customer where store_path = '".$data['store_path']."' and active = true and visible = true LIMIT 1")->result_array();
          $store_table = "sys_user_customer";

          $id_customer = $store[0]['id'];
          $id_store = "";
      }

      if (count($store) >0){

        $currency_decimals = $this->db->query("select decimal_places from sys_currency where currency = '".$store[0]['customer_currency']."' LIMIT 1")->result_array();
        $decimals = 2;
        if (count($currency_decimals)>0){
          $decimals = $currency_decimals[0]['decimal_places'];
        }


        $price_key = $this->get_price_key($store[0]['customer_currency']);

        if (strlen($data['parentcode']) >= 9 && strpos($data['parentcode'], 'PRODUCTO_') === 0) {

          $product = $this->db->query("select item_code,parent_code,
                price_".strtolower($store[0]['customer_currency'])."_base as price,
                stock, color_name as color, nombre, talla, tamano, producto_peso,
                1 as is_adicional,
                1 as pzas_min,
                images as images_text
                from tb_products_search_".$tb_number." tp, ".$store_table." su
                WHERE tp.id_app = ".$store[0]['id_app']." AND parent_code = '".$data['parentcode']."'
                AND su.id = '". $store[0]['id']."'
                AND ".$price_key." > 0
                AND LOWER(color_name) = '".strtolower($data['color'])."' and talla = '".$data['talla']."'
                AND is_esp = TRUE
                ORDER BY color_name ASC
                limit 1")->result_array();

           if (count($product)>0){
             $response['success'] = 1;
             $response['product'] = $product[0];
             $response['product']['images'] = array();

             foreach (explode('///',$response['product']['images_text']) as $image) {
               if ($image != ""){
                 array_push($response['product']['images'],$this->media_url.$image);
               }
             }
           }
        } else {

          if ($store[0]['active_min'] == ""){
            $store[0]['active_min'] = "0";
          }

          $where_extra_catmin = " and id_customer_site is null ";

          if (isset($data['store_name']) && $data['store_name'] != ""){
            $where_extra_catmin = " and id_customer_site = '".$store[0]['id']."' ";
          }

          $where = "";
          if ($store[0]['display_by_sku']){
            //Mostrar solo productos indicados en admin
            if($id_store != ""){
              $where .= " AND (item_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site = '".$id_store."') OR parent_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $id_customer."' and id_customer_site = '".$id_store."')) ";
            } else {
              $where .= " AND (item_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $store[0]['id']."' and id_customer_site is null) OR parent_code IN (SELECT sku FROM tb_products_customer_display where id_customer = '". $store[0]['id']."' and id_customer_site is null)) ";
            }
          }

          $product = $this->db->query("select item_code,parent_code,
                ".$this->get_price_function($price_key, $store[0], $decimals, FALSE, FALSE, TRUE)." as price,
                stock, color_name as color, nombre, talla, tamano, producto_peso,
                0 as is_adicional,
                CASE WHEN ".$store[0]['active_min']." = '1'
                THEN COALESCE((SELECT pzas_min FROM tb_categories_margin WHERE id_customer = '".$id_customer."' ".$where_extra_catmin."  AND id_category::character varying = tp.id_category::character varying LIMIT 1),1)
                ELSE 1 END as pzas_min,
                CASE
                  WHEN ('".$store[0]['display_by_sku']."' = '1' AND (SELECT MAX(custom_images::text) FROM tb_products_customer_display where id_customer = '".$id_customer."' and (sku = tp.item_code or sku = tp.parent_code) ) = 'true' )
                  THEN  (SELECT string_agg(images,'///') FROM tb_products_customer_display where id_customer = '".$id_customer."' and (sku = tp.item_code or sku = tp.parent_code) AND custom_images )
                  ELSE '0'
                END as images_text
                from tb_products_search_".$tb_number." tp, ".$store_table." su
                WHERE tp.id_app = ".$store[0]['id_app']." AND parent_code = '".$data['parentcode']."'
                AND su.id = '". $store[0]['id']."'
                AND ".$price_key." > 0
                AND LOWER(color_name) = '".strtolower($data['color'])."' and talla = '".$data['talla']."'
                AND is_esp = FALSE ".$where."
                ORDER BY color_name ASC
                limit 1")->result_array();

           if (count($product)>0){

             $response['success'] = 1;
             $response['product'] = $product[0];
             $response['product']['images'] = array();

             if ($response['product']['images_text'] == "0"){
               $images = $this->db->query("SELECT url from tb_products_images WHERE
                 id_app = '".$store[0]['id_app']."'
                 and item_code = '".$product[0]['item_code']."' and
                 type IN ('color') ORDER BY name asc")->result_array();
               foreach ($images as $img) {
                 array_push($response['product']['images'], $img['url']);
               }
               $images = $this->db->query("SELECT url from tb_products_images WHERE
                 id_app = '".$store[0]['id_app']."'
                 and parent_code = '".$product[0]['parent_code']."'
                 and type IN ('padre','vector') ORDER BY type ASC")->result_array();
               foreach ($images as $img) {
                 array_push($response['product']['images'], $img['url']);
               }
             } else {

               foreach (explode('///',$response['product']['images_text']) as $image) {
                 if ($image != ""){
                   array_push($response['product']['images'],$this->media_url.$image);
                 }
               }

             }
           }
        }
      }

      echo json_encode($response);
  }

  public function getLoginInfo(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

    header("Content-Type: application/json");

    $response = array("success"=>1);

    $response['app'] = $this->db->query("SELECT id, sales_org, login_magento from sys_app where active = TRUE order by sales_org ASC")->result_array();

    $response['labels']= $this->get_labels();

    echo json_encode($response);
  }

  public function authentication()
	{

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

    header("Content-Type: application/json");

    $response = array("success"=>0);

    $data = json_decode(file_get_contents('php://input'), true);

    $app = $this->db->query("SELECT id, login_magento, code FROM sys_app where active = TRUE and sales_org = '".$data['sales_org']."' LIMIT 1")->result_array();

    if (count($app)>0){

      if (isset($data['store_name']) && $data['store_name'] != ""){

        if (isset($data['username']) && isset($data['password']) && $data['username'] != ''
        && $data['password'] != ''){
          //TIENDA DE CLIENTE FINAL
          $store = $this->db->query("SELECT suc.id as id_customer, scs.id as id_customer_site
            from sys_customer_sites scs, sys_user_customer suc where scs.id_customer = suc.id
            AND suc.store_path = '".$data['store_path']."'
            AND LOWER(scs.store_path) = '".strtolower($data['store_name'])."'
            and suc.active = true and suc.visible = true
            and scs.active = true and scs.visible = true
            AND suc.active_sites = true LIMIT 1")->result_array();

          if (count($store)>0){

            $user = $this->db->query("SELECT id FROM sys_customer_sites_users WHERE
              id_customer = '".$store[0]['id_customer']."' and id_customer_site = '".$store[0]['id_customer_site']."'
              and lower(email) = '".strtolower($data['username'])."'
              and password = '".$data['password']."'")->result_array();

            if (count($user)>0){
              $this->db->query("INSERT INTO sys_session_sites (id_customer, id_customer_site,id_customer_site_user, ip, useragent)
              VALUES ('".$store[0]['id_customer']."',
                '".$store[0]['id_customer_site']."',
                '".$user[0]['id']."',
                '".$_SERVER['REMOTE_ADDR']."',
                '".$_SERVER['HTTP_USER_AGENT']."')");

              $insert_id = $this->db->insert_id();
              $response['success'] = 1;
              $response['token'] = md5($insert_id."-".$user[0]['id']);
              $user[0]['id'] = "";
              $response['user'] = $user[0];

            } else{
                $response['error'] = 'Usuario y/o contraseña incorrectos.';
            }

          } else {
              $response['error'] = 'Tienda no disponible.';
          }

        } else{
            $response['error'] = 'Usuario y contraseña son requeridos.';
        }

      } elseif ($app[0]['login_magento'] == "1"){


        if (isset($data['username']) && isset($data['password']) && $data['username'] != ''
        && $data['password'] != ''){

          //LOGIN CON CUENTA ADMIN START
          if (!isset($data['store_path']) || $data['store_path'] == ""){
            //Se trata de un administrador (es posible)

            $user = $this->db->query("SELECT id, name, email, 1 as is_admin,
              (SELECT text_value from sys_texts where text_name = 'color_primary' limit 1) as color_primary,
              (SELECT text_value from sys_texts where text_name = 'color_secundary' limit 1) as color_secundary,
              '' as store_path, app from sys_user_admin where email = '".$data['username']."' and password = md5('".$data['password']."') LIMIT 1")->result_array();

            if (count($user) >0){

              $this->db->query("INSERT INTO sys_session (id_user_admin, ip, useragent)
              VALUES ('".$user[0]['id']."',
                '".$_SERVER['REMOTE_ADDR']."',
                '".$_SERVER['HTTP_USER_AGENT']."')");

              $user["id"] = "";
              $insert_id = $this->db->insert_id();
              $response['success'] = 1;
              $response['token'] = md5($insert_id."-".$user[0]['id']);
              $response['user'] = $user[0];


            }
          }
          //lOGIN CON CUENTA ADMIN END

          //LOGIN CON CUENTA MAGENTO START
          if ($response['success'] == 0){

            $where = "";
            if (isset($data['store_path']) && $data['store_path'] != ""){
              $where .= " AND store_path = '".$data['store_path']."' ";
            }

            $store = $this->db->query("SELECT id, store_path,name, color_primary, color_secundary, cardcode as card_code, public_name, active_sites, active_min, active_2_1_products
            from sys_user_customer where email = '".$data['username']."' and
            id_app = '".$app[0]['id']."'  and active = true and visible= true ".$where." LIMIT 1")->result_array();



            if (count($store)>0){

              $customer = array();

              $curl = curl_init();
              if (!$this->_temp){

                $fields_string = http_build_query(array(
                  'source'=>'micro',
                  'appCode' => 'MX',
                  'username' => $data['username'],
                  'password' => $data['password']
                ));
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://promoapp.aplicatulogo.net/api_external/magento_login");
                //curl_setopt($ch, CURLOPT_URL, "https://www.contenidopromo.com/middleware/customer_magento.php");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $data_R = curl_exec($ch);
                curl_close($ch);
                $data_R = json_decode($data_R, true);

                if (isset($data_R['success']) && $data_R['success'] === 1){
                  $customer = $data_R['customer'];
                }

              } else {

                //TEMP START
                $params = array(
                  "request"=>"token",
                  "data"=>array(
                    'username' => $data['username'],
                    'password' => $data['password']
                  )
                );

                curl_setopt($curl, CURLOPT_URL, "https://promoapp.aplicatulogo.net/request_to_api");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($params));
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                     //"User-Agent: PostmanRuntime/7.26.8",
                     //"Accept-Encoding: gzip, deflated, br",
                     //"Connection: keep-alive"
                ));

                $curl_response = curl_exec($curl);
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $customer = json_decode($curl_response, true);

              }
              //TEMP END

              /*
              if ( $data['username'] == 'harrison@ilitchi.com'){
                echo "<pre>".print_r($customer['card_code'],1)."</pre>";
                echo "<pre>".print_r($store[0]['card_code'],1)."</pre>";
              }
              */


              //Validar que cardcode coincida
              if ((isset($customer['card_code']) && $customer['card_code'] == $store[0]['card_code'])){


                $this->db->query("INSERT INTO sys_session (id_user_customer, ip, useragent)
                VALUES ('".$store[0]['id']."',
                  '".$_SERVER['REMOTE_ADDR']."',
                  '".$_SERVER['HTTP_USER_AGENT']."')");

                $insert_id = $this->db->insert_id();
                $response['success'] = 1;
                $response['token'] = md5($insert_id."-".$store[0]['id']);
                $response['user'] = array();
                $response['user']['is_admin'] = "0";
                $response['user']['active_2_1_products'] = $store[0]['active_2_1_products'];
                $response['user']['active_sites'] = $store[0]['active_sites'];
                $response['user']['active_min'] = $store[0]['active_min'];
                $response['user']['name'] = $customer['first_name'] . " " .$customer['last_name'];
                $response['user']['email'] = $customer['email'];
                $response['user']['public_name'] = $store[0]['public_name'];
                $response['user']['store_path'] = $store[0]['store_path'];
                $response['user']['color_primary'] = $store[0]['color_primary'];
                $response['user']['color_secundary'] = $store[0]['color_secundary'];
                $response['user']['id'] = $store[0]['id'];

                //Validar sesión por middleware
                $fields = array('source'=>'web',
                'sales_org'=>$data['sales_org'],
                'web_access'=>'',
                'card_code'=>$customer['card_code'],
                'phone_access'=>'');
                $fields_string = http_build_query($fields);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://www.contenidopromo.com/middleware/customer.php");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $customer = curl_exec($ch);
                curl_close($ch);
                $customer = json_decode($customer, true);

                if (isset($customer['code']) && $customer['code'] != "") {
                  //Permiso para ingresar
                  $this->db->query("UPDATE sys_user_customer set name = '".$response['user']['name']."',
                    customer_level = '".$customer['customer_level']."',
                    customer_currency = '".$customer['currency']."' WHERE id = '".$store[0]['id']."'");
                }
              }

            }

          }
          //LOGIN CON CUENTA MAGENTO END

          if ($response['success'] == 0){
              $response['error'] = 'Usuario o contraseña incorrectos.';
          }

        }


      } else {
        //Login con middleware

        //Validar sesión por middleware
        if (isset($data['store_path']) && $data['store_path'] != ""){
          $fields = array('source'=>'web', 'sales_org'=>$data['sales_org'], 'web_access'=>$data['password'], 'card_code'=>'', 'phone_access'=>'');
        } else {
          $fields = array('source'=>'web', 'sales_org'=>$data['sales_org'], 'web_access'=>$data['claveweb'], 'card_code'=>'', 'phone_access'=>'');
        }

        $fields_string = http_build_query($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.contenidopromo.com/middleware/customer.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $customer = curl_exec($ch);
        curl_close($ch);
        $customer = json_decode($customer, true);

        if (isset($customer['code']) && $customer['code'] != ""){
          $success = TRUE;

          if ((!isset($data['store_path']) || $data['store_path'] == "")
          && $data['sales_org'] == "1210" && (!isset($data['accept_doc']) || $data['accept_doc'] == 0)){
            $success = FALSE;
            $response['accept_doc'] = 1;
          }


          if ($success){

            $where = "";
            if (isset($data['store_path']) && $data['store_path'] != ""){
              $where .= " AND store_path = '".$data['store_path']."' ";
            }elseif (isset($data['selected_site']) && $data['selected_site'] != ""){
              $where .= " AND store_path = '".$data['selected_site']."' ";
            }
            //Comentar para agregar multiples sitios
            $limit = "";//" LIMIT 1";

            $store = $this->db->query("SELECT id, store_path,name, color_primary, color_secundary, cardcode as card_code, public_name, active_sites, active_min, active_2_1_products
            from sys_user_customer where cardcode = '".$customer['code']."'
            and id_app = '".$app[0]['id']."' and active = true and visible = true ".$where." ORDER BY id ASC ".$limit)->result_array();


            /*
            if( $data['claveweb'] == "AF75760"){
              echo "<pre>".print_r($response,1)."</pre>";
              echo "<pre>".print_r($customer,1)."</pre>";
              echo "<pre>".print_r($store,1)."</pre>";
            }
            */
            if (count($store)==1){
                //Permiso para ingresar
                $this->db->query("INSERT INTO sys_session (id_user_customer, ip, useragent)
                VALUES ('".$store[0]['id']."',
                  '".$_SERVER['REMOTE_ADDR']."',
                  '".$_SERVER['HTTP_USER_AGENT']."')");

                $insert_id = $this->db->insert_id();
                $response['success'] = 1;
                $response['token'] = md5($insert_id."-".$store[0]['id']);
                $response['user'] = array();
                $response['user']['is_admin'] = "0";
                $response['user']['active_2_1_products'] = $store[0]['active_2_1_products'];
                $response['user']['active_sites'] = $store[0]['active_sites'];
                $response['user']['active_min'] = $store[0]['active_min'];
                $response['user']['name'] = $customer['name'];
                $response['user']['email'] = $customer['email'];
                $response['user']['public_name'] = $store[0]['public_name'];
                $response['user']['store_path'] = $store[0]['store_path'];
                $response['user']['color_primary'] = $store[0]['color_primary'];
                $response['user']['color_secundary'] = $store[0]['color_secundary'];
                $response['user']['id'] = $store[0]['id'];

                $this->db->query("UPDATE sys_user_customer set name = '".$response['user']['name']."',
                  customer_level = '".$customer['customer_level']."',
                  customer_currency = '".$customer['currency']."' WHERE id = '".$store[0]['id']."'");

            } elseif (count($store)>1){
              //Tiene mas de 1 cuenta
                $response['error'] = 'Por favor selecciona el micrositios';
                $response['sites_list'] = array();
                foreach ($store as $store_det) {
                  array_push($response['sites_list'], array(
                    "name"=>$store_det['public_name'],
                    "path"=>$store_det['store_path']
                  ));
                }
            }

          }

        } else {
          $response['error'] = 'Clave de acceso incorrecta';
        }

      }

    }


    echo json_encode($response);
	}

  public function token_validate()
	{

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

    header("Content-Type: application/json");

    $response = array("success"=>0);

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['token']) && $data['token'] != ''){

      if (isset($data['store_name']) && $data['store_name'] != ""){
        //TIENDA cliente final

        $session = $this->db->query("select id_customer, id_customer_site, id_customer_site_user from sys_session_sites where active = TRUE AND md5(CONCAT(id::text,'-',id_customer_site_user)) = '".$data['token']."' LIMIT 1")->result_array();

        if (count($session) >0){

          $user = $this->db->query("select is_admin, scsu.email, scs.color_primary,scs.color_secundary, scs.public_name as name, suc.id_app,
          scsu.id,
          suc.store_path, scs.store_path as store_name, scsu.credit_ammount, scs.company_name, scs.location_state, scs.location_city, scsu.phone, scsu.name,
          suc.key, suc.active_2_1_products
          from sys_user_customer suc, sys_customer_sites scs, sys_customer_sites_users scsu where
          suc.id = scs.id_customer AND
          scsu.id_customer_site = scs.id
          AND scsu.id_customer_site = '".$session[0]['id_customer_site']."'
          AND scsu.id = '".$session[0]['id_customer_site_user']."'
          and scsu.active = TRUE
          and scs.active = TRUE and scs.visible = true
          AND suc.active_sites = true ")->result_array();

          if (count($user)>0){
            $response['success'] = 1;
            $response['user'] = $user[0];
          }

          if (isset($response['user'])){
            if ($response['user']['color_primary'] == ""){
              $response['user']['color_primary'] = '#8A8A8A';
            }
            if ($response['user']['color_secundary'] == ""){
              $response['user']['color_secundary'] = '#BABABA';
            }
          }

        }

      } else{
        //USUARIO DISTRIBUIDOR O PROMOOPCIÓN

        $session = $this->db->query("select id_user_admin, id_user_customer from sys_session where active = TRUE AND ( md5(CONCAT(id::text,'-',id_user_admin)) = '".$data['token']."' OR  md5(CONCAT(id::text,'-',id_user_customer)) = '".$data['token']."') LIMIT 1")->result_array();

        if (count($session) >0){

          if ($session[0]['id_user_admin'] != ""){

            $user = $this->db->query("select 1 as is_admin, name, email, '' as cardcode, '' as store_path, '' as color_primary, '' as color_secundary, app, '' as id_app from sys_user_admin where id = '".$session[0]['id_user_admin']."' and active = TRUE")->result_array();
            if (count($user)>0){
              $response['success'] = 1;
              $response['user'] = $user[0];
            }

          } elseif ($session[0]['id_user_customer'] != ""){

            $user = $this->db->query("select 0 as is_admin, id, name, email, store_path, color_primary,color_secundary, public_name, key, id_app, active_sites, active_min
            from sys_user_customer where id = '".$session[0]['id_user_customer']."' and active = TRUE and visible = true")->result_array();

            if (count($user)>0){
              $response['success'] = 1;
              $response['user'] = $user[0];
            }

          }

          if (isset($response['user'])){
            if ($response['user']['color_primary'] == ""){
              $response['user']['color_primary'] = '#8A8A8A';
            }
            if ($response['user']['color_secundary'] == ""){
              $response['user']['color_secundary'] = '#BABABA';
            }
          }

        }

      }

    }

    echo base64_encode(json_encode($response));
	}

  function login_getCustomerInfo_magento($token){
          $token =  str_replace("\"","",$token);

          $response = array();

          $curl = curl_init();

          $curl = curl_init();

          curl_setopt($curl, CURLOPT_URL, "https://www.promoopcion.com"
            . "/rest/V1/customers/me?fields=email,firstname,lastname,extension_attributes[company_attributes[customer_id,company_id]],custom_attributes");
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
          curl_setopt($curl, CURLOPT_HTTPHEADER, array(
              'Authorization: Bearer '.$token
          ));
          curl_setopt($curl, CURLOPT_USERAGENT, "promoopcion");
          curl_setopt($curl, CURLOPT_REFERER, 'https://www.promocionalesenlinea.com/');

          $curl_response = curl_exec($curl);
          $curl_response = json_decode($curl_response, true);
          $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);



          if ($httpcode == 200 && isset($curl_response["extension_attributes"])){

            $response["mg_company_id"] = $curl_response["extension_attributes"]["company_attributes"]["company_id"];
            $response["mg_customer_id"] = $curl_response["extension_attributes"]["company_attributes"]["customer_id"];

            $response["email"] = $curl_response["email"];
            $response["mg_role_id"] = "";
            $response["role_name"] = "";

            $response["first_name"] = $curl_response["firstname"];
            $response["last_name"] = $curl_response["lastname"];

            foreach ($curl_response["custom_attributes"] as $value) {
              if ($value["attribute_code"] == "card_code"){
                $response["card_code"] = $value["value"];
                break;
              }
            }
          } else {
            //echo $token. " = ". $httpcode. " <pre>".print_r($curl_response,1)."</pre>";
          }

          curl_close($curl);
          return $response;
  }




  function save_upload_image(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

    header("Content-Type: application/json");

		$result['success'] = FALSE;

		$_POST = file_get_contents('php://input');
		$_POST = json_decode($_POST,true);

    $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session
      where (md5(CONCAT(id::text,'-',id_user_admin)) = '".$_POST['token']."' OR
      md5(CONCAT(id::text,'-',id_user_customer)) = '".$_POST['token']."') ")->result_array();

    if (count($session) >0){
      $id_product = $_POST['id_inserted'];
  		//$position_img = 1;

      if (isset($_POST['image_base64'])){
        $image = urldecode($_POST['image_base64']);

    		if ($id_product != "" &&  $image != ""){
          $form_name = $_POST['form_name'];
          if (!file_exists($this->media_path.'_form_files')) {
              mkdir($this->media_path.'_form_files', 0777, true);
          }
          if (!file_exists($this->media_path.'_form_files/'.$form_name)) {
              mkdir($this->media_path.'_form_files/'.$form_name, 0777, true);
          }
          if (!file_exists($this->media_path.'_form_files/'.$form_name."/".md5($id_product))) {
              mkdir($this->media_path.'_form_files/'.$form_name."/".md5($id_product), 0777, true);
          }

          $images_names = "";


          $position_img = 1;
          $latest_filename = '';
          $files = glob($this->media_path.'_form_files/'.$form_name."/".md5($id_product)."/*.png");
          foreach($files as $file)
          {
                  if (is_file($file))
                  {
                      if ($images_names != ""){
                        $images_names.="///";
                      }
                      $images_names .= str_replace($this->media_path,'',$file);

                      $file_p = explode("_",$file);

                      $latest_filename = intval(str_replace('.png','',$file_p[count($file_p)-1]));

                      if ($position_img <= $latest_filename){
                        $position_img= $latest_filename+1;
                      }
                  }
          }

          //$explode_data = $image;

    			$explode_data = explode("base64,",$image);
    			if (count($explode_data)>1){
    				$explode_data = $explode_data[1];
    			} else {
    				$explode_data = $explode_data[0];
    			}
  				$explode_data = urldecode($explode_data);

          $data = 'data:image/png;base64,'.str_replace(' ','+',$explode_data);

    			list($type, $data) = explode(';', $data);
    			list(, $data)      = explode(',', $data);
    			$data = base64_decode($data);
    			file_put_contents($this->media_path.'_form_files/'.$form_name."/".md5($id_product).'/image_'.$position_img.'.png', $data);

          if ($images_names != ""){
            $images_names.="///";
          }
          $images_names .= '_form_files/'.$form_name."/".md5($id_product).'/image_'.$position_img.'.png';

          if ($form_name == 'fcustomer_products_other'){
            $this->db->query("UPDATE tb_products_customer SET images = '".$images_names."' where id = '".$id_product."' and id_customer = '".$session[0]['id_user_customer']."'");
          } elseif ($form_name == 'fcustomer_products_display'){
            $this->db->query("UPDATE tb_products_customer_display SET images = '".$images_names."' where id = '".$id_product."' and id_customer = '".$session[0]['id_user_customer']."'");
          }

          $result['path'] = '_form_files/'.$form_name."/".md5($id_product).'/';
          $result['name'] = 'image_'.$position_img.'.png';
    			$result['success'] = TRUE;
    		}
      }
    }

		echo json_encode($result);
	}




  function deleteFile(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

    header("Content-Type: application/json");

		$result['success'] = FALSE;

		$_POST = file_get_contents('php://input');
		$_POST = json_decode($_POST,true);

    $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session
      where (md5(CONCAT(id::text,'-',id_user_admin)) = '".$_POST['token']."' OR
      md5(CONCAT(id::text,'-',id_user_customer)) = '".$_POST['token']."') ")->result_array();

    if (count($session) >0){
      $file = $_POST['img_path'];
      if (file_exists($this->media_path.$file) && is_file($this->media_path.$file)) {
          unlink($this->media_path.$file);
      }

      $path = explode('/',$file);
      $form_name = $path[1];
      $id_product = $path[2];

      $images_names = "";

      $latest_ctime = 0;
      $latest_filename = '';
      $files = glob($this->media_path.'_form_files/'.$form_name."/".$id_product."/*.png");
      foreach($files as $file)
      {
              if (is_file($file))
              {
                  if ($images_names != ""){
                    $images_names.="///";
                  }
                  $images_names .= str_replace($this->media_path,'',$file);
                  if (filectime($file) > $latest_ctime){
                      $latest_ctime = filectime($file);
                      $latest_filename = $file;
                  }
              }
      }

      if ($form_name == 'fcustomer_products_other'){
            $this->db->query("UPDATE tb_products_customer SET images = '".$images_names."' where md5(id::varchar) = '".$id_product."' and id_customer = '".$session[0]['id_user_customer']."'");
      } elseif ($form_name == 'fcustomer_products_display'){
            $this->db->query("UPDATE tb_products_customer_display SET images = '".$images_names."' where md5(id::varchar) = '".$id_product."' and id_customer = '".$session[0]['id_user_customer']."'");
      }


      $result['success'] = TRUE;


    }

		echo json_encode($result);
	}


  public function save_categories_margin(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }
      header("Content-Type: application/json");

      $data = json_decode(file_get_contents('php://input'), true);


  		$result = array("success"=>FALSE);

      $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session where (md5(CONCAT(id::text,'-',id_user_admin)) = '".$data['token']."' OR md5(CONCAT(id::text,'-',id_user_customer)) = '".$data['token']."') ")->result_array();


      if (count($session)>0){
        //Sesión activa

        $checked = "TRUE";
        if ($data['is_checked'] == "1"){
          $checked = "FALSE";
        }
        $default_category = "NULL";
        if ($data['default_category'] != ""){
          $default_category = $data['default_category'];
        }

        if ($data['store_id'] != ""){
          //MICROSITIO CLIENTE FINAL
          $this->db->query("UPDATE sys_customer_sites SET id_category_default = $default_category, pct_general = '".$checked."', id_categories = '".$data['id_categories']."' WHERE id_customer = '".$session[0]['id_user_customer']."' AND id = '".$data['store_id']."'");

          foreach ($data['categories'] as $id_category => $pct) {
            $this->db->query("UPDATE tb_categories_margin SET pct = '".$pct."',pct_outlet = '".$data['categories_pu'][$id_category]."' WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site = '".$data['store_id']."' AND id_category = '".$id_category."'");
            if ($this->db->affected_rows() == 0){
              $this->db->query("INSERT INTO tb_categories_margin (id_customer,id_customer_site, id_category, pct, pct_outlet) VALUES ('".$session[0]['id_user_customer']."','".$data['store_id']."','".$id_category."','".$pct."','".$data['categories_pu'][$id_category]."')");
            }
          }

          foreach ($data['categories_minpzas'] as $id_category => $pzas_min) {
            $this->db->query("UPDATE tb_categories_margin SET pzas_min = '".$pzas_min."',costo_adicional = '".number_format((float)($data['categories_costo'][$id_category]), 2, '.', '')."' WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site = '".$data['store_id']."' AND id_category = '".$id_category."'");
            if ($this->db->affected_rows() == 0){
              $this->db->query("INSERT INTO tb_categories_margin (id_customer, id_customer_site,id_category, pzas_min,costo_adicional) VALUES ('".$session[0]['id_user_customer']."','".$data['store_id']."','".$id_category."','".$pzas_min."', '".number_format((float)($data['categories_costo'][$id_category]), 2, '.', '')."')");
            }
          }
        } else {
          $this->db->query("UPDATE sys_user_customer SET id_category_default = $default_category, pct_general = '".$checked."' WHERE id = '".$session[0]['id_user_customer']."'");

          foreach ($data['categories'] as $id_category => $pct) {
            $this->db->query("UPDATE tb_categories_margin SET pct = '".$pct."',pct_outlet = '".$data['categories_pu'][$id_category]."' WHERE id_customer = '".$session[0]['id_user_customer']."' AND id_customer_site IS NULL AND id_category = '".$id_category."'");
            if ($this->db->affected_rows() == 0){
              $this->db->query("INSERT INTO tb_categories_margin (id_customer, id_category, pct, pct_outlet) VALUES ('".$session[0]['id_user_customer']."','".$id_category."','".$pct."','".$data['categories_pu'][$id_category]."')");
            }
          }

          foreach ($data['categories_minpzas'] as $id_category => $pzas_min) {
            $this->db->query("UPDATE tb_categories_margin SET pzas_min = '".$pzas_min."',costo_adicional = '".number_format((float)($data['categories_costo'][$id_category]), 2, '.', '')."' WHERE id_customer = '".$session[0]['id_user_customer']."' AND id_customer_site IS NULL AND id_category = '".$id_category."'");
            if ($this->db->affected_rows() == 0){
              $this->db->query("INSERT INTO tb_categories_margin (id_customer, id_category, pzas_min, costo_adicional) VALUES ('".$session[0]['id_user_customer']."','".$id_category."','".$pzas_min."', '".number_format((float)($data['categories_costo'][$id_category]), 2, '.', '')."')");
            }
          }


        }


        $result['success'] = TRUE;
      }

  		echo json_encode($result);
  }


  public function save_display_by_sku(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }
      header("Content-Type: application/json");

      $data = json_decode(file_get_contents('php://input'), true);


  		$result = array("success"=>FALSE);

      $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session where (md5(CONCAT(id::text,'-',id_user_admin)) = '".$data['token']."' OR md5(CONCAT(id::text,'-',id_user_customer)) = '".$data['token']."') ")->result_array();


      if (count($session)>0){
        //Sesión activa

        $checked = "FALSE";
        if ($data['display_by_sku'] == "true"){
          $checked = "TRUE";
        }

        if ($data['store_id'] != ""){
          //MICROSITIO CLIENTE FINAL
          $this->db->query("UPDATE sys_customer_sites SET display_by_sku = '".$checked."' WHERE id_customer = '".$session[0]['id_user_customer']."' AND id = '".$data['store_id']."'");
        } else {
          $this->db->query("UPDATE sys_user_customer SET display_by_sku = '".$checked."' WHERE id = '".$session[0]['id_user_customer']."'");
        }

        $result['success'] = TRUE;
      }

  		echo json_encode($result);
  }


  public function save_form(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $data = json_decode(file_get_contents('php://input'), true);


			$file_to_save = array();

      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);

      $response = array("success"=>0);

      if (isset($data['is_store']) && $data['is_store'] == "1"){
        //SE TRATA DE UNA TIENDA DE CLIENTE FINAL
        $session = $this->db->query("SELECT id_customer_site, id_customer_site_user, COALESCE((SELECT is_admin FROM sys_customer_sites_users where id = id_customer_site_user limit 1),FALSE) as is_admin, COALESCE((SELECT id_customer FROM sys_customer_sites where id = id_customer_site limit 1),NULL) as id_user_customer from sys_session_sites where active = TRUE AND md5(CONCAT(id::text,'-',id_customer_site_user)) = '".$data['token']."' LIMIT 1")->result_array();
      } else {
        $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session where (md5(CONCAT(id::text,'-',id_user_admin)) = '".$data['token']."' OR md5(CONCAT(id::text,'-',id_user_customer)) = '".$data['token']."') ")->result_array();
      }
      if (count($session)>0){
        //Sesión activa

        $form = $this->db->query("SELECT * FROM sys_form_forms where form_name = '".$data['form_name']."'")->result_array();

        if (count($form )>0){

            $response['error'] = "";
            if ($form[0]['table_name'] == 'sys_user_customer' && isset($data['data']['store_path'])){
              $where_extra_f = "";
              if (isset($data['primary_key']) && $data['primary_key'] != ""){
                $where_extra_f = " AND id <> '".$data['primary_key']."' ";
              }
              $store_path = $this->db->query("SELECT id from sys_user_customer
                where lower(store_path) = lower('".$data['data']['store_path']."') ".$where_extra_f)->result_array();
              if (count($store_path)>0){
                $response['error'] = "URL de tienda no disponible";
              }
            } elseif ($form[0]['table_name'] == 'sys_customer_sites' && isset($data['data']['store_path'])){
              $where_extra_f = "";
              if (isset($data['primary_key']) && $data['primary_key'] != ""){
                $where_extra_f = " AND id <> '".$data['primary_key']."' ";
              }
              $store_path = $this->db->query("SELECT id from sys_customer_sites
                where lower(store_path) = lower('".$data['data']['store_path']."')
                 and id_customer = '".$session[0]['id_user_customer']."' ".$where_extra_f)->result_array();

              if (count($store_path)>0){
                $response['error'] = "URL de tienda de cliente no disponible";
              } else {
                //Ver si email ya esta registrado
                $where_extra_f = "";
                if (isset($data['primary_key']) && $data['primary_key'] != ""){
                  $where_extra_f = " AND id <> '".$data['primary_key']."' ";
                }

                if (isset($data['data']['email'])){
                  $store_path = $this->db->query("SELECT id from sys_customer_sites_users
                    where email = '".$data['data']['email']."' ".$where_extra_f)->result_array();

                  if (count($store_path)>0){
                    $response['error'] = "Correo electrónico no disponible.";
                  } else {
                    $store_path = $this->db->query("SELECT id from sys_user_customer
                      where email = '".$data['data']['email']."' ")->result_array();

                    if (count($store_path)>0){
                      $response['error'] = "Correo electrónico no disponible.";
                    }
                  }
                }

              }



            } elseif ($form[0]['table_name'] == 'sys_customer_sites_users'){
              $where_extra_f = "";
              if (isset($data['primary_key']) && $data['primary_key'] != ""){
                $where_extra_f = " AND id <> '".$data['primary_key']."' ";
              }

              //Se permite repetir entre tiendas
              //$where_extra_f .= " AND id_customer = '".$session[0]['id_user_customer']."' ";

              $store_path = $this->db->query("SELECT id from sys_customer_sites_users
                where email = '".$data['data']['email']."' ".$where_extra_f)->result_array();

              if (count($store_path)>0){
                $response['error'] = "Correo electrónico no disponible.";
              } else {
                $store_path = $this->db->query("SELECT id from sys_user_customer
                  where email = '".$data['data']['email']."' ")->result_array();

                if (count($store_path)>0){
                  $response['error'] = "Correo electrónico no disponible.";
                }
              }
            } elseif ($form[0]['table_name'] == 'tb_products_customer'){
              $where_extra_f = "";
              if (isset($data['primary_key']) && $data['primary_key'] != ""){
                $where_extra_f = " AND id <> '".$data['primary_key']."' ";
              }

              $where_extra_f .= " AND id_customer = '".$session[0]['id_user_customer']."' ";

              $store_path = $this->db->query("SELECT id from tb_products_customer
                where item_code = '".$data['data']['item_code']."' ".$where_extra_f)->result_array();

              if (count($store_path)>0){
                $response['error'] = "SKU de producto ya registrado.";
              } elseif ($data['data']['item_code'] == $data['data']['parent_code']){
                $response['error'] = "SKU y código padre no pueden ser iguales.";
              }
            }

            if ($response['error'] == ""){

              $fields = $this->db->query("SELECT field_name, field_type FROM sys_form_fields where form_name = '".$form[0]['form_name']."'")->result_array();

              if (count($fields)> 0){

                if (isset($data['primary_key']) && $data['primary_key'] != ""){

                  if ($form[0]['table_name'] == 'sys_user_customer' && isset($data['data']['pct'])){
                    $store = $this->db->query("SELECT id, pct from sys_user_customer
                      where id = '".$data['primary_key']."' ")->result_array();
                  }

                  //UPDATE
                  $data_updated = "";

                  foreach ($fields as $field) {



                    switch ($field['field_type']) {
                      case 'text':
                      case 'text-decimal':
                      case 'textarea':
                      case 'select':
                      case 'select_color':
                      case 'text-number':
                      case 'radio':
                      case 'color':
                      case 'email':
                      case 'select_multiple_categories':
                      case 'autocomplete_optional':
                        if (!isset($data['data'][$field['field_name']])){
                          continue 2;
                        }
                        if ($data_updated != ""){
                          $data_updated .= ", ";
                        }
                        $data_updated .= $field['field_name'] . " = '". $data['data'][$field['field_name']] ."' ";
                        break;
                      case 'select_multiple':
                        if ($data_updated != ""){
                          $data_updated .= ", ";
                        }
                        $data_updated .= $field['field_name'] . " = '". implode(",",$data['data'][$field['field_name']]) ."' ";
                        break;
                      case 'password':
                        if ($data['data'][$field['field_name']] != ""){
                          if ($data_updated != ""){
                            $data_updated .= ", ";
                          }
                          $data_updated .= $field['field_name'] . " = md5('". $data['data'][$field['field_name']] ."') ";
                        }
                        break;
                      case 'number':
                        if ($data_updated != ""){
                          $data_updated .= ", ";
                        }
                        $data_updated .= $field['field_name'] . " = '". intval($data['data'][$field['field_name']]) ."' ";
                        break;
                      case 'number_update':
                        if ($data['data'][$field['field_name']] != ""){

                          if ($data_updated != ""){
                            $data_updated .= ", ";
                          }
                          $data_updated .= $field['field_name'] . " = '". intval($data['data'][$field['field_name']]) ."' ";

                          /*
                          if ($field['field_name'] == 'stock'){
                            $data_updated .= ", ";
                            $data_updated .= $field['field_name'] . "_initial = '". intval($data['data'][$field['field_name']]) ."' ";
                          }
                          */
                        }
                        break;
                      case 'img':
                        if ($data_updated != ""){
                          $data_updated .= ", ";
                        }
                        $data_updated .= $field['field_name'] . " = '". $data['data'][$field['field_name']] ."' ";
                        break;
                      case 'pdf':
                        if ($data_updated != ""){
                          $data_updated .= ", ";
                        }
                        $data_updated .= $field['field_name'] . " = '". $data['data'][$field['field_name']] ."' ";
                        break;
                      case 'boolean':

                        if (!isset($data['data'][$field['field_name']])){
                          continue 2;
                        }
                        if ($data_updated != ""){
                          $data_updated .= ", ";
                        }
                        if ($data['data'][$field['field_name']] == "true"){
                          $data_updated .= $field['field_name'] . " = TRUE ";
                        } else {
                          $data_updated .= $field['field_name'] . " = FALSE ";
                        }
                        break;
                    }
                  }

                  $extra_where = "";

                  if (isset($data['is_store']) && $data['is_store'] == "1"){
                    //Validar que solo se actualicen sus datos
                    if ($form[0]['table_name'] == "sys_customer_sites"){
                      $extra_where .= " AND id = '".$session[0]['id_customer_site']."' ";
                    } else {
                      $extra_where .= " AND id_customer_site = '".$session[0]['id_customer_site']."' ";
                    }
                  } elseif (isset($session[0]['id_user_customer']) && $session[0]['id_user_customer'] != ""){
                    //Validar que solo se actualicen sus datos
                    if ($form[0]['table_name'] == "sys_user_customer"){
                      $extra_where .= " AND id = '".$session[0]['id_user_customer']."' ";
                    } else {
                      $extra_where .= " AND id_customer = '".$session[0]['id_user_customer']."' ";
                    }
                  }

                  $query = "UPDATE ". $form[0]['table_name']. " SET ". $data_updated. " WHERE ".$form[0]['primary_key'] ." = '".$data['primary_key']."' ".$extra_where ;

                  $response['id_inserted'] = $data['primary_key'];

                  if ($form[0]['table_name'] == 'sys_user_customer' && isset($data['data']['pct']) && $store[0]['pct'] != $data['data']['pct']){
                    $this->db->query("INSERT INTO tb_customer_changes (id_customer, pct) VALUES ('".$data['primary_key']."','".$data['data']['pct']."')");
                  }

                  if (count($file_to_save)>0){
                    //Crear carpeta con id e producto md5 si no existe

                    $form_name = $form[0]['form_name'];
                    if (!file_exists($this->media_path.'_form_files')) {
												mkdir($this->media_path.'_form_files', 0777, true);
									  }
										if (!file_exists($this->media_path.'_form_files/'.$form_name)) {
												mkdir($this->media_path.'_form_files/'.$form_name, 0777, true);
									  }
										if (!file_exists($this->media_path.'_form_files/'.$form_name."/".md5($data['primary_key']))) {
												mkdir($this->media_path.'_form_files/'.$form_name."/".md5($data['primary_key']), 0777, true);
									  }
                  }

                  $result = $this->db->query($query);

                  if($result >=0){
                    $response['success'] = 1;
                  }
                } else {
                  //INSERT
                  $data_keys = "";
                  $data_values = "";

                  foreach ($fields as $field) {
                    switch ($field['field_type']) {
                      case 'text':
                      case 'text-decimal':
                      case 'textarea':
                      case 'select':
                      case 'select_color':
                      case 'text-number':
                      case 'radio':
                      case 'color':
                      case 'email':
                      case 'select_multiple_categories':
                      case 'autocomplete_optional':
                        if ($data_keys != ""){
                          $data_keys .= ", ";
                          $data_values .= ", ";
                        }
                        $data_keys .= $field['field_name'];
                        $data_values .= " '". $data['data'][$field['field_name']] ."' ";
                        break;
                      case 'select_multiple':
                        if ($data_keys != ""){
                          $data_keys .= ", ";
                          $data_values .= ", ";
                        }
                        $data_keys .= $field['field_name'];
                        $data_values .= " '". implode(",",$data['data'][$field['field_name']]) ."' ";
                        break;
                      case 'password':
                        if ($data['data'][$field['field_name']] != ""){
                          if ($data_keys != ""){
                            $data_keys .= ", ";
                            $data_values .= ", ";
                          }
                          $data_keys .= $field['field_name'];
                          $data_values .= " md5('". $data['data'][$field['field_name']] ."') ";
                        }
                        break;
                      case 'number':
                        if ($data_keys != ""){
                          $data_keys .= ", ";
                          $data_values .= ", ";
                        }
                        $data_keys .= $field['field_name'];
                        $data_values .= " '". intval($data['data'][$field['field_name']]) ."' ";
                        break;

                      case 'number_update':
                        if ($data['data'][$field['field_name']] != ""){

                          if ($data_keys != ""){
                            $data_keys .= ", ";
                            $data_values .= ", ";
                          }

                          $data_keys .= $field['field_name'];
                          $data_values .= " '". intval($data['data'][$field['field_name']]) ."' ";

                          if ($field['field_name'] == 'stock'){

                            $data_keys .= ", ";
                            $data_values .= ", ";
                            $data_keys .= $field['field_name']."_initial";
                            $data_values .= " '". intval($data['data'][$field['field_name']]) ."' ";

                          }
                        }
                        break;

                      case 'img':
                        if ($data_keys != ""){
                          $data_keys .= ", ";
                          $data_values .= ", ";
                        }
                        $data_keys .= $field['field_name'];
                        $data_values .= " '". $data['data'][$field['field_name']] ."' ";
                        break;

                      case 'pdf':
                        if ($data_keys != ""){
                          $data_keys .= ", ";
                          $data_values .= ", ";
                        }
                        $data_keys .= $field['field_name'];
                        $data_values .= " '". $data['data'][$field['field_name']] ."' ";
                        break;

                      case 'boolean':
                        if ($data_keys != ""){
                          $data_keys .= ", ";
                          $data_values .= ", ";
                        }
                        $data_keys .= $field['field_name'];
                        if ($data['data'][$field['field_name']] == "true"){
                          $data_values .= " TRUE ";
                        } else {
                          $data_values .= " FALSE ";
                        }
                        break;
                    }
                  }

                  if (isset($data['is_store']) && $data['is_store'] == "1"){
                    //Validar que solo se actualicen sus datos
                    $data_keys .= ', id_customer, id_customer_site';
                    $data_values .= ", '".$session[0]['id_user_customer']."' , '".$session[0]['id_customer_site']."' ";
                  } elseif ($session[0]['id_user_customer'] != ""){
                    //Validar que solo se actualicen sus datos
                    $data_keys .= ', id_customer';
                    $data_values .= ", '".$session[0]['id_user_customer']."' ";
                  }

                  if (isset($data['app_name']) && $data['app_name'] != ""){
                    $data_keys .= ", id_app";
                    $data_values .= ", (SELECT id from sys_app where lower(code) = '".$data['app_name']."' LIMIT 1) ";
                  }

                  if (isset($data['id_customer_site']) && $data['id_customer_site'] != ""){
                    $data_keys .= ", id_customer_site";
                    $data_values .= ", '".$data['id_customer_site']."' ";
                  }

                  $query = "INSERT INTO ". $form[0]['table_name']. " (". $data_keys. ") VALUES (".$data_values.")" ;

                  $result = $this->db->query($query);
                  $insert_id = $this->db->insert_id();
                  $response['id_inserted'] = $insert_id;




                  if (count($file_to_save)>0){
                    //Crear carpeta con id e producto md5 si no existe


                    $form_name = $form[0]['form_name'];
                    if (!file_exists($this->media_path.'_form_files')) {
												mkdir($this->media_path.'_form_files', 0777, true);
									  }
										if (!file_exists($this->media_path.'_form_files/'.$form_name)) {
												mkdir($this->media_path.'_form_files/'.$form_name, 0777, true);
									  }
										if (!file_exists($this->media_path.'_form_files/'.$form_name."/".md5($insert_id))) {
												mkdir($this->media_path.'_form_files/'.$form_name."/".md5($insert_id), 0777, true);
									  }
                  }



                  if ($form[0]['table_name'] == 'sys_user_customer'){
                    //Correo de bienvenida
                    $labels = $this->get_labels();

                    $file_url = "";
                    switch (strtolower($data['app_name'])) {
                      case 'mx':
                        $file_url = base_url()."file/aviso-de-privacidad/2";
                        break;
                      case 'col':
                        $file_url = base_url()."file/aviso-de-privacidad/3";
                        break;
                      case 'gtm':
                        $file_url = base_url()."file/aviso-de-privacidad/5";
                        break;
                    }

                    $data_msg=array();
                    $data_msg['site_url'] = 'https://www.promocionalesenlinea.com/';
                    $data_msg['file_url'] = $file_url;
                    $data_msg['store_path'] = $data['data']['store_path'];
                    $data_msg['color1'] = $labels['color_primary'];
                    $data_msg['color1_text'] = $this->getContrastColor($data_msg['color1']);
                    $data_msg['color2'] = $labels['color_secundary'];
                    $data_msg['welcome_msg'] = $labels['welcome_msg'];

                    $body_html = $this->load->view('email_master/template/header',$data_msg,TRUE);
                    $body_html .= $this->load->view('email_master/welcome',$data_msg,TRUE);
                    $body_html .= $this->load->view('email_master/template/footer',$data_msg,TRUE);

                    $title = "TU TIENDA HA SIDO CREADA";

                    $this->load->model('Email');
                    $this->Email->sendMail(
                      array($data['data']["email"]),array(),$title,$body_html
                    );
                  } elseif ($form[0]['table_name'] == 'sys_customer_sites'){

                    //Insert usuario en sys_customer_sites_users
                    $random_password = '';
                    if (!isset($data['data']['password']) || $data['data']['password'] == ''){
                      $random_password = $this->randomPassword();
                      $password_insert = md5($random_password);
                    } else {
                      $password_insert = $data['data']['password'];
                    }

                    /*
                    $this->db->query("INSERT INTO sys_customer_sites_users (id_customer,id_customer_site,email, credit_ammount, password, active, is_admin)
                    VALUES (
                      '".$session[0]['id_user_customer']."',
                      '".$insert_id."',
                      '".$data['data']["email"]."',
                      '".floatval($data['data']["credit_ammount"])."',
                      '".$password_insert."',
                      TRUE,
                      TRUE
                    )");
                    */
                    $this->db->query("INSERT INTO sys_customer_sites_users (id_customer,id_customer_site,email, credit_ammount, password, active, is_admin, name, phone)
                    VALUES (
                      '".$session[0]['id_user_customer']."',
                      '".$insert_id."',
                      '".$data['data']["email"]."',
                      '0',
                      '".$password_insert."',
                      TRUE,
                      TRUE,
                      '".$data['data']["email"]."',
                      '-'
                    )");

                    $main_store = $this->db->query("SELECT id, email_budget, store_path, color_primary, color_secundary, public_name FROM sys_user_customer where id = '".$session[0]['id_user_customer']."' LIMIT 1")->result_array();

                    if (count($main_store)>0){
                      //Correo de bienvenida
                      $labels = $this->get_labels();

                      $data_msg=array();
                      $data_msg['store'] = $main_store[0];
                      $data_msg['site_url'] = 'https://www.promocionalesenlinea.com/'.$data_msg['store']['store_path']."/";
                      $data_msg['store_path'] = "tienda/".$data['data']['store_path'];
                      $data_msg['color1'] = $data_msg['store']['color_primary'];
                      $data_msg['color1_text'] = $this->getContrastColor($data_msg['store']['color_primary']);
                      $data_msg['color2'] = $data_msg['store']['color_secundary'];
                      $data_msg['welcome_msg'] = $labels['welcome_msg'];
                      $data_msg['password'] = $random_password;

                      $body_html = $this->load->view('email/template/header',$data_msg,TRUE);
                      $body_html .= $this->load->view('email/welcome_site',$data_msg,TRUE);
                      $body_html .= $this->load->view('email/template/footer',$data_msg,TRUE);

                      $title = "TU TIENDA HA SIDO CREADA";

                      $this->load->model('Email');
                      $this->Email->sendMail(
                        array($data['data']["email"]),array(),$title,$body_html, $data_msg['store']['public_name'], $data_msg['store']['email_budget']
                      );
                    }

                  } elseif ($form[0]['table_name'] == 'sys_customer_sites_users'){


                    //Insert usuario en sys_customer_sites_users
                    $random_password = '';
                    if (!isset($data['data']['password']) || $data['data']['password'] == ''){
                      $random_password = $this->randomPassword();
                      $password_insert = md5($random_password);
                    } else {
                      $password_insert = $data['data']['password'];
                    }

                    $this->db->query("UPDATE sys_customer_sites_users
                      SET password = '".$password_insert."'
                      WHERE id = '".$insert_id."' ");


                    if (isset($data['is_store']) && $data['is_store'] == "1"){
                      //Validar que solo se actualicen sus datos
                      $store_site = $this->db->query("select store_path FROM sys_customer_sites where id = '".$session[0]['id_customer_site']."' LIMIT 1")->result_array()[0];
                    } else {
                      $store_site = $this->db->query("select store_path FROM sys_customer_sites where id = '".$data["id_customer_site"]."' LIMIT 1")->result_array()[0];
                    }

                    $main_store = $this->db->query("SELECT id, email_budget, store_path, color_primary, color_secundary, public_name FROM sys_user_customer where id = '".$session[0]['id_user_customer']."' LIMIT 1")->result_array();

                    if (count($main_store)>0){
                      //Correo de bienvenida
                      $labels = $this->get_labels();

                      $data_msg=array();
                      $data_msg['store'] = $main_store[0];
                      $data_msg['site_url'] = 'https://www.promocionalesenlinea.com/'.$data_msg['store']['store_path']."/";
                      $data_msg['store_path'] = "tienda/".$store_site['store_path'];
                      $data_msg['color1'] = $data_msg['store']['color_primary'];
                      $data_msg['color1_text'] = $this->getContrastColor($data_msg['store']['color_primary']);
                      $data_msg['color2'] = $data_msg['store']['color_secundary'];
                      $data_msg['welcome_msg'] = $labels['welcome_msg'];
                      $data_msg['password'] = $random_password;

                      $body_html = $this->load->view('email/template/header',$data_msg,TRUE);
                      $body_html .= $this->load->view('email/welcome_site',$data_msg,TRUE);
                      $body_html .= $this->load->view('email/template/footer',$data_msg,TRUE);

                      $title = "TU CUENTA HA SIDO CREADA";

                      $this->load->model('Email');
                      $this->Email->sendMail(
                        array($data['data']["email"]),array(),$title,$body_html, $data_msg['store']['public_name'], $data_msg['store']['email_budget']
                      );
                    }

                  }

                  if($insert_id >=0){
                    $response['success'] = 1;
                  }
                }

              }

            }

        }

        if ($response['success'] == 1 && $session[0]['id_user_customer'] != "" && (
            $data['form_name'] == 'fcustomer_cat_special' ||
            $data['form_name'] == 'fcustomer_products_other'
          )) {

            //ACTUALIZAR SOLO LO DEL DISTRIBUIDOR (CUANDO SE EDITE CATEGORÍA, MARGENES O )
            $this->db->query("DELETE FROM tb_products_search_temp WHERE id_customer = '".$session[0]['id_user_customer']."';");
            $this->db->query("insert into tb_products_search_temp select * from vtb_products_categories_esp_other WHERE id_customer = '".$session[0]['id_user_customer']."';");
            $this->db->query("insert into tb_products_search_temp select * from vtb_products_categories_esp_promoopcion WHERE id_customer = '".$session[0]['id_user_customer']."';");
            $this->db->query("DELETE FROM tb_products_search WHERE id_customer = '".$session[0]['id_user_customer']."';");
            $this->db->query("INSERT INTO tb_products_search SELECT * FROM tb_products_search_temp WHERE id_customer = '".$session[0]['id_user_customer']."';");

            for ($i=$this->min_tb_number; $i <= $this->max_tb_number; $i++) {
              $this->db->query("TRUNCATE TABLE tb_products_search_".$i.";");
              $this->db->query("INSERT INTO tb_products_search_".$i." SELECT * FROM tb_products_search;");
            }
        }

      }




      echo json_encode($response);
  }

  function randomPassword() {
		  $data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
		  return substr(str_shuffle($data), 0, 6);
	}


  public function get_category_list(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $data = json_decode(file_get_contents('php://input'), true);

      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);

      $response = array("success"=>0);

      $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session where ( md5(concat(id::text,'-',id_user_admin)) = '".$data['token']."'  OR  md5(concat(id::text,'-',id_user_customer)) = '".$data['token']."' )")->result_array();

      if (count($session)>0){

        if ($session[0]['id_user_admin'] != "" && $data['app_name'] != ""){
          $response['success'] = TRUE;

          $response['categories'] = $this->db->query("SELECT id, id_parent, category, (SELECT count(id) from tb_products_categories where id_app = tb_categories.id_app and id_category = tb_categories.id) FROM tb_categories
            WHERE id_app = (SELECT id from sys_app where lower(code) = '".$data['app_name']."' LIMIT 1) ORDER BY orderitem ASC, id_parent DESC ")->result_array();
        } elseif ($session[0]['id_user_customer'] != "") {
          $response['success'] = TRUE;

          if ($data['store_id'] != ""){
            //Tienda de cliente final

            $response['display_by_sku'] = $this->db->query("select display_by_sku from sys_customer_sites where id_customer = '".$session[0]['id_user_customer']."' and id = '".$data['store_id']."'  limit 1")->result_array()[0]['display_by_sku'];

            $response['pct_general'] = $this->db->query("SELECT pct from sys_customer_sites where id_customer = '".$session[0]['id_user_customer']."' and id = '".$data['store_id']."' LIMIT 1")->result_array()[0]['pct'];

            $response['category_info'] = $this->db->query("SELECT pct_general,id_category_default, id_categories FROM sys_customer_sites WHERE id_customer = '".$session[0]['id_user_customer']."' and id = '".$data['store_id']."' LIMIT 1")->result_array()[0];

            $response['selected_categories'] = $this->db->query("select COALESCE(id_categories,'') AS id_categories  from sys_customer_sites where id_customer = '".$session[0]['id_user_customer']."' and id = '".$data['store_id']."'  limit 1")->result_array()[0]['id_categories'];

            $response['categories'] = $this->db->query("SELECT id, id_parent, category, (SELECT count(id) from tb_products_categories where id_app = tc.id_app and id_category = tc.id),
              COALESCE((SELECT pct from tb_categories_margin WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site = '".$data['store_id']."' and id_category = tc.id limit 1),0) as pct,
              COALESCE((SELECT pct_outlet from tb_categories_margin WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site = '".$data['store_id']."' and id_category = tc.id limit 1),0) as pct_outlet,
              COALESCE((SELECT costo_adicional from tb_categories_margin WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site = '".$data['store_id']."' and id_category = tc.id limit 1),0) as costo_adicional,
              COALESCE((SELECT pzas_min from tb_categories_margin WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site = '".$data['store_id']."' and id_category = tc.id limit 1),0) as min
              FROM tb_categories tc
              WHERE id_app = (SELECT id_app from sys_user_customer where id = '".$session[0]['id_user_customer']."' LIMIT 1)
               ORDER BY orderitem ASC, id_parent DESC ")->result_array();
            /*
            $response['categories'] = $this->db->query("SELECT id, id_parent, category, (SELECT count(id) from tb_products_categories where id_app = tc.id_app and id_category = tc.id),
              COALESCE((SELECT pct from tb_categories_margin WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site = '".$data['store_id']."' and id_category = tc.id limit 1),0) as pct
              FROM tb_categories tc
              WHERE id_app = (SELECT id_app from sys_user_customer where id = '".$session[0]['id_user_customer']."' LIMIT 1)
              and ( id in (".$response['category_info']['id_categories'].") or id_parent is null) ORDER BY orderitem ASC, id_parent DESC ")->result_array();
              */
          } else {
            $response['display_by_sku'] = $this->db->query("select display_by_sku from sys_user_customer where id = '".$session[0]['id_user_customer']."' limit 1")->result_array()[0]['display_by_sku'];

            $response['pct_general'] = $this->db->query("SELECT pct from sys_user_customer where id = '".$session[0]['id_user_customer']."' LIMIT 1")->result_array()[0]['pct'];

            $response['category_info'] = $this->db->query("SELECT pct_general,id_category_default FROM sys_user_customer WHERE id = '".$session[0]['id_user_customer']."' LIMIT 1")->result_array()[0];

            $response['categories'] = $this->db->query("SELECT id, id_parent, category, (SELECT count(id) from tb_products_categories where id_app = tc.id_app and id_category = tc.id),
              COALESCE((SELECT pct from tb_categories_margin WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site is null and id_category = tc.id limit 1),0) as pct,
              COALESCE((SELECT pct_outlet from tb_categories_margin WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site is null and id_category = tc.id limit 1),0) as pct_outlet,
              COALESCE((SELECT costo_adicional from tb_categories_margin WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site is null and id_category = tc.id limit 1),0) as costo_adicional,
              COALESCE((SELECT pzas_min from tb_categories_margin WHERE id_customer = '".$session[0]['id_user_customer']."' and id_customer_site is null and id_category = tc.id limit 1),0) as min
              FROM tb_categories tc
              WHERE id_app = (SELECT id_app from sys_user_customer where id = '".$session[0]['id_user_customer']."' LIMIT 1) ORDER BY orderitem ASC, id_parent DESC ")->result_array();
          }

        }

      }

      echo json_encode($response);
  }


  public function form_autocomplete_options(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $data = json_decode(file_get_contents('php://input'), true);

      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);

      $response = array("success"=>0);

      $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session where ( md5(concat(id::text,'-',id_user_admin)) = '".$data['token']."' OR  md5(concat(id::text,'-',id_user_customer)) = '".$data['token']."' )")->result_array();

      if (count($session)>0){

        $response['success'] = TRUE;
        $response['options'] = [];

        if ($session[0]['id_user_customer'] != "" && $this->db->field_exists('id_customer', $data['table_name']))
        {
            $options = $this->db->query("SELECT ".$data['key']." FROM ". $data['table_name'] . " WHERE id_customer = '".$session[0]['id_user_customer']."' AND LOWER(".$data['key'].") LIKE '%".strtolower($data['filter'])."%' LIMIT 50")->result_array();
        } else {
          $options = $this->db->query("SELECT ".$data['key']." FROM ". $data['table_name'] . " WHERE LOWER(".$data['key'].") LIKE '%".strtolower($data['filter'])."%' LIMIT 50")->result_array();
        }
        foreach ($options as $option) {
          array_push($response['options'], $option[$data['key']]);
        }

      }

      echo json_encode($response);
  }

  public function delete_form(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $data = json_decode(file_get_contents('php://input'), true);

      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);

      $response = array("success"=>0);

      $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session
        where ( md5(CONCAT(id::text,'-',id_user_admin)) = '".$data['token']."' OR  md5(CONCAT(id::text,'-',id_user_customer)) = '".$data['token']."') ")->result_array();

      if (count($session)>0){
        //Sesión activa


        $table= $this->db->query("SELECT * FROM sys_table_tables where table_name = '".$data['table_name']."'")->result_array();
        if (count($table)>0 && isset($data['primary_key']) && $data['primary_key'] != ''){

          $form = $this->db->query("SELECT * FROM sys_form_forms where form_name = '".$table[0]['form_name']."'")->result_array();

          if (count($form )>0){

                $extra_where = "";
                if ($session[0]['id_user_customer'] != ""){
                  $extra_where = " AND id_customer = '".$session[0]['id_user_customer']."' ";
                }

                if ( $form[0]['table_name'] == 'sys_user_customer'){
                  $query = "UPDATE ". $form[0]['table_name']. " SET visible = FALSE, db_update = NOW() WHERE ".$form[0]['primary_key'] ." = '".$data['primary_key']."' " .$extra_where;
                } elseif (
                  $session[0]['id_user_customer'] != "" && (
                  $form[0]['table_name'] == 'fcustomer_cat_special' ||
                  $form[0]['table_name'] == 'fcustomer_products_other'
                )) {

                  //ACTUALIZAR SOLO LO DEL DISTRIBUIDOR (CUANDO SE EDITE CATEGORÍA, MARGENES O )
              		$this->db->query("DELETE FROM tb_products_search_temp WHERE id_customer = '".$session[0]['id_user_customer']."';");
              		$this->db->query("insert into tb_products_search_temp select * from vtb_products_categories_esp_other WHERE id_customer = '".$session[0]['id_user_customer']."';");
              		$this->db->query("insert into tb_products_search_temp select * from vtb_products_categories_esp_promoopcion WHERE id_customer = '".$session[0]['id_user_customer']."';");
              		$this->db->query("DELETE FROM tb_products_search WHERE id_customer = '".$session[0]['id_user_customer']."';");
              		$this->db->query("INSERT INTO tb_products_search SELECT * FROM tb_products_search_temp WHERE id_customer = '".$session[0]['id_user_customer']."';");

                  for ($i=$this->min_tb_number; $i <= $this->max_tb_number; $i++) {
                    $this->db->query("TRUNCATE TABLE tb_products_search_".$i.";");
                    $this->db->query("INSERT INTO tb_products_search_".$i." SELECT * FROM tb_products_search;");
                  }

                } else {
                  $query = "DELETE FROM ". $form[0]['table_name']. " WHERE ".$form[0]['primary_key'] ." = '".$data['primary_key']."' " .$extra_where;
                }

                $this->db->query($query);
                $result = $this->db->affected_rows();;

                if($result >=0){
                  $response['success'] = 1;
                }
          }

        }

      }



      echo json_encode($response);
  }



  public function bulk_action_exec(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $data = json_decode(file_get_contents('php://input'), true);

      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);

      $response = array("success"=>0);

      $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session
        where ( md5(CONCAT(id::text,'-',id_user_admin)) = '".$data['token']."' OR  md5(CONCAT(id::text,'-',id_user_customer)) = '".$data['token']."') ")->result_array();

      if (count($session)>0 && isset($data['bulk_action'])){

        if ($data['bulk_action'] == "correo_bienvenida"){

          foreach ($data['records'] as $id_customer) {

            $customer = $this->db->query("SELECT id_app, email,store_path FROM sys_user_customer WHERE id = '".$id_customer."' LIMIT 1")->result_array();

            if (count($customer)>0){
              $response['success'] = 1;

              $labels = $this->get_labels();

              $file_url = "";
              switch ($customer[0]['id_app']) {
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
              $data_msg['site_url'] = 'https://www.promocionalesenlinea.com/';
              $data_msg['file_url'] = $file_url;
              $data_msg['store_path'] = $customer[0]['store_path'];
              $data_msg['color1'] = $labels['color_primary'];
              $data_msg['color1_text'] = $this->getContrastColor($data_msg['color1']);
              $data_msg['color2'] = $labels['color_secundary'];
              $data_msg['welcome_msg'] = $labels['welcome_msg'];

              $body_html = $this->load->view('email_master/template/header',$data_msg,TRUE);
              $body_html .= $this->load->view('email_master/welcome',$data_msg,TRUE);
              $body_html .= $this->load->view('email_master/template/footer',$data_msg,TRUE);

              $title = "TU TIENDA HA SIDO CREADA";

              $this->load->model('Email');
              $this->Email->sendMail(
                array($customer[0]['email']),array(),$title,$body_html
              );
            }
          }


        }

      }



      echo json_encode($response);
  }

  public function generate_form(){

      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
      header("Allow: GET, POST, OPTIONS");

      $method = $_SERVER['REQUEST_METHOD'];

      if ($method == "OPTIONS") {
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
          header("HTTP/1.1 200 OK");
          die();
      }else{
          header('Access-Control-Allow-Origin: *');
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
      }

      header("Content-Type: application/json");

      $data = json_decode(file_get_contents('php://input'), true);


      $response = array("success"=>0);

      $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session where (md5(concat(id::text,'-',id_user_admin)) = '".$data['token']."' OR md5(concat(id::text,'-',id_user_customer)) = '".$data['token']."') ")->result_array();


      if (isset($data['is_store']) && $data['is_store'] == "1"){
        //SE TRATA DE UNA TIENDA DE CLIENTE FINAL
        $session = $this->db->query("SELECT id_customer_site, id_customer_site_user, COALESCE((SELECT is_admin FROM sys_customer_sites_users where id = id_customer_site_user limit 1),FALSE) as is_admin from sys_session_sites where active = TRUE AND md5(CONCAT(id::text,'-',id_customer_site_user)) = '".$data['token']."' LIMIT 1")->result_array();
      } else {
        $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session where (md5(concat(id::text,'-',id_user_admin)) = '".$data['token']."' OR md5(concat(id::text,'-',id_user_customer)) = '".$data['token']."') ")->result_array();
      }

      if (count($session)>0){
        //Sesión activa

        $table= $this->db->query("SELECT * FROM sys_table_tables where table_name = '".$data['table_name']."'")->result_array();
        if (count($table)>0){
          $response['form_name'] = $table[0]['form_name'];
          $response['edit_key'] = $table[0]['edit_key'];


          $form = $this->db->query("SELECT * FROM sys_form_forms where form_name = '".$response['form_name']."'")->result_array();

          if (count($form )>0){
              $response['form'] = $form[0];

              $fields_arr = array();
              $_where = "";
              array_push($fields_arr,$table[0]['primary_key']);
              $fields = $this->db->query("SELECT * FROM sys_form_fields where form_name = '".$response['form_name']."' order by field_x asc, field_y asc")->result_array();

              foreach ($fields as $field_v) {
                if (!isset($fields_arr[$field_v['field_name']])){
                  if ($field_v['field_type'] == 'img'){
                    array_push($fields_arr, " encode(".$field_v['field_name']. ", 'base64') AS ".$field_v['field_name']);
                  } else if ($field_v['field_type'] != 'password'
                            && $field_v['field_type'] != 'pdf'
                            && $field_v['field_type'] != 'label') {
                    array_push($fields_arr,$field_v['field_name']);
                  }
                }
              }

              if (!(isset($data['is_store']) && $data['is_store'] == "1")){
                for ($i=0; $i < count($fields); $i++) {
                  if (
                    $fields[$i]['field_type'] == 'select'
                    || $fields[$i]['field_type'] == 'select_multiple'
                  ){
                    $fields[$i]['db_table'] = explode(",",$fields[$i]['db_table']);
                    if(count($fields[$i]['db_table'])>1){
                      if ($fields[$i]['db_table'][1] == 'id_app' && isset($session[0]['id_user_customer'])){
                        $fields[$i]['select_options'] = $this->db->query("SELECT ".$fields[$i]['db_field']." as key, ".$fields[$i]['db_format']." as value
                          FROM ".$fields[$i]['db_table'][0]. " WHERE id_app = (SELECT id_app FROM sys_user_customer WHERE id = '".$session[0]['id_user_customer']."' LIMIT 1)
                          ORDER BY ".$fields[$i]['db_format']." ASC")->result_array();

                      } elseif ($fields[$i]['db_table'][1] == 'id_app' && !isset($session[0]['id_user_customer']) ){

                        if ($form[0]['table_name'] == 'sys_app'){
                          $id_app = $this->db->query("SELECT id as id_app FROM ".$form[0]['table_name']." WHERE ".$response['form']['primary_key']." = '".$data['edit_key']."' LIMIT 1 ;")->result_array()[0]['id_app'];
                        } else {
                          $id_app = $this->db->query("SELECT id_app FROM ".$form[0]['table_name']." WHERE ".$response['form']['primary_key']." = '".$data['edit_key']."' LIMIT 1 ;")->result_array()[0]['id_app'];
                        }
                        $fields[$i]['select_options'] = $this->db->query("SELECT ".$fields[$i]['db_field']." as key, ".$fields[$i]['db_format']." as value
                          FROM ".$fields[$i]['db_table'][0]. " WHERE id_app = '".$id_app."'
                          ORDER BY ".$fields[$i]['db_format']." ASC")->result_array();
                      }
                    } else {
                      $fields[$i]['select_options'] = $this->db->query("SELECT ".$fields[$i]['db_field']." as key, ".$fields[$i]['db_format']." as value
                          FROM ".$fields[$i]['db_table'][0]. "
                          ORDER BY ". $fields[$i]['db_format']." ASC")->result_array();
                    }
                  }
                }
            }

              $response['fields'] = $fields;

              $response['data'] = array();
              if ($response['edit_key'] != "" && isset($data['edit_key']) &&  $data['edit_key'] != ""){
                $extra_where = "";

                if (isset($data['is_store']) && $data['is_store'] == "1"){
                  $extra_where .= " AND id_customer_site = '".$session[0]['id_customer_site']."' ";
                } else {
                  if ($session[0]['id_user_customer']!=""){
                    if ($form[0]['table_name'] == "sys_user_customer"){
                      $extra_where .= " AND id = '".$session[0]['id_user_customer']."' ";
                    } else {
                      $extra_where .= " AND id_customer = '".$session[0]['id_user_customer']."' ";
                    }
                  }
                }

                $data_info = $this->db->query("SELECT ".implode(",",$fields_arr)." FROM ".$form[0]['table_name']." WHERE ".$response['form']['primary_key']." = '".$data['edit_key']."' ".$extra_where." LIMIT 1 ;")->result_array();
                if (count($data_info)>0){
                  $response['data'] = $data_info[0];
                }
              }

              $response['success'] = 1;
          }

        }

      }



      echo json_encode($response);
  }

  public function export_table(){

    error_reporting(0);

    require_once('PHPExcel.php');
    $doc = new PHPExcel();
    $doc->setActiveSheetIndex(0);


    $abc= array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P");
    $column = 1;
    $row = 0;
    $wizard = new PHPExcel_Helper_HTML;

    $session = $this->db->query("SELECT id_user_admin FROM sys_session where (md5(concat(id::text,'-',id_user_admin)) = '".$_GET['token']."' )")->result_array();
    if (count($session)>0){
      $data = array();
      $table= $this->db->query("SELECT * FROM sys_table_tables where table_name = '".$_GET['table_name']."'")->result_array();
      if (count($table)>0){
        $fields = $this->db->query("SELECT * FROM sys_table_fields where table_name = '".$table[0]['table_name']."' ORDER BY field_order ASC")->result_array();

        $_where = "";
        if (isset($_GET['app_name']) && $_GET['app_name'] != ""){
          $_where = " WHERE id_app = (SELECT id from sys_app where lower(code) = '".$_GET['app_name']."' LIMIT 1) ";
        }
        if (isset($_GET['where']) && $_GET['where'] != ""){
          if ($_where == ""){
            $_where .= " WHERE ";
          } else {
            $_where .= " AND ";
          }
          $_where .= $_GET['where'];
        }

        if ($table[0]['order'] != ""){
          $order = $table[0]['order']. " ";
        } else {
          $order = $table[0]['edit_key']. " ASC ";
        }

        $fields_arr = array();
        /*
        array_push($fields_arr);
        */

        $data[0] = array();

        foreach (explode(" ",$_GET['filter']) as $key) {
          $_where_part = "";
          foreach ($fields as $field_v) {
            if (!isset($fields_arr[$field_v['field_name']])){
              switch ($field_v['html_format']) {
                case 'img':
                  //array_push($data[0],$field_v['label']);
                  $doc->getActiveSheet()->setCellValue($abc[$row].$column, $field_v['label']);
                  $doc->getActiveSheet()->getColumnDimension($abc[$row])->setAutoSize(true);
                  array_push($fields_arr, " CASE WHEN ".$field_v['field_name']." IS NULL THEN '' ELSE '1' END as ".$field_v['field_name']." ");//encode(".$field_v['field_name'].", 'base64')");
                  break;
                case 'currency_format':
                  //array_push($data[0],$field_v['label']);
                  array_push($fields_arr, " sign_before, sign_after, decimal_places,  '.' as decimal_separator, ',' as thousand_separator ");//encode(".$field_v['field_name'].", 'base64')");
                  break;
                case 'bool':
                  //array_push($data[0],$field_v['label']);
                  $doc->getActiveSheet()->setCellValue($abc[$row].$column, $field_v['label']);
                  $doc->getActiveSheet()->getColumnDimension($abc[$row])->setAutoSize(true);
                  array_push($fields_arr, " CASE WHEN ".$field_v['field_name']." IS TRUE THEN 'SI' ELSE 'NO' END as ".$field_v['field_name']." ");//encode(".$field_v['field_name'].", 'base64')");
                  break;
                case 'currency_format':
                case 'budget_detail':
                case 'customer_changes':
                case 'pdf':
                  //array_push($fields_arr, " sign_before, sign_after, decimal_places, decimal_separator, thousand_separator ");//encode(".$field_v['field_name'].", 'base64')");
                  break;
                case 'text_url':
                  $doc->getActiveSheet()->setCellValue($abc[$row].$column, $field_v['label']);
                  $doc->getActiveSheet()->getColumnDimension($abc[$row])->setAutoSize(true);
                  $row++;
                  $doc->getActiveSheet()->setCellValue($abc[$row].$column, $field_v['label']. " URL");
                  $doc->getActiveSheet()->getColumnDimension($abc[$row])->setAutoSize(true);
                  //array_push($data[0],$field_v['label']);
                  array_push($fields_arr,$field_v['field_name']);
                  break;
                case 'image_list':
                  $doc->getActiveSheet()->setCellValue($abc[$row].$column, $field_v['label']);
                  $doc->getActiveSheet()->getColumnDimension($abc[$row])->setWidth(20);
                  //array_push($data[0],$field_v['label']);
                  array_push($fields_arr,$field_v['field_name']);
                  break;
                default:
                  $doc->getActiveSheet()->setCellValue($abc[$row].$column, $field_v['label']);
                  $doc->getActiveSheet()->getColumnDimension($abc[$row])->setAutoSize(true);
                  //array_push($data[0],$field_v['label']);
                  array_push($fields_arr,$field_v['field_name']);
                  break;
              }
              if ($field_v['filter'] == "1"){
                if ($key != ""){
                  if ($_where_part != ""){
                    $_where_part .= " OR ";
                  }
                  $_where_part .= " lower(".$field_v['field_name']."::text) LIKE '%".strtolower($key)."%' ";
                }
              }
            }
            $row++;
          }
          if ($_where_part != ""){
            if ($_where == ""){
              $_where .= " WHERE ";
            } else {
              $_where .= " AND ";
            }
            $_where .= " (".$_where_part.") ";
          }
        }
        $data = $this->db->query("SELECT ".implode(",",$fields_arr)." FROM ".$table[0]['view_name']." ".$_where . " ORDER BY ". $order . " ;")->result_array();
      }


      //echo "<pre>".print_r($fields,1)."</pre>";
      //echo "<pre>".print_r($data,1)."</pre>";

      foreach ($data as $data_value) {
        $column++;
        $row = 0;

        foreach ($fields as $field_v) {
          switch ($field_v['html_format']) {
            case 'img':
              break;
            case 'currency_format':
            case 'budget_detail':
            case 'customer_changes':
            case 'pdf':
              //array_push($fields_arr, " sign_before, sign_after, decimal_places, decimal_separator, thousand_separator ");//encode(".$field_v['field_name'].", 'base64')");
              break;
            case 'image_list':

              $doc->getActiveSheet()->getStyle($abc[$row].$column)->getAlignment()->setIndent(1);

              $gdImage = imagecreatefromjpeg('media/'.explode("///",$data_value[$field_v['field_name']])[0]);
              // Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
              $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
              $objDrawing->setName('Sample image');$objDrawing->setDescription('Sample image');
              $objDrawing->setImageResource($gdImage);
              $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
              $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
              $objDrawing->setHeight(140);
              $objDrawing->setWidth(80);
              $objDrawing->setCoordinates($abc[$row].$column);


              $colWidth = $doc->getActiveSheet()->getColumnDimension($abc[$row])->getWidth();
              if ($colWidth == -1) { //not defined which means we have the standard width
                  $colWidthPixels = 64; //pixels, this is the standard width of an Excel cell in pixels = 9.140625 char units outer size
              } else {                  //innner width is 8.43 char units
                  $colWidthPixels = ($colWidth * 7.0017094); //colwidht in Char Units * Pixels per CharUnit
              }
              $offsetX = $colWidthPixels - $objDrawing->getWidth(); //pixels
              $objDrawing->setOffsetX(($offsetX/2)); //pixels
              $objDrawing->setOffsetY(2); //pixels

              $objDrawing->setWorksheet($doc->getActiveSheet());

              $doc->getActiveSheet()->getRowDimension($column)->setRowHeight($objDrawing->getHeight());

              //$doc->getActiveSheet()->setCellValue($abc[$row].$column, explode("///",$data_value[$field_v['field_name']])[0]);
              break;
            case 'text_url':


              $link = explode('href="',$data_value[$field_v['field_name']]);
              if (count($link)>0){
                $link = explode('"',$link[1])[0];
                $url = str_replace('https://www.', '', $link);
                $doc->getActiveSheet()->getCellByColumnAndRow($row,$column)->getHyperlink()->setUrl('http://www.'.$url);
              }

              $richText = $wizard->toRichTextObject($data_value[$field_v['field_name']]);
              $doc->getActiveSheet()->setCellValue($abc[$row].$column, $richText);
              $row++;
              $doc->getActiveSheet()->setCellValue($abc[$row].$column, 'http://www.'.$url);

              /*
              $styleArray = array(
                  'alignment' => array(
                      'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
                      'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                  ),
                  "font"  => array(
                      "color" => array("rgb" => "FF0000")
                  )
              );

              $doc->getActiveSheet()->getStyle($abc[$row].$column)->applyFromArray($styleArray);
              $doc->getActiveSheet()->getStyle($abc[$row].$column)->getFont()->getColor()->setRGB("FF0000");
              */

              break;
            case 'date':
              $dt = new DateTime($data_value[$field_v['field_name']], new DateTimeZone('UTC'));
              // change the timezone of the object without changing its time
              $dt->setTimezone(new DateTimeZone('America/Mexico_city'));
              $doc->getActiveSheet()->setCellValue($abc[$row].$column, $dt->format('Y-m-d H:i'));
              //2023-11-16 14:59
              break;
            default:
              $doc->getActiveSheet()->setCellValue($abc[$row].$column, $data_value[$field_v['field_name']]);
              break;
          }
          $row++;
        }

      }

      $style = array(
          'alignment' => array(
              'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
              'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
          )
      );
      $doc->getActiveSheet()->getDefaultStyle()->applyFromArray($style);


      $file_name = "exportar_".$_GET['table_name'].".xls";

      //$doc->getActiveSheet()->fromArray($data, null, 'A1');
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="'.$file_name.'"');
      header('Cache-Control: max-age=0');

      // Do your stuff here
      $writer = PHPExcel_IOFactory::createWriter($doc, 'Excel5');

      $writer->save('php://output');
    }


  }

  public function generate_table()
	{
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Allow: GET, POST, OPTIONS");

    header("Content-Type: application/json");

    $data = json_decode(file_get_contents('php://input'), true);
    /*
    $data = array(
      "table_name"=>"tsys_users",
      "filter"=>"",
      "page"=>"1",
      "limit"=>"50"
    );
    */

    $response = array("success"=>0);

    if (isset($data['is_store']) && $data['is_store'] == "1"){
      //SE TRATA DE UNA TIENDA DE CLIENTE FINAL
      $session = $this->db->query("SELECT id_customer_site, id_customer_site_user, COALESCE((SELECT is_admin FROM sys_customer_sites_users where id = id_customer_site_user limit 1),FALSE) as is_admin from sys_session_sites where active = TRUE AND md5(CONCAT(id::text,'-',id_customer_site_user)) = '".$data['token']."' LIMIT 1")->result_array();
    } else {
      $session = $this->db->query("SELECT id_user_customer, id_user_admin FROM sys_session where (md5(concat(id::text,'-',id_user_admin)) = '".$data['token']."' OR md5(concat(id::text,'-',id_user_customer)) = '".$data['token']."') ")->result_array();
    }


    if (count($session)>0){
      //Sesión activa

      $table= $this->db->query("SELECT * FROM sys_table_tables where table_name = '".$data['table_name']."'")->result_array();

      if (count($table)>0){
        $response['page_current'] = intval($data['page']);
        $response['page_total'] = 1;
        $response['table'] = $table[0];
        $fields_arr = array();
        $_where = "";
        if ($data['app_name'] != ''){
          $_where = " WHERE id_app = (SELECT id from sys_app where lower(code) = '".$data['app_name']."' LIMIT 1) ";
        }
        array_push($fields_arr,$table[0]['primary_key']);
        $fields = $this->db->query("SELECT * FROM sys_table_fields where table_name = '".$data['table_name']."' ORDER BY field_order ASC")->result_array();

        if (isset($data['where']) && $data['where'] != ""){
          if ($_where == ""){
            $_where .= " WHERE ";
          } else {
            $_where .= " AND ";
          }
          $_where .= $data['where'];
        }

        foreach (explode(" ",$data['filter']) as $key) {
          $_where_part = "";
          foreach ($fields as $field_v) {
            if (!isset($fields_arr[$field_v['field_name']])){
              switch ($field_v['html_format']) {
                case 'img':
                  array_push($fields_arr, " CASE WHEN ".$field_v['field_name']." IS NULL THEN '' ELSE '1' END as img ");//encode(".$field_v['field_name'].", 'base64')");
                  break;
                case 'currency_format':
                  array_push($fields_arr, " sign_before, sign_after, decimal_places,  '.' as decimal_separator, ',' as thousand_separator ");//encode(".$field_v['field_name'].", 'base64')");
                  break;
                case 'budget_detail':
                case 'customer_changes':
                case 'edit_store':
                case 'pdf':
                  //array_push($fields_arr, " sign_before, sign_after, decimal_places, decimal_separator, thousand_separator ");//encode(".$field_v['field_name'].", 'base64')");
                  break;
                default:
                  array_push($fields_arr,$field_v['field_name']);
                  break;
              }
              if ($field_v['filter'] == "1"){
                if ($key != ""){
                  if ($_where_part != ""){
                    $_where_part .= " OR ";
                  }
                  $_where_part .= " lower(".$field_v['field_name']."::text) LIKE '%".strtolower($key)."%' ";
                }
              }
            }
          }
          if ($_where_part != ""){
            if ($_where == ""){
              $_where .= " WHERE ";
            } else {
              $_where .= " AND ";
            }
            $_where .= " (".$_where_part.") ";
          }
        }

        if ($response['table']['order'] != ""){
          $order = $response['table']['order']. " ";
        } else {
          $order = $response['table']['edit_key']. " ASC ";
        }

        $response['fields'] = $fields;
        $response['currency'] = array(
          "sign_before"=>"",
          "sign_after"=>"",
          "decimal_places"=>"2",
          "decimal_separator"=>".",
          "thousand_separator"=>","
        );


        if (isset($data['is_store']) && $data['is_store'] == "1"){
          //SE TRATA DE UNA TIENDA DE CLIENTE FINAL
          if ($_where == ""){
            $_where = " WHERE ";
          } else {
            $_where .= " AND ";
          }
          $_where .= " id_customer_site = '".$session[0]['id_customer_site']."' ";

          if ($session[0]['is_admin'] != "1"){
            if ($data['table_name'] == "tcustomer_store_users"){
              $_where .= " AND id = '".$session[0]['id_customer_site_user']."' ";
            } else {
              $_where .= " AND id_customer_site_user = '".$session[0]['id_customer_site_user']."' ";
            }
          }
          $currency = $this->db->query("select sign_before, sign_after, decimal_places,
           '.' as decimal_separator, ',' as thousand_separator from sys_currency where
          currency = (SELECT customer_currency from sys_user_customer where id =(SELECT id_customer from sys_customer_sites where id = '".$session[0]['id_customer_site']."' LIMIT 1) LIMIT 1)")->result_array();
          if (count($currency)>0){
            $response['currency'] = $currency[0];
          }

        } elseif (isset($session[0]['id_user_customer']) && $session[0]['id_user_customer'] != ""){
          if ($_where == ""){
            $_where = " WHERE ";
          } else {
            $_where .= " AND ";
          }
          $_where .= " id_customer = '".$session[0]['id_user_customer']."' ";
          $currency = $this->db->query("select sign_before, sign_after, decimal_places,
           '.' as decimal_separator, ',' as thousand_separator from sys_currency where
          currency = (SELECT customer_currency from sys_user_customer where id ='".$session[0]['id_user_customer']."' LIMIT 1)")->result_array();
          if (count($currency)>0){
            $response['currency'] = $currency[0];
          }
        }

        $response['total'] = $this->db->query("SELECT count(*) as count FROM ".$table[0]['view_name']." ".$_where)->result_array()[0]['count'];
        $response['data'] = $this->db->query("SELECT ".implode(",",$fields_arr)." FROM ".$table[0]['view_name']." ".$_where . " ORDER BY ". $order . " LIMIT ".$data['limit']. " OFFSET ".($data['limit']*($data['page']-1)).";")->result_array();

        for ($i=0; $i < count($response['data']); $i++) {
          foreach ($fields as $field_v) {
            switch ($field_v['html_format']) {
              case 'currency_format':
                $format_money = "0".$response['data'][$i]['thousand_separator']."000".$response['data'][$i]['decimal_separator'];
                for ($j=0; $j < intval($response['data'][$i]['decimal_places']); $j++) {
                  $format_money .= "0";
                }
                $response['data'][$i][$field_v['field_name']] = $response['data'][$i]['sign_before']." ". $format_money . " ".$response['data'][$i]['sign_after'];
                break;
            }
          }

        }

        $response['success'] = 1;
      }


    }



    echo json_encode($response);
  }
}
