<?php 
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__.'/php_errors.log');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/Config.php';


if(isset($_REQUEST['hub_challenge'])){
$challenge = $_REQUEST['hub_challenge']; 
$verify_token = $_REQUEST['hub_verify_token'];    
if($verify_token  === 'abc123') 
	echo $challenge;
	exit;
}
	
$data = json_decode(file_get_contents('php://input'),true);

use \FacebookAds\Api;
use \FacebookAds\Object\Lead;
use FacebookAds\Object\LeadgenForm;

$app_id       = '1840667266185922289';
$app_secret   = '84164f23152ce6b60220a2332995bf64fb8c';

$pageId = $data['entry'][0]['changes'][0]['value']['page_id'];
switch ($pageId)
{
    case '2209374386007232647': //robotime (ru)
        $access_token = 'EAAblBs8HOwUBAEt34sNAxNtRalaZBw0cwyMwLVko7q2EVoiwulfVm1hmPWSWZCk5rf1BWIjbROAaTD16s3RIZCt4AA0nZAxOZAk97upWjjYYX53uoID42X4MYeZBrFrjnQjzwaEospzZBiEXo0ZAZApEuJrhCYnfm8hZCAOg1x0NNfrx8jwlzHRnnAVl';
        break;
    case '183217573247042051': //robocode
        $access_token = 'EAAblB32s8HOwUBANuZAavGH37y9KGstUil37UfKthy3sog0ZCBS6R9h9ZCxQMSh2RDpjmSPX9CDksFCWxFrZAZCPoUAnj8IlXCu4Nr4KOO1KAgHFzkBaE9sYDyR6g3i73z59uDtUGJ2p4FmB43z4BhUgw3ZC2DDtP2QDEZCBpBmxT1QLAZDZD';
        break;
}



//"EAAblBs8HOwUBANuZAavGH37y9K43GstUil37UfKthy3sog0ZCBS6R9h9ZCxQMSh2RDpjmSPX9CDksFCWxFrZAZCPoUAnj8IlXCu4Nr4KOO1KAgHFzkBaE9sYDyR6g3i73z59uDtUGJ2p4FmBz4BhUgw3ZC2DDtP2QDEZCBpBmxT1QLAZDZD";
$fields = [
	'full_name'=>'fullName',
	'номер_телефона' => 'phone',
    'phone_number' => 'phone',
	'email'    => 'email',
	'выберите_школу'   => 'school'
	
];
	Api::init($app_id, $app_secret, $access_token);
	$api = Api::instance(); 
	
	$lead = $data['entry'][0]['changes'][0]['value']['leadgen_id'];
	$formId = $data['entry'][0]['changes'][0]['value']['form_id'];
	$ad_id = $data['entry'][0]['changes'][0]['value']['ad_id'];

if(!in_array($formId,array_keys(Config::FORMS))){
		exit;
	}
	
	if(empty($lead))
		exit;

	//$data = json_decode(file_get_contents("https://graph.facebook.com/v2.5/$lead?access_token=$access_token"),true);
	$form = new Lead($lead);
	$data = $form->read()->getData();
	$params = []; $name = '';
	foreach($data['field_data'] as $item ){
		$params[$fields[$item['name']]] = $item['values'][0];
	}
	

	
	if(empty($params['school']) && !empty($params)){
		$params['school'] = Config::FORMS[$formId];
	}
	$params['utm_source'] = 'Facebook';
	$params['utm_medium'] = "CPC";
	$params['utm_term'] = $formId;
	$params['utm_content'] = $lead;
	$params['description'] = 'facebook.com';
	$params['utm_campaign'] = 	$ad_id;	

	$log = date('Y-m-d H:i:s')." Lead: $lead Form: $formId Page: $pageId\n";
	$log .= serialize ($data)."\n#";
	file_put_contents('log_new.txt',$log,FILE_APPEND | LOCK_EX);

$endPoint = 'https://crm.pro/Api/V1/AddStudyRequest';

	if(!empty($params)){
		curl($endPoint,$params);
	}
 function curl($url,$request_params)
{
	$ch = curl_init();
	curl_setopt_array( $ch, array(
		CURLOPT_POST            => TRUE,
		CURLOPT_RETURNTRANSFER  => TRUE,
		CURLOPT_SSL_VERIFYPEER  => FALSE,
		CURLOPT_SSL_VERIFYHOST  => FALSE,
		CURLOPT_POSTFIELDS      => $request_params,
		CURLOPT_URL             => $url,
	));
	$result = curl_exec($ch);
	curl_close($ch);
	return json_decode($result);
}