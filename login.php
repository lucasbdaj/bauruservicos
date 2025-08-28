<?php
require_once __DIR__ . '/bootstrap.php';

// Se o usuário já estiver logado, redireciona para a página principal.
if ($is_logged_in) {
    header('Location: index.php');
    exit();
}

$csrf_token = generateCSRFToken();

// Captura e limpa mensagens de feedback da sessão.
$message_type = $_SESSION['message_type'] ?? '';
$message_content = $_SESSION['message_content'] ?? '';
unset($_SESSION['message_type'], $_SESSION['message_content']);

// Processamento do login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!isset($_POST["csrf_token"]) || !validateCSRFToken($_POST["csrf_token"])) {
        redirectWithMessage('error', 'Erro de segurança. Tente novamente.', 'login.php');
    }
    
    $email_login = filter_input(INPUT_POST, 'email_login', FILTER_SANITIZE_EMAIL);
    $senha_login = $_POST['senha_login'] ?? '';

    if (empty($email_login) || empty($senha_login)) {
        redirectWithMessage('error', 'Os campos e-mail e senha são obrigatórios.', 'login.php');
    }

    $sql_login = "SELECT id_profissional, senha, ativo FROM profissional WHERE email = ?";
    $stmt_login = $conn->prepare($sql_login);
    
    if (!$stmt_login) {
        error_log("Erro ao preparar query de login: " . $conn->error);
        redirectWithMessage('error', 'Ocorreu um erro interno. Tente novamente mais tarde.', 'login.php');
    }

    $stmt_login->bind_param("s", $email_login);
    $stmt_login->execute();
    $result = $stmt_login->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar se a senha corresponde ao hash
        if (password_verify($senha_login, $user['senha'])) {
            // Sucesso! Senha correta.
            
            // Reativar conta se estava inativa
            if ($user['ativo'] === 'N') {
                $conn->query("UPDATE profissional SET ativo = 'S' WHERE id_profissional = " . $user['id_profissional']);
            }

            $_SESSION['id_profissional'] = $user['id_profissional'];
            redirectWithMessage('success', 'Login bem-sucedido!', 'index.php');

        } else {
            // Erro: Senha incorreta
            redirectWithMessage('error', 'E-mail ou senha inválidos.', 'login.php');
        }
    } else {
        // Erro: E-mail não encontrado
        redirectWithMessage('error', 'E-mail ou senha inválidos.', 'login.php');
    }
    
    // Fecha o statement e a conexão
    $stmt_login->close();
    $conn->close();
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
	<meta name="description" content="Faça login na sua conta Bauru Serviços para gerenciar seu perfil e divulgar seus serviços.">
    <meta name="author" content="Lucas Borges">
    <meta name="keywords" content="bauru, serviços, login, prestador de serviço">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bauru Serviços</title>
    <link rel="stylesheet" href="styles/styles.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="cadastro">

    <?php require_once __DIR__ . "/partials/header.php"; ?>
    
    <main>
        <div class="form-container">
            <h2>Login de Prestador de Serviço</h2>
            
            <?php if ($message_content): ?>
                <p class="message <?php echo htmlspecialchars($message_type); ?>">
                    <?php echo htmlspecialchars($message_content); ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="email_login">E-mail:</label>
                    <input type="email" id="email_login" name="email_login" placeholder="Digite seu e-mail" required>
                    <div id="erro_email" class="message error" style="display: none; font-size: 0.85em; margin-top: 5px; padding: 8px;"></div>
                </div>

                <div class="form-group">
                    <label for="senha_login">Senha:</label>
                    <div class="password-container">
                        <input type="password" id="senha_login" name="senha_login" placeholder="Digite sua senha" required>
                        <span class="toggle-password" onclick="togglePassword('senha_login')">👁️</span>
                    </div>
                    <div id="erro_senha" class="message error" style="display: none; font-size: 0.85em; margin-top: 5px; padding: 8px;"></div>
                </div>
                
                <div class="form-buttons-container">
                    <button type="submit" class="submit-btn">Entrar</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='index.php'">Cancelar</button>                    
                </div>
            </form>

            <p>Ainda não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a></p>
        </div>
    </main>

    <?php require_once __DIR__ . "/partials/footer.php"; ?>
    
    <script src="js/scripts.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            const email = document.getElementById("email_login");
            const senha = document.getElementById("senha_login");
            const erroEmail = document.getElementById("erro_email");
            const erroSenha = document.getElementById("erro_senha");
            let valido = true;

            // Limpa mensagens de erro antigas e esconde os containers
            erroEmail.textContent = "";
            erroEmail.style.display = 'none';
            erroSenha.textContent = "";
            erroSenha.style.display = 'none';

            // CORREÇÃO 2: Mensagem de erro mais clara no JavaScript
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email.value.trim() === "") {
                erroEmail.textContent = "O campo e-mail é obrigatório.";
                erroEmail.style.display = 'block';
                valido = false;
            } else if (!emailRegex.test(email.value)) {
                erroEmail.textContent = "Por favor, insira um formato de e-mail válido.";
                erroEmail.style.display = 'block';
                valido = false;
            }

            if (senha.value.trim() === "") {
                erroSenha.textContent = "O campo senha é obrigatório.";
                erroSenha.style.display = 'block';
                valido = false;
            }

            if (valido === false) {
                event.preventDefault(); // Impede o envio do formulário
            }
        });
    </script>	
</body>
</html>