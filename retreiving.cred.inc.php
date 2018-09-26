<?php
	global $wpdb;
	$account_sid='';
	$auth_token='';
	$twilio_phone_number='';
	$your_phone_number='';
	
	//retreiving credentials
	$result = $wpdb->get_results("select `account_sid`, `auth_token`, `twilio_phone_number`, `your_phone_number` from {$wpdb->prefix}cg_twilio_credentials limit 1;");
	if(is_array($result) && count($result)>0)
	{
		$account_sid = $result[0]->account_sid;
		$auth_token = $result[0]->auth_token;
		$twilio_phone_number = $result[0]->twilio_phone_number;
		$your_phone_number = $result[0]->your_phone_number;
	}