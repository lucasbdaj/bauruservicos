<?php
require_once __DIR__ . "/config/db_connection.php";
require_once __DIR__ . "/logic/csrf_token.php";
session_start();

// Verificar login
if (!isset($_SESSION['id_profissional'])) {
    header("Location: login.php");
    exit;
}

$id_profissional = $_SESSION['id_profissional'];
$csrf_token = generateCSRFToken();

// Mensagens de feedback
$message_type = $_SESSION['message_type'] ?? '';
$message_content = $_SESSION['message_content'] ?? '';
unset($_SESSION['message_type'], $_SESSION['message_content']);

// Processar a atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (toda a sua lógica PHP de validação e atualização continua a mesma) ...
    // Validação CSRF
    if (!isset($_POST["csrf_token"]) || !validateCSRFToken($_POST["csrf_token"])) {
        $_SESSION['message_type'] = 'error'; $_SESSION['message_content'] = 'Erro de segurança.'; header("Location: alterar_senha.php"); exit();
    }

    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_nova_senha = $_POST['confirmar_nova_senha'];

    // 1. Validar a senha atual
    $sql_get_pass = "SELECT senha FROM profissional WHERE id_profissional = ?";
    $stmt_pass = $conn->prepare($sql_get_pass);
    $stmt_pass->bind_param("i", $id_profissional);
    $stmt_pass->execute();
    $result_pass = $stmt_pass->get_result();
    $user_data = $result_pass->fetch_assoc();
    $stmt_pass->close();

    if (!$user_data || !password_verify($senha_atual, $user_data['senha'])) {
        $_SESSION['message_type'] = 'error'; $_SESSION['message_content'] = 'Senha atual incorreta!'; header("Location: alterar_senha.php"); exit();
    }

    // 2. Validar a nova senha
    if (empty($nova_senha) || $nova_senha !== $confirmar_nova_senha) {
        $_SESSION['message_type'] = 'error'; $_SESSION['message_content'] = 'As novas senhas não coincidem ou estão em branco.'; header("Location: alterar_senha.php"); exit();
    }
    
    // Validar força da nova senha
    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*()_+|~=`{}\[\]:'\";<>?,.\/-]).{8,}$/", $nova_senha)) {
        $_SESSION['message_type'] = 'error'; $_SESSION['message_content'] = 'A nova senha é fraca. Use pelo menos 8 caracteres, com letras, números e símbolos.'; header("Location: alterar_senha.php"); exit();
    }
    
    // 3. Gerar hash e atualizar no banco
    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    $sql_update = "UPDATE profissional SET senha = ? WHERE id_profissional = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $nova_senha_hash, $id_profissional);

    if ($stmt_update->execute()) {
        session_destroy();
        session_start();
        $_SESSION['message_type'] = 'success';
        $_SESSION['message_content'] = 'Senha alterada com sucesso! Por favor, faça login novamente.';
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['message_type'] = 'error'; $_SESSION['message_content'] = 'Erro ao alterar a senha.';
        header("Location: alterar_senha.php");
        exit();
    }
    $stmt_update->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar senha - Bauru Serviços</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <?php require_once __DIR__ . "/partials/header.php"; ?>
    <main>
        <div class="form-container">
            <h2>Alterar senha</h2>
            <?php if ($message_content): ?>
                <p class="message <?php echo htmlspecialchars($message_type); ?>"><?php echo htmlspecialchars($message_content); ?></p>
            <?php endif; ?>

            <form action="alterar_senha.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="senha_atual">Senha atual:<span class="required">*</span></label>
                    <div class="password-container">
                        <input type="password" id="senha_atual" name="senha_atual" placeholder="Digite sua senha atual" required>
                        <span class="toggle-password" onclick="togglePassword('senha_atual')">👁️</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nova_senha">Nova senha:<span class="required">*</span></label>
                    <div class="password-container">
                        <input type="password" id="nova_senha" name="nova_senha" placeholder="Digite sua nova senha" required>
                        <span class="toggle-password" onclick="togglePassword('nova_senha')">👁️</span>
                    </div>
                    <small class="password-info">Mínimo 8 caracteres, com letras, números e símbolos.</small>
                </div>

                <div class="form-group">
                    <label for="confirmar_nova_senha">Confirmar nova senha:<span class="required">*</span></label>
                    <div class="password-container">
                        <input type="password" id="confirmar_nova_senha" name="confirmar_nova_senha" placeholder="Repita sua senha" required>
                        <span class="toggle-password" onclick="togglePassword('confirmar_nova_senha')">👁️</span>
                    </div>
                </div>

                <div class="form-buttons-container">
                    <button type="submit">Alterar senha</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='gerenciar.php'">Cancelar</button>
                </div>
            </form>
        </div>
    </main>
    <?php require_once __DIR__ . "/partials/footer.php"; $conn->close(); ?>

    <script>
        // Esta função é global, não precisa estar dentro de um listener específico
        function togglePassword(id) {
            var passwordField = document.getElementById(id);
            if (passwordField) {
                var type = passwordField.type === "password" ? "text" : "password";
                passwordField.type = type;
            }
        }
    </script>
</body>
</html>