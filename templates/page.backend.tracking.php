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
<?php
?>
<div class="wrap checkrobin tracking ajaxresponse">

    	<?php settings_errors(); ?>

    <form method="post" action="<?php esc_url($adminURL) ?>" class="refresh-form">

            <?php wp_nonce_field( 'checkrobin_tracking', 'checkrobin_tracking_form_nonce' ); ?>

        <div class="container-fluid intro">
            <div class="row">
                <div>
                    <h1><?php esc_html_e('Orders transferred to Checkrobin', 'checkrobin'); ?></h1>
                    <p>
                    <?php echo wp_kses(__('<p>Below you can see all orders that have already been transferred from your shop to checkrobin-business. </p><strong>Please note:</strong><p>Orders must have the status "paid / processed" in your WooCommerce Shop to be transferred. <br />The transfehr takes place automatically when the status changes. In addition, for the transfer, only orders that have already been received in your shop prior to the activation of the plugin will be considered!</p>', 'checkrobin'), 
                    [
                        'p' => [], 
                        'strong' => []
                    ]); ?>
                    </p>
                </div>

                <div class="buttons">
                    <button name="refresh" type="submit" value="1" class="btn btn-primary">
                        <i class="fa fa-refresh"></i> <?php esc_html_e('Update list / send orders', 'checkrobin'); ?>
                    </button>
                    <button name="cancel" type="button" class="btn btn-secondary">
                        <i class="fa fa-cancel"></i> <?php esc_html_e('Cancel selected orders', 'checkrobin'); ?>
                    </button>
                    <button name="enable" type="button" class="btn btn-secondary">
                        <i class="fa fa-refresh"></i> <?php esc_html_e('Re-enable selected orders', 'checkrobin'); ?>
                    </button>
                    <button name="archive" type="button" class="btn btn-secondary">
                        <i class="fa fa-trash"></i> <?php esc_html_e('Archive selected orders', 'checkrobin'); ?>
                    </button>
                </div>

            </div>

                        <hr>
        </div>

        <table id="dataTable" class="table table-striped table-bordered" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th><input type="checkbox" class="selectAll" name="selectAll" value="all"></th>
                    <th><?php esc_html_e('ID', 'checkrobin'); ?></th>
                    <th><?php esc_html_e('PK', 'checkrobin'); ?></th>
                    <th><?php esc_html_e('Order-ID', 'checkrobin'); ?></th>
                    <th><?php esc_html_e('Reference-ID', 'checkrobin'); ?></th>
                    <th><?php esc_html_e('Tracking-Code', 'checkrobin'); ?></th>
                    <th><?php esc_html_e('Tracking-URL', 'checkrobin'); ?></th>
                    <th><?php esc_html_e('Order-Status', 'checkrobin'); ?></th>
                    <?php  ?>
                    <th><?php esc_html_e('Date created', 'checkrobin'); ?></th>
                    <th><?php esc_html_e('Date changed', 'checkrobin'); ?></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($checkrobinData as $key => $value) { ?>
                <tr>
                    <td></td>
                    <td><?php esc_html_e($value->id) ?></td>
                    <td><?php esc_html_e($value->pk) ?></td>
                    <td><?php esc_html_e($value->orderId) ?></td>
                    <td><?php esc_html_e($value->orderReference) ?></td>
                    <td><?php esc_html_e($value->trackingCode) ?></td>
                    <td><?php print '<a href="'. esc_url($value->trackingUrl) .'" title="' . esc_attr('Open Tracking-Code Info', 'checkrobin') . '" target="_blank">' . esc_url($value->trackingUrl) . '</a>'; ?></td>
                    <td><?php esc_html_e($value->orderStatus) ?></td>
                    <?php  ?>
                    <td><?php esc_html_e($value->dCreated) ?></td>
                    <td><?php esc_html_e($value->dChanged) ?></td>
                </tr>
                <?php } ?>
            </tbody>

        </table>

            </form>

</div>
