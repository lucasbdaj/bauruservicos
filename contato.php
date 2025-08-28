<?php
require_once __DIR__ . '/bootstrap.php';

// Captura e limpa mensagens de feedback
$status = $_SESSION['message_type'] ?? '';
$message = $_SESSION['message_content'] ?? '';
unset($_SESSION['message_type'], $_SESSION['message_content']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Entre em contato com o Bauru Serviços para dúvidas, sugestões ou suporte. Fale conosco por formulário ou e-mail.">
    <meta name="author" content="Lucas Borges">
    <meta name="keywords" content="bauru, serviços, contato, fale conosco, suporte, sugestões">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Contato - Bauru Serviços</title>
</head>
<body>

    <?php require_once __DIR__ . "/partials/header.php"; ?>

    <main class="contato-page">
        <div class="form-container">
            <form action="processa_contato.php" method="POST">
            <h2>Entre em Contato</h2>
            <?php if ($message): ?>
                <p class="message <?php echo htmlspecialchars($status); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            <?php endif; ?>
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
            </div>
            <div class="form-group">
                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone" placeholder="(14) XXXXX-XXXX" required pattern="\(\d{2}\) \d{4,5}-\d{4}" title="Formato: (XX) XXXXX-XXXX ou (XX) XXXX-XXXX">
                <small>Formato: (14) 99999-9999 ou (14) 9999-9999</small>
            </div>
            <div class="form-group">
                <label for="mensagem">Mensagem:</label>
                <textarea id="mensagem" name="mensagem" rows="5" placeholder="Digite a mensagem de contato. Aqui você pode fazer solicitações de inclusão de profissões que ainda não temos cadastradas, solicitar ajustes no seu cadastro ou até mesmo sugestões gerais para melhorias do nosso site." required></textarea>
            </div>
            <button type="submit">Enviar Mensagem</button>
            <p></p>
            <p>Você também pode nos enviar um e-mail para <a href="mailto:contato@servicosbauru.com.br">contato@servicosbauru.com.br</a></p>

            <p>
                Ou nos chame no WhatsApp: 
                <a href="https://wa.me/5514991079668" target="_blank" class="whatsapp-button" title="Conversar no WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                    (14) 99107-9668
                </a>
            </p>
</a>
        </form>
        </div>
    </main>

    <?php require_once __DIR__ . "/partials/footer.php"; ?>

    <script>
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