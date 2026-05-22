<?php
// Disable error reporting to prevent notices/warnings from corrupting the JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Helper function to send email with various methods
function attempt_mail($to, $subject, $message, $headersStr, $mode5, $fromEmail, $fromName, $replyTo, $useSuppress = true) {
    if ($mode5 === 'socket') {
        $host = '127.0.0.1';
        $port = 25;
        $timeout = 2;
        $socket = $useSuppress ? @fsockopen($host, $port, $errno, $errstr, $timeout) : fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$socket) {
            return false;
        }
        
        $readResponse = function($socket) {
            $response = "";
            while (($line = fgets($socket, 512)) !== false) {
                $response .= $line;
                if (substr($line, 3, 1) == " ") {
                    break;
                }
            }
            return $response;
        };
        
        $readResponse($socket);
        fwrite($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        $readResponse($socket);
        
        fwrite($socket, "MAIL FROM:<" . $fromEmail . ">\r\n");
        $readResponse($socket);
        
        fwrite($socket, "RCPT TO:<" . $to . ">\r\n");
        $rcptRes = $readResponse($socket);
        if (strpos($rcptRes, '250') === false && strpos($rcptRes, '251') === false) {
            fclose($socket);
            return false;
        }
        
        fwrite($socket, "DATA\r\n");
        $readResponse($socket);
        
        $mailHeaders = "MIME-Version: 1.0\r\n";
        $mailHeaders .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
        $mailHeaders .= "From: " . $fromName . " <" . $fromEmail . ">\r\n";
        $mailHeaders .= "To: " . $to . "\r\n";
        $mailHeaders .= "Reply-To: " . $replyTo . "\r\n";
        $mailHeaders .= "Subject: " . $subject . "\r\n";
        
        fwrite($socket, $mailHeaders . "\r\n" . $message . "\r\n.\r\n");
        $dataRes = $readResponse($socket);
        
        fwrite($socket, "QUIT\r\n");
        fclose($socket);
        
        return strpos($dataRes, '250') !== false;
    }
    
    $param5 = null;
    if ($mode5 === 'f') {
        $param5 = "-f " . $fromEmail;
    } elseif ($mode5 === 'f_nospace') {
        $param5 = "-f" . $fromEmail;
    } elseif ($mode5 === 'r') {
        $param5 = "-r " . $fromEmail;
    } elseif ($mode5 === 'r_nospace') {
        $param5 = "-r" . $fromEmail;
    }
    
    if ($param5 !== null) {
        if ($useSuppress) {
            return @mail($to, $subject, $message, $headersStr, $param5);
        } else {
            return mail($to, $subject, $message, $headersStr, $param5);
        }
    } else {
        if ($useSuppress) {
            return @mail($to, $subject, $message, $headersStr);
        } else {
            return mail($to, $subject, $message, $headersStr);
        }
    }
}

// Configure sender domain
$domain = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'grupoetca.com.br';
if (substr($domain, 0, 4) == 'www.') {
    $domain = substr($domain, 4);
}
// Fallback if domain is localhost, local IP, or not set
if (filter_var($domain, FILTER_VALIDATE_IP) || $domain === 'localhost' || $domain === '127.0.0.1') {
    $domain = 'grupoetca.com.br';
}

$fromEmail = 'contato@' . $domain;
$fromName = 'Site ETCA';
$from = $fromName . ' <' . $fromEmail . '>';

// Receivers: sent individually for maximum compatibility
$emails = array('etcatopografia@yahoo.com.br', 'kaitereies@gmail.com');

$subject = 'Nova mensagem do formulario de contato';
$fields = array('name' => 'Nome', 'phone' => 'Telefone', 'subject' => 'Assunto', 'email' => 'E-mail', 'message' => 'Mensagem'); 
$okMessage = 'Obrigado! Sua mensagem foi entregue com sucesso e recebemos seu contato.';
$errorMessage = 'Ocorreu um erro ao enviar a mensagem. Por favor, tente novamente mais tarde.';

