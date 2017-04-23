<?php
$template = \PetrovEgor\templates\Template::getInstance();
$params = $template->getParams();
?>
<h1>Unavailable Videos</h1>
<table>
    <thead>
    <tr>
        <th>Posts</th>
        <th>Type</th>
        <th>Videos</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($params['posts'] as $post) {
        $wpPost = get_post((int)$post['post_id']);
        ?>
    <tr>
        <td>
            <a href="<?=$wpPost->guid?>" target="_blank"><?=$wpPost->post_title?></a>
        </td>
        <td><span><?=$wpPost->post_type?></span></td>
        <?php
        $ids = \PetrovEgor\Common::getUnavailableVideoList($wpPost);
        ?>
        <td>
            <?php
            foreach ($ids as $id) {
                echo "<a href='https://www.youtube.com/watch?v=$id' target='_blank'>
                        https://www.youtube.com/watch?v=$id
                      </a><br>";
            }
            ?>
        </td>
    </tr>
    <?php } ?>
    </tbody>
</table>
