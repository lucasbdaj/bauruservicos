<?php // Iniciar a sessão
session_start();

// Inclua a conexão com o banco de dados
require_once __DIR__ . "/config/db_connection.php";
require_once __DIR__ . "/logic/csrf_token.php";

$csrf_token = generateCSRFToken();

// Captura e limpa mensagens de feedback da sessão
$message_type = $_SESSION['message_type'] ?? '';
$message_content = $_SESSION['message_content'] ?? '';
unset($_SESSION['message_type'], $_SESSION['message_content']); // Limpa a sessão após exibir

// Processamento do login
if (isset($_POST["login"])) {
    // Validar CSRF token
    if (!isset($_POST["csrf_token"]) || !validateCSRFToken($_POST["csrf_token"])) {
        $_SESSION['message_type'] = 'error';
        $_SESSION['message_content'] = 'Erro de segurança: Token CSRF inválido.';
        header("Location: login.php");
        exit();
    }
    // Obter e sanitizar os dados de login
    $email_login = filter_input(INPUT_POST, 'email_login', FILTER_SANITIZE_EMAIL);
    $senha_login = $_POST['senha_login'];

    if (empty($email_login) || empty($senha_login)) {
        $_SESSION['message_type'] = 'error';
        $_SESSION['message_content'] = 'Os campos e-mail e senha são obrigatórios';
        header("Location: login.php");
        exit();
    }

    // Consultar o usuário pelo email
    $sql_login = "SELECT id_profissional, senha, ativo FROM profissional WHERE email = ?";
    $stmt_login = $conn->prepare($sql_login);
    
    if (!$stmt_login) {
        error_log("Erro ao preparar query de login: " . $conn->error);
        $_SESSION['message_type'] = 'error';
        $_SESSION['message_content'] = 'Erro interno ao tentar fazer login.';
        header("Location: login.php");
        exit();
    }

    $stmt_login->bind_param("s", $email_login);
    $stmt_login->execute();
    $stmt_login->store_result();

    // ALTERAÇÃO 1: Lógica de erro unificada
    if ($stmt_login->num_rows > 0) {
        $stmt_login->bind_result($id_profissional, $senha_hash, $ativo);
        $stmt_login->fetch();

        // Verificar se a senha fornecida corresponde ao hash armazenado
        if (password_verify($senha_login, $senha_hash)) {
            // Se a senha estiver correta, o login é bem-sucedido.
            
            // Reativar conta se necessário
            if ($ativo === 'N') {
                $sql_update_ativo = "UPDATE profissional SET ativo = 'S' WHERE id_profissional = ?";
                $stmt_update = $conn->prepare($sql_update_ativo);
                if ($stmt_update) {
                    $stmt_update->bind_param("i", $id_profissional);
                    $stmt_update->execute();
                    $stmt_update->close();
                }
            }

            // Armazenar o ID do prestador de serviço na sessão
            $_SESSION['id_profissional'] = $id_profissional;
            $_SESSION['message_type'] = 'success';
            $_SESSION['message_content'] = 'Login bem-sucedido!';
            header("Location: index.php");
            exit;
        }
    }
    
    // Se o código chegou até aqui, significa que o e-mail não existe OU a senha está incorreta.
    // Em ambos os casos, mostramos a mesma mensagem de erro genérica.
    $_SESSION['message_type'] = 'error';
    $_SESSION['message_content'] = 'E-mail ou senha inválidos.';
    header("Location: login.php");
    exit();
    // FIM DA ALTERAÇÃO 1

    $stmt_login->close();
    $conn->close();
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
                <p class="erro-msg<?php echo htmlspecialchars($message_type); ?>">
                    <?php echo htmlspecialchars($message_content); ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="login" value="1">
                
                <div class="form-group">
                    <label for="email_login">E-mail:</label>
                    <input type="email" id="email_login" name="email_login" placeholder="Digite seu e-mail" >
                    
                </div>

                <div class="form-group">
                    <label for="senha_login">Senha:</label>
                    <div class="password-container">
                        <input type="password" id="senha_login" name="senha_login" placeholder="Digite sua senha">
                        <span class="toggle-password" onclick="togglePassword('senha_login')">👁️</span>
                    </div>
                    
                </div>
                
                <div class="form-buttons-container">
                    <button onclick="validarLogin(event)" type="submit" name="login" class="submit-btn">Entrar</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='index.php'">Cancelar</button>                    
                </div>
            </form>

            <p>Ainda não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a></p>
        </div>
    </main>

    <script>
        // Função para exibir/ocultar a senha
        function togglePassword(id) {
            var passwordField = document.getElementById(id);
            var type = passwordField.type === "password" ? "text" : "password";
            passwordField.type = type;
        }

        function validarLogin(event) {
            const email = document.getElementById("email_login");
            const senha = document.getElementById("senha_login");
            const erroEmail = document.getElementById("erro_email");
            const erroSenha = document.getElementById("erro_senha");

            let valido = true;

            // Limpa mensagens de erro antigas
            erroEmail.textContent = "";
            erroSenha.textContent = "";

            // ALTERAÇÃO 2: Validação do formato do e-mail
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email.value.trim() === "") {
                erroEmail.textContent = "O campo e-mail é obrigatório.";
                valido = false;
            } else if (!emailRegex.test(email.value)) {
                erroEmail.textContent = "E-mail ou senha inválidos.";
                valido = false;
            }
            // FIM DA ALTERAÇÃO 2

            if (senha.value.trim() === "") {
                erroSenha.textContent = "O campo senha é obrigatório.";
                valido = false;
            }

            // Apenas impede o envio se a validação falhar
            if (valido === false) {
                event.preventDefault(); // Impede o envio do formulário
            }
        }
    </script>	

    <?php require_once __DIR__ . "/partials/footer.php"; ?>

</body>
</html>