// Diagnostic / Debug mode
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<h1>Formulário de Contato - Modo de Depuração</h1>";
    echo "<p>Testando o envio de e-mails usando a função nativa mail() do PHP...</p>";
    
    $testEmailText = "Este é um e-mail de teste gerado pelo sistema de depuração do formulário de contato do Site ETCA.\n=============================\n";
    $testEmailText .= "Hora do teste: " . date('Y-m-d H:i:s') . "\n";
    
    echo "<p>Destinatários:</p><ul>";
    foreach ($emails as $to) {
        echo "<li>$to</li>";
    }
    echo "</ul>";
    
    echo "<p>Remetente configurado: $from</p>";
    echo "<p>E-mail do remetente: $fromEmail</p>";
    
    echo "<h2>Iniciando envios de teste:</h2>";
    
    foreach ($emails as $to) {
        echo "<h3>Enviando para $to:</h3>";
        
        $headers1 = array(
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset="UTF-8"',
            'From: ' . $from,
            'Reply-To: ' . $fromEmail,
            'Return-Path: ' . $fromEmail
        );
        
        $headers2 = array(
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset="UTF-8"',
            'From: ' . $fromEmail,
            'Reply-To: ' . $fromEmail,
            'Return-Path: ' . $fromEmail
        );

        $attempts = array(
            array($headers1, "\r\n", 'f', "CRLF (\\r\\n), Com Nome no From, Com parâmetro '-f [email]'"),
            array($headers1, "\r\n", 'f_nospace', "CRLF (\\r\\n), Com Nome no From, Com parâmetro '-f[email]'"),
            array($headers1, "\r\n", 'r', "CRLF (\\r\\n), Com Nome no From, Com parâmetro '-r [email]' (Comum na Locaweb)"),
            array($headers1, "\r\n", 'r_nospace', "CRLF (\\r\\n), Com Nome no From, Com parâmetro '-r[email]' (Locaweb Linux)"),
            array($headers1, "\r\n", 'none', "CRLF (\\r\\n), Com Nome no From, Sem 5º parâmetro"),
            
            array($headers1, "\n", 'f', "LF (\\n), Com Nome no From, Com parâmetro '-f [email]'"),
            array($headers1, "\n", 'r', "LF (\\n), Com Nome no From, Com parâmetro '-r [email]'"),
            array($headers1, "\n", 'none', "LF (\\n), Com Nome no From, Sem 5º parâmetro"),
            
            array($headers2, "\r\n", 'f', "CRLF (\\r\\n), Apenas e-mail no From, Com parâmetro '-f [email]'"),
            array($headers2, "\r\n", 'r', "CRLF (\\r\\n), Apenas e-mail no From, Com parâmetro '-r [email]'"),
            array($headers2, "\r\n", 'none', "CRLF (\\r\\n), Apenas e-mail no From, Sem 5º parâmetro"),
            
            array($headers2, "\n", 'f', "LF (\\n), Apenas e-mail no From, Com parâmetro '-f [email]'"),
            array($headers2, "\n", 'r', "LF (\\n), Apenas e-mail no From, Com parâmetro '-r [email]'"),
            array($headers2, "\n", 'none', "LF (\\n), Apenas e-mail no From, Sem 5º parâmetro"),
            
            array(array(), "", 'socket', "SMTP Local Socket Relay na porta 25")
        );
        
        $success = false;
        foreach ($attempts as $index => $attempt) {
            $headersList = $attempt[0];
            $eol = $attempt[1];
            $mode5 = $attempt[2];
            $desc = $attempt[3];
            
            $headersStr = implode($eol, $headersList);
            
            echo "Tentativa " . ($index + 1) . " ($desc): ";
            
            // Set useSuppress = false in debug mode so PHP warnings are printed on screen
            $sent = attempt_mail($to, "Teste de Contato ETCA " . ($index + 1), $testEmailText, $headersStr, $mode5, $fromEmail, $fromName, $fromEmail, false);
            
            if ($sent) {
                echo "<strong style='color: green;'>SUCESSO</strong><br>";
                $success = true;
                break;
            } else {
                echo "<strong style='color: red;'>FALHA</strong><br>";
            }
        }
        
        if ($success) {
            echo "<p style='color: green;'>E-mail para $to enviado com sucesso em uma das tentativas!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>Todas as tentativas de envio para $to falharam.</p>";
        }
    }
    
    echo "<h2>Resumo do Diagnóstico</h2>";
    echo "<p>Se todas as tentativas falharam, as causas mais comuns são:</p>";
    echo "<ol>";
    echo "<li>A função <code>mail()</code> está desabilitada no servidor PHP (verifique a diretiva <code>disable_functions</code> no php.ini).</li>";
    echo "<li>O servidor local de e-mails (Sendmail/Postfix/Exim) não está instalado ou configurado no servidor web.</li>";
    echo "<li>O provedor de hospedagem exige envio autenticado via SMTP (nesse caso, seria necessário usar uma biblioteca como PHPMailer).</li>";
    echo "</ol>";
    exit;
}

try
{
    $emailText = "Você recebeu uma nova mensagem do formulário de contato:\n=============================\n";

    foreach ($_POST as $key => $value) {
        if (isset($fields[$key])) {
            $emailText .= "$fields[$key]: $value\n";
        }
    }

    $replyTo = !empty($_POST['email']) ? $_POST['email'] : $fromEmail;
    
    $headers1 = array(
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset="UTF-8"',
        'From: ' . $from,
        'Reply-To: ' . $replyTo,
        'Return-Path: ' . $fromEmail
    );
    
    $headers2 = array(
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset="UTF-8"',
        'From: ' . $fromEmail,
        'Reply-To: ' . $replyTo,
        'Return-Path: ' . $fromEmail
    );

    $ok = true;
    
    foreach ($emails as $to) {
        $sent = false;
        
        $attempts = array(
            array($headers1, "\r\n", 'f'),
            array($headers1, "\r\n", 'f_nospace'),
            array($headers1, "\r\n", 'r'),
            array($headers1, "\r\n", 'r_nospace'),
            array($headers1, "\r\n", 'none'),
            array($headers1, "\n", 'f'),
            array($headers1, "\n", 'r'),
            array($headers1, "\n", 'none'),
            array($headers2, "\r\n", 'f'),
            array($headers2, "\r\n", 'r'),
            array($headers2, "\r\n", 'none'),
            array($headers2, "\n", 'f'),
            array($headers2, "\n", 'r'),
            array($headers2, "\n", 'none'),
            array(array(), "", 'socket')
        );
        
        foreach ($attempts as $attempt) {
            $headersList = $attempt[0];
            $eol = $attempt[1];
            $mode5 = $attempt[2];
            
            $headersStr = implode($eol, $headersList);
            
            $sent = attempt_mail($to, $subject, $emailText, $headersStr, $mode5, $fromEmail, $fromName, $replyTo, true);
            
            if ($sent) {
                break;
            }
        }
        
        if (!$sent) {
            $ok = false;
        }
    }

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

// Always return JSON
header('Content-Type: application/json');
echo json_encode($responseArray);
exit;
