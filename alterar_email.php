<?php
// Caminhos corrigidos com a adição de "/"
require_once __DIR__ . "/config/db_connection.php";
require_once __DIR__ . "/logic/csrf_token.php";
session_start();

// Verificar login (o caminho do Location já estava correto)
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
    // Validação CSRF
    if (!isset($_POST["csrf_token"]) || !validateCSRFToken($_POST["csrf_token"])) {
        $_SESSION['message_type'] = 'error'; $_SESSION['message_content'] = 'Erro de segurança.'; header("Location: alterar_email.php"); exit();
    }

    // 1. Validar a senha atual
    $senha_atual = $_POST['senha_atual'];
    $sql_get_pass = "SELECT senha FROM profissional WHERE id_profissional = ?";
    $stmt_pass = $conn->prepare($sql_get_pass);
    $stmt_pass->bind_param("i", $id_profissional);
    $stmt_pass->execute();
    $result_pass = $stmt_pass->get_result();
    $user_data = $result_pass->fetch_assoc();
    $stmt_pass->close();

    if (!$user_data || !password_verify($senha_atual, $user_data['senha'])) {
        $_SESSION['message_type'] = 'error'; $_SESSION['message_content'] = 'Senha atual incorreta!'; header("Location: alterar_email.php"); exit();
    }

    // 2. Validar o novo e-mail
    $novo_email = filter_input(INPUT_POST, 'novo_email', FILTER_SANITIZE_EMAIL);
    if (!filter_var($novo_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message_type'] = 'error'; $_SESSION['message_content'] = 'Formato de e-mail inválido.'; header("Location: alterar_email.php"); exit();
    }

    // 3. Verificar se o novo e-mail já está em uso por outro profissional
    $sql_check_email = "SELECT id_profissional FROM profissional WHERE email = ? AND id_profissional != ?";
    $stmt_check = $conn->prepare($sql_check_email);
    $stmt_check->bind_param("si", $novo_email, $id_profissional);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $_SESSION['message_type'] = 'error'; $_SESSION['message_content'] = 'Este e-mail já está cadastrado em outra conta.'; header("Location: alterar_email.php"); exit();
    }
    $stmt_check->close();
    
    // 4. Atualizar o e-mail no banco
    $sql_update = "UPDATE profissional SET email = ? WHERE id_profissional = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $novo_email, $id_profissional);

    if ($stmt_update->execute()) {
        $_SESSION['message_type'] = 'success'; $_SESSION['message_content'] = 'E-mail alterado com sucesso!';
    } else {
        $_SESSION['message_type'] = 'error'; $_SESSION['message_content'] = 'Erro ao alterar o e-mail.';
    }
    $stmt_update->close();
    header("Location: alterar_email.php");
    exit();
}

// Buscar e-mail atual para exibição
$sql_get_email = "SELECT email FROM profissional WHERE id_profissional = ?";
$stmt_get_email = $conn->prepare($sql_get_email);
$stmt_get_email->bind_param("i", $id_profissional);
$stmt_get_email->execute();
$result_email = $stmt_get_email->get_result();
$email_atual = $result_email->fetch_assoc()['email'];
$stmt_get_email->close();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar E-mail - Bauru Serviços</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <?php require_once __DIR__ . "/partials/header.php"; ?>
    <main>
        <div class="form-container">
            <h2>Alterar e-mail</h2>
            <p>Seu e-mail atual é: <strong><?php echo htmlspecialchars($email_atual); ?></strong></p>
            <?php if ($message_content): ?>
                <p class="message <?php echo htmlspecialchars($message_type); ?>"><?php echo htmlspecialchars($message_content); ?></p>
            <?php endif; ?>

            <form action="alterar_email.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="novo_email">Novo e-mail:<span class="required">*</span></label>
                    <input type="email" id="novo_email" name="novo_email" placeholder="Digite seu novo e-mail" required>
                </div>


                <div class="form-group">
                    <label for="senha_atual">Senha:<span class="required">*</span></label>
                    <div class="password-container">
                        <input type="password" id="senha_atual" name="senha_atual" placeholder="Digite sua senha" required>
                        <span class="toggle-password" onclick="togglePassword('senha_atual')">👁️</span>
                    </div>
                </div>
                
                <div class="form-buttons-container">
                    <button type="submit">Alterar e-mail</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='gerenciar.php'">Cancelar</button>
                </div>
            </form>
        </div>
    </main>
    <?php require_once __DIR__ . "/partials/footer.php"; $conn->close(); ?>

    <script>
    // Função para exibir/ocultar a senha
    function togglePassword(id) {
        var passwordField = document.getElementById(id);
        var type = passwordField.type === "password" ? "text" : "password";
        passwordField.type = type;
    }
    </script>

</body>
</html>