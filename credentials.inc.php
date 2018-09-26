<?php
if($_POST)
{
	global $wpdb;
	if(isset($_POST['account_sid']) && isset($_POST['auth_token']) && $_POST['account_sid'] != '')
	{
		if(isset($_POST['id']) && filter_var($_POST['id'], FILTER_VALIDATE_INT))
		{
			$id = $_POST['id'];	
		}
		$account_sid = htmlentities($_POST['account_sid']);
		$auth_token  = htmlentities($_POST['auth_token']);
		$twilio_phone_number = htmlentities($_POST['twilio_phone_number']);
		$your_phone_number = htmlentities($_POST['your_phone_number']);

		if(isset($id)){
			$strSql = "update {$wpdb->prefix}cg_twilio_credentials set `account_sid`= '%s', `auth_token`= '%s', `twilio_phone_number`= '%s', `your_phone_number` = '%s' where `id`=%d;";
			$res = $wpdb->query($wpdb->prepare($strSql, $account_sid, $auth_token, $twilio_phone_number, $your_phone_number, $id));	
			($res == 1) ? $message="Twilio Credentials updated!" : $message="Credentials was not updated.";
		}else{
			$strSql = "insert into {$wpdb->prefix}cg_twilio_credentials (`account_sid`, `auth_token`, `twilio_phone_number`, `your_phone_number`) values ('%s', '%s', '%s', '%s');";			
			$res = $wpdb->query($wpdb->prepare($strSql, $account_sid, $auth_token, $twilio_phone_number, $your_phone_number));		
			($res == 1) ? $message="Twilio Credentials Saved!" : $message="Twilio Credentials was not saved.";
		}
	}elseif(isset($_POST['action']) && filter_var($_POST['id'], FILTER_VALIDATE_INT) && $_POST['action'] == 'del')
	{
		$id = $_POST['id'];
		$strSql = "delete from {$wpdb->prefix}cg_twilio_credentials where `id`=%d";
		$res = $wpdb->query($wpdb->prepare($strSql, $id));
		unset($id);
		($res == 1)? $message="Twilio Credentials deleted." : $message="Twilio Credentials was not deleted.";
	}
	echo '<h2>'.$message.'</h2>';
}
try{
	global $wpdb;
	$account_sid='';
	$auth_token='';
	$your_phone_number='';
	$twilio_phone_number='';
	
	//retreiving credentials
	$result = $wpdb->get_results("select `id`, `account_sid`, `auth_token`, `twilio_phone_number`, `your_phone_number` from {$wpdb->prefix}cg_twilio_credentials limit 1;");
	if(is_array($result) && count($result)>0)
	{
		$id = $result[0]->id;
		$account_sid = $result[0]->account_sid;
		$auth_token = $result[0]->auth_token;
		$twilio_phone_number = $result[0]->twilio_phone_number;
		$your_phone_number = $result[0]->your_phone_number;
	}
	?>
	<script>
		jQuery( document ).on("click", "#delete", function(){
			jQuery( "#action" ).val('del');
			jQuery( ".data-frm" ).each( function( index ){
				jQuery( this ).val( null );
			});
			jQuery( "#frm" ).submit();
		});
	</script>
	<p><h4>Twilio Credentials</h4></p>
	<form name="frm" id="frm" method="POST" action="">
	<input type="hidden" name="id" value="<?=$id;?>">
	<input type="hidden" id="action" name="action" value="">
	<span>Sid</span><br><input class="data-frm" type="text" id="account_sid" name="account_sid" value="<?=$account_sid;?>" size="40"><br>
	<span>Auth</span><br><input class="data-frm" type="text" id="auth_token" name="auth_token" value="<?=$auth_token;?>" size="40"><br>
	<span>Twilio's Phone Number</span><br><input class="data-frm" type="text" name="twilio_phone_number" value="<?=$twilio_phone_number;?>" size="12"><br>
	<span>Your Phone Number</span><br><input class="data-frm" type="text" name="your_phone_number" value="<?=$your_phone_number;?>" size="12"><br><br>
	<input type="submit" value="Send">
	<input type="button" value="Delete" id="delete">
	</form>
	<?php
}
catch(Exception $e)
{
	echo $e->getMessage();
}