<?php

use DonerenMetMollie\MollieApi;

class Dmm_Admin {

    private $wpdb;

    /**
     * Dmm_Admin constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        add_action('admin_menu', array($this, 'dmm_admin_menu'));
        add_action('admin_init', array($this, 'dmm_register_settings'));
        add_action('admin_post_dmm_export', array($this, 'dmm_export_donations'));
        add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);

        if (!get_option('permalink_structure'))
            add_action('admin_notices', array($this, 'dmm_admin_notice__warning'));
    }

    /**
     * Admin notices
     *
     * @since 2.4.1
     */
    function dmm_admin_notice__warning() {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e('In order for the plugin "Donate with Mollie" to function properly, it is necessary to enable permalinks in your <a href="options-permalink.php">Wordpress settings</a>.', 'doneren-met-mollie'); ?></p>
        </div>
        <?php
    }

    /**
     * Plugin row meta
     *
     * @param $links
     * @param $file
     * @return array
     */
    function plugin_row_meta($links, $file)
    {
        if (DMM_PLUGIN_BASE == $file)
        {
            $row_meta = array(
                'support'    => '<a href="https://support.wobbie.nl" target="_blank">' . esc_html__('Support', 'mollie-forms') . '</a>',
                'feature-requests'    => '<a href="https://features.wobbie.nl" target="_blank">' . esc_html__('Feature requests', 'mollie-forms') . '</a>',
                'donate'    => '<a href="https://wobbie.nl/doneren" target="_blank">' . esc_html__('Donate', 'mollie-forms') . '</a>'
            );

            return array_merge($links, $row_meta);
        }
        return (array) $links;
    }

    /**
     * Admin menu
     *
     * @since 1.0.0
     */
    public function dmm_admin_menu() {
        add_menu_page(
            __('Donate with Mollie', 'doneren-met-mollie'),
            __('Donations', 'doneren-met-mollie'),
            get_option('dmm_rights_donations', DMM_PLUGIN_ROLE),
            DMM_PAGE_DONATIONS,
            array(
                $this,
                'dmm_page_donations'
            ),
            'dashicons-money'
        );

        if (get_option('dmm_recurring'))
        {
            add_submenu_page(
                DMM_PAGE_DONATIONS,
                __('Subscriptions', 'doneren-met-mollie') . ' | ' . __('Donate with Mollie', 'doneren-met-mollie'),
                __('Subscriptions', 'doneren-met-mollie'),
                get_option('dmm_rights_subscriptions', DMM_PLUGIN_ROLE),
                DMM_PAGE_SUBSCRIPTIONS,
                array(
                    $this,
                    'dmm_page_subscriptions'
                )
            );
            add_submenu_page(
                DMM_PAGE_DONATIONS,
                __('Donors', 'doneren-met-mollie') . ' | ' . __('Donate with Mollie', 'doneren-met-mollie'),
                __('Donors', 'doneren-met-mollie'),
                get_option('dmm_rights_subscriptions', DMM_PLUGIN_ROLE),
                DMM_PAGE_DONORS,
                array(
                    $this,
                    'dmm_page_donors'
                )
            );
        }

        add_submenu_page(
            DMM_PAGE_DONATIONS,
            __('Settings', 'doneren-met-mollie') . ' | ' . __('Donate with Mollie', 'doneren-met-mollie'),
            __('Settings', 'doneren-met-mollie'),
            DMM_PLUGIN_ROLE,
            DMM_PAGE_SETTINGS,
            array(
                $this,
                'dmm_page_settings'
            )
        );

        // Hidden
        add_submenu_page(
            '',
            __('Donation', 'doneren-met-mollie'),
            __('Donation', 'doneren-met-mollie'),
            get_option('dmm_rights_donations', DMM_PLUGIN_ROLE),
            DMM_PAGE_DONATION,
            array(
                $this,
                'dmm_page_donation'
            )
        );
    }

    /**
     * Register settings
     *
     * @since 1.0.0
     */
    public function dmm_register_settings() {
        register_setting('dmm-settings-mollie', 'dmm_mollie_apikey');

        register_setting('dmm-settings-recurring', 'dmm_recurring');
        register_setting('dmm-settings-recurring', 'dmm_recurring_interval');
        register_setting('dmm-settings-recurring', 'dmm_default_interval');
        register_setting('dmm-settings-recurring', 'dmm_name_foundation');

        register_setting('dmm-settings-general', 'dmm_amount');
        register_setting('dmm-settings-general', 'dmm_currency');
        register_setting('dmm-settings-general', 'dmm_currency_switch');
        register_setting('dmm-settings-general', 'dmm_free_input');
        register_setting('dmm-settings-general', 'dmm_default_amount');
        register_setting('dmm-settings-general', 'dmm_minimum_amount');
        register_setting('dmm-settings-general', 'dmm_payment_description');
        register_setting('dmm-settings-general', 'dmm_methods_display');
        register_setting('dmm-settings-general', 'dmm_redirect_success');
        register_setting('dmm-settings-general', 'dmm_redirect_failure');
        register_setting('dmm-settings-general', 'dmm_projects');
        register_setting('dmm-settings-general', 'dmm_rights_donations');
        register_setting('dmm-settings-general', 'dmm_rights_subscriptions');
        register_setting('dmm-settings-general', 'dmm_metadata');

        register_setting('dmm-settings-form', 'dmm_form_fields');
        register_setting('dmm-settings-form', 'dmm_success_cls');
        register_setting('dmm-settings-form', 'dmm_failure_cls');
        register_setting('dmm-settings-form', 'dmm_form_cls');
        register_setting('dmm-settings-form', 'dmm_fields_cls');
        register_setting('dmm-settings-form', 'dmm_button_cls');
        register_setting('dmm-settings-form', 'dmm_gdpr_link');
        register_setting('dmm-settings-form', 'dmm_recaptcha_v3_site_key');
        register_setting('dmm-settings-form', 'dmm_recaptcha_v3_secret_key');
    }

