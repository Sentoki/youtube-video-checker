<?php

$template = \PetrovEgor\templates\Template::getInstance();
$params = $template->getParams();

?>
<h1>Main info</h1>
<div class="notice notice-info inline">
    <p>
        Last check: <?=$params['lastCheck']?>
    </p>
</div>
<div class="notice notice-info inline">
    <p>
        Next check: <?php
        if (is_null($params['nextScheduled'])) {
            echo "not planned";
        } else {
            echo  $params['nextScheduled']->format('Y-m-d H:i:s');
        }
        ?>
    </p>
</div>
<div class="notice notice-success">
    <p>
        Available:  <?=$params['availableCounter']?> videos
    </p>
</div>
<div class="notice notice-error">
    <p>
        Unavailable: <?=$params['unavailableCounter']?> videos
    </p>
</div>


<?php
if (\PetrovEgor\Common::isDevelopMode()) {
?>
<br>
<a class="button-secondary" href='/wp-admin/admin.php?page=youtube-checker&youtube_checker_action=search-videos-in-posts' title="Search videos in posts">Search videos in posts</a>
    <a class="button-secondary" href='/wp-admin/admin.php?page=youtube-checker&youtube_checker_action=check-by-api' title="Check videos by API">Check videos by API</a>
<?php } ?>