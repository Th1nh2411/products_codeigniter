<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['demo'] = 'api/ApiDemoController/index';

$route['products']['GET'] = 'api/ProductController/getProducts';
$route['products/(:num)']['GET'] = 'api/ProductController/getProductById/$1';
$route['products']['POST'] = 'api/ProductController/createProduct';
$route['products/(:num)']['PUT'] = 'api/ProductController/updateProduct/$1';
$route['products/(:num)']['DELETE'] = 'api/ProductController/deleteProduct/$1';

$route['products/categories'] = 'api/ProductController/getCategories';
$route['products/category/(:any)'] = 'api/ProductController/getProductsByCategory/$1';

$route['products/image/(:num)']['GET'] = 'api/ProductController/getProductImages/$1';
$route['products/image']['POST'] = 'api/ProductController/createProductImage';
$route['products/image/(:num)']['PUT'] = 'api/ProductController/updateProductImage/$1';
$route['products/image/(:num)']['DELETE'] = 'api/ProductController/deleteProductImage/$1';

$route['users']['GET'] = 'api/UserController/getUsers';
$route['login']['POST'] = 'api/UserController/login';
$route['register']['POST'] = 'api/UserController/register';
