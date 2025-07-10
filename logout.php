<?php
// Inicia a sessão para poder manipulá-la.
session_start();

// 1. Limpa todas as variáveis da sessão.
$_SESSION = array();

// 2. Destrói a sessão.
session_destroy();

// 3. Define uma mensagem de feedback para o usuário.
session_start(); // Inicia uma nova sessão para a mensagem
$_SESSION['message_type'] = 'success';
$_SESSION['message_content'] = 'Você saiu com sucesso!';

// 4. Redireciona o usuário para a página inicial.
header("Location: index.php");
exit();
?>