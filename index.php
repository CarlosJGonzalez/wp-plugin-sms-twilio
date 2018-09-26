<?php
/*
Plugin name: SMS from WP using Twilio
Description: inserting contact form to send SMS using a shortcode [SmS]
Author: Carlos Gonzalez (Guarracuco) siscond@hotmail.com
Version: 1.0.0
*/
global $wpdb;
function _cg_twilio_sms_sender_installing()
{
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	//////////////////////////
	//Twilio credentials table
	//////////////////////////
	$table	=	$wpdb->prefix."cg_twilio_credentials";
	$structure = "CREATE TABLE IF NOT EXISTS `$table` (
		`id` tinyint NOT NULL AUTO_INCREMENT,
		`account_sid` CHAR(34) NOT NULL,
		`auth_token` CHAR(32) NOT NULL,
		`twilio_phone_number` CHAR(11) NOT NULL,
		`your_phone_number` CHAR(11) NOT NULL,
		PRIMARY KEY(`id`)
	)$charset_collate;";

	require_once (ABSPATH. 'wp-admin/includes/upgrade.php');
	dbDelta($structure);

	if($wpdb->last_error):		
		error_log($wpdb->last_error);	
		print ($wpdb->last_error);
	endif;
}

register_activation_hook(__file__, '_cg_twilio_sms_sender_installing');

/////////////////////////////////////////////////////////////////////



function _cg_twilio_sms_sender_options()
{
	global $wpdb;
	echo '<div class="wrap woocommerce">';
		echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper">';
		$tabs[] = 'setting';
		//$tabs[] = 'help';
		
		foreach ($tabs as $tab)
		{
			$activetab = 'setting';
			if($_REQUEST)
			{
				if(isset($_REQUEST['tab'])):				
					$activetab = strtolower($_REQUEST['tab']);
				endif;
			}
			
			$strOption ='<a href="./options-general.php?page=_cg_twilio_credentials&tab=label" class="nav-tab active">label</a>';
			if($tab === $activetab):
				$strOption = str_replace('active', 'nav-tab-active', $strOption);
			else:
				$strOption = str_replace('active', '', $strOption);
			endif;
			
			$strOption = str_replace('label', ucfirst($tab), $strOption);
			echo $strOption;
		}
		echo '</nav>';

		if($_REQUEST)
		{
			echo '<div class="clear">';
			if(isset($_REQUEST['tab']))
			{
				$tab = strtolower($_REQUEST['tab']);
				global $wpdb;
				if($wpdb){
					switch($tab)
					{
						case 'help':
							//include 'help.inc.php';
							echo '<h3>Under construction</h3>';
							break;
						default:
							include 'credentials.inc.php';
					}					
				}
			}else{
				include 'credentials.inc.php';				
			}			
			echo '</div>';
		}
	echo '</div>';
}




function _cg_twilio_admin_panel()
{
	add_options_page ('Twilio Credentials', 'Twilio Credentials', 'manage_options', '_cg_twilio_credentials', '_cg_twilio_sms_sender_options');
	cg_creating_thankyou_page();
}

add_action ('admin_menu', '_cg_twilio_admin_panel');


function cg_creating_sms_sender_form(){
$body = '<?php
?>
<script>
function showSMS(){
document.getElementById(\'divSMS\').style.display=\'block\';
document.getElementById(\'btnSMS\').style.display=\'none\';
}

function cancela(){
document.getElementById(\'divSMS\').style.display=\'none\';
document.getElementById(\'btnSMS\').style.display=\'block\';
}

</script>
<div id="divSMS" style="display: none;">
<form action="" method="POST" name="frmSMS" action="../" target="_self">
<label>Your Name (10 chars)</label><br>
<input type="hidden" name="cg_creating_sms_sender_form">
<input name="your-name" type="text" required /><br>
<label>Email or Phone Number (max. 15 chars)</label><br>
<input name="your-email" type="text" required /><br>
<label>Message (max. 135 chars)</label><br>
<textarea cols="40" name="your-message" rows="2" required></textarea><br>
<input type="submit" value="Send"  />&nbsp;<input type="button" value="Cancel" onclick="javascript: cancela();" />
</form>
</div>
<input id="btnSMS" type="button" value="Send Us a SMS" onclick="javascript: showSMS();" />';
echo $body;
}

add_shortcode('SmS', 'cg_creating_sms_sender_form');



function cg_twilio_sms_sent()
{
	if($_POST)
    {
		if (isset($_POST['cg_creating_sms_sender_form'])){
			$n=substr($_POST['your-name'],0,10);
			$e=substr($_POST['your-email'],0,15);
			$m=$n.'>'.$e.'>'.substr(htmlentities(trim($_POST['your-message'])),0,135);		

			///////////////////////////////////////
			//pulling credentials from the database: account_sid, auth_token, twilio_phone_number, your_phone_number
			include 'retreiving.cred.inc.php';
			///////////////////////////////////////

			$url = "https://api.twilio.com/2010-04-01/Accounts/$account_sid/Messages.json";
			$headers = array("content-type: application/x-www-form-urlencoded");
			$data = "To=+$your_phone_number&From=+$twilio_phone_number&Body=$m";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_USERPWD, $account_sid.':'.$auth_token);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$json = curl_exec($ch);
			if(curl_errno($ch)){
				echo "Error:". curl_error($ch);
			}
			curl_close($ch);	
			$json = json_decode($json, true);
			
			if(is_array($json)){
				if(array_key_exists('sid', $json))
				{
					wp_redirect('twilio-sms-sent', 301);					
					exit;
				}elseif(array_key_exists('status', $json))
				{
					if($json['status'] != 200){
						echo $json['message'];
					}	
				}
			}else{
				echo '<h2>The SMS was not sent. We are sorry for the inconvenience.</h2>';
			}
		}
	}
}


add_action('init', 'cg_twilio_sms_sent');


//////////////////////////////////////////
//THANK YOU  TWILIO SMS SENT PAGE
//////////////////////////////////////////
function cg_creating_thankyou_page()
{
$slug = 'twilio-sms-sent';
if( !post_exist_by_slug( $slug ) )
{
$body ='<?php
/*
Template Name: twilio-sms-sent
*/
get_header();
echo "<h1>Thank you! We received your SMS</h1><p><h3>ASAP we will contacting you.</h3></p>";
get_footer();';

		$my_file = get_template_directory() . '/twilio-sms-sent.php';
		$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
		$data = $body;
		fwrite($handle, $data);	
		if($handle)
		{
			fclose($handle);
			wp_insert_post(
				array(
					'comment_status'	=>	'closed',
					'ping_status'		=>	'closed',
					'post_author'		=>	1,
					'post_name'			=>	$slug,
					'post_title'		=>	$slug,
					'post_content'		=>	'',
					'post_status'		=>	'publish',
					'post_type'			=>	'page',
					'page_template'		=>	'twilio-sms-sent.php'
				)
			);
		}
	}
}

 /**
 * post_exists_by_slug.
 *
 * @return mixed boolean false if no post exists; post ID otherwise.
 */
if(!function_exists('post_exist_by_slug'))
{
	function post_exist_by_slug( $post_slug ) {
		$args_posts = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'name'           => $post_slug,
			'posts_per_page' => 1
		);
		$loop_posts = new WP_Query( $args_posts );
		if ( ! $loop_posts->have_posts() ) {
			return false;
		} else {
			$loop_posts->the_post();
			return $loop_posts->post->ID;
		}
	}
}