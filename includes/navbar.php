<nav class="navbar">
    <div class="nav-container">
        <a href="/PlsworkUKK/index.php" class="nav-logo">Booking Ruangan</a>
        
        <!-- Hamburger Menu Button for Mobile -->
        <button class="nav-hamburger" id="navHamburger" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <ul class="nav-menu" id="navMenu">
            <?php if (is_logged_in()): ?>
                <?php 
                // Get user profile picture for navbar
                if (is_user()) {
                    $user_id = $_SESSION['user_id'];
                    $query_profile = "SELECT profile_picture, nama_lengkap FROM users WHERE id = ?";
                    $stmt_profile = mysqli_prepare($conn, $query_profile);
                    mysqli_stmt_bind_param($stmt_profile, "i", $user_id);
                    mysqli_stmt_execute($stmt_profile);
                    $result_profile = mysqli_stmt_get_result($stmt_profile);
                    $user_profile = mysqli_fetch_assoc($result_profile);
                }
                ?>
                <?php if (is_admin() || is_petugas()): ?>
                    <li><a href="<?php echo is_admin() ? '/PlsworkUKK/admin/index.php' : '/PlsworkUKK/petugas/index.php'; ?>">Dashboard</a></li>
                    <li><a href="/PlsworkUKK/admin/ruangan.php">Ruangan</a></li>
                    <li><a href="/PlsworkUKK/admin/booking.php">Booking</a></li>
                    <li><a href="/PlsworkUKK/admin/jadwal.php">Jadwal</a></li>
                    <li><a href="/PlsworkUKK/admin/laporan.php">Laporan</a></li>
                    <li><a href="/PlsworkUKK/admin/history.php">History</a></li>
                    <?php if (is_admin()): ?>
                    <li>
                        <a href="/PlsworkUKK/admin/users.php" class="nav-with-badge">Users
                        <?php
                        // Count pending reset password requests
                        $count_query = "SELECT COUNT(*) as total FROM password_reset_requests WHERE status = 'pending'";
                        $count_result = mysqli_query($conn, $count_query);
                        $pending_count = mysqli_fetch_assoc($count_result)['total'];
                        if ($pending_count > 0):
                        ?>
                            <span class="nav-notification-badge"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="/PlsworkUKK/index.php">Home</a></li>
                    <li><a href="/PlsworkUKK/user/booking.php">Booking</a></li>
                    <li><a href="/PlsworkUKK/user/list_booking.php">List Booking</a></li>
                    <li><a href="/PlsworkUKK/user/riwayat.php">Riwayat</a></li>
                    <li><a href="/PlsworkUKK/jadwal_view.php">Jadwal</a></li>
                <?php endif; ?>
                <li><a href="/PlsworkUKK/logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a></li>
                <?php if (is_user()): ?>
                    <li class="nav-profile-menu">
                        <a href="/PlsworkUKK/user/profile.php" class="nav-profile-link">
                            <div class="nav-profile-avatar">
                                <?php if (isset($user_profile['profile_picture']) && $user_profile['profile_picture']): ?>
                                    <img src="/PlsworkUKK/uploads/profile/<?php echo htmlspecialchars($user_profile['profile_picture']); ?>" alt="Profile">
                                <?php else: ?>
                                    <span class="nav-avatar-text"><?php echo strtoupper(substr($user_profile['nama_lengkap'], 0, 2)); ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>
            <?php else: ?>
                <li><a href="/PlsworkUKK/index.php">Home</a></li>
                <li><a href="/PlsworkUKK/jadwal_view.php">Jadwal Ruangan</a></li>
                <li><a href="/PlsworkUKK/login.php">Login</a></li>
                <li><a href="/PlsworkUKK/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <script>
    // Mobile Navigation Toggle
    (function() {
        const hamburger = document.getElementById('navHamburger');
        const navMenu = document.getElementById('navMenu');
        
        if (hamburger && navMenu) {
            hamburger.addEventListener('click', function() {
                this.classList.toggle('active');
                navMenu.classList.toggle('active');
                document.body.classList.toggle('nav-open');
            });
            
            // Close menu when clicking on a link
            const navLinks = navMenu.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    hamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                    document.body.classList.remove('nav-open');
                });
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!hamburger.contains(event.target) && !navMenu.contains(event.target)) {
                    hamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                    document.body.classList.remove('nav-open');
                }
            });
        }
    })();
    </script>
</nav>
