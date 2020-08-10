<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.auderset.dev
 * @since      1.0.0
 *
 * @package    Crowd
 * @subpackage Crowd/admin/partials
 */
?>

<div class="wrap">
    <h2>Crowd Login</h2>
    <p></p>
    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('crowd_login_option_group');
        do_settings_sections('crowd-login-admin');
        submit_button();
        ?>
    </form>
</div>