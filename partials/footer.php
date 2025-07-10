<?php
// Garante que a sessão seja iniciada em todas as páginas que incluem este header.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['id_profissional']);
$nome_usuario = '';

// Se o usuário estiver logado, busca o nome dele no banco de dados.
if ($is_logged_in) {
    // Inclui a conexão com o banco de dados apenas se for necessário.
    require_once __DIR__ . "/../config/db_connection.php";
    
    $id_profissional = $_SESSION['id_profissional'];
    $sql = "SELECT nome_profissional FROM profissional WHERE id_profissional = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $id_profissional);
        $stmt->execute();
        
        // CORREÇÃO: Renomear a variável de $result para $header_user_result
        $header_user_result = $stmt->get_result();
        
        if ($header_user_result->num_rows > 0) {
            $user = $header_user_result->fetch_assoc();
            $nome_usuario = strtok($user['nome_profissional'], ' '); 
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    </head>
<body>
    <footer>
        <div class="footer-grid">
            <div class="footer-column">
                <h4>Bauru Serviços</h4>
                <p>Conectando clientes a prestadores de serviço qualificados em Bauru e região desde 2025.</p>
            </div>
            <div class="footer-column">
                <h4>Navegação</h4>
                <ul>
                    <li><a href="index.php" class="cab-link">Início</a></li>
                    <li><a href="sobre.php" class="cab-link">Sobre Nós</a></li>
                    <li><a href="contato.php" class="cab-link">Contato</a></li>

                    <?php if ($is_logged_in): ?>
                        
                    <?php else: ?>
                        <li><a href="cadastro.php" class="cab-link">Cadastro</a></li>
                        <li><a href="login.php" class="cab-link">Entrar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Contato & Legal</h4>
                <ul>
                    <li><a href="mailto:contato@servicosbauru.com.br" class="cab-link">contato@servicosbauru.com.br</a></li>
                    <li><a href="politica_privacidade.php" class="cab-link">Política de Privacidade</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Redes Sociais</h4>
                <div class="footer-social-icons">
                    <a href="https://www.instagram.com/bauruservicos" target="_blank" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.facebook.com/bauruservicosoficial" target="_blank" aria-label="Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 - <strong>Bauru Serviços</strong>. Todos os direitos reservados.</p>
        </div>
    </footer>