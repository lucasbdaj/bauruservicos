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

// Verificar se o usuário está logado
if (!isset($_SESSION['id_profissional'])) {
    header("Location: login.php"); // Redirecionar para login caso não esteja logado
    exit;
}

$id_profissional = $_SESSION['id_profissional'];

// Buscar dados do profissional no banco
$sql = "SELECT nome_profissional, id_profissao, data_nascimento, tempo_profissao, descricao, telefone, email, rede_social, link_google, site_prestador, endereco, presta_servico_endereco, ativo, senha
        FROM profissional WHERE id_profissional = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Erro ao preparar query de busca de perfil: " . $conn->error);
    $_SESSION['message_type'] = 'error';
    $_SESSION['message_content'] = 'Erro interno ao buscar dados do perfil.';
    header("Location: index.php"); // Redireciona para um local seguro
    exit();
}

$stmt->bind_param("i", $id_profissional);
$stmt->execute();
$result = $stmt->get_result();
$dados_profissional = $result->fetch_assoc();
$stmt->close();

// Se não encontrou o profissional (sessão inválida ou dado corrompido)
if (!$dados_profissional) {
    session_destroy();
    $_SESSION['message_type'] = 'error';
    $_SESSION['message_content'] = 'Perfil não encontrado ou sessão inválida. Faça login novamente.';
    header("Location: login.php");
    exit;
}

