<?php

// echo '{"status":"success"}';
// die();

// EDIT THE 2 LINES BELOW AS REQUIRED
$email_to = "jack@realitygems.com";
$email_subject = "Band Pioneer Contact";

function died($error) {
    echo '{"message":"' . $error . '"}';
    die();
}

if(strlen($_POST['poobear']) > 0) // Simple HPot Logic
{
    died('We are sorry, but there appears to be a problem with the form you submitted.');  
}

// if(strlen(trim($_POST['name'])) <= 0) // Simple HPot Logic
// {
//     died('A name is required');  
// }

// if(strlen(trim($_POST['email'])) <= 0 && strlen(trim($_POST['phone'])) <= 0) // Simple HPot Logic
// {
//     died('An email or phone number is required');  
// }

// if(strlen(trim($_POST['comments'])) <= 0) // Simple HPot Logic
// {
//     died('A comment is required');  
// }

// // validation expected data exists
// if(!isset($_POST['name']) ||
//     !isset($_POST['email']) ||
//     !isset($_POST['phone']) ||
//     !isset($_POST['comments'])) {
//     died('We are sorry, but there appears to be a problem with the form you submitted.');       
// }

$name = $_POST['name']; // required
$email = $_POST['email']; // required
$phone = $_POST['phone']; // not required
$comments = $_POST['comments']; // required

$error_message = "";
$email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';

if(strlen(trim($email)) > 0 && !preg_match($email_exp, $email))
{
  $error_message .= 'The email you entered is invalid.<br />';
}
elseif(strlen(trim($email)) <= 0 && strlen(trim($phone)) <= 0)
{
  $error_message .= 'An email or phone number is required.<br />';
}

$string_exp = "/^[A-Za-z .'-]+$/";

if(!preg_match($string_exp, $name))
{
  $error_message .= 'A valid name is required.<br />';
}

if(strlen(trim($comments)) <= 0)
{
  $error_message .= 'A comment is required.<br />';
}

if(strlen($error_message) > 0)
{
  died($error_message);
}

$email_message = "Form details below.\n\n";

 
function clean_string($string) {
  $bad = array("content-type","bcc:","to:","cc:","href");
  return str_replace($bad,"",$string);
}

$email_message .= "Name: ".clean_string($name)."\n";
$email_message .= "Email: ".clean_string($email)."\n";
$email_message .= "Telephone: ".clean_string($phone)."\n";
$email_message .= "Comments: ".clean_string($comments)."\n";
 
// create email headers
$headers = 'From: Band Pioneer <no-reply@bandpioneer.com>' . "\r\n".
// 'Reply-To: '.$email_from."\r\n" .
'X-Mailer: PHP/' . phpversion();
@mail($email_to, $email_subject, $email_message, $headers);  

echo '{"status":"success"}';
die();

?>