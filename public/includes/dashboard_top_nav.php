<?php
/**
 * Common Dashboard Top Navigation
 * Use this in all dashboard files
 * 
 * @param string $pageTitle - Title to display in the navbar
 * @param string $userName - Name of the logged-in user
 * @param string $userRole - Role abbreviation (A, S, I)
 */
$pageTitle = $pageTitle ?? 'Dashboard';
$userName = $userName ?? 'User';
$userRole = $userRole ?? 'U';
?>

<!-- Top Navigation Bar -->
<div class="top-nav">
    <h5><i class="fas fa-tachometer-alt"></i> <?php echo htmlspecialchars($pageTitle); ?></h5>
    <div class="user-info">
        <span><i class="fas fa-bell"></i></span>
        <div class="user-avatar">
            <?php echo strtoupper(substr($userRole, 0, 1)); ?>
        </div>
        <span><?php echo htmlspecialchars($userName); ?></span>
    </div>
</div>