// Processar a atualização dos dados se o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar CSRF token
    if (!isset($_POST["csrf_token"]) || !validateCSRFToken($_POST["csrf_token"])) {
        $_SESSION['message_type'] = 'error';
        $_SESSION['message_content'] = 'Erro de segurança: Token CSRF inválido.';
        header("Location: editar_perfil.php");
        exit();
    }

    // Lógica para desativar cadastro
    if (isset($_POST['desativar_cadastro'])) {
        $sql_update = "UPDATE profissional SET ativo = 'N' WHERE id_profissional = ?";
        $stmt_update = $conn->prepare($sql_update);

        if (!$stmt_update) {
            error_log("Erro ao preparar query de desativação: " . $conn->error);
            $_SESSION['message_type'] = 'error';
            $_SESSION['message_content'] = 'Erro interno ao tentar desativar cadastro.';
            header("Location: editar_perfil.php");
            exit();
        }

        $stmt_update->bind_param("i", $id_profissional);

        if ($stmt_update->execute()) {
            $_SESSION['message_type'] = 'success';
            $_SESSION['message_content'] = 'Cadastro desativado com sucesso! Caso você faça login nos próximos 30 dias, seu perfil será mantido. Caso contrário, será excluído.';
            session_destroy(); // Encerrar a sessão após desativação
            header("Location: index.php"); // Redirecionar para a página inicial ou de login
            exit();
        } else {
            error_log("Erro ao desativar cadastro: " . $stmt_update->error);
            $_SESSION['message_type'] = 'error';
            $_SESSION['message_content'] = 'Erro ao desativar cadastro. Tente novamente.';
            header("Location: editar_perfil.php");
            exit();
        }
        $stmt_update->close();
    } else { // Lógica para atualizar os dados do cadastro
        // Sanitizar e validar dados do formulário
        $nome_profissional = filter_input(INPUT_POST, 'nome_profissional', FILTER_SANITIZE_STRING);
        $id_profissao = filter_input(INPUT_POST, 'id_profissao', FILTER_SANITIZE_NUMBER_INT);
        $data_nascimento = filter_input(INPUT_POST, 'data_nascimento', FILTER_SANITIZE_STRING);
        $tempo_profissao = filter_input(INPUT_POST, 'tempo_profissao', FILTER_SANITIZE_NUMBER_INT);
        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
        $telefone = preg_replace('/\D/', '', $_POST['telefone']); // Remove tudo que não for dígito
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $rede_social = filter_input(INPUT_POST, 'rede_social', FILTER_SANITIZE_URL);
        $link_google = filter_input(INPUT_POST, 'link_google', FILTER_SANITIZE_URL);
        $site_prestador = filter_input(INPUT_POST, 'site_prestador', FILTER_SANITIZE_URL);
        $endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_STRING);
        $presta_servico_endereco = filter_input(INPUT_POST, 'servicos_endereco', FILTER_SANITIZE_STRING);
        
        $senha_nova = $_POST['senha'];
        $confirmar_senha_nova = $_POST['confirmar_senha'];

        // Validações básicas
        if (empty($nome_profissional) || empty($id_profissao) || empty($data_nascimento) || !isset($tempo_profissao) || empty($descricao) || empty($telefone) || empty($email) || empty($presta_servico_endereco)) {
             $_SESSION['message_type'] = 'error';
            $_SESSION['message_content'] = 'Por favor, preencha todos os campos obrigatórios.';
            header("Location: editar_perfil.php");
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['message_type'] = 'error';
            $_SESSION['message_content'] = 'Formato de e-mail inválido.';
            header("Location: editar_perfil.php");
            exit();
        }
        
        if (!preg_match("/^\d{10,11}$/", $telefone)) {
            $_SESSION['message_type'] = 'error';
            $_SESSION['message_content'] = 'Formato de telefone inválido. Deve conter 10 ou 11 dígitos numéricos.';
            header("Location: editar_perfil.php");
            exit();
        }

        // Inicia a query SQL e os parâmetros base
        $sql_update = "UPDATE profissional SET 
            nome_profissional = ?, 
            id_profissao = ?, 
            data_nascimento = ?, 
            tempo_profissao = ?, 
            descricao = ?, 
            telefone = ?, 
            email = ?, 
            rede_social = ?, 
            link_google = ?,
            site_prestador = ?,
            endereco = ?, 
            presta_servico_endereco = ? ";
        $params = [
            $nome_profissional, $id_profissao, $data_nascimento, $tempo_profissao,
            $descricao, $telefone, $email, $rede_social, $link_google, $site_prestador,
            $endereco, $presta_servico_endereco
        ];
        
        // CORREÇÃO APLICADA AQUI
        $types = "sisissssssss"; 

        // Validação e tratamento da nova senha, se fornecida
        if (!empty($senha_nova)) {
            if ($senha_nova !== $confirmar_senha_nova) {
                $_SESSION['message_type'] = 'error';
                $_SESSION['message_content'] = 'As senhas não coincidem!';
                header("Location: editar_perfil.php");
                exit();
            }
            if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!$%^&*()_+|~=`{}\[\]:\'\";<>?,.\/]).{8,}$/", $senha_nova)) {
                $_SESSION['message_type'] = 'error';
                $_SESSION['message_content'] = 'A nova senha é fraca. Use pelo menos 8 caracteres, com letras, números e símbolos.';
                header("Location: editar_perfil.php");
                exit();
            }
            $senha_hash_para_atualizar = password_hash($senha_nova, PASSWORD_DEFAULT);
            $sql_update .= ", senha = ?";
            $params[] = $senha_hash_para_atualizar;
            $types .= "s";
        } else {
            if (!empty($confirmar_senha_nova)) {
                $_SESSION['message_type'] = 'error';
                $_SESSION['message_content'] = 'Para manter a senha atual, deixe os dois campos de senha em branco.';
                header("Location: editar_perfil.php");
                exit();
            }
        }
        
        $sql_update .= " WHERE id_profissional = ?";
        $params[] = $id_profissional;
        $types .= "i";

        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update === false) {
            error_log("Erro ao preparar statement UPDATE em editar_perfil.php: " . $conn->error);
            $_SESSION['message_type'] = 'error';
            $_SESSION['message_content'] = 'Erro interno ao preparar atualização dos dados.';
            header("Location: editar_perfil.php");
            exit();
        }
        
        $stmt_update->bind_param($types, ...$params);

        if ($stmt_update->execute()) {
            $_SESSION['message_type'] = 'success';
            $_SESSION['message_content'] = 'Dados atualizados com sucesso!';
            header("Location: editar_perfil.php"); 
            exit();
        } else {
            error_log("Erro ao atualizar dados em editar_perfil.php: " . $stmt_update->error);
            $_SESSION['message_type'] = 'error';
            $_SESSION['message_content'] = 'Erro ao atualizar dados. Tente novamente.';
            header("Location: editar_perfil.php");
            exit();
        }
        $stmt_update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Edite e atualize seu perfil de prestador de serviço no Bauru Serviços. Mantenha suas informações sempre atualizadas.">
    <meta name="author" content="Lucas Borges">
    <meta name="keywords" content="bauru, serviços, editar perfil, atualizar cadastro, prestador">
	<meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cadastro - Bauru Serviços</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="imagem/bauru_servico.png" alt="Bauru Serviços" class="logo"> <h1>Editar Cadastro</h1>
            </div>
            <nav class="header-links">
                <a href="index.php" class="cab-link">Início</a>
                <a href="cadastro.php" class="cab-link">Cadastrar</a>            
                <a href="contato.php" class="cab-link">Contato</a>
                <a href="sobre.php" class="cab-link">Sobre</a>
                <a href="login.php" class="cab-link">Entrar</a>
            </nav>
        </div>
    </header>
	<main>
        <div class="form-container">
            <h2>Atualizar Seus Dados</h2>
            <?php if ($message_content): ?>
                <p class="message <?php echo htmlspecialchars($message_type); ?>">
                    <?php echo htmlspecialchars($message_content); ?>
                </p>
            <?php endif; ?>
            <form action="editar_perfil.php" method="POST" id="edit-profile-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="nome_profissional">Nome:</label>
                    <input type="text" id="nome_profissional" name="nome_profissional" value="<?php echo htmlspecialchars($dados_profissional['nome_profissional'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="id_profissao">Profissão:</label>
                    <select id="id_profissao" name="id_profissao" required>
                        <?php
                        $sql_profissao = "SELECT id_profissao, nome_profissao FROM profissao ORDER BY nome_profissao";
                        $result_profissao = $conn->query($sql_profissao);
                        if ($result_profissao && $result_profissao->num_rows > 0) {
                            while ($row = $result_profissao->fetch_assoc()) {
                                $selected = ($row['id_profissao'] == $dados_profissional['id_profissao']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['id_profissao']) . "' $selected>" . htmlspecialchars($row['nome_profissao']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>Nenhuma profissão disponível</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento:</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($dados_profissional['data_nascimento'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="tempo_profissao">Tempo de Profissão (anos):</label>
                    <input type="number" id="tempo_profissao" name="tempo_profissao" value="<?php echo htmlspecialchars($dados_profissional['tempo_profissao'] ?? ''); ?>" required min="0">
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao" required><?php echo htmlspecialchars($dados_profissional['descricao'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone:</label>
                    <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($dados_profissional['telefone'] ?? ''); ?>" required placeholder="(XX) XXXXX-XXXX">
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($dados_profissional['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="rede_social">Rede Social:</label>
                    <input type="url" id="rede_social" name="rede_social" value="<?php echo htmlspecialchars($dados_profissional['rede_social'] ?? ''); ?>" placeholder="Link da sua rede social (Ex: https://instagram.com/seu_perfil)">
                </div>

                <div class="form-group">
                    <label for="link_google">Link do Google (Google My Business):</label>
                    <input type="url" id="link_google" name="link_google" value="<?php echo htmlspecialchars($dados_profissional['link_google'] ?? ''); ?>" placeholder="Link da sua página no Google">
                </div>

                <div class="form-group">
                    <label for="site_prestador">Site Próprio:</label>
                    <input type="url" id="site_prestador" name="site_prestador" value="<?php echo htmlspecialchars($dados_profissional['site_prestador'] ?? ''); ?>" placeholder="Link do seu site (Ex: https://seusite.com.br)">
                </div>

                <div class="form-group">
                    <label for="endereco">Endereço:</label>
                    <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($dados_profissional['endereco'] ?? ''); ?>" placeholder="Digite o endereço completo">
                </div>

                <div class="form-group">
                    <label for="servicos_endereco">Presta serviços no endereço informado?</label>
                    <select id="servicos_endereco" name="servicos_endereco" required>
                        <option value="S" <?php echo ($dados_profissional['presta_servico_endereco'] == 'S') ? 'selected' : ''; ?>>Sim</option>
                        <option value="N" <?php echo ($dados_profissional['presta_servico_endereco'] == 'N') ? 'selected' : ''; ?>>Não</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="senha">Nova Senha (deixe em branco para não alterar):</label>
                    <div class="password-container">
                        <input type="password" id="senha" name="senha" placeholder=" ******** ">
                        <span class="toggle-password" onclick="togglePassword('senha')">👁️</span>
                    </div>
                    <small id="senha-info" class="password-info">A senha deve ter pelo menos 8 caracteres, incluindo letras, números e caracteres especiais.</small>
                </div>

                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Nova Senha:</label>
                    <div class="password-container">
                        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder=" ******** ">
                        <span class="toggle-password" onclick="togglePassword('confirmar_senha')">👁️</span>
                    </div>
                </div>

                <button type="submit">Salvar Alterações</button>
                <button type="submit" name="desativar_cadastro" class="desativar-btn" onclick="return confirm('Tem certeza que deseja desativar seu cadastro? Você terá 30 dias para reativar antes que seja permanentemente excluído.')">Desativar Cadastro</button>
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

        document.getElementById('edit-profile-form').addEventListener("submit", function(event) {
            var senha = document.getElementById("senha").value;
            var confirmar_senha = document.getElementById("confirmar_senha").value;

            // Só valida a senha se um dos campos foi preenchido
            if (senha.length > 0 || confirmar_senha.length > 0) {
                if (senha !== confirmar_senha) {
                    alert("As senhas não coincidem!");
                    event.preventDefault();
                    return;
                }

                var senha_regex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[!$%^&*()_+|~=`{}\[\]:'\";<>?,.\/]).{8,}$/;
                if (!senha.match(senha_regex)) {
                    alert("A nova senha é fraca. Recomendamos uma senha com pelo menos 8 caracteres, incluindo letras, números e caracteres especiais.");
                    event.preventDefault(); 
                }
            }
        });

        // Adicionar máscara ao telefone
        document.addEventListener('DOMContentLoaded', function() {
            var telefoneInput = document.getElementById('telefone');
            if (telefoneInput) {
                // Formata o valor inicial que vem do banco
                var initialValue = telefoneInput.value.replace(/\D/g, '');
                if(initialValue.length > 0) {
                    var formattedInitial = initialValue.match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
                    telefoneInput.value = !formattedInitial[2] ? formattedInitial[1] : '(' + formattedInitial[1] + ') ' + formattedInitial[2] + (formattedInitial[3] ? '-' + formattedInitial[3] : '');
                }

                // Adiciona o listener para formatação durante a digitação
                telefoneInput.addEventListener('input', function (e) {
                    var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
                    e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
                });
            }
        });
    </script>	

    <?php
    // Fechando a conexão com o banco de dados aqui, no final de todo o script.
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>