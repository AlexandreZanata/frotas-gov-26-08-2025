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
                <li class="active"><a href="<?php echo BASE_URL; ?>/sector-manager/users/create"><i class="fas fa-users-cog"></i> Gerenciar Usuários</a></li>
            <?php endif; ?>

            <li><a href="<?php echo BASE_URL; ?>/logout"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
        </ul>
    </nav>
</aside>