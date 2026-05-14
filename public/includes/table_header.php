<?php
/**
 * Common Table Page Header
 * Use this in all table list pages
 * 
 * @param string $pageTitle - Title of the page
 */
$pageTitle = $pageTitle ?? 'List';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
</div>

<!-- Search & Filter Bar -->
<div class="search-filter-bar">
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search here...">
    </div>
</div>
