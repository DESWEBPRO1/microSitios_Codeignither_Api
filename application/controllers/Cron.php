<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

	public function index(){
	}

  public $min_tb_number = 0;
  public $max_tb_number = 14;


	public $parentcode_price = array(
		"1"=> array(
			"MXN"=>array()
		)
	);
	/*
	array(
		"1"=> array(
			"MXN"=>array(
				"AGMT 023"=>"73.66",
			)
		)
	);
	*/

	public function normalize_category(){
		$mc_list = $this->db->query("SELECT id, category FROM tb_categories WHERE id_parent IS NULL")->result_array();
		foreach ($mc_list as $mc) {
			$href = urlencode(strtolower(str_replace(" ","-",$mc['category'])));
			$this->db->query("UPDATE tb_categories SET href_path = '".$href."' WHERE id = '".$mc['id']."'");
			$cat_list = $this->db->query("SELECT id, category FROM tb_categories where id_parent = '".$mc['id']."'")->result_array();
			foreach ($cat_list as $cat) {
				$href_new = urlencode(strtolower($cat['category']));
				$this->db->query("UPDATE tb_categories SET href_path = '".$href."-".$href_new."' WHERE id = '".$cat['id']."'");
			}
		}
	}

	public function stock(){

		$this->db->query("INSERT INTO sys_cron (db_start, name) VALUES (NOW(),'existencias')");
		$insertId = $this->db->insert_id();


		$apps = array(
			"1"=>"http://www.contenidopromo.com/wsds_obsoleto-2024/_files/mx_stock.json",
			"2"=>"http://www.contenidopromo.com/wsds_obsoleto-2024/_files/col_stock.json",
			"3"=>"http://www.contenidopromo.com/wsds_obsoleto-2024/_files/gtm_stock.json"
		);

		echo "stock";

		foreach ($apps as $id_app => $stock_url) {
			$options=array(
					"http"=>array('header' => "User-Agent:PromoApp_Cron/1.0\r\n"),
					"ssl"=>array(
							"verify_peer"=>false,
							"verify_peer_name"=>false,
					)
			);

			$context  = stream_context_create($options);
			$json_first = file_get_contents($stock_url,false,$context);

			$json_first = $this->eliminar_especiales($json_first);

			$json = json_decode(json_encode(json_decode("$json_first")), true);
			echo "<pre>".print_r($json,1)."</pre>";
			echo "<hr>";

			foreach ($json as $item_code => $stock) {
				$this->db->query("UPDATE tb_products SET stock = '".floatval($stock)."' where id_app = '".$id_app."' and item_code = '".$item_code."'");
			}

		}


		$this->db->query("UPDATE sys_cron SET db_end = NOW() where id = '".$insertId."'");

		//ACTUALIZAR TODO
		$this->db->query("TRUNCATE TABLE tb_products_search_temp;");
		$this->db->query("insert into tb_products_search_temp select * from vtb_products_categories;");
		$this->db->query("insert into tb_products_search_temp select * from vtb_products_categories_esp_other;");
		$this->db->query("insert into tb_products_search_temp select * from vtb_products_categories_esp_promoopcion;");
		$this->db->query("TRUNCATE TABLE tb_products_search;");
		$this->db->query("INSERT INTO tb_products_search SELECT * FROM tb_products_search_temp;");

		for ($i=$this->min_tb_number; $i <= $this->max_tb_number; $i++) {
			$this->db->query("TRUNCATE TABLE tb_products_search_".$i.";");
			$this->db->query("INSERT INTO tb_products_search_".$i." SELECT * FROM tb_products_search;");
		}
		/*
		*/


	}

	public function update_users_info(){
		$users = $this->db->query("SELECT * FROM sys_user_customer WHERE id_app = 1")->result_array();

		//Actualizar info por middleware
		foreach ($users as $user) {
			$fields = array('source'=>'web',
			'sales_org'=>'1010',
			'web_access'=>'',
			'card_code'=>$user['cardcode'],
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

			if (
				$customer['customer_level'] != $user['customer_level']
			){
							echo "<pre>".print_r($user,1)."</pre>";
							echo "<pre>".print_r($customer,1)."</pre></hr>";
							$this->db->query("update sys_user_customer set customer_level = '".$customer['customer_level']."' where id = '".$user['id']."'");
			}

		}
	}


	public function exec(){

		$body_html = "<h1>[MICROSITIOS] ACTUALIZACIÓN DE PRODUCTOS</h1><br>USER_AGENT: <u>".$_SERVER['HTTP_USER_AGENT']. "</u><br><br>";

		$this->db->query("INSERT INTO sys_cron (db_start, name) VALUES (NOW(),'productos,precios')");
		$insertId = $this->db->insert_id();

		$this->normalize_category();

		date_default_timezone_set('America/Mexico_City');
		$date = new DateTime();
		$date = $date->format("Y-m-d H:i:s");
		echo "--START:".$date."--<br>";
		$body_html .= "--START:".$date."--<br>";;

		ini_set('max_execution_time', 0); //0=NOLIMIT
		ini_set("allow_url_fopen", true);
		set_time_limit(0);

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

		$apps = array(
			
			"1"=>array(
				"url"=>"https://www.contenidopromo.com/ws/promoapp/products_sync_ms.php?sales_org=1010",
				"list"=>"E",
				"currency"=>array(
					"MXN"
				),
				"levels"=>array(
					"E"=>3,
					"EP"=>10,
					"B"=>17,
					"BP"=>20,
					"S1"=>23,
					"S2"=>25,
					"S3"=>27,
					"S4"=>28.5,
					"S5"=>30,
					"S6"=>32
				)
			),
			"2"=>array(
				"url"=>"https://www.contenidopromo.com/ws/promoapp/products_sync_ms.php?sales_org=1210",
				"list"=>"base",
				"currency"=>array(
					"COP"
				),
				"levels"=>array(
					"E"=>0,
					"B"=>0,
					"BP"=>0,
					"S1"=>0
				)
			),
			
		 	 
			
			"3"=>array(
				"url"=>"https://www.contenidopromo.com/ws/promoapp/products_sync_ms.php?sales_org=1310",
				"list"=>"E",
				"currency"=>array(
					"GTQ",
					"USD"
				),
				"levels"=>array(
					"E"=>0,
					"EP"=>0,
					"B"=>0,
					"BP"=>0,
					"S0"=>0,
					"S1"=>0,
					"S2"=>0,
					"S3"=>0
				)
			)
		);
		/*
		if(ini_get('allow_url_fopen') ) {
			echo '<br>allow_url_fopen is enabled. file_get_contents should work well<br><br>';
		} else {
		    die('<br>allow_url_fopen is disabled. file_get_contents would not work');
		}
		*/

    foreach ($apps as $id_app => $update_url) {

			$body_html .= "<h1>".$id_app. " = ". $update_url["url"]."</h1>";

			$options=array(
					"http"=>array(
								'header' => "User-Agent:PromoApp_Cron/1.0\r\n".
														"Access-Token:0b513f96080842a12bd36e2bd42273a6\r\n"
					),
			    "ssl"=>array(
			        "verify_peer"=>false,
			        "verify_peer_name"=>false,
			    )
			);

			$context  = stream_context_create($options);
      $json_first = file_get_contents($update_url["url"],false,$context);

			$json_first = $this->eliminar_especiales($json_first);
			$json = json_decode($json_first, true);

			/*
			if ($id_app  == 2){
				echo "<pre>".print_r($json,1)."</pre>";
			}
			*/


			echo "<h2>PRODUCTOS (".count($json['products']).")</h2>";
			$body_html .= "<h2>PRODUCTOS (".count($json['products']).")</h2>";
			//PRODUCTOS
			if (isset($json["products"]) && count($json["products"])>0){
				$this->db->query("DELETE FROM tb_products_categories_temp WHERE id_app = '".$id_app."';");
				$this->db->query("UPDATE tb_products set update_status = 0 where id_app = '".$id_app."'");
			} else {
				$this->db->query("UPDATE tb_products set update_status = 1 where id_app = '".$id_app."'");
			}

			echo "<ul>";
			$body_html .= "<ul>";
			foreach ($json['products'] as $itemcode => $details) {

						if ($details['attr_active'] != "1"){
							//echo "<br>eliminar:<pre>".print_r($details,1)."</pre>";
							continue;
						}

						$price_default = array();
						foreach ($update_url['currency'] as $currency) {
							$price_default[$currency] = 0;
							if (
								isset($this->parentcode_price[$id_app]) &&
								isset($this->parentcode_price[$id_app][$currency])
							){
								if (
									isset($this->parentcode_price[$id_app][$currency][$details["parent_code"]]) &&
									floatval($this->parentcode_price[$id_app][$currency][$details["parent_code"]]) > 0
								){
									$price_default[$currency] = $this->parentcode_price[$id_app][$currency][$details["parent_code"]];
								}
							}

							$details['price_'.strtolower($currency).'_base'] = $price_default[$currency];

							foreach ($update_url['levels'] as $level=>$discount) {
								if ($price_default[$currency] > 0 && $discount > 0 && $discount < 50){
									$details['price_'.strtolower($currency).'_'.strtolower($level)] = floatval($price_default[$currency])*((100-$discount)*0.01);
								} else {
									$details['price_'.strtolower($currency).'_'.strtolower($level)] = $price_default[$currency];
								}
							}
						}

						foreach ($update_url['currency'] as $currency) {
							if ($price_default[$currency] == 0){
								if (isset($details['prices']) && is_array($details['prices'])){
									foreach ($details['prices'] as $precio) {

										if (strtolower($precio['currency'] ) != strtolower($currency)){
											continue;
										}

										if ($precio['customer_level'] == $update_url['list']){
											$details['price_'.strtolower($currency).'_base'] = $precio['base_price'];
										}
										$details['price_'.strtolower($currency).'_'.strtolower($precio['customer_level'])] = $precio['base_price'];
										if ($details["attr_preciounico"] == "0"){
											//Solo si propiedad precio unico no activa
											$details['price_'.strtolower($currency).'_'.strtolower($precio['customer_level'])] += $precio['level_discount'];
										}
									}
								} else {
									echo "<br>-No existen precios-<pre>".print_r($details['item_code'],1)."</pre><br>";

									foreach ($update_url['currency'] as $currency) {
										$details['price_'.strtolower($currency).'_base'] = 0;
										foreach ($update_url['levels'] as $level=>$discount) {
											$details['price_'.strtolower($currency).'_'.strtolower($level)] = 0;
										}
									}
								}
							}
						}

						if ($id_app == 3){
							//echo "<pre>".print_r($details,1)."</pre>";
							//die();
						}

						$price_keys = "";
						$price_values = "";
						$price_keys_values = "";
						foreach ($update_url['currency'] as $currency) {
							$currency = strtolower($currency);
							$price_keys.= ', price_'.$currency.'_base';
							$price_values.= ", '".$details['price_'.$currency.'_base']."' ";
							$price_keys_values.= ", price_".$currency."_base = '".$details['price_'.$currency.'_base']."'";
							foreach ($update_url['levels'] as $level=>$discount) {
								$level = strtolower($level);
								$price_keys.= ', price_'.$currency.'_'.$level;
								$price_values.= ", '".$details['price_'.$currency.'_'.$level]."' ";
								$price_keys_values.= ", price_".$currency."_".$level." = '".$details['price_'.$currency.'_'.$level]."'";
							}
						}

						$details["talla"] = $this->getTalla($details['parent_code'], $itemcode);
						//echo "<pre>".print_r($details,1)."</pre>";

						$category_query = "SELECT id FROM vapp_categories_import where id_app = '".$id_app."' and hijos = 0 ";


						$details["subfamilia"] = strtolower($details["subfamilia"]);
						$details["familia"] = strtolower($details["familia"]);

						$category = array();
		        //$category = $this->db->query($category_query . " and (LOWER(family) like LOWER('%;". $itemcode .";%') OR LOWER(family) like LOWER('%;". $details["parent_code"] .";%'))")->result_array();
		        if (count($category) == 0){
							$category_query = "";
							if ($details["subfamilia"] != ""){
								$details['category_name'] = $details["subfamilia"];
			          //$category_query .= " and (LOWER(category) = LOWER('". $this->eliminar_acentos($details["subfamilia"]) ."')  or LOWER(family) like LOWER('%;". $this->eliminar_acentos($details["subfamilia"]) .";%'))";
			        } else if ($details["familia"] != ""){
								$details['category_name'] = $details["familia"];
			          //$category_query .= " and (LOWER(category) = LOWER('". $this->eliminar_acentos($details["familia"]) ."') )";
			        } else {
								//array_push($lostCategory,$itemcode);
			          //continue;
			        }
							if ($category_query != ""){
			        	$category = $this->db->query($category_query)->result_array();
							}
						}

						if ($details["fuelle"] == "0"){
							$details["fuelle"] = "";
						}
						if ($details["baterias"] == "0"){
							$details["baterias"] = "";
						}
						if ($details["descripcion"] == "*"){
							$details["descripcion"] = "";
						}

						$product = $this->db->query("SELECT * FROM tb_products where item_code = '". $itemcode ."' and id_app = '".$id_app."'")->result_array();

						if (count($product) == 0 ){
							$query = "INSERT INTO tb_products
															(id_app,
															parent_code,
															item_code,
															nombre,
															descripcion,
															material,
															talla,
															category_name,
															color_name,
															color_hex,
															tamano,
															fuelle,
															capacidad,
															baterias,
															printing_tech,
															printing_area,
															producto_altura,
															producto_profundidad,
															producto_base,
															producto_peso,
															empaque_cantidad,
															producto_volumen,
															keywords,
															stock,
															attr_active,
															attr_outlet,
															attr_promocion,
															attr_preciounico,
															attr_nuevo,
															prop_grabable,
															prop_bluetooth,
															prop_algodon,
															prop_hechoenmexico,
															prop_resina,
															prop_ecologico,
															prop_wifi,
															required,
															marca,
															update_status
															".$price_keys.")
															VALUES
															(
																'".$id_app."',
																'".$details["parent_code"]."',
																'".$details["item_code"]."',
																'".$details["nombre"]."',
																'".$details["descripcion"]."',
																'".$details["material"]."',
																'".$details["talla"]."',
																'".$details["category_name"]."',
																'".rtrim(ltrim($details["color_name"]))."',
																'".$details["color_hex"]."',
																'".$details["tamano"]."',
																'".$details["fuelle"]."',
																'".$details["capacidad"]."',
																'".$details["baterias"]."',
																'".$details["printing_tech"]."',
																'".$details["printing_area"]."',
																'".$details["producto_altura"]."',
																'".$details["producto_profundidad"]."',
																'".$details["producto_base"]."',
																'".$details["producto_peso"]."',
																'".$details["empaque_cantidad"]."',
																'".$details["producto_volumen"]."',
																'".$details["keywords"]."',
																0,
																'".$details["attr_active"]."',
																'".$details["attr_outlet"]."',
																'".$details["attr_promocion"]."',
																'".$details["attr_preciounico"]."',
																'".$details["attr_nuevo"]."',
																'".$details["prop_grabable"]."',
																'".$details["prop_bluetooth"]."',
																'".$details["prop_algodon"]."',
																'".$details["prop_hechoenmexico"]."',
																'".$details["prop_resina"]."',
																'".$details["prop_ecologico"]."',
																'".$details["prop_wifi"]."',
																'".$details["required"]."',
																'".$details["marca"]."',
																2
																".$price_values."
															);
															";
								$this->db->query($query);
								echo "<li>".$itemcode." INSERTADO</li>";
								$body_html .= "<li>".$itemcode." INSERTADO</li>";
								//break;
						} else {

							$this->db->query("UPDATE tb_products SET
								update_status = 1 where item_code = '". $itemcode ."' and id_app = '".$id_app."'");


							$compare = $this->compareItems($details, $product[0]);

							if (count($compare) > 0){
								/*
								echo "<pre>".print_r($compare,1)."</pre>";
								echo "<pre>".print_r($details,1)."</pre>";
								die();
								*/
								//EDITAR PRODUCTO
								//echo "<pre>".print_r($details,1)."</pre>";

								$query = "UPDATE tb_products
													SET
													parent_code = '".$details["parent_code"]."',
													nombre = '".$details["nombre"]."',
													descripcion = '".$details["descripcion"]."',
													material = '".$details["material"]."',
													talla = '".$details["talla"]."',
													category_name = '".$details["category_name"]."',
													color_name = '".$details["color_name"]."',
													color_hex = '".$details["color_hex"]."',
													tamano = '".$details["tamano"]."',
													fuelle = '".$details["fuelle"]."',
													capacidad = '".$details["capacidad"]."',
													baterias = '".$details["baterias"]."',
													printing_tech = '".$details["printing_tech"]."',
													printing_area = '".$details["printing_area"]."',
													producto_altura = '".$details["producto_altura"]."',
													producto_profundidad = '".$details["producto_profundidad"]."',
													producto_base = '".$details["producto_base"]."',
													producto_peso = '".$details["producto_peso"]."',
													empaque_cantidad = '".$details["empaque_cantidad"]."',
													producto_volumen = '".$details["producto_volumen"]."',
													keywords = '".$details["keywords"]."',
													attr_active = '".$details["attr_active"]."',
													attr_outlet = '".$details["attr_outlet"]."',
													attr_promocion = '".$details["attr_promocion"]."',
													attr_preciounico = '".$details["attr_preciounico"]."',
													attr_nuevo = '".$details["attr_nuevo"]."',
													prop_grabable = '".$details["prop_grabable"]."',
													prop_bluetooth = '".$details["prop_bluetooth"]."',
													prop_algodon = '".$details["prop_algodon"]."',
													prop_hechoenmexico = '".$details["prop_hechoenmexico"]."',
													prop_resina = '".$details["prop_resina"]."',
													prop_ecologico = '".$details["prop_ecologico"]."',
													prop_wifi = '".$details["prop_wifi"]."',
													required = '".$details["required"]."',
													marca = '".$details["marca"]."',
													db_update = NOW()
													".$price_keys_values."
													WHERE item_code = '". $itemcode ."'
													and id_app = '".$id_app."'";

								$this->db->query($query);
								echo "<li>".$itemcode." MODIFICADO</li>";
								$body_html .= "<li>".$itemcode." MODIFICADO</li>";


								//break;
							}

						}
			}
			echo "</ul>";
			$body_html .= "</ul>";
			if (isset($json['products']) && count($json['products'])>0){


				$this->db->query("DELETE FROM tb_products WHERE
					update_status = 0 AND id_app = '".$id_app."'");

				$categories = $this->db->query("SELECT id,category, filter_categoryname, filter_parentcode,
					filter_itemcode FROM tb_categories tb where id_app = '".$id_app."' and (SELECT count(id)
					from tb_categories tb1 where tb1.id_parent = tb.id) = 0 AND (filter_categoryname <> '' OR filter_itemcode <> '' OR filter_parentcode <> '') ")->result_array();
				foreach ($categories as $category) {

					$where_cat = "";
					if ($category['filter_categoryname'] != ""){
						$where_cat .= " ';".strtolower($category["filter_categoryname"]).";' LIKE CONCAT('%;',lower(category_name),';%') ";

					}
					if ($category['filter_parentcode'] != ""){
						if ($where_cat != ""){
							$where_cat .= " OR ";
						}
						$where_cat .= " ';".strtolower($category["filter_parentcode"]).";' LIKE CONCAT('%;',lower(parent_code),';%') ";
					}
					if ($category['filter_itemcode'] != ""){
						$where_cat .= " OR ";
						$where_cat .= " ';".strtolower($category["filter_itemcode"]).";' LIKE CONCAT('%;',lower(item_code),';%') ";
					}
					//echo "<br>".$where_cat."<br>";


					$query = "INSERT INTO tb_products_categories_temp
						(id_product, id_category,id_app)
						SELECT id,'".$category["id"]."',id_app FROM tb_products where id_app = '".$id_app."' and (".$where_cat.");";
					//echo $query."<br><br>";
					$this->db->query($query);
				}
				//Nuevas categorías

			}



			echo "<hr><h2>IMAGENES (".count($json['images']).")</h2>";
			//$body_html .= "<hr><h2>IMAGENES (".count($json['images']).")</h2>";
			//IMAGENES
			if (isset($json["images"]) && count($json["images"])>0){
      	$this->db->query("UPDATE tb_products_images set update_status = 0 where id_app = '".$id_app."'");
			} else {
      	$this->db->query("UPDATE tb_products_images set update_status = 1 where id_app = '".$id_app."'");
			}

			echo "<ul>";
			$body_html .= "<ul>";
			foreach ($json['images'] as $image_name => $details) {
						//echo "<pre>".print_r($details,1)."</pre>";

						//Check if already exists
						$image = $this->db->query("SELECT id, db_update_server FROM tb_products_images where name = '". $image_name ."' and id_app = '".$id_app."'")->result_array();
		        //echo "<br>".$itemcode. ":".count($product);

						$insert = FALSE;
						if (count($image)>0 ){
							if ($details["date"] > $image[0]['db_update_server']) {
								//Modificar
								$this->db->query("UPDATE tb_products_images SET
									update_status = 1,
									db_update_server = '".$details["date"]."',
									url = '".$details["url"]."',
									type = '".$details["type"]."',
									parent_code = '".$details["parent_code"]."',
									item_code = '".$details["item_code"]."'
									where name = '". $image_name ."' and id_app = '".$id_app."'");
								echo "<li>".$image_name." MODIFICADO</li>";
								$body_html .= "<li>".$image_name." MODIFICADO</li>";
							}
						} else {
							//Insertar
							$this->db->query("INSERT INTO tb_products_images
								(id_app, name, db_update_server, update_status, url, type, parent_code, item_code)
								values
								(
									'".$id_app."' ,
									'".$image_name."',
									'".$details["date"]."',
									2,
									'".$details["url"]."',
									'".$details["type"]."',
									'".$details["parent_code"]."',
									'".$details["item_code"]."'
								)");
								echo "<li>".$image_name." INSERTADO</li>";
								$body_html .= "<li>".$image_name." INSERTADO</li>";
						}
			}
			echo "</ul>";
			$body_html .= "</ul>";
			if (isset($json["images"]) && count($json["images"])>0){
				//Eliminar imagenes que ya no se usan de esa app
				/*
				$this->db->query("DELETE FROM tb_products_images
					WHERE update_status = 0 AND id_app = '".$id_app."'");
					*/
			}


			echo "<hr>";
			$body_html .= "<hr>";
    }

		$this->db->query("TRUNCATE TABLE tb_products_categories;");
		$this->db->query("INSERT INTO tb_products_categories SELECT * FROM tb_products_categories_temp;");



		foreach ($apps as $id_app => $update_url) {
			/*
			SELECT category_name,  string_agg(item_code, ','),count(*) from tb_products where id_app = 1
			and id not in (SELECT id_product from tb_products_categories)
			group by category_name  order by count(*) desc
			*/
			$products_without_category = $this->db->query("SELECT category_name,  string_agg(item_code, ','),count(*) from tb_products
				where id_app = '".$id_app."'
				and id not in (SELECT id_product from tb_products_categories)
				group by category_name  order by count(*) desc")->result_array();
			if (count($products_without_category)>0){
				echo "</hr><h1>PRODUCTOS SIN CATEGORÍA: ".$id_app."</h1>";
				$body_html .= "</hr><h1>PRODUCTOS SIN CATEGORÍA: ".$id_app."</h1>";

				echo "<pre>".print_r($products_without_category,1)."</pre></hr>";
				$body_html .= "<pre>".print_r($products_without_category,1)."</pre></hr>";
			}
		}

		//ACTUALIZAR TODO
		$this->db->query("TRUNCATE TABLE tb_products_search_temp;");
		$this->db->query("insert into tb_products_search_temp select * from vtb_products_categories;");
		$this->db->query("insert into tb_products_search_temp select * from vtb_products_categories_esp_other;");
		$this->db->query("insert into tb_products_search_temp select * from vtb_products_categories_esp_promoopcion;");
		$this->db->query("TRUNCATE TABLE tb_products_search;");
		$this->db->query("INSERT INTO tb_products_search SELECT * FROM tb_products_search_temp;");

		for ($i=$this->min_tb_number; $i <= $this->max_tb_number; $i++) {
			$this->db->query("TRUNCATE TABLE tb_products_search_".$i.";");
			$this->db->query("INSERT INTO tb_products_search_".$i." SELECT * FROM tb_products_search;");
		}
		/*
		*/

		$date = new DateTime();
		$date = $date->format("Y-m-d H:i:s");
		echo "<br>--END1:".$date."--<br>";
		$body_html .= "<br>--END1:".$date."--<br>";


		$this->db->query("UPDATE sys_cron SET db_end = NOW() where id = '".$insertId."'");

		$title = "[MICROSITIOS] ACTUALIZACIÓN DE PRODUCTOS";


		$this->load->model('Email');
		$this->Email->sendMail(
			array("soporte.ecommerce@promoopcion.com"),
			array(
				"desarrolloweb@promoopcion.com",
				"promoopcion@bersta.mx",
				"equintero@promoopcion.com",
				"sistemas14@promoopcion.com",
				"sistemas2@promoopcion.com"
			),
			$title,$body_html
		);

	}

	function compareItems($itemSync, $itemCurrent){
		$compare = array();

		//echo "<br><br>COMPARE<br><br>";
		//echo "<pre>".print_r($itemSync,1)."</pre>";

		foreach ($itemCurrent as $key => $value) {

			$key = strtolower($key);

			if ($key == "id" || $key == "id_app" || $key == "_version"
			|| $key == "db_date"  || $key == "db_update"  || $key == "db_update_server"
		  || $key == "update_status"  || $key == "published" || $key == "stock" ){
				continue;
			}

			if (strpos($key, 'price_') === 0 ) {
					if (isset($itemSync[$key])){
						if ((string) floatval($value) != (string)floatval($itemSync[$key])) {
							echo "<span style='color:red'>".$key.": '". floatval($value). "' != '".floatval($itemSync[$key]) ."'</span><br>";
							$compare[$key] = array(
								$value,
								floatval($itemSync[$key])
							);
						}
					}
			} else {
				if ($value != $itemSync[$key]) {
					echo "<span style='color:red'>".$key.": '". $value. "' != '".$itemSync[$key] ."'</span><br>";
					$compare[$key] = array(
						$value,
						$itemSync[$key]
					);
				}
			}
		}
		//die();
		return $compare;
	}

	function eliminar_especiales($cadena){

		$cadena = str_replace(
		array('&Aacute;','&aacute;',
					'&Eacute;','&eacute;',
					'&Iacute;','&iacute;',
					'&Oacute;','&oacute;',
					'&Uacute;','&uacute;',
					'&Ntilde;','&ntilde;',
					'\u00a4',
					'\u00e0','\u00a2',
					'\u00a0','\u00b5',//á,Á
					'\u00a1','\u00d6',
					'\u0082','\u0090',//é
					'\u00a3','\u00e9',//ú
					'\u00ff'),
		array('Á','á',
					'É','é',
					'Í','í',
					'Ó','ó',
					'Ú','ú',
					'Ñ','ñ',
					'ñ',
					'Ó','ó',
					'á','Á',
					'í','Í',
					'é','É',
					'ú','Ú',
					' '),
		$cadena
		);

		return $cadena;
	}


  function eliminar_acentos($cadena){

      $cadena = strtolower($cadena);

      //Reemplazamos la A y a
      $cadena = str_replace(
      array('&Aacute;','&aacute;','Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
      array('A','a','A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
      $cadena
      );

      //Reemplazamos la E y e
      $cadena = str_replace(
      array('&Eacute;','&eacute;','É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
      array('E','e','E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
      $cadena );

      //Reemplazamos la I y i
      $cadena = str_replace(
      array('&Iacute;','&iacute;','Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
      array('I','i','I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
      $cadena );

      //Reemplazamos la O y o
      $cadena = str_replace(
      array('&Oacute;','&oacute;','Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
      array('O','o','O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
      $cadena );

      //Reemplazamos la U y u
      $cadena = str_replace(
      array('&Uacute;','&uacute;','Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
      array('U','&uacute;','U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
      $cadena );

      //Reemplazamos la N, n, C y c
      $cadena = str_replace(
      array('Ñ', 'ñ', 'Ç', 'ç'),
      array('N', 'n', 'C', 'c'),
      $cadena
      );

      return $cadena;
  }

	function getTalla($parent_code, $itemcode){
		$talla_return = "";

		if ($parent_code != $itemcode){

			$tallas = array(
				"XS",
				"CH",
				"S",
				"M",
				"G",
				"EG",
				"L",
				"XL",
				"XXL",
				"2EG"
			);

			foreach ($tallas as $talla) {
				if ($this->endsWith($itemcode, "-".$talla)){
					$talla_return = $talla;
					break;
				}
			}

		}

		return $talla_return;
	}

	function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if( !$length ) {
	        return true;
	    }
	    return substr( $haystack, -$length ) === $needle;
	}

}
