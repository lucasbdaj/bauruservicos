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
<header>
    <div class="header-container">
        <a href="index.php" class="logo-container">
            <img src="imagem/bauru_servico.png" alt="Logo Bauru Serviços" class="logo">
            <h1>Bauru Serviços</h1>
        </a>
        <nav class="header-links">
            <a href="index.php" class="cab-link">Início</a>
            <a href="sobre.php" class="cab-link">Sobre</a>
            <a href="contato.php" class="cab-link">Contato</a>

            <?php if ($is_logged_in): ?>
                <div class="user-menu-container">
                    <a href="gerenciar.php" class="user-name"> Olá, <?php echo htmlspecialchars($nome_usuario); ?> &#9662;
                    </a>
                    <div class="dropdown-content">
                        <a href="gerenciar.php">Gerenciar</a>
                        <a href="editar_cadastro.php">Editar cadastro</a>
                        <a href="alterar_email.php">Alterar e-mail</a>
                        <a href="alterar_senha.php">Alterar senha</a>
                        <a href="logout.php">Sair</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="cadastro.php" class="cab-link">Cadastro</a>
                <a href="login.php" class="cab-link">Entrar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>