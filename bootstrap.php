<?php
// bootstrap.php

// 1. Gerenciamento de Sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Conexão com o Banco de Dados
require_once __DIR__ . "/config/db_connection.php";

// 3. Helpers e Funções Globais <<-- ESTA É A LINHA CRÍTICA
require_once __DIR__ . "/logic/helpers.php"; // Garanta que esta linha exista!
require_once __DIR__ . "/logic/csrf_token.php";

// 4. Lógica de Autenticação e Dados do Usuário
$is_logged_in = isset($_SESSION['id_profissional']);
$user_info = null; 

if ($is_logged_in && isset($conn)) {
    $id_profissional = $_SESSION['id_profissional'];
    $sql = "SELECT nome_profissional FROM profissional WHERE id_profissional = ?";
    
    // Usar um nome de variável diferente para evitar conflitos
    $stmt_user = $conn->prepare($sql); 
    
    if ($stmt_user) {
        $stmt_user->bind_param("i", $id_profissional);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        
        if ($result_user->num_rows > 0) {
            $user_data = $result_user->fetch_assoc();
            $user_info = [
                'nome_completo' => $user_data['nome_profissional'],
                'primeiro_nome' => strtok($user_data['nome_profissional'], ' ')
            ];
        }
        $stmt_user->close(); // Fecha o statement específico do usuário
    }
}
?>