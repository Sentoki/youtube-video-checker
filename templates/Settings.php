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
    $twiceDayChecked = '';
    $onceDayChecked = '';
    if (isset($params['checkFreq'])) {
        switch ($params['checkFreq']) {
            case 'hourly':
                $hourChecked = "checked";
                break;
            case 'twicedaily':
                $twiceDayChecked = "checked";
                break;
            case 'daily':
                $onceDayChecked = "checked";
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
            <input type="radio" name="sync_frequency" value="hourly" <?=$hourChecked?> />
            <span>Every hour</span>
        </label><br>
        <label title='g:i a'>
            <input type="radio" name="sync_frequency" value="twicedaily" <?=$twiceDayChecked?>/>
            <span>Twice a day</span>
        </label><br>
        <label title='g:i a'>
            <input type="radio" name="sync_frequency" value="daily" <?=$onceDayChecked?>/>
            <span>Once a day</span>
        </label>
    </fieldset>
    <br>
    <input class="button-primary" type="submit" name="Save settings" value="Save settings">
</form>