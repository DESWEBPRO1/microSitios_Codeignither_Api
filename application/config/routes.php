<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['test'] = 'Api/test';

$route['getLoginInfo'] = 'Api/getLoginInfo';


$route['file/(:any)'] = 'Api/file/$1';
$route['file/(:any)/(:any)'] = 'Api/file/$1/$2';
$route['brand/(:any)'] = 'Api/brand/$1';
$route['image/(:any)'] = 'Api/image/$1';

$route['media_store/(:any)/(:any)'] = 'Api/media_store/$1/$2';
$route['media/(:any)/(:any)/(:any)'] = 'Api/media/$1/$2/$3';
$route['token_validate'] = 'Api/token_validate';

$route['authentication_store'] = 'Api/authentication_store';
$route['get_store'] = 'Api/get_store';
$route['place_order'] = 'Api/place_order';
$route['place_purchase'] = 'Api/place_purchase';


$route['get_product_det'] = 'Api/get_product_det';
$route['get_product'] = 'Api/get_product';
$route['get_products_v2'] = 'Api/get_products_v2';
$route['get_products'] = 'Api/get_products';
$route['set_category_list'] = 'Api/set_category_list';
$route['get_category_list'] = 'Api/get_category_list';
$route['form_autocomplete_options'] = 'Api/form_autocomplete_options';
$route['delete_form'] = 'Api/delete_form';
$route['bulk_action_exec'] = 'Api/bulk_action_exec';
$route['save_form'] = 'Api/save_form';
$route['save_upload_image'] = 'Api/save_upload_image';
$route['deleteFile'] = 'Api/deleteFile';

$route['save_display_by_sku'] = 'Api/save_display_by_sku';
$route['save_categories_margin'] = 'Api/save_categories_margin';
$route['generate_form'] = 'Api/generate_form';
$route['generate_table'] = 'Api/generate_table';
$route['export_table'] = 'Api/export_table';
$route['authentication'] = 'Api/authentication';

$route['update_users_info'] = 'Cron/update_users_info';

$route['cron_stock'] = 'Cron/stock';
$route['cron_exec'] = 'Cron/exec';

$route['default_controller'] = 'Api';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
