<?php
$template = \PetrovEgor\templates\Template::getInstance();
$params = $template->getParams();

if ($params['is_wrong_api_key']) {
?>
    <div class="notice notice-warning">
        <p>
            Google API key is incorrect. Check key in developer console: <a href="https://console.developers.google.com/apis/credentials" target="_blank">https://console.developers.google.com/apis/credentials</a>
        </p>
    </div>
<?php
}
?>
<h1>Plugin settings</h1>
<form action="/wp-admin/admin.php?page=youtube-checker-settings" method="post">
    <?php
    $value = isset($params['apiKey']) ? $params['apiKey'] : '';
    $hourChecked = '';
    $dayChecked = '';
    $weekChecked = '';
    if (isset($params['checkFreq'])) {
        switch ($params['checkFreq']) {
            case 'every_hour':
                $hourChecked = "checked";
                break;
            case 'every_day':
                $dayChecked = "checked";
                break;
            case 'every_week':
                $weekChecked = "checked";
                break;
        }
    }

    ?>
    Google API key: <input type="text" placeholder="api key from developer console" class="regular-text" name="api_key" value="<?=$value?>"><br>
    <p>You can get API key here: <a href="https://console.developers.google.com/apis/credentials" target="_blank">https://console.developers.google.com/apis/credentials</a></p>

    <h2>Checking frequency</h2>
    <fieldset>
        <legend class="screen-reader-text"><span>input type="radio"</span></legend>
        <label title='g:i a'>
            <input type="radio" name="sync_frequency" value="every_hour" <?=$hourChecked?> />
            <span>Every hour</span>
        </label><br>
        <label title='g:i a'>
            <input type="radio" name="sync_frequency" value="every_day" <?=$dayChecked?>/>
            <span>Every day</span>
        </label><br>
        <label title='g:i a'>
            <input type="radio" name="sync_frequency" value="every_week" <?=$weekChecked?>/>
            <span>Every week</span>
        </label>
    </fieldset>
    <br>
    <input class="button-primary" type="submit" name="Save settings" value="Save settings">
</form>