<?php
require_once __DIR__ . "/config/db_connection.php";
// require_once __DIR__ . "/logic/fetch_cadastro.php"; // Removida, pois não parece necessária ou pode causar output indesejado.
require_once __DIR__ . "/logic/csrf_token.php";

session_start();

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
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="imagem/bauru_servico.png" alt="Bauru Serviços" class="logo">
                <h1>Cadastro de Prestador</h1>
            </div>
            <nav class="header-links">
                <a href="index.php" class="cab-link">Início</a>
                <a href="cadastro.php" class="cab-link" aria-current="page">Cadastrar</a>           
                <a href="contato.php" class="cab-link">Contato</a>
                <a href="sobre.php" class="cab-link">Sobre</a>
                <a href="login.php" class="cab-link">Entrar</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="form-container">
            <h2>Formulário de Cadastro</h2>
            <?php if ($message_content): ?>
                <p class="message <?php echo htmlspecialchars($message_type); ?>">
                    <?php echo htmlspecialchars($message_content); ?>
                </p>
            <?php endif; ?>

            <form action="envia_cadastro.php" method="POST" id ="cadForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-group">
                <label for="nome_profissional">Nome: <span class="required">*</span></label>
                <input type="text" id="nome_profissional" name="nome_profissional" placeholder="Seu nome ou Nome do seu negócio" required style="text-transform: capitalize;">
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
                <label for="data_nascimento">Data de Nascimento: <span class="required">*</span></label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>
            </div>

            <div class="form-group">
                <label for="tempo_profissao">Tempo de Profissão (anos): <span class="required">*</span></label>
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
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Digite seu e-mail">
            </div>

            <div class="form-group">
                <label for="rede_social">Rede Social:</label>
                <input type="url" id="rede_social" name="rede_social" placeholder="Coloque o link da sua rede social ex: https://www.instagram.com/seuperfil">
            </div>

            <div class="form-group">
                <label for="link_google">Link do Google (Google My Business):</label>
                <input type="url" id="link_google" name="link_google" placeholder="Link da sua página no Google ex: https://maps.google.com/...">
            </div>

            <div class="form-group">
                <label for="site_prestador">Site Próprio:</label>
                <input type="url" id="site_prestador" name="site_prestador" placeholder="Link do seu site ex: https://www.seusite.com.br">
            </div>

            <div class="form-group">
                <label for="endereco">Endereço:</label>
                <input type="text" id="endereco" name="endereco" placeholder="Digite o endereço completo exemplo: Rua x Nº 1-23">
            </div>

			<div class="form-group">
				<label for="servicos_endereco">Presta serviços no endereço informado? <span class="required">*</span></label>
				<select id="servicos_endereco" name="servicos_endereco" required>
					<option value="N">Não</option>
					<option value="S">Sim</option>
				</select>
			</div>
			<div class="form-group password-group">
				<label for="senha">Senha: <span class="required">*</span></label>
				<div class="password-container">
					<input type="password" id="senha" name="senha" placeholder=" ******** " required>
					<span class="toggle-password" onclick="togglePassword('senha')">👁️</span>
				</div>
                <small id="senha-info" class="password-info">A senha deve ter pelo menos 8 caracteres, incluindo letras, números e caracteres especiais.</small>
			</div>

			<div class="form-group password-group">
				<label for="confirmar_senha">Confirmar Senha: <span class="required">*</span></label>
				<div class="password-container">
					<input type="password" id="confirmar_senha" name="confirmar_senha" placeholder=" ******** " required>
					<span class="toggle-password" onclick="togglePassword('confirmar_senha')">👁️</span>
				</div>
			</div>
			<div class="form-group">
				<input type="checkbox" id="aceite_termos" name="aceite_termos" required>
				<label for="aceite_termos">Eu li e aceito os <a href="politica_privacidade.php" target="_blank">Termos de Uso e Política de Privacidade</a> <span class="required">*</span></label>
			</div>		
            <button type="submit">Cadastrar</button>
			
			<p>Já possui cadastro? <a href="login.php">Clique aqui para fazer login</a>.</p>
        </form>
        </div>
    </main>

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