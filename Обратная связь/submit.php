<?php

/* config start */

$emailAddress = '';

/* config end */


require "phpmailer/class.phpmailer.php";

session_name("fancyform");
session_start();


foreach($_POST as $k=>$v)
{
	if(ini_get('magic_quotes_gpc'))
	$_POST[$k]=stripslashes($_POST[$k]);
	
	$_POST[$k]=htmlspecialchars(strip_tags($_POST[$k]));
}


$err = array();

if(!checkLen('name'))
	$err[]='The name field is too short or empty!';

if(!checkLen('email'))
	$err[]='The email field is too short or empty!';
else if(!checkEmail($_POST['email']))
	$err[]='Your email is not valid!';

if(!checkLen('subject'))
	$err[]='You have not selected a subject!';

if(!checkLen('message'))
	$err[]='The message field is too short or empty!';

if((int)$_POST['captcha'] != $_SESSION['expect'])
	$err[]='The captcha code is wrong!';


if(count($err))
{
	if($_POST['ajax'])
	{
		echo '-1';
	}

	else if($_SERVER['HTTP_REFERER'])
	{
		$_SESSION['errStr'] = implode('<br />',$err);
		$_SESSION['post']=$_POST;
		
		header('Location: '.$_SERVER['HTTP_REFERER']);
	}

	exit;
}


$msg=
'<strong>Имя:</strong>	'.$_POST['name'].'<br />
<strong>e-mail:</strong>	'.$_POST['email'].'<br />
<strong>IP:</strong>	'.$_SERVER['REMOTE_ADDR'].'<br /><br />

<strong>Сообщение:</strong><br /><br />

'.nl2br($_POST['message']).'

';


$mail = new PHPMailer();
$mail->IsMail();

$mail->AddReplyTo($_POST['email'], $_POST['name']);
$mail->AddAddress($emailAddress);
$mail->SetFrom($_POST['email'], $_POST['name']);
$mail->Subject = "Tема письма: ".$_POST['subject'];

$mail->MsgHTML($msg);

$mail->Send();


unset($_SESSION['post']);

if($_POST['ajax'])
{
	echo '1';
}
else
{
	$_SESSION['sent']=1;
	
	if($_SERVER['HTTP_REFERER'])
		header('Location: '.$_SERVER['HTTP_REFERER']);
	
	exit;
}

function checkLen($str,$len=2)
{
	return isset($_POST[$str]) && mb_strlen(strip_tags($_POST[$str]),"utf-8") > $len;
}

function checkEmail($str)
{
	return preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $str);
}

?>
