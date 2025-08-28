<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Novo Usuário</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/create_user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="overlay"></div> <!-- Usado para o menu mobile -->

    <!-- ESTRUTURA DA SIDEBAR CORRIGIDA (IDÊNTICA AO DASHBOARD) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Frotas Gov</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/dashboard"><i class="fas fa-tachometer-alt"></i> Painel</a></li>
                <li><a href="#"><i class="fas fa-car"></i> Veículos</a></li>
                <li><a href="#"><i class="fas fa-road"></i> Corridas</a></li>
                <li><a href="#"><i class="fas fa-gas-pump"></i> Abastecimentos</a></li>
                
                <?php if (isset($_SESSION['user_role_id']) && $_SESSION['user_role_id'] == 2): ?>
                <li class="active"><a href="<?php echo BASE_URL; ?>/sector-manager/users/create"><i class="fas fa-user-plus"></i> Cadastrar Usuário</a></li>
                <?php endif; ?>

                <li><a href="<?php echo BASE_URL; ?>/logout"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>Cadastrar Novo Usuário</h1>
            <div class="user-info">
                <span>Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>
        </header>

        <div class="content-body">
            <div class="form-container">
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                    unset($_SESSION['success_message']);
                }
                ?>
                <!-- Adicionado um ID ao formulário para facilitar a seleção no JS -->
                <form id="createUserForm" action="<?php echo BASE_URL; ?>/sector-manager/users/store" method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cpf">CPF (somente números) <span class="text-danger">*</span></label>
                            <input type="text" id="cpf" name="cpf" required>
                        </div>
                        <div class="form-group">
                            <label for="role_id">Cargo / Função <span class="text-danger">*</span></label>
                            <select id="role_id" name="role_id" required>
                                <option value="">Selecione um cargo</option>
                                <?php foreach ($roles as $role): ?>
                                    <?php if ($role['id'] > $_SESSION['user_role_id']): ?>
                                    <option value="<?php echo htmlspecialchars($role['id']); ?>">
                                        <?php echo htmlspecialchars($role['name']); ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                         <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" required>
                                <option value="active" selected>Ativo</option>
                                <option value="inactive">Inativo</option>
                            </select>
                        </div>
                    </div>

                    <hr class="form-section-divider">
                    <h4 class="form-section-title">Informações Adicionais (Opcional)</h4>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cnh_number">Nº da CNH</label>
                            <input type="text" id="cnh_number" name="cnh_number">
                        </div>
                        <div class="form-group">
                            <label for="cnh_expiry_date">Data de Validade da CNH</label>
                            <input type="date" id="cnh_expiry_date" name="cnh_expiry_date">
                        </div>
                         <div class="form-group">
                            <label for="phone">Telefone / Celular</label>
                            <input type="tel" id="phone" name="phone" placeholder="(XX) XXXXX-XXXX">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Cadastrar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <!-- SCRIPT ADICIONADO AQUI -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
