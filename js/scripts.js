/**
 * Alterna a visibilidade de um campo de senha.
 * @param {string} id O ID do campo de senha.
 */
function togglePassword(id) {
    const passwordField = document.getElementById(id);
    if (passwordField) {
        const type = passwordField.type === "password" ? "text" : "password";
        passwordField.type = type;
    }
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

//Controle de dropdown para opções caso usuario esteja logado
function toggleDropdown() {
    document.getElementById("userDropdown").classList.toggle("show");
}

// Opcional: Fechar o dropdown se o usuário clicar fora dele
window.onclick = function(event) {
    if (!event.target.matches('.user-name')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const hamburgerButton = document.getElementById('hamburger-button');
    const nav = document.getElementById('main-nav');

    if (hamburgerButton && nav) {
        hamburgerButton.addEventListener('click', function() {
            nav.classList.toggle('active');
        });
    }
});