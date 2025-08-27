<?php
session_start();

require_once __DIR__ . "/config/db_connection.php";
require_once __DIR__ . "/logic/csrf_token.php";

$csrf_token = generateCSRFToken();

// Captura e limpa mensagens de feedback da sessão (vindo de envia_cadastro.php)
$message_type = $_SESSION['message_type'] ?? '';
$message_content = $_SESSION['message_content'] ?? '';
unset($_SESSION['message_type'], $_SESSION['message_content']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Cadastre-se como prestador de serviço no Bauru Serviços e divulgue suas habilidades para milhares de clientes em Bauru.">
    <meta name="author" content="Lucas Borges">
    <meta name="keywords" content="bauru, serviços, cadastro, prestador de serviço, registrar, profissional">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Prestador de Serviço - Bauru Serviços</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="cadastro">

    <?php require_once __DIR__ . "/partials/header.php"; ?>

    <main>
        <div class="form-container">
            <h2>Formulário de Cadastro</h2>
            <?php if ($message_content): ?>
                <p class="message <?php echo htmlspecialchars($message_type); ?>">
                    <?php echo htmlspecialchars($message_content); ?>
                </p>
            <?php endif; ?>

            <form action="process/envia_cadastro.php" method="POST" id ="cadForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-group">
            <label for="nome_profissional">
                Nome: <span class="required">*</span>
                <span class="tooltip">
                <span class="info-icon">i</span>
                <span class="tooltiptext">Informe seu nome completo ou o nome do seu negócio.</span>
                </span>
            </label>
            <input type="text" id="nome_profissional" name="nome_profissional" placeholder="Informe seu nome completo" required>
            </div>

            <div class="form-group">
                <label for="id_profissao">Profissão: <span class="required">*</span></label>
                <select id="id_profissao" name="id_profissao" required>
                    <?php
                    // A conexão já deve estar aberta pelo require_once db_connectionl.php
                    $sql_profissao = "SELECT * FROM profissao ORDER BY nome_profissao"; // Alterado para ORDER BY nome_profissao
                    $result_profissao = $conn->query($sql_profissao);
                    if ($result_profissao && $result_profissao->num_rows > 0) {
                        while ($row = $result_profissao->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id_profissao']) . "'>" . htmlspecialchars($row['nome_profissao']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Nenhuma profissão disponível</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="data_nascimento">Data de nascimento: <span class="required">*</span></label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>
            </div>

            <div class="form-group">
                <label for="tempo_profissao">Tempo de profissão (anos): <span class="required">*</span></label>
                <input type="number" id="tempo_profissao" name="tempo_profissao" placeholder="A quantos anos você atua na área?" required min="0">
            </div>

            <div class="form-group">
                <label for="descricao">Descrição: <span class="required">*</span></label>
                <textarea id="descricao" name="descricao" rows="4" placeholder="Descreva aqui sobre o seu negócio, pode ser uma breve descrição." required></textarea>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone: <span class="required">*</span></label>
                <input type="text" id="telefone" name="telefone" placeholder="(14) XXXXX-XXXX" required pattern="\(\d{2}\) \d{4,5}-\d{4}" title="Formato: (XX) XXXXX-XXXX ou (XX) XXXX-XXXX">
            </div>

            <div class="form-group">
                <label for="endereco">Endereço:</label>
                <input type="text" id="endereco" name="endereco" placeholder="Endereço completo">
            </div>

            <div class="form-group">
                <label for="servicos_endereco">
                    <input type="checkbox" id="servicos_endereco" name="servicos_endereco" value="S">
                    Presta serviços no endereço informado? <span></span>
                </label>
            </div>

            <div class="form-group">
                <label for="rede_social">Rede social:</label>
                <input type="url" id="rede_social" name="rede_social" placeholder="Informe o link da sua rede social">
            </div>

            <div class="form-group">
                <label for="link_google">Link do google:</label>
                <input type="url" id="link_google" name="link_google" placeholder="Informe o link da página no Google">
            </div>

            <div class="form-group">
                <label for="site_prestador">Site próprio:</label>
                <input type="url" id="site_prestador" name="site_prestador" placeholder="Informe o link do seu site">
            </div>

            <div class="form-group">
                <label for="email">E-mail:<span class="required">*</span></label>
                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
            </div>

			<div class="form-group password-group">
				<label for="senha">Senha: <span class="required">*</span></label>
				<div class="password-container">
					<input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
					<span class="toggle-password" onclick="togglePassword('senha')">👁️</span>
				</div>
                <small id="senha-info" class="password-info">A senha deve ter pelo menos 8 caracteres, incluindo letras, números e caracteres especiais.</small>
			</div>

			<div class="form-group password-group">
				<label for="confirmar_senha">Confirmar senha: <span class="required">*</span></label>
				<div class="password-container">
					<input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a senha" required>
					<span class="toggle-password" onclick="togglePassword('confirmar_senha')">👁️</span>
				</div>
			</div>
			<div class="form-group">
				<input type="checkbox" id="aceite_termos" name="aceite_termos" required>
				<label for="aceite_termos">Declaro que li e aceito os <a href="politica_privacidade.php" target="_blank">Termos de uso e Política de privacidade</a> <span class="required">*</span></label>
			</div>	
            <div class="form-buttons-container">	
                <button type="submit">Cadastrar</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='index.php'">Cancelar</button>
            </div>
			
			<p>Já possui cadastro? <a href="login.php">Clique aqui para fazer login</a>.</p>
        </form>
        </div>
    </main>

    <?php require_once __DIR__ . "/partials/footer.php"; ?>
	
    <script>
        // Função para exibir/ocultar a senha
        function togglePassword(id) {
            var passwordField = document.getElementById(id);
            var type = passwordField.type === "password" ? "text" : "password";
            passwordField.type = type;
        }

        document.getElementById("cadForm").addEventListener("submit", function(event) {
            var senha = document.getElementById("senha").value;
            var confirmar_senha = document.getElementById("confirmar_senha").value;

            // Verificar se as senhas coincidem
            if (senha !== confirmar_senha) {
                event.preventDefault();
                alert("As senhas não coincidem!");
                return;
            }

            // Validar a força da senha (mínimo 8 caracteres, com letras, números e caracteres especiais)
            var senha_regex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*()_+|~=`{}\[\]:'\";<>?,.\/-]).{8,}$/;
            if (!senha.match(senha_regex)) {
                event.preventDefault(); // Impede o envio se a senha for fraca
                alert("A senha é fraca. Recomendamos uma senha com pelo menos 8 caracteres, incluindo letras, números e caracteres especiais.");
            }
        });

        // Adicionar máscara ao telefone
        document.addEventListener('DOMContentLoaded', function() {
            var telefoneInput = document.getElementById('telefone');
            if (telefoneInput) {
                telefoneInput.addEventListener('input', function (e) {
                    var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
                    e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
                });
            }
        });
    </script>
	
</body>
</html>
<?php
// A conexão deve ser fechada apenas uma vez, idealmente ao final do script ou em um hook de shutdown
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
?>