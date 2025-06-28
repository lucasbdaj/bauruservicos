<?php
// Inclua a conexão com o banco de dados
require_once __DIR__ . "/config/db_connection.php";
require_once __DIR__ . "/logic/csrf_token.php";

// Iniciar a sessão
session_start();

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
            header("Location: editar_perfil.php");
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
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="imagem/bauru_servico.png" alt="Bauru Serviços" class="logo"> <h1>Login</h1>
            </div>
            <nav class="header-links">
                <a href="index.php" class="cab-link">Início</a>
                <a href="cadastro.php" class="cab-link">Cadastrar</a>
                <a href="contato.php" class="cab-link">Contato</a>
                <a href="sobre.php" class="cab-link">Sobre</a>
                <a href="login.php" class="cab-link" aria-current="page">Entrar</a> 
            </nav>
        </div>
    </header>
    
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
                    <span id="erro_email" class="erro-msg"></span>
                </div>

                <div class="form-group">
                    <label for="senha_login">Senha:</label>
                    <div class="password-container">
                        <input type="password" id="senha_login" name="senha_login" placeholder=" ******** ">
                        <span class="toggle-password" onclick="togglePassword('senha_login')">👁️</span>
                    </div>
                    <span id="erro_senha" class="erro-msg"></span>
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
                erroEmail.textContent = "Formato de e-mail inválido. Ex: contato@dominio.com";
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

    <footer>
        <div class="footer-grid">
            <div class="footer-column">
                <h4>Bauru Serviços</h4>
                <p>Conectando clientes a prestadores de serviço qualificados em Bauru e região desde 2025.</p>
            </div>
            <div class="footer-column">
                <h4>Navegação</h4>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="cadastro.php">Cadastro de Profissional</a></li>
                    <li><a href="contato.php">Contato</a></li>
                    <li><a href="sobre.php">Sobre Nós</a></li>
                    <li><a href="login.php">Entrar</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Contato & Legal</h4>
                <ul>
                    <li><a href="mailto:contato@servicosbauru.com.br">contato@servicosbauru.com.br</a></li>
                    <li><a href="politica_privacidade.php">Política de Privacidade</a></li>
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
</body>
</html>