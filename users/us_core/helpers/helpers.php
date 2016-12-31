<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
//echo "helpers.php included";

//escapes strings and sets character set
function sanitize($string) {
	return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

function currentPage() {
	$uri = $_SERVER['PHP_SELF'];
	$path = explode('/', $uri);
	$currentPage = end($path);
	return $currentPage;
}

function currentFolder() {
	$uri = $_SERVER['PHP_SELF'];
	$path = explode('/', $uri);
	$currentFolder=$path[count($path)-2];
	return $currentFolder;
}

function format_date($date,$tz){
	//return date("m/d/Y ~ h:iA", strtotime($date));
	$format = 'Y-m-d H:i:s';
	$dt = DateTime::createFromFormat($format,$date);
	// $dt->setTimezone(new DateTimeZone($tz));
	return $dt->format("m/d/y ~ h:iA");
}

function abrev_date($date,$tz){
	$format = 'Y-m-d H:i:s';
	$dt = DateTime::createFromFormat($format,$date);
	// $dt->setTimezone(new DateTimeZone($tz));
	return $dt->format("M d,Y");
}

function money($ugly){
	return '$'.number_format($ugly,2,'.',',');
}

function name_from_id($id){
	if($id!=0){
		$nfi = DB::getInstance()->get('users', array('id', '=', $id));
		return $nfi->first()->username;
	}else{
		return "Guest";
	}
}

function display_errors($errors = array()){
	$html = '<ul class="bg-danger">';
	foreach($errors as $error){
		if(is_array($error)){
			$html .= '<li class="text-danger">'.$error[0].'</li>';
		}else{
			$html .= '<li class="text-danger">'.$error.'</li>';
		}
	}
	$html .= '</ul>';
	return $html;
}

function display_successes($successes = array()){
	$html = '<ul>';
	foreach($successes as $success){
		if(is_array($success)){
			$html .= '<li>'.$success[0].'</li>';
		}else{
			$html .= '<li>'.$success.'</li>';
		}
	}
	$html .= '</ul>';
	return $html;
}

function email($to,$subject,$body,$attachment=false, $debug_level=0){
	$db = DB::getInstance();
	$site_settings_results = $db->query("SELECT * FROM settings");
	$site_settings = $site_settings_results->first();

	$from = configGet('from_email');
	$from_name=configGet('from_name');
	$smtp_server=configGet('smtp_server');
	$smtp_port=configGet('smtp_port');
	$smtp_username=configGet('email_login');
	$smtp_password=configGet('email_pass');
	$smtp_transport=configGet('smtp_transport');

	$mail = new PHPMailer;

	if (configGet('mail_method')=='smtp'){
		$mail->SMTPDebug = $debug_level;                               // Enable verbose debug output
        if ($debug_level)
            $mail->Debugoutput = 'html';
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = $smtp_server;  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $smtp_username;                 // SMTP username
		$mail->Password = $smtp_password;                           // SMTP password
		$mail->SMTPSecure = $smtp_transport;                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = $smtp_port;                                    // TCP port to connect to
		$mail->setFrom($from, $from_name);
		$mail->addAddress($to);     // Add a recipient, name is optional
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $body;

		ob_start();
		$result = $mail->send();
		$debug = ob_get_clean();

	}elseif(configGet('mail_method')=='sendmail'){
		$mail->isSendmail();
		$mail->setFrom($from, $from_name);
		$mail->addAddress($to);
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body = $body;

		ob_start();
		$result = $mail->send();
		$debug = ob_get_clean();

	}elseif(configGet('mail_method')=='phpmail'){
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/plain; charset=iso-8859-1";
		$headers[] = "From: ".$from_name." <".$from.">";
		$headers[] = "Subject: ".$subject;
		$headers[] = "X-Mailer: PHP/".phpversion();
		$result=mail($to, $subject, $body, implode("\r\n", $headers));
		$debug="";
	}else{
		/*
		Do nothing since not a recognized option
		*/
	}

	/*
	Return the result as well as the output buffer containing the PHPMailer connection summary
	*/
	return [$result,$debug];
}

function email_body($templateString,$options = array()){
	$placeholderStrings=['{{fname}}','{{url}}','{{sitename}}'];
	$placeholderValues=[$options['fname'],$options['url'],$options['sitename']];

	$body='<!DOCTYPE html><html><head><meta charset="utf-8"><title></title></head><body>';
	$body.=str_replace($placeholderStrings,$placeholderValues,$templateString);
	$body.='</body></html>';

	return html_entity_decode($body);
}

function inputBlock($type,$label,$id,$divAttr=array(),$inputAttr=array(),$helper=''){
	$divAttrStr = '';
	foreach($divAttr as $k => $v){
		$divAttrStr .= ' '.$k.'="'.$v.'"';
	}
	$inputAttrStr = '';
	foreach($inputAttr as $k => $v){
		$inputAttrStr .= ' '.$k.'="'.$v.'"';
	}
	$html = '<div'.$divAttrStr.'>';
	$html .= '<label for="'.$id.'">'.$label.'</label>';
	if($helper != ''){
		$html .= '<button class="help-trigger"><span class="glyphicon glyphicon-question-sign"></span></button>';
	}
	$html .= '<input type="'.$type.'" id="'.$id.'" name="'.$id.'"'.$inputAttrStr.'>';
  if($helper != ''){
		$html .= '<div class="helper-text">'.$helper.'</div>';
	}
	$html .= '</div>';
    return $html;
}

//preformatted var_dump function
function dump($var){
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
}

//preformatted dump and die function
function dnd($var){
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
	die();
}

function bold($text){
	echo "<text padding='1em' align='center'><h4><span style='background:white'>";
	echo $text;
	echo "</h4></span></text>";
}
function redirect($location){
	header("Location: {$location}");
}

function output_message($message) {
return $message;
}

function timezone_dropdown($selected){
	$regions = array(
		'Africa' => DateTimeZone::AFRICA,
		'America' => DateTimeZone::AMERICA,
		'Antarctica' => DateTimeZone::ANTARCTICA,
		'Asia' => DateTimeZone::ASIA,
		'Atlantic' => DateTimeZone::ATLANTIC,
		'Europe' => DateTimeZone::EUROPE,
		'Indian' => DateTimeZone::INDIAN,
		'Pacific' => DateTimeZone::PACIFIC
	);

	$timezones = array();
	foreach ($regions as $name => $mask){
		$zones = DateTimeZone::listIdentifiers($mask);
		foreach($zones as $timezone){
			// Lets sample the time there right now
			$time = new DateTime(NULL, new DateTimeZone($timezone));
			// Us dumb Americans can't handle millitary time
			$ampm = $time->format('H') > 12 ? ' ('. $time->format('g:i a'). ')' : '';
			// Remove region name and add a sample time
			$timezones[$name][$timezone] = substr($timezone, strlen($name) + 1) . ' - ' . $time->format('H:i') . $ampm;
		}
	}

	// View
	$dropdownString='<select class="form-control" name="timezone_string" id="timezone_string">';
	foreach($timezones as $region => $list)
	{
		$dropdownString.= '<optgroup label="' . $region . '">' . "\n";
		foreach($list as $timezone => $name)
		{
			$dropdownString.= '<option value="'.$timezone.'" ';
			if($selected==$timezone){
				$dropdownString.= 'selected="selected"';
			}
			$dropdownString.= '>' . $name . '</option>' . "\n";
		}
		$dropdownString.='<optgroup>' . "\n";
	}
	$dropdownString.='</select>';

	return $dropdownString;
}
