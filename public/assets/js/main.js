document.addEventListener('DOMContentLoaded', () => {

    // --- FORMATAÇÃO DE NOME COMPLETO (CAPITALIZAÇÃO) ---
    const nameField = document.querySelector('input[name="name"]');
    if (nameField) {
        nameField.addEventListener('input', () => {
            const words = nameField.value.toLowerCase().split(' ');
            const formattedWords = words.map(word => {
                if (word.length > 0) {
                    return word.charAt(0).toUpperCase() + word.slice(1);
                }
                return '';
            });
            nameField.value = formattedWords.join(' ');
        });
    }

    // --- VALIDAÇÃO DE NOME E SOBRENOME (MÍNIMO DE 2 PALAVRAS) ---
    const registerForm = document.querySelector('form[action*="register/store"]');
    if (registerForm) {
        registerForm.addEventListener('submit', (event) => {
            if (nameField) {
                // Remove espaços extras e divide o nome em palavras
                const words = nameField.value.trim().split(/\s+/);
                if (words.length < 2) {
                    // Impede o envio do formulário
                    event.preventDefault();
                    // Exibe uma mensagem de alerta para o usuário
                    alert('Por favor, insira seu nome e sobrenome.');
                    // Foca no campo para facilitar a correção
                    nameField.focus();
                }
            }
        });
    }


    // --- CÓDIGO EXISTENTE (MÁSCARA DE CPF E LÓGICA DO OLHO) ---
    
    // Função para aplicar máscara de CPF e limitar o tamanho
    const applyCpfMask = (inputField) => {
        let value = inputField.value.replace(/\D/g, ''); // Remove tudo que não for dígito
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        inputField.value = value;
    };

    // 1. LÓGICA INTELIGENTE PARA O CAMPO DE LOGIN (Email ou CPF)
    const loginField = document.querySelector('input[name="login"]');
    if (loginField) {
        loginField.addEventListener('input', () => {
            const value = loginField.value;
            if (value.includes('@') || /[a-zA-Z]/.test(value)) {
                // É um e-mail, não faz nada
            } else {
                applyCpfMask(loginField);
            }
        });
    }

    // 2. MÁSCARA DEDICADA PARA O CAMPO DE CADASTRO DE CPF
    const cpfRegisterField = document.querySelector('input[name="cpf"]');
    if (cpfRegisterField) {
        cpfRegisterField.addEventListener('input', () => {
            applyCpfMask(cpfRegisterField);
        });
    }
    
    // 3. VISIBILIDADE DA SENHA ("OLHO")
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const passwordField = toggle.closest('.password-wrapper').querySelector('input');
            const use = toggle.querySelector('use');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                use.setAttribute('xlink:href', '#icon-eye-off');
            } else {
                passwordField.type = 'password';
                use.setAttribute('xlink:href', '#icon-eye');
            }
        });
    });

});