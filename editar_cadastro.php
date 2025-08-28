<?php
require_once __DIR__ . "/config/db_connection.php";
require_once __DIR__ . "/logic/csrf_token.php";
session_start();

// 1. Verificar se o usuário está logado
if (!isset($_SESSION['id_profissional'])) {
    header("Location: login.php");
    exit;
}

$id_profissional = $_SESSION['id_profissional'];
$csrf_token = generateCSRFToken();

// 2. Gerenciar mensagens de feedback da sessão
$message_type = $_SESSION['message_type'] ?? '';
$message_content = $_SESSION['message_content'] ?? '';
unset($_SESSION['message_type'], $_SESSION['message_content']);

// 3. Processar o formulário via POST (Bloco Inteiramente Corrigido)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validação do Token CSRF (executa para ambas as ações)
    if (!isset($_POST["csrf_token"]) || !validateCSRFToken($_POST["csrf_token"])) {
        $_SESSION['message_type'] = 'error';
        $_SESSION['message_content'] = 'Erro de segurança. Tente novamente.';
        header("Location: editar_cadastro.php");
        exit();
    }

    // Validação da senha atual para autorizar qualquer alteração
    $senha_atual = $_POST['senha_atual'];
    if (empty($senha_atual)) {
        $_SESSION['message_type'] = 'error';
        $_SESSION['message_content'] = 'Você precisa informar sua senha atual para salvar as alterações.';
        header("Location: editar_cadastro.php");
        exit();
    }

    $sql_get_pass = "SELECT senha FROM profissional WHERE id_profissional = ?";
    $stmt_pass = $conn->prepare($sql_get_pass);
    $stmt_pass->bind_param("i", $id_profissional);
    $stmt_pass->execute();
    $result_pass = $stmt_pass->get_result();
    $user_data = $result_pass->fetch_assoc();
    $stmt_pass->close();

    if (!$user_data || !password_verify($senha_atual, $user_data['senha'])) {
        $_SESSION['message_type'] = 'error';
        $_SESSION['message_content'] = 'Senha atual incorreta!';
        header("Location: editar_cadastro.php");
        exit();
    }

    // --- LÓGICA SEPARADA PARA CADA AÇÃO ---

    // AÇÃO 1: Desativar o cadastro
    if (isset($_POST['desativar_cadastro'])) {
        $sql_update = "UPDATE profissional SET ativo = 'N' WHERE id_profissional = ?";
        $stmt_update = $conn->prepare($sql_update);

        if (!$stmt_update) {
            error_log("Erro ao preparar query de desativação: " . $conn->error);
            $_SESSION['message_type'] = 'error';
            $_SESSION['message_content'] = 'Erro interno ao tentar desativar cadastro.';
            header("Location: editar_cadastro.php");
            exit();
        }

        $stmt_update->bind_param("i", $id_profissional);

        if ($stmt_update->execute()) {
            $_SESSION['message_type'] = 'success';
            $_SESSION['message_content'] = 'Cadastro desativado com sucesso! Caso você faça login nos próximos 30 dias, seu perfil será mantido. Caso contrário, será excluído.';
            session_destroy(); // Encerrar a sessão após desativação
            header("Location: index.php"); // Redirecionar para a página inicial
            exit();
        } else {
            error_log("Erro ao desativar cadastro: " . $stmt_update->error);
            $_SESSION['message_type'] = 'error';
            $_SESSION['message_content'] = 'Erro ao desativar cadastro. Tente novamente.';
            header("Location: editar_cadastro.php");
            exit();
        }
        $stmt_update->close();

    // AÇÃO 2: Atualizar o perfil
    } elseif (isset($_POST['atualizar_perfil'])) {
        // Coleta e sanitização de TODOS os dados do perfil (com filtros melhorados)
        $nome_profissional = filter_input(INPUT_POST, 'nome_profissional', FILTER_SANITIZE_SPECIAL_CHARS);
        $id_profissao = filter_input(INPUT_POST, 'id_profissao', FILTER_VALIDATE_INT);
        $data_nascimento = filter_input(INPUT_POST, 'data_nascimento', FILTER_SANITIZE_SPECIAL_CHARS);
        $tempo_profissao = filter_input(INPUT_POST, 'tempo_profissao', FILTER_VALIDATE_INT);
        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
        $telefone = preg_replace('/\D/', '', $_POST['telefone']);
        $rede_social = filter_input(INPUT_POST, 'rede_social', FILTER_VALIDATE_URL);
        $link_google = filter_input(INPUT_POST, 'link_google', FILTER_VALIDATE_URL);
        $site_prestador = filter_input(INPUT_POST, 'site_prestador', FILTER_VALIDATE_URL);
        $endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_SPECIAL_CHARS);
        $presta_servico_endereco = isset($_POST['servicos_endereco']) ? 'S' : 'N';

        // Preparar e executar a atualização no banco de dados
        $sql_update = "UPDATE profissional SET 
            nome_profissional = ?, id_profissao = ?, data_nascimento = ?, tempo_profissao = ?, 
            descricao = ?, telefone = ?, rede_social = ?, link_google = ?, site_prestador = ?, 
            endereco = ?, presta_servico_endereco = ?
            WHERE id_profissional = ?";
        
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sisisssssssi", 
            $nome_profissional, $id_profissao, $data_nascimento, $tempo_profissao, $descricao, 
            $telefone, $rede_social, $link_google, $site_prestador, $endereco, 
            $presta_servico_endereco, $id_profissional
        );

        if ($stmt_update->execute()) {
            $_SESSION['message_type'] = 'success';
            $_SESSION['message_content'] = 'Perfil atualizado com sucesso!';
        } else {
            $_SESSION['message_type'] = 'error';
            $_SESSION['message_content'] = 'Erro ao atualizar o perfil. Tente novamente.';
            error_log("Erro ao atualizar perfil: " . $stmt_update->error);
        }
        $stmt_update->close();
        header("Location: editar_cadastro.php");
        exit();
    }
}


