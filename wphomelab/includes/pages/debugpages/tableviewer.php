<?php
function homelab_render_table_viewer_page()
{
    global $wpdb;
    $tables = array(
        $wpdb->prefix . 'homelab_services',
        $wpdb->prefix . 'homelab_service_images',
        $wpdb->prefix . 'homelab_service_status_logs',
        $wpdb->prefix . 'homelab_service_data',
        $wpdb->prefix . 'homelab_service_notes',
    );

    if (isset($_GET['table']) && in_array($_GET['table'], $tables)) {
        $selected_table = $_GET['table'];
    } else {
        $selected_table = '';
    }

    if (!empty($selected_table)) {
        $table_structure = $wpdb->get_results("DESCRIBE $selected_table");
        $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM $selected_table");

        $page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $per_page = 20; // Number of rows per page
        $offset = ($page - 1) * $per_page;
        $table_data = $wpdb->get_results("SELECT * FROM $selected_table LIMIT $offset, $per_page");
        $total_pages = ceil($total_rows / $per_page);
    }
    ?>

    <div class="wrap">
        <h1>Table Viewer</h1>

        <form method="get">
            <input type="hidden" name="page" value="homelab-table-viewer">
            <select name="table">
                <option value="">Select a table</option>
                <?php foreach ($tables as $table): ?>
                    <option value="<?php echo esc_attr($table); ?>" <?php selected($_GET['table'], $table); ?>>
                        <?php echo esc_html($table); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button">View Table</button>
        </form>

        <?php if (!empty($selected_table)): ?>
            <h2>
                <?php echo esc_html($selected_table); ?>
            </h2>
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <?php foreach ($table_structure as $column): ?>
                            <th>
                                <?php echo esc_html($column->Field); ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($table_data as $row): ?>
                        <tr>
                            <?php foreach ($table_structure as $column): ?>
                                <td>
                                    <?php echo esc_html($row->{$column->Field}); ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <style>
                .wp-list-table th,
                .wp-list-table td {
                    padding: 8px;
                    text-align: left;
                    vertical-align: top;
                }

                .wp-list-table th {
                    font-weight: bold;
                }

                .wp-list-table tr:nth-child(odd) {
                    background-color: #f9f9f9;
                }
            </style>

            <!-- Pagination links -->
            <?php
            $pagination_args = array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'total' => $total_pages,
                'current' => $page,
                'show_all' => false,
                'prev_next' => true,
                'prev_text' => __('&laquo; Previous'),
                'next_text' => __('Next &raquo;'),
                'type' => 'plain',
                'add_args' => array('table' => $selected_table),
            );
            echo paginate_links($pagination_args);
            ?>
        <?php endif; ?>
    </div>
    <?php
}
