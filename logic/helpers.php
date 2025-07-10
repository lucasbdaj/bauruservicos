<?php
function renderSocialLink($link) {
    if (strpos($link, 'instagram.com') !== false) {
        return "<p><strong>Rede Social:</strong> 
                <a href='$link' target='_blank'>
                    <i class='fab fa-instagram' style='font-size: 20px; color: #E1306C;'></i> @" . basename($link) . "
                </a></p>";
    } elseif (strpos($link, 'facebook.com') !== false) {
        return "<p><strong>Rede Social:</strong> 
                <a href='$link' target='_blank'>
                    <i class='fab fa-facebook' style='font-size: 20px; color: #1877F2;'></i> @" . basename($link) . "
                </a></p>";
    }
    return "";
}
?>