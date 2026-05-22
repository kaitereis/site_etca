<?php

// configure
$from = 'Site ETCA <no-reply@grupoetca.com.br>';
$sendTo = 'etcatopografia@yahoo.com.br'; // Add Your Email
$subject = 'Nova mensagem do formulario de contato';
$fields = array('name' => 'Nome', 'phone' => 'Telefone', 'subject' => 'Assunto', 'email' => 'E-mail', 'message' => 'Mensagem'); // array variable name => Text to appear in the email
$okMessage = 'Obrigado! Sua mensagem foi entregue com sucesso e recebemos seu contato.';
$errorMessage = 'Ocorreu um erro ao enviar a mensagem. Por favor, tente novamente mais tarde.';

// let's do the sending

try
{
    $emailText = "Você recebeu uma nova mensagem do formulário de contato:\n=============================\n";

    foreach ($_POST as $key => $value) {

        if (isset($fields[$key])) {
            $emailText .= "$fields[$key]: $value\n";
        }
    }

    $replyTo = !empty($_POST['email']) ? $_POST['email'] : $from;
    $headers = array('Content-Type: text/plain; charset="UTF-8";',
        'From: ' . $from,
        'Reply-To: ' . $replyTo,
        'Cc: kaitereies@gmail.com',
        'Return-Path: ' . $from,
    );
    
    $ok = @mail($sendTo, $subject, $emailText, implode("\r\n", $headers));

    if ($ok) {
        $responseArray = array('type' => 'success', 'message' => $okMessage);
    } else {
        $responseArray = array('type' => 'danger', 'message' => $errorMessage);
    }
}
catch (\Exception $e)
{
    $responseArray = array('type' => 'danger', 'message' => $errorMessage);
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);

    header('Content-Type: application/json');

    echo $encoded;
}
else {
    echo $responseArray['message'];
}
