<?php
$template = \PetrovEgor\templates\Template::getInstance();
$params = $template->getParams();
?>
<h1>Unavailable Videos</h1>
<table class="widefat">
    <thead>
    <tr>
        <th class="row-title">Posts</th>
        <th>Type</th>
        <th>Videos</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $counter = 0;
    foreach ($params['posts'] as $post) {
        $counter++;
        $class = $counter % 2 == 0 ? ' class="alternate"' : '';
        $wpPost = get_post((int)$post['post_id']);
        ?>
    <tr<?=$class?>>
        <td class="row-title"><label for="tablecell">
                <div alt="f135" class="dashicons dashicons-align-left"></div> <a href="<?=$wpPost->guid?>" target="_blank"><?=$wpPost->post_title?></a>
            </label>

        </td>
        <td><span><?=$wpPost->post_type?></span></td>
        <?php
        $ids = \PetrovEgor\Common::getUnavailableVideoList($wpPost);
        ?>
        <td>
            <?php
            foreach ($ids as $id) {
                echo "<div alt=\"f236\" class=\"dashicons dashicons-video-alt3\"></div> <a href='https://www.youtube.com/watch?v=$id' target='_blank'>https://www.youtube.com/watch?v=$id</a><br>";
            }
            ?>
        </td>
    </tr>
    <?php } ?>
    </tbody>
</table>
<br>
<span style="font-size: larger;"><?=$params['paginationLinks']?></span>
<?php