// 4. Buscar dados atuais do profissional para exibir no formulário
$sql_get_data = "SELECT nome_profissional, id_profissao, data_nascimento, tempo_profissao, descricao, telefone, rede_social, link_google, site_prestador, endereco, presta_servico_endereco FROM profissional WHERE id_profissional = ?";
$stmt_get_data = $conn->prepare($sql_get_data);
$stmt_get_data->bind_param("i", $id_profissional);
$stmt_get_data->execute();
$result_data = $stmt_get_data->get_result();
$dados_profissional = $result_data->fetch_assoc();
$stmt_get_data->close();

if (!$dados_profissional) {
    // Caso raro de erro, destrói a sessão e manda para o login
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Bauru Serviços</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once __DIR__ . "/partials/header.php"; ?>
    <main>
        <div class="form-container">
            <h2>Editar cadastro</h2>
            <?php if ($message_content): ?>
                <p class="message <?php echo htmlspecialchars($message_type); ?>"><?php echo htmlspecialchars($message_content); ?></p>
            <?php endif; ?>

            <form action="editar_cadastro.php" method="POST" id="edit-profile-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="nome_profissional">
                        Nome:<span class="required">*</span>
                        <span class="tooltip">
                        <span class="info-icon">i</span>
                        <span class="tooltiptext">Informe seu nome completo ou o nome do seu negócio.</span>
                    </label>
                    <input type="text" id="nome_profissional" name="nome_profissional" value="<?php echo htmlspecialchars($dados_profissional['nome_profissional'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="id_profissao">Profissão:<span class="required">*</span></label>
                    <select id="id_profissao" name="id_profissao" required>
                        <?php
                        $sql_profissao = "SELECT id_profissao, nome_profissao FROM profissao ORDER BY nome_profissao";
                        $result_profissao = $conn->query($sql_profissao);
                        if ($result_profissao && $result_profissao->num_rows > 0) {
                            while ($row = $result_profissao->fetch_assoc()) {
                                $selected = ($row['id_profissao'] == $dados_profissional['id_profissao']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['id_profissao']) . "' $selected>" . htmlspecialchars($row['nome_profissao']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_nascimento">Data de nascimento:<span class="required">*</span></label>
                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($dados_profissional['data_nascimento'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="tempo_profissao">Tempo de profissão (anos):<span class="required">*</span></label>
                    <input type="number" id="tempo_profissao" name="tempo_profissao" value="<?php echo htmlspecialchars($dados_profissional['tempo_profissao'] ?? '0'); ?>" required min="0">
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição:<span class="required">*</span></label>
                    <textarea id="descricao" name="descricao" rows="5" required><?php echo htmlspecialchars($dados_profissional['descricao'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone:<span class="required">*</span></label>
                    <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($dados_profissional['telefone'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="endereco">Endereço:</label>
                    <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($dados_profissional['endereco'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="servicos_endereco">
                        <input type="checkbox" id="servicos_endereco" name="servicos_endereco" value="S" <?php echo ($dados_profissional['presta_servico_endereco'] ?? 'N') === 'S' ? 'checked' : ''; ?>>
                        Presta serviços no endereço informado? <span></span>
                    </label>
                </div>

                <div class="form-group">
                    <label for="rede_social">Rede social:</label>
                    <input type="url" id="rede_social" name="rede_social" value="<?php echo htmlspecialchars($dados_profissional['rede_social'] ?? ''); ?>" placeholder="Informe o link da sua rede social">
                </div>

                <div class="form-group">
                    <label for="link_google">Link do google:</label>
                    <input type="url" id="link_google" name="link_google" value="<?php echo htmlspecialchars($dados_profissional['link_google'] ?? ''); ?>" placeholder="Informe o link da página no Google">
                </div>

                <div class="form-group">
                    <label for="site_prestador">Site próprio:</label>
                    <input type="url" id="site_prestador" name="site_prestador" value="<?php echo htmlspecialchars($dados_profissional['site_prestador'] ?? ''); ?>" placeholder="Informe o link do seu site">
                </div>

                <div class="form-group">
                    <label for="senha_atual">Senha:<span class="required">*</span></label>
                    <div class="password-container">
                        <input type="password" id="senha_atual" name="senha_atual" placeholder="Digite sua senha para salvar" required>
                        <span class="toggle-password" onclick="togglePassword('senha_atual')">👁️</span>
                    </div>
                </div>
                
                <div class="form-buttons-container">
                    <button type="submit" name="atualizar_perfil">Salvar Alterações do Perfil</button>
                    <button type="submit" name="desativar_cadastro" class="desativar-btn" onclick="return confirm('Tem certeza que deseja desativar seu cadastro? Você terá 30 dias para reativar antes que seja permanentemente excluído.')">Desativar Cadastro</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='gerenciar.php'">Cancelar</button>
                </div>
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

        // Script para máscara de telefone (opcional, mas recomendado)
        document.addEventListener('DOMContentLoaded', function() {
            var telefoneInput = document.getElementById('telefone');
            if (telefoneInput) {
                const formatPhone = (value) => {
                    const x = value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
                    return !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
                };
                telefoneInput.value = formatPhone(telefoneInput.value);
                telefoneInput.addEventListener('input', (e) => {
                    e.target.value = formatPhone(e.target.value);
                });
            }
        });
    </script>
    <?php
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>