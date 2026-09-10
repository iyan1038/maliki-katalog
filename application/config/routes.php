<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
$route['default_controller'] = 'catalog';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
| -------------------------------------------------------------------------
| ROUTES AUTENTIKASI (Fase 1)
| -------------------------------------------------------------------------
*/
$route['auth/login']            = 'auth/login';
$route['auth/logout']           = 'auth/logout';
$route['auth/google']           = 'auth/google';
$route['auth/google_callback']  = 'auth/google_callback';
$route['auth/forgot_password']  = 'auth/forgot_password';
$route['auth/reset_password']   = 'auth/reset_password';
$route['auth/reset_password/(:any)'] = 'auth/reset_password/$1';

/*
| -------------------------------------------------------------------------
| ROUTES ADMIN (Fase 2)
| -------------------------------------------------------------------------
*/
$route['admin'] = 'admin/dashboard';
$route['admin/dashboard'] = 'admin/dashboard';
$route['admin/wa'] = 'admin/wa/index';
$route['admin/wa/send_promo'] = 'admin/wa/send_promo';
$route['admin/wa/send_recommendation/(:num)'] = 'admin/wa/send_recommendation/$1';

/*
| -------------------------------------------------------------------------
| ROUTES KATALOG & PRODUK (Fase 3)
| -------------------------------------------------------------------------
*/
$route['catalog']               = 'catalog/index';
$route['catalog/index']         = 'catalog/index';
$route['product']               = 'catalog/index';
$route['product/(:num)']        = 'product/index/$1';
$route['company/cv/(:num)']     = 'company/cv/$1';
$route['company/contact']       = 'company/contact';
$route['company/(:num)']        = 'company/index/$1';

/*
| -------------------------------------------------------------------------
| ROUTES MEMBER (Fase 4)
| -------------------------------------------------------------------------
*/
$route['rating/submit']                = 'rating/submit';
$route['favorite']                     = 'favorite/index';
$route['favorite/toggle']              = 'favorite/toggle';
$route['profile']                      = 'profile/index';
$route['profile/update']               = 'profile/update';
$route['profile/change_password']      = 'profile/change_password';
$route['setting']                      = 'setting/index';
$route['setting/toggle_dark_mode']     = 'setting/toggle_dark_mode';

/*
| -------------------------------------------------------------------------
| ROUTES PERSONALISASI (Fase 5)
| -------------------------------------------------------------------------
*/
$route['behavior/track']               = 'behavior/track';
