<?php
/**
 * Checkrobin
 * The Checkrobin plugin enables you to transfer order data from your WooCommerce shop directly to Checkrobin.
 * 
 * @version 0.0.13
 * @link https://www.checkrobin.com/de/integration
 * @license GPLv2
 * @author checkrobin <support@checkrobin.com>
 * 
 * Copyright (c) 2018-2022 Checkrobin GmbH
 */
?>
<div class="wrap checkrobin">

    <h1><?php esc_html_e('Checkrobin Settings Page', 'checkrobin'); ?></h1>

    <?php wp_nonce_field('checkrobin_update', 'checkrobin_settings_form_nonce'); ?>

    <h2><?php esc_html_e('Checkrobin-Bussiness Settings', 'checkrobin'); ?></h2>
    <table class="form-table settings">
        <tbody>

            <!-- If Token is set, show token field as disabled field -->
            <?php if ($token) { ?>          

                <tr>
                    <th><label for="checkrobin_api_token"><?php esc_html_e('Checkrobin-Business API-Token', 'checkrobin'); ?></label></th>
                    <td><input disabled autocomplete="off" name="checkrobin_api_token" id="checkrobin_api_token" type="text" value="<?php esc_attr_e(get_option('checkrobin_api_token')); ?>" class="regular-text" /></td>
                </tr>

                <!-- Reset-Settings Button -->
                <tr>
                    <th><label for="checkrobin_reset_settings">&nbsp;</label></th>
                    <td><button name="checkrobin_reset_settings" id="checkrobin_reset_settings" type="submit" value="1" class="button-secondary" /><?php esc_html_e('Reset settings', 'checkrobin'); ?></button>
                </tr>

            <?php } else { ?>

            <!-- If Token is NOT set, show username and password fields to get token -->

                <tr>
                    <th><label for="checkrobin_api_username"><?php esc_html_e('Checkrobin-Business Username', 'checkrobin'); ?></label></th>
                    <td><input required autocomplete="off" placeholder="Please enter your Checkrobin-Business username (E-Mail) here." name="checkrobin_api_username" id="checkrobin_api_username" type="text" value="" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="checkrobin_api_password"><?php esc_html_e('Checkrobin-Business Password', 'checkrobin'); ?></label></th>
                    <td><input required autocomplete="off" placeholder="Please enter your Checkrobin-Business password here." name="checkrobin_api_password" id="checkrobin_api_password" type="password" value="" class="regular-text" /></td>
                </tr>

            <?php } ?>

        </tbody>
    </table>

    <br />

        <h2><?php esc_html_e('Allowed order status', 'checkrobin'); ?></h2>
    <table class="form-table settings">
        <tbody>
            <!-- Status for sync field -->
            <tr>
                <th><label for="checkrobin_transfer_settings"><?php esc_html_e('The transfer of an order to Checkrobin depends on the status of the order. Please make sure to configure all status that you would like to use for sending to checkrobin here. An order is sent to Checkrobin for each status configured (selected).', 'checkrobin'); ?></label></th>
                <td>
                <select id="status" name="checkrobin_transfer_settings[]" multiple="multiple" size="10" style="height: 100%;" autocomplete="off">
                    <option <?php if ($orderStatusSelected[0] == '999') { echo ' selected'; } ?> value="999">
                        <?php esc_html_e('-- DISABLE ALL --', 'checkrobin'); ?>
                    </option>
                    <?php foreach ($orderStatusFromSystem as $key => $value) { ?>
                        <option <?php if ($orderStatusSelected[0] != '999' && in_array($key, $orderStatusSelected)) { echo ' selected'; } ?> value="<?php esc_html_e($key) ?>">
                            <?php esc_html_e($value); ?> (ID: <?php esc_html_e($key); ?>)
                        </option>
                    <?php } ?>
                </select>
                </td>
            </tr>
        </tbody>
    </table>

    <br />

    <h2><?php esc_html_e('E-Mail Settings', 'checkrobin'); ?></h2>
    <table class="form-table settings">
        <tbody>
            <!-- Admin E-Mail field -->
            <tr>
                <th><label for="checkrobin_admin_email"><?php esc_html_e('E-Mail for failure messages', 'checkrobin'); ?></label></th>
                <td><input placeholder="E-Mail address for notification e-mails on failures" autocomplete="off" name="checkrobin_admin_email" id="checkrobin_admin_email" type="email" value="<?php esc_attr_e(get_option('checkrobin_admin_email')); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="checkrobin_tracking_email"><?php esc_html_e('Add Tracking-Link to WooCommerce order E-Mails?', 'checkrobin'); ?></label></th>
                <td><input <?php esc_attr_e(get_option('checkrobin_tracking_email', 1) ? ' checked ' : ''); ?> value="1" autocomplete="off" name="checkrobin_tracking_email" id="checkrobin_tracking_email" type="checkbox" class="regular-text" /></td>
            </tr>

        </tbody>
    </table>

        <br />

    <h2><?php esc_html_e('Last automatic cron run', 'checkrobin'); ?></h2>
    <table class="form-table settings">
        <tbody>
            <!-- Last cron run field -->
            <tr>
                <th><label for="checkrobin_cron_last_run"><?php esc_html_e('Cron runs every 5 minutes. Here you can see the time of the last automatic cron run:', 'checkrobin'); ?></label></th>
                <td><input disabled autocomplete="off" name="checkrobin_cron_last_run" id="checkrobin_cron_last_run" type="text" value="<?php esc_attr_e($checkrobin_cron_last_run); ?>" class="regular-text" /></td>
            </tr>
        </tbody>
    </table>

    <style>
        .notice.wcs-nux__notice {
            display: none;
        }
    </style>

</div>
