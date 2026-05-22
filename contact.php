<?php
// Disable error reporting to prevent notices/warnings from corrupting the JSON response
error_reporting(0);
ini_set('display_errors', 0);

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
            array($headers1, "\r\n", true, "CRLF (\\r\\n), Com Nome no From, Com parâmetro -f"),
            array($headers1, "\r\n", false, "CRLF (\\r\\n), Com Nome no From, Sem parâmetro -f"),
            array($headers1, "\n", true, "LF (\\n), Com Nome no From, Com parâmetro -f"),
            array($headers1, "\n", false, "LF (\\n), Com Nome no From, Sem parâmetro -f"),
            array($headers2, "\r\n", true, "CRLF (\\r\\n), Apenas e-mail no From, Com parâmetro -f"),
            array($headers2, "\r\n", false, "CRLF (\\r\\n), Apenas e-mail no From, Sem parâmetro -f"),
            array($headers2, "\n", true, "LF (\\n), Apenas e-mail no From, Com parâmetro -f"),
            array($headers2, "\n", false, "LF (\\n), Apenas e-mail no From, Sem parâmetro -f")
        );
        
        $success = false;
        foreach ($attempts as $index => $attempt) {
            $headersList = $attempt[0];
            $eol = $attempt[1];
            $useF = $attempt[2];
            $desc = $attempt[3];
            
            $headersStr = implode($eol, $headersList);
            
            echo "Tentativa " . ($index + 1) . " ($desc): ";
            
            if ($useF) {
                $sent = @mail($to, "Teste de Contato ETCA " . ($index + 1), $testEmailText, $headersStr, "-f " . $fromEmail);
            } else {
                $sent = @mail($to, "Teste de Contato ETCA " . ($index + 1), $testEmailText, $headersStr);
            }
            
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
            array($headers1, "\r\n", true),
            array($headers1, "\r\n", false),
            array($headers1, "\n", true),
            array($headers1, "\n", false),
            array($headers2, "\r\n", true),
            array($headers2, "\r\n", false),
            array($headers2, "\n", true),
            array($headers2, "\n", false)
        );
        
        foreach ($attempts as $attempt) {
            $headersList = $attempt[0];
            $eol = $attempt[1];
            $useF = $attempt[2];
            
            $headersStr = implode($eol, $headersList);
            
            if ($useF) {
                $sent = @mail($to, $subject, $emailText, $headersStr, "-f " . $fromEmail);
            } else {
                $sent = @mail($to, $subject, $emailText, $headersStr);
            }
            
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