    /**
     * Donations table
     *
     * @return string
     * @since 1.0.0
     */
    public function dmm_page_donations()
    {
        if (!get_option('dmm_mollie_apikey')) {
            echo '<div class="error notice"><p>' . esc_html__('No API-key set', 'doneren-met-mollie') . '</p></div>';
            return;
        }

        $mollie = new MollieApi(get_option('dmm_mollie_apikey'));

        if (isset($_GET['action']) && $_GET['action'] == 'refund' && isset($_GET['payment']) && check_admin_referer('refund-donation_' . $_GET['payment']))
        {
            try {
                $payment = $mollie->get('payments/' . sanitize_text_field($_GET['payment']));
                $mollie->post('payments/' . $payment->id . '/refunds', array(
                    'amount' => array(
                        'currency'  => $payment->amount->currency,
                        'value'     => $payment->amount->value
                    )
                ));

                wp_redirect('?page=' . sanitize_text_field($_REQUEST['page']) . '&msg=refund-ok');
            } catch (Exception $e) {
                wp_redirect('?page=' . sanitize_text_field($_REQUEST['page']) . '&msg=refund-nok');
            }
        }

        if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['payment']) && check_admin_referer('delete-donation_' . $_GET['payment']))
        {
            $update = $this->wpdb->query($this->wpdb->prepare("DELETE FROM " . DMM_TABLE_DONATIONS . " WHERE payment_id = %s",
                $_GET['payment']
            ));

            if ($update)
                wp_redirect('?page=' . sanitize_text_field($_REQUEST['page']) . '&msg=delete-ok');
            else
                wp_redirect('?page=' . sanitize_text_field($_REQUEST['page']) . '&msg=delete-nok');
        }

        if (isset($_GET['msg']))
        {
            switch ($_GET['msg'])
            {
                case 'refund-ok':
                    $dmm_msg = '<div class="updated notice"><p>' . esc_html__('The donation is successful refunded to the donator', 'doneren-met-mollie') . '</p></div>';
                    break;
                case 'refund-nok':
                    $dmm_msg = '<div class="error notice"><p>' . esc_html__('The donation can not be refunded', 'doneren-met-mollie') . '</p></div>';
                    break;
                case 'delete-ok':
                    $dmm_msg = '<div class="updated notice"><p>' . esc_html__('The donation is successful deleted', 'doneren-met-mollie') . '</p></div>';
                    break;
                case 'delete-nok':
                    $dmm_msg = '<div class="error notice"><p>' . esc_html__('The donation can not be deleted', 'doneren-met-mollie') . '</p></div>';
                    break;
                case 'truncate-ok':
                    $dmm_msg = '<div class="updated notice"><p>' . esc_html__('The donations have been successfully removed from the database', 'doneren-met-mollie') . '</p></div>';
                    break;
            }
        }

        $dmmTable = new Dmm_List_Table();
        $dmmTable->prepare_items();
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Donations', 'doneren-met-mollie') ?></h2>

            <?php echo isset($dmm_msg) ? $dmm_msg : '';?>

            <form action="admin.php" style="float: right;">
                <input type="hidden" name="page" value="<?php echo DMM_PAGE_DONATIONS;?>">

                <?php if (current_user_can('export')): ?>
                    <a href="<?php echo admin_url('admin-post.php?action=dmm_export' . (isset($_GET['subscription']) ? '&subscription=' . esc_url($_GET['subscription']) : '') . (isset($_GET['search']) ? '&search=' . esc_url($_GET['search']) : ''));?>"><?php esc_html_e('Export', 'doneren-met-mollie') ?></a>
                <?php endif ?>

                <input type="text" name="search" value="<?php echo esc_attr(isset($_GET['search']) ? $_GET['search'] : '');?>" placeholder="<?php esc_html_e('Search') ?>">
                <input type="submit" class="button action" value="<?php esc_html_e('Search') ?>">
            </form>

            <form method="post">
                <div class="alignleft actions">
                    <select name="action">
                        <option value="" selected='selected'>-------</option>
                        <option value="delete"><?php esc_html_e('Delete selected donations', 'doneren-met-mollie') ?></option>
                    </select>
                    <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
                    <input type="submit" id="doaction" class="button action" value="Submit"  />
                </div>

                <?php $dmmTable->display();?>
            </form>
        </div>
    <?php
    }

    public function dmm_export_donations()
    {
        if (!current_user_can('export')) {
            exit('No permissions');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=donations.csv');
        $output = fopen('php://output', 'w');

        fputcsv($output, array(
            __('Date/time', 'doneren-met-mollie'),
            __('Name', 'doneren-met-mollie'),
            __('Company name', 'doneren-met-mollie'),
            __('Email address', 'doneren-met-mollie'),
            __('Phone number', 'doneren-met-mollie'),
            __('Address', 'doneren-met-mollie'),
            __('Zipcode', 'doneren-met-mollie'),
            __('City', 'doneren-met-mollie'),
            __('Country', 'doneren-met-mollie'),
            __('Project', 'doneren-met-mollie'),
            __('Message', 'doneren-met-mollie'),
            __('Currency', 'doneren-met-mollie'),
            __('Amount', 'doneren-met-mollie'),
            __('Status', 'doneren-met-mollie'),
            __('Payment method', 'doneren-met-mollie'),
            __('Recurring payment', 'doneren-met-mollie'),
            __('Donation ID', 'doneren-met-mollie'),
            __('Payment ID', 'doneren-met-mollie'),
        ));

        $where = '';
        if (isset($_GET['subscription'])) {
            $subscription = sanitize_title_for_query($_GET['subscription']);
            $where .= ' WHERE subscription_id="' . esc_sql($subscription) . '"';
        }

        if (isset($_GET['search'])) {
            $search = sanitize_title_for_query($_GET['search']);
            $where .= ($where ? ' AND' : ' WHERE') . ' (dm_name LIKE "%' . esc_sql($search) . '%" OR dm_email LIKE "%' . esc_sql($search) . '%" OR dm_company LIKE "%' . esc_sql($search) . '%" OR donation_id LIKE "%' . esc_sql($search) . '%" OR payment_id LIKE "%' . esc_sql($search) . '%")';
        }

        $donations = $this->wpdb->get_results("SELECT * FROM " . DMM_TABLE_DONATIONS . $where . " ORDER BY time DESC");
        foreach ($donations as $donation) {
            fputcsv($output, array(
                $donation->time,
                $donation->dm_name,
                $donation->dm_company,
                $donation->dm_email,
                $donation->dm_phone,
                $donation->dm_address,
                $donation->dm_zipcode,
                $donation->dm_city,
                $donation->dm_country,
                $donation->dm_project,
                trim(preg_replace('/\s+/', ' ', $donation->dm_message)),
                $donation->dm_currency,
                $donation->dm_amount,
                $donation->dm_status,
                $donation->payment_method,
                $donation->customer_id ? __('Yes', 'doneren-met-mollie') : __('No', 'doneren-met-mollie'),
                $donation->donation_id,
                $donation->payment_id,
            ));
        }
    }

    public function dmm_page_donation()
    {
        $donation = $this->wpdb->get_row("SELECT * FROM " . DMM_TABLE_DONATIONS . " WHERE id = '" . esc_sql($_REQUEST['id']) . "'");
        $donor    = $this->wpdb->get_row("SELECT * FROM " . DMM_TABLE_DONORS . " WHERE customer_id = '" . esc_sql($donation->customer_id) . "'");

	    $subscriptions = [];
        if ($donor) {
	        $subscriptions = $this->wpdb->get_results("SELECT * FROM " . DMM_TABLE_SUBSCRIPTIONS . " WHERE customer_id='" . esc_sql($donor->id) . "'", ARRAY_A);
        }
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Donation', 'doneren-met-mollie') ?></h2>

            <table class="widefat fixed striped">
                <thead>
                <tr valign="top">
                    <th id="empty" class="manage-column column-empty" style="width:5px;">&nbsp;</th>
                    <th id="a" class="manage-column column-a" style="width: 200px;">&nbsp;</th>
                    <th id="b" class="manage-column column-b">&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Name', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->dm_name);?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Email address', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->dm_email);?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Company name', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->dm_company);?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Phone number', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->dm_phone);?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Address', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->dm_address);?><br><?php echo esc_html($donation->dm_zipcode . ' ' . $donation->dm_city);?><br><?php echo esc_html($donation->dm_country);?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Project', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->dm_project);?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Message', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo nl2br(esc_html($donation->dm_message));?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Amount', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo dmm_get_currency_symbol($donation->dm_currency) . ' ' . number_format($donation->dm_amount, dmm_get_currencies($donation->dm_currency), ',', '');?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Settlement amount', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo dmm_get_currency_symbol($donation->dm_settlement_currency) . ' ' . number_format($donation->dm_settlement_amount, dmm_get_currencies($donation->dm_settlement_currency ?: 'EUR'), ',', '');?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Payment method', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->payment_method);?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Payment status', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->dm_status);?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Donation ID', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->donation_id);?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Payment ID', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->payment_id);?></td>
                    </tr>
                    <tr>
                        <th class="column-empty"></th>
                        <th class="column-a" scope="row"><strong><?php esc_html_e('Customer ID', 'doneren-met-mollie');?></strong></th>
                        <td class="column-b"><?php echo esc_html($donation->customer_id);?></td>
                    </tr>
                </tbody>
            </table>
            <?php if (count($subscriptions) > 0) { $subs = new Dmm_Subscriptions_Table(); ?>
                <br>
                <h2><?php esc_html_e('Subscriptions', 'doneren-met-mollie') ?></h2>

                <?php foreach($subscriptions as $subscription) {
		            $url_cancel = wp_nonce_url('?page=' . DMM_PAGE_SUBSCRIPTIONS . '&action=cancel&subscription=' . $subscription['subscription_id'] . '&customer=' . $subscription['customer_id'], 'cancel-subscription_' . $subscription['subscription_id']); ?>
                    <table class="widefat fixed striped">
                        <thead>
                        <tr valign="top">
                            <th id="empty" class="manage-column column-empty" style="width:5px;">&nbsp;</th>
                            <th id="a" class="manage-column column-a" style="width: 200px;"></th>
                            <th id="b" class="manage-column column-b" style="text-align: right">
					            <?php echo sprintf('<a href="%s" style="color:#a00;" onclick="return confirm(\'' . __('Are you sure?', 'doneren-met-mollie') . '\')">' . esc_html__('Cancel', 'doneren-met-mollie') . '</a>', $url_cancel);?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th class="column-empty"></th>
                            <th class="column-a" scope="row"><strong><?php esc_html_e('Payment description', 'doneren-met-mollie');?></strong></th>
                            <td class="column-b"><?php echo esc_html($subscription['sub_description']);?></td>
                        </tr>
                        <tr>
                            <th class="column-empty"></th>
                            <th class="column-a" scope="row"><strong><?php esc_html_e('Amount', 'doneren-met-mollie');?></strong></th>
                            <td class="column-b"><?php echo dmm_get_currency_symbol($subscription['sub_currency']) . ' ' . number_format($subscription['sub_amount'], dmm_get_currencies($subscription['sub_currency']), ',', '');?></td>
                        </tr>
                        <tr>
                            <th class="column-empty"></th>
                            <th class="column-a" scope="row"><strong><?php esc_html_e('Interval', 'doneren-met-mollie');?></strong></th>
                            <td class="column-b"><?php echo esc_html($subs->getInterval($subscription['sub_interval']));?></td>
                        </tr>
                        <tr>
                            <th class="column-empty"></th>
                            <th class="column-a" scope="row"><strong><?php esc_html_e('Times', 'doneren-met-mollie');?></strong></th>
                            <td class="column-b"><?php echo esc_html($subscription['sub_times'] ?: esc_html_e('Infinite', 'doneren-met-mollie'));?></td>
                        </tr>
                        <tr>
                            <th class="column-empty"></th>
                            <th class="column-a" scope="row"><strong><?php esc_html_e('Payment method', 'doneren-met-mollie');?></strong></th>
                            <td class="column-b"><?php echo esc_html($subscription['sub_method']);?></td>
                        </tr>
                        <tr>
                            <th class="column-empty"></th>
                            <th class="column-a" scope="row"><strong><?php esc_html_e('Status', 'doneren-met-mollie');?></strong></th>
                            <td class="column-b"><?php echo esc_html($subs->getStatus($subscription['sub_status']));?></td>
                        </tr>
                        <tr>
                            <th class="column-empty"></th>
                            <th class="column-a" scope="row"><strong><?php esc_html_e('Subscription ID', 'doneren-met-mollie');?></strong></th>
                            <td class="column-b"><?php echo esc_html($subscription['subscription_id']);?></td>
                        </tr>
                        </tbody>
                    </table><br>
                <?php } ?>

            <?php } ?>
        </div>
        <?php
    }

    public function dmm_page_donors()
    {
        if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['customer']) && check_admin_referer('delete-donor_' . $_GET['customer']))
        {
            $mollie = new MollieApi(get_option('dmm_mollie_apikey'));

            try {
                $mollie->delete('customers/' . sanitize_text_field($_GET['customer']));

                $this->wpdb->query($this->wpdb->prepare("DELETE FROM " . DMM_TABLE_DONORS . " WHERE customer_id = %s",
                    $_GET['customer']
                ));

                $this->wpdb->query($this->wpdb->prepare("UPDATE " . DMM_TABLE_SUBSCRIPTIONS . " SET sub_status = 'canceled' WHERE customer_id = %s",
                    $_GET['customer']
                ));

                wp_redirect('?page=' . sanitize_text_field($_REQUEST['page']));
            } catch (Exception $e) {
                echo 'Error: ' . $e->getMessage();
            }
        }

        $dmmTable = new Dmm_Donors_Table();
        $dmmTable->prepare_items();
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Donors', 'doneren-met-mollie') ?></h2>

            <form method="post">
                <div class="alignleft actions">
                    <select name="action">
                        <option value="" selected='selected'>-------</option>
                        <option value="delete"><?php esc_html_e('Delete selected donors', 'doneren-met-mollie') ?></option>
                    </select>
                    <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
                    <input type="submit" id="doaction" class="button action" value="Submit"  />
                </div>

                <?php $dmmTable->display();?>
            </form>
        </div>
        <?php
    }

    public function dmm_page_subscriptions()
    {
        if (!get_option('dmm_mollie_apikey')) {
            echo '<div class="error notice"><p>' . esc_html__('No API-key set', 'doneren-met-mollie') . '</p></div>';
            return;
        }

        $mollie = new MollieApi(get_option('dmm_mollie_apikey'));

        if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['subscription']) && check_admin_referer('cancel-subscription_' . $_GET['subscription']))
        {
            $customer = $this->wpdb->get_row("SELECT * FROM " . DMM_TABLE_DONORS . " WHERE id = '" . esc_sql(sanitize_title_for_query($_GET['customer'])) . "'");

            try {
                $cancelledSub = $mollie->delete('customers/' . $customer->customer_id . '/subscriptions/' . sanitize_text_field($_GET['subscription']));
            } catch (Exception $e) {
				try {
					$subscription = $mollie->get('customers/' . $customer->customer_id . '/subscriptions/' . sanitize_text_field($_GET['subscription']));

					if ($subscription->status === 'canceled') {
						$this->wpdb->query($this->wpdb->prepare("UPDATE " . DMM_TABLE_SUBSCRIPTIONS . " SET sub_status = %s WHERE subscription_id = %s",
								$subscription->status,
								$subscription->id
						));
						wp_redirect('?page=' . sanitize_text_field($_REQUEST['page']) . '&msg=cancel-ok');
					}
				} catch (Exception $e) { }

                wp_redirect('?page=' . sanitize_text_field($_REQUEST['page']) . '&msg=cancel-nok&status=unknown');
				return;
            }

            if ($cancelledSub->status === 'canceled') {
                $this->wpdb->query($this->wpdb->prepare("UPDATE " . DMM_TABLE_SUBSCRIPTIONS . " SET sub_status = %s WHERE subscription_id = %s",
                    $cancelledSub->status,
                    $_GET['subscription']
                ));
                wp_redirect('?page=' . sanitize_text_field($_REQUEST['page']) . '&msg=cancel-ok');
            } else  {
	            wp_redirect('?page=' . sanitize_text_field($_REQUEST['page']) . '&msg=cancel-nok&status=' . $cancelledSub->status);
            }
        }

        if (isset($_GET['msg']))
        {
            switch ($_GET['msg'])
            {
                case 'cancel-ok':
                    $dmm_msg = '<div class="updated notice"><p>' . esc_html__('The subscription is successful cancelled', 'doneren-met-mollie') . '</p></div>';
                    break;
                case 'cancel-nok':
                    $dmm_msg = '<div class="error notice"><p>' . esc_html__('The subscription is not cancelled', 'doneren-met-mollie') . '</p></div>';
                    break;
            }
        }

        $dmmTable = new Dmm_Subscriptions_Table();
        $dmmTable->prepare_items();
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Subscriptions', 'doneren-met-mollie') ?></h2>

            <?php
            echo isset($dmm_msg) ? $dmm_msg : '';

            $dmmTable->display();
            ?>
        </div>
        <?php
    }

    public function dmm_page_settings()
    {
        if (!isset($_GET['tab']))
            $tab = 'general';
        else
            $tab = sanitize_text_field($_GET['tab']);
        ?>
        <div class="wrap">
            <h2 class="nav-tab-wrapper">
                <a href="?page=<?php echo DMM_PAGE_SETTINGS ?>" class="nav-tab<?php echo $tab === 'general' ? ' nav-tab-active' : '';?>"><?php esc_html_e('General', 'doneren-met-mollie');?></a>
                <a href="?page=<?php echo DMM_PAGE_SETTINGS ?>&tab=form" class="nav-tab<?php echo $tab === 'form' ? ' nav-tab-active' : '';?>"><?php esc_html_e('Form', 'doneren-met-mollie');?></a>
                <a href="?page=<?php echo DMM_PAGE_SETTINGS ?>&tab=mollie" class="nav-tab<?php echo $tab === 'mollie' ? ' nav-tab-active' : '';?>"><?php esc_html_e('Mollie settings', 'doneren-met-mollie');?></a>
                <a href="?page=<?php echo DMM_PAGE_SETTINGS ?>&tab=recurring" class="nav-tab<?php echo $tab === 'recurring' ? ' nav-tab-active' : '';?>"><?php esc_html_e('Recurring payments', 'doneren-met-mollie');?></a>
                <a href="https://wobbie.nl/doneren" target="_blank" class="nav-tab" style="float: right"><?php esc_html_e('Donate', 'doneren-met-mollie');?></a>
                <a href="https://features.wobbie.nl" target="_blank" class="nav-tab" style="float: right"><?php esc_html_e('Feature Requests', 'doneren-met-mollie');?></a>
                <a href="https://support.wobbie.nl" target="_blank" class="nav-tab" style="float: right"><?php esc_html_e('Support', 'doneren-met-mollie');?></a>
            </h2>
            <?php
            settings_errors();

            switch ($tab)
            {
                case 'recurring':
                    $this->dmm_tab_settings_recurring();
                    break;
                case 'mollie':
                    $this->dmm_tab_settings_mollie();
                    break;
                case 'form':
                    $this->dmm_tab_settings_form();
                    break;
                default:
                    $this->dmm_tab_settings_general();
            }
            ?>
        </div>
        <?php
    }

    private function dmm_tab_settings_general()
    {
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('dmm-settings-general');?>

            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Currency', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <select name="dmm_currency">
                                <?php foreach (dmm_get_currencies() as $currency => $decimals): ?>
                                    <option value="<?php echo esc_attr($currency);?>" <?php echo (get_option('dmm_currency') === $currency ? 'selected' : '');?>><?php echo esc_attr($currency);?></option>
                                <?php endforeach;?>
                            </select><br>
                            <small><?php esc_html_e('Default currency used for preset amounts', 'doneren-met-mollie');?></small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Amounts', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <input type="text" size="50" name="dmm_amount" value="<?php echo esc_attr(get_option('dmm_amount'));?>"><br>
                            <small><?php printf(esc_html__('Separate amounts with /. Example: "%s"', 'doneren-met-mollie'), '5,00/10,00/25,00/50,00');?></small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Donor can choose currency', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <select name="dmm_currency_switch">
                                <option value="0"><?php esc_html_e('No', 'doneren-met-mollie');?></option>
                                <option value="1" <?php echo (get_option('dmm_currency_switch') == '1' ? 'selected' : '');?>><?php esc_html_e('Yes', 'doneren-met-mollie');?></option>
                            </select><br>
                            <small><?php esc_html_e('If enabled, the donor can choose their own currency for donations', 'doneren-met-mollie');?></small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Free input', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <input type="checkbox" name="dmm_free_input" <?php echo (get_option('dmm_free_input', 0) ? 'checked' : '');?>>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Default amount', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <input type="text" size="50" name="dmm_default_amount" value="<?php echo esc_attr(get_option('dmm_default_amount'));?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Minimum amount', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <input type="text" size="50" name="dmm_minimum_amount" value="<?php echo esc_attr(get_option('dmm_minimum_amount'));?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Payment description', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <input type="text" size="50" name="dmm_payment_description" value="<?php echo esc_attr(get_option('dmm_payment_description', __('Donation') . ' {id}'));?>"><br>
                            <small><?php printf(esc_html__('You can use: %s', 'doneren-met-mollie'), '{id} {name} {email} {project} {amount} {company} {interval}');?></small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Payment methods', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <select name="dmm_methods_display">
                                <option value="list"><?php esc_html_e('Icons & text', 'doneren-met-mollie');?></option>
                                <option value="list_no_icons" <?php echo (get_option('dmm_methods_display') === 'list_no_icons' ? 'selected' : '');?>><?php esc_html_e('Only text', 'doneren-met-mollie');?></option>
                                <option value="list_icons" <?php echo (get_option('dmm_methods_display') === 'list_icons' ? 'selected' : '');?>><?php esc_html_e('Only icons', 'doneren-met-mollie');?></option>
                                <option value="dropdown" <?php echo (get_option('dmm_methods_display') === 'dropdown' ? 'selected' : '');?>><?php esc_html_e('Dropdown', 'doneren-met-mollie');?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php esc_html_e('Page after successful donation', 'doneren-met-mollie');?></th>
                        <td class="forminp"><?php $dmm_redirect_success = $this->get_page_id_by_slug(get_option('dmm_redirect_success'));wp_dropdown_pages(array('value_field' => 'post_name', 'selected' => $dmm_redirect_success, 'name' => 'dmm_redirect_success', 'show_option_no_change' => '-- ' . __('Default') . ' --'));?></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php esc_html_e('Page after failed donation', 'doneren-met-mollie');?></th>
                        <td class="forminp"><?php $dmm_redirect_failure = $this->get_page_id_by_slug(get_option('dmm_redirect_failure'));wp_dropdown_pages(array('value_field' => 'post_name', 'selected' => $dmm_redirect_failure, 'name' => 'dmm_redirect_failure', 'show_option_no_change' => '-- ' . __('Default') . ' --'));?></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Projects', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <textarea rows="10" name="dmm_projects" style="width: 370px;"><?php echo esc_attr(get_option('dmm_projects'));?></textarea><br>
                            <small><?php esc_html_e('Each project on a new line', 'doneren-met-mollie');?></small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Send metadata to Mollie', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <select name="dmm_metadata">
                                <option value="1"><?php esc_html_e('Yes', 'doneren-met-mollie');?></option>
                                <option value="0" <?php echo (get_option('dmm_metadata') == '0' ? 'selected' : '');?>><?php esc_html_e('No', 'doneren-met-mollie');?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Rights donations', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <select name="dmm_rights_donations">
                                <option value="edit_dashboard"><?php esc_html_e('Administrator', 'doneren-met-mollie');?></option>
                                <option value="edit_pages" <?php echo (get_option('dmm_rights_donations') === 'edit_pages' ? 'selected' : '');?>><?php esc_html_e('Editor', 'doneren-met-mollie');?></option>
                                <option value="edit_posts" <?php echo (get_option('dmm_rights_donations') === 'edit_posts' ? 'selected' : '');?>><?php esc_html_e('Author', 'doneren-met-mollie');?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Rights subscriptions', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <select name="dmm_rights_subscriptions">
                                <option value="edit_dashboard"><?php esc_html_e('Administrator', 'doneren-met-mollie');?></option>
                                <option value="edit_pages" <?php echo (get_option('dmm_rights_subscriptions') === 'edit_pages' ? 'selected' : '');?>><?php esc_html_e('Editor', 'doneren-met-mollie');?></option>
                                <option value="edit_posts" <?php echo (get_option('dmm_rights_subscriptions') === 'edit_posts' ? 'selected' : '');?>><?php esc_html_e('Author', 'doneren-met-mollie');?></option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button();?>
        </form>
        <?php
    }

    private function dmm_tab_settings_form()
    {
        $dmm_form_fields = get_option('dmm_form_fields');
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('dmm-settings-form');?>

            <h3><?php esc_html_e('Fields', 'doneren-met-mollie');?></h3>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <td class="forminp">
                        <table class="widefat fixed striped">
                            <thead>
                                <tr valign="top">
                                    <th id="empty" class="manage-column column-empty" style="width:5px;">&nbsp;</th>
                                    <th id="field" class="manage-column column-field"><?php esc_html_e('Field', 'doneren-met-mollie');?></th>
                                    <th id="active" class="manage-column column-active"><?php esc_html_e('Active', 'doneren-met-mollie');?></th>
                                    <th id="required" class="manage-column column-required"><?php esc_html_e('Required', 'doneren-met-mollie');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th class="column-empty"></th>
                                    <th class="column-field" scope="row"><?php esc_html_e('Name', 'doneren-met-mollie');?></th>
                                    <td class="column-active"><input type="checkbox" name="dmm_form_fields[Name][active]" <?php echo (isset($dmm_form_fields['Name']['active']) ? 'checked' : '');?>></td>
                                    <td class="column-required"><input type="checkbox" name="dmm_form_fields[Name][required]" <?php echo (isset($dmm_form_fields['Name']['required']) ? 'checked' : '');?>></td>
                                </tr>
                                <tr>
                                    <th class="column-empty"></th>
                                    <th class="column-field" scope="row"><?php esc_html_e('Email address', 'doneren-met-mollie');?></th>
                                    <td class="column-active"><input type="checkbox" name="dmm_form_fields[Email address][active]" <?php echo (isset($dmm_form_fields['Email address']['active']) ? 'checked' : '');?>></td>
                                    <td class="column-required"><input type="checkbox" name="dmm_form_fields[Email address][required]" <?php echo (isset($dmm_form_fields['Email address']['required']) ? 'checked' : '');?>></td>
                                </tr>
                                <tr>
                                    <th class="column-empty"></th>
                                    <th class="column-field" scope="row"><?php esc_html_e('Phone number', 'doneren-met-mollie');?></th>
                                    <td class="column-active"><input type="checkbox" name="dmm_form_fields[Phone number][active]" <?php echo (isset($dmm_form_fields['Phone number']['active']) ? 'checked' : '');?>></td>
                                    <td class="column-required"><input type="checkbox" name="dmm_form_fields[Phone number][required]" <?php echo (isset($dmm_form_fields['Phone number']['required']) ? 'checked' : '');?>></td>
                                </tr>
                                <tr>
                                    <th class="column-empty"></th>
                                    <th class="column-field" scope="row"><?php esc_html_e('Address', 'doneren-met-mollie');?></th>
                                    <td class="column-active"><input type="checkbox" name="dmm_form_fields[Address][active]" <?php echo (isset($dmm_form_fields['Address']['active']) ? 'checked' : '');?>></td>
                                    <td class="column-required"><input type="checkbox" name="dmm_form_fields[Address][required]" <?php echo (isset($dmm_form_fields['Address']['required']) ? 'checked' : '');?>></td>
                                </tr>
                                <tr>
                                    <th class="column-empty"></th>
                                    <th class="column-field" scope="row"><?php esc_html_e('Company name', 'doneren-met-mollie');?></th>
                                    <td class="column-active"><input type="checkbox" name="dmm_form_fields[Company name][active]" <?php echo (isset($dmm_form_fields['Company name']['active']) ? 'checked' : '');?>></td>
                                    <td class="column-required"><input type="checkbox" name="dmm_form_fields[Company name][required]" <?php echo (isset($dmm_form_fields['Company name']['required']) ? 'checked' : '');?>></td>
                                </tr>
                                <tr>
                                    <th class="column-empty"></th>
                                    <th class="column-field" scope="row"><?php esc_html_e('Message', 'doneren-met-mollie');?></th>
                                    <td class="column-active"><input type="checkbox" name="dmm_form_fields[Message][active]" <?php echo (isset($dmm_form_fields['Message']['active']) ? 'checked' : '');?>></td>
                                    <td class="column-required"><input type="checkbox" name="dmm_form_fields[Message][required]" <?php echo (isset($dmm_form_fields['Message']['required']) ? 'checked' : '');?>></td>
                                </tr>
                                <tr>
                                    <th class="column-empty"></th>
                                    <th class="column-field" scope="row"><?php esc_html_e('Project', 'doneren-met-mollie');?></th>
                                    <td class="column-active"><input type="checkbox" name="dmm_form_fields[Project][active]" <?php echo (isset($dmm_form_fields['Project']['active']) ? 'checked' : '');?>></td>
                                    <td class="column-required"><input type="checkbox" name="dmm_form_fields[Project][required]" <?php echo (isset($dmm_form_fields['Project']['required']) ? 'checked' : '');?>></td>
                                </tr>
                                <tr>
                                    <th class="column-empty"></th>
                                    <th class="column-field" scope="row"><?php esc_html_e('GDPR checkbox', 'doneren-met-mollie');?></th>
                                    <td class="column-active"><input type="checkbox" name="dmm_form_fields[GDPR checkbox][active]" <?php echo (isset($dmm_form_fields['GDPR checkbox']['active']) ? 'checked' : '');?>></td>
                                    <td class="column-required"><input type="checkbox" name="dmm_form_fields[GDPR checkbox][required]" <?php echo (isset($dmm_form_fields['GDPR checkbox']['required']) ? 'checked' : '');?>></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>

            <h3><?php esc_html_e('GDPR checkbox', 'doneren-met-mollie');?></h3>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Link to privacy policy', 'doneren-met-mollie');?></label>
                    </th>
                    <td class="forminp">
                        <input type="text" size="50" name="dmm_gdpr_link" value="<?php echo esc_attr(get_option('dmm_gdpr_link'));?>">
                    </td>
                </tr>
                </tbody>
            </table><hr>

            <h3><?php esc_html_e('Google reCaptcha V3', 'doneren-met-mollie');?></h3>
	        <small>
		        <?php esc_html_e('Generate a reCAPTCHA v3 key at:', 'doneren-met-mollie');?> <a target="_blank" href="https://www.google.com/recaptcha/admin/create">Google</a>
	        </small>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Site key', 'doneren-met-mollie');?></label>
                    </th>
                    <td class="forminp">
                        <input type="text" size="50" name="dmm_recaptcha_v3_site_key" value="<?php echo esc_attr(get_option('dmm_recaptcha_v3_site_key'));?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Secret key', 'doneren-met-mollie');?></label>
                    </th>
                    <td class="forminp">
                        <input type="text" size="50" name="dmm_recaptcha_v3_secret_key" value="<?php echo esc_attr(get_option('dmm_recaptcha_v3_secret_key'));?>">
                    </td>
                </tr>
                </tbody>
            </table><hr>

            <h3><?php esc_html_e('Classes', 'doneren-met-mollie');?></h3>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Form class', 'doneren-met-mollie');?></label>
                    </th>
                    <td class="forminp">
                        <input type="text" size="50" name="dmm_form_cls" value="<?php echo esc_attr(get_option('dmm_form_cls'));?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Form fields class', 'doneren-met-mollie');?></label>
                    </th>
                    <td class="forminp">
                        <input type="text" size="50" name="dmm_fields_cls" value="<?php echo esc_attr(get_option('dmm_fields_cls'));?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Form button class', 'doneren-met-mollie');?></label>
                    </th>
                    <td class="forminp">
                        <input type="text" size="50" name="dmm_button_cls" value="<?php echo esc_attr(get_option('dmm_button_cls'));?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Message success class', 'doneren-met-mollie');?></label>
                    </th>
                    <td class="forminp">
                        <input type="text" size="50" name="dmm_success_cls" value="<?php echo esc_attr(get_option('dmm_success_cls'));?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Message failure class', 'doneren-met-mollie');?></label>
                    </th>
                    <td class="forminp">
                        <input type="text" size="50" name="dmm_failure_cls" value="<?php echo esc_attr(get_option('dmm_failure_cls'));?>">
                    </td>
                </tr>
                </tbody>
            </table>

            <?php submit_button();?>
        </form>
        <?php
    }

    private function dmm_tab_settings_recurring()
    {
        $recurring = false;
        try {
            if (!get_option('dmm_mollie_apikey')) {
                echo '<div class="error notice"><p>' . esc_html__('No API-key set', 'doneren-met-mollie') . '</p></div>';
                return;
            }

            $mollie = new MollieApi(get_option('dmm_mollie_apikey'));

            if (count($mollie->all('methods', array('sequenceType' => 'recurring')))) {
                $recurring = true;
            }

        } catch (Exception $e) {
            echo "<div class=\"error notice\"><p>Error: " . htmlspecialchars($e->getMessage()) . "</p></div>";
        }
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('dmm-settings-recurring');?>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Activate recurring payments', 'doneren-met-mollie');?></label>
                    </th>
                    <td class="forminp">
                        <input type="checkbox" name="dmm_recurring" <?php echo get_option('dmm_recurring') ? 'checked' : '';?> value="1" <?php echo $recurring ? '' : 'disabled';?>><br>
                        <small><?php esc_html_e('Creditcard or SEPA Direct Debit is necessary', 'doneren-met-mollie');?></small>
                    </td>
                </tr>


                <?php if (get_option('dmm_recurring')) {
                    $intervals = get_option('dmm_recurring_interval');
                    ?>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Name of the foundation', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <input type="text" size="50" name="dmm_name_foundation" value="<?php echo esc_attr(get_option('dmm_name_foundation'));?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Possible intervals', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <label><input type="checkbox" name="dmm_recurring_interval[month]" <?php echo isset($intervals['month']) ? 'checked' : '';?> value="1"> <?php esc_html_e('Monthly', 'doneren-met-mollie');?></label><br>
                            <label><input type="checkbox" name="dmm_recurring_interval[quarter]" <?php echo isset($intervals['quarter']) ? 'checked' : '';?> value="1"> <?php esc_html_e('Each quarter', 'doneren-met-mollie');?></label><br>
                                <label><input type="checkbox" name="dmm_recurring_interval[year]" <?php echo isset($intervals['year']) ? 'checked' : '';?> value="1"> <?php esc_html_e('Annually', 'doneren-met-mollie');?></label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label><?php esc_html_e('Default interval', 'doneren-met-mollie');?></label>
                        </th>
                        <td class="forminp">
                            <select name="dmm_default_interval">
                                <option value="one"><?php esc_html_e('One-time donation', 'doneren-met-mollie');?></option>
                                <option value="month" <?php echo get_option('dmm_default_interval') === 'month' ? 'selected' : '';?>><?php esc_html_e('Monthly', 'doneren-met-mollie');?></option>
                                <option value="quarter" <?php echo get_option('dmm_default_interval') === 'quarter' ? 'selected' : '';?>><?php esc_html_e('Each quarter', 'doneren-met-mollie');?></option>
                                <option value="year" <?php echo get_option('dmm_default_interval') === 'year' ? 'selected' : '';?>><?php esc_html_e('Annually', 'doneren-met-mollie');?></option>
                            </select>
                        </td>
                    </tr>
                <?php } ?>

                </tbody>
            </table>

            <?php submit_button();?>
        </form>
        <?php
    }

    private function dmm_tab_settings_mollie()
    {
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('dmm-settings-mollie');?>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('API-key', 'doneren-met-mollie');?></label>
                    </th>
                    <td class="forminp">
                        <input type="text" size="50" name="dmm_mollie_apikey" value="<?php echo esc_attr(get_option('dmm_mollie_apikey'));?>"><br>
                        <small><?php esc_html_e('Starts with live_ or test_', 'doneren-met-mollie');?></small>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php submit_button();?>
        </form>
        <?php
    }

    public function get_page_id_by_slug($slug)
    {
        return $this->wpdb->get_var("SELECT id FROM " . $this->wpdb->posts . " WHERE post_name = '" . esc_sql(sanitize_title_for_query($slug)) . "' AND post_type = 'page'");
    }
}
