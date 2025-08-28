<?php

function renderSocialLink($link) {
    $iconClass = '';
    $platformName = '';

    if (strpos($link, 'instagram.com') !== false) {
        $iconClass = 'fab fa-instagram social-icon';
        $platformName = basename(parse_url($link, PHP_URL_PATH)); // Pega o nome de usuário de forma mais segura
    } elseif (strpos($link, 'facebook.com') !== false) {
        $iconClass = 'fab fa-facebook social-icon';
        $platformName = basename(parse_url($link, PHP_URL_PATH));
    } else {
        return ""; // Retorna vazio se não for um link conhecido
    }

    // Gera o HTML sem estilos inline
    return "<p><strong>Rede Social:</strong> 
            <a href='" . htmlspecialchars($link) . "' target='_blank' rel='noopener noreferrer'>
                <i class='" . $iconClass . "'></i> @" . htmlspecialchars($platformName) . "
            </a></p>";
}

/**
 * Define uma mensagem de sessão e redireciona o usuário.
 * Esta função deve estar neste arquivo.
 *
 * @param string $type 'success' ou 'error'.
 * @param string $message A mensagem a ser exibida.
 * @param string $location A página para a qual redirecionar.
 */
function redirectWithMessage($type, $message, $location) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['message_type'] = $type;
    $_SESSION['message_content'] = $message;
    header("Location: " . $location);
    exit();
}
    
?>