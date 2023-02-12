<?php

// required for sendgrid api code
// require 'vendor/autoload.php';
// require 'vendor/composer/autoload_real.php';


// EDIT THE 2 LINES BELOW AS REQUIRED
$email_to = "jack@realitygems.com";
// $email_to = "realitygems@zohomail.com";
// $email_from = "no-reply@bandpioneer.com";
$email_from = "support@realitygems.com";
$email_subject = "Website Contact";

function died($error) {
    echo '{"message":"' . $error . '"}';
    die();
}

if(strlen($_POST['poobear']) > 0) // Simple HPot Logic
{
    died('We are sorry, but there appears to be a problem with the form you submitted.');  
}

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
 
// PHP SENDMAIL CODE

$headers = 'From: Band Pioneer <' . $email_from . '>' . "\r\n".
'X-Mailer: PHP/' . phpversion();
@mail($email_to, $email_subject, $email_message, $headers);  




// // PHP SENDGRID API CODE

// $email = new \SendGrid\Mail\Mail(); 
// $email->setFrom($email_from, "Band Pioneer");
// $email->setSubject($email_subject);
// $email->addTo($email_to, "Band Pioneer");
// $email->addContent("text/plain", $email_message);
// // $email->addContent(
// //     "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
// // );
// $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
// try {
//     $response = $sendgrid->send($email);
//     print $response->statusCode() . "\n";
//     print_r($response->headers());
//     print $response->body() . "\n";
// } catch (Exception $e) {
//     echo 'Caught exception: '. $e->getMessage() ."\n";
// }



echo '{"status":"success"}';
die();

?>