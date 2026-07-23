<?php

class Dmm_Subscriptions_Table extends WP_List_Table
{
    function get_columns()
    {
        $columns = array();
        $columns['created_at'] = __('Date/time', 'doneren-met-mollie');
        $columns['customer_name'] = __('Name', 'doneren-met-mollie');
        $columns['sub_amount'] = __('Amount', 'doneren-met-mollie');
        $columns['sub_interval'] = __('Interval', 'doneren-met-mollie');
        $columns['sub_status'] = __('Status', 'doneren-met-mollie');
        $columns['total_donated'] = __('Total donated', 'doneren-met-mollie');
        $columns['subscription_id'] = __('Subscription ID', 'doneren-met-mollie');

        return $columns;
    }

    function column_subscription_id($item)
    {
        if ($item['sub_status'] != 'active')
            return $item['subscription_id'];

        $url_view = '?page=' . DMM_PAGE_DONATIONS . '&subscription=' . $item['subscription_id'];
        $url_cancel = wp_nonce_url('?page=' . DMM_PAGE_SUBSCRIPTIONS . '&action=cancel&subscription=' . $item['subscription_id'] . '&customer=' . $item['customer_id'], 'cancel-subscription_' . $item['subscription_id']);
        $actions = array(
            'view'    => sprintf('<a href="%s">' . esc_html__('View', 'doneren-met-mollie') . '</a>', $url_view),
            'cancel'    => sprintf('<a href="%s" style="color:#a00;" onclick="return confirm(\'' . __('Are you sure?', 'doneren-met-mollie') . '\')">' . esc_html__('Cancel', 'doneren-met-mollie') . '</a>', $url_cancel),
        );

        //Return the title contents
        return sprintf('%1$s %2$s',
            $item['subscription_id'],
            $this->row_actions($actions)
        );
    }

    function column_customer_name($item){
        global $wpdb;
        $customer = $wpdb->get_row("SELECT * FROM " . DMM_TABLE_DONORS . " WHERE id = '" . esc_sql(sanitize_title_for_query($item['customer_id'])) . "'");
        return $customer->customer_name;
    }

    function prepare_items() {
        global $wpdb;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $subscriptions = $wpdb->get_results("SELECT * FROM " . DMM_TABLE_SUBSCRIPTIONS, ARRAY_A);

        $per_page = 25;
        $current_page = $this->get_pagenum();
        $total_items = count($subscriptions);

        $d = array_slice($subscriptions,(($current_page-1)*$per_page),$per_page);

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page)
        ) );
        $this->items = $d;
    }

    function getInterval($interval) {
        return dmm_get_interval_label($interval);
    }

    function getStatus($status) {
        switch ($status) {
            case 'pending':
                $return = esc_html__('Pending', 'doneren-met-mollie');
                break;
            case 'active':
                $return = esc_html__('Active', 'doneren-met-mollie');
                break;
            case 'canceled':
            case 'cancelled':
                $return = esc_html__('Cancelled', 'doneren-met-mollie');
                break;
            case 'suspended':
                $return = esc_html__('Suspended', 'doneren-met-mollie');
                break;
            case 'completed':
                $return = esc_html__('Completed', 'doneren-met-mollie');
                break;
        }

        return $return;
    }

    function column_default($item, $column_name)
    {
		global $wpdb;

        switch($column_name) {
            case 'created_at':
                return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item[ $column_name ]));

            case 'sub_amount':
                return dmm_get_currency_symbol($item['sub_currency']) . ' ' . number_format($item['sub_amount'], dmm_get_currencies($item['sub_currency']), ',', '');

            case 'sub_interval':
                return esc_html($this->getInterval($item['sub_interval']));

            case 'sub_status':
                return $this->getStatus($item['sub_status']);

	        case 'total_donated':
		        $currency = $wpdb->get_var("SELECT dm_settlement_currency FROM " . DMM_TABLE_DONATIONS . " WHERE customer_id='" . esc_sql($item['customer_id']) . "' AND dm_status='paid'");
		        $total = $wpdb->get_var("SELECT SUM(dm_settlement_amount) FROM " . DMM_TABLE_DONATIONS . " WHERE customer_id='" . esc_sql($item['customer_id']) . "' AND dm_status='paid'");
		        if (!$total) {
			        return '';
		        }

		        return dmm_get_currency_symbol($currency) . ' ' . number_format_i18n($total, 2);

            default:
                return $item[$column_name];
        }
    }

    public function display_tablenav($which) {
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">
            <?php $this->pagination($which);?>
            <br class="clear" />
        </div>
        <?php
    }
}