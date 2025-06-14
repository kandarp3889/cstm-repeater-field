<?php 

/*
Plugin Name: Custom Repeater Plugin
Description: Adds a custom repeater field to posts without relying on ACF.
Version: 1.1
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add metabox for custom repeater field
function custom_repeater_meta_box() {
    add_meta_box(
        'custom_repeater',
        'Custom Repeater',
        'custom_repeater_meta_box_callback',
        'post',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'custom_repeater_meta_box');

// Callback function to render the custom repeater field
function custom_repeater_meta_box_callback($post) {
    $repeater_data = get_post_meta($post->ID, 'custom_repeater_data', true);
    
    if (empty($repeater_data) || !is_array($repeater_data)) {
        $repeater_data = [['field1' => '', 'field2' => '', 'field3' => '']];
    }
    ?>
    <div>
        <table id="custom_repeater_table" style="width:100%;">
            <tbody>
                <tr><td>First Field</td><td>Second Field</td><td>Third Field</td></tr>
                <?php
                foreach ($repeater_data as $index => $row) {
                    $field1 = isset($row['field1']) ? esc_attr($row['field1']) : '';
                    $field2 = isset($row['field2']) ? esc_attr($row['field2']) : '';
                    $field3 = isset($row['field3']) ? esc_attr($row['field3']) : '';

                    echo '<tr>';
                    echo '<td><input type="text" name="custom_repeater_data[' . $index . '][field1]" value="' . $field1 . '" style="width:100%;"></td>';
                    echo '<td><input type="text" name="custom_repeater_data[' . $index . '][field2]" value="' . $field2 . '" style="width:100%;"></td>';
                    echo '<td><input type="text" name="custom_repeater_data[' . $index . '][field3]" value="' . $field3 . '" style="width:100%;"></td>';
                    echo '<td style="display:flex; justify-content:end; align-items:center; height:32px;"><button class="remove-row-button" style="height:25px; width:25px;">-</button></td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        <div style="width:100%;"><button id="add_row_button" style="margin-left:auto; display:flex; height:25px; width:25px; margin-right:2px;">+</button></div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var addRowButton = document.getElementById('add_row_button');
            var tableBody = document.getElementById('custom_repeater_table').getElementsByTagName('tbody')[0];
            var rowIndex = <?php echo count($repeater_data); ?>;

            addRowButton.addEventListener('click', function() {
                var newRow = document.createElement('tr');
                newRow.innerHTML = '<td><input type="text" name="custom_repeater_data[' + rowIndex + '][field1]" value="" style="width:100%;"></td>' +
                                   '<td><input type="text" name="custom_repeater_data[' + rowIndex + '][field2]" value="" style="width:100%;"></td>' +
                                   '<td><input type="text" name="custom_repeater_data[' + rowIndex + '][field3]" value="" style="width:100%;"></td>' +
                                   '<td style="display:flex; justify-content:end; align-items:center; height:32px;"><button class="remove-row-button" style="height:25px; width:25px;">-</button></td>';
                tableBody.appendChild(newRow);
                rowIndex++; // Increment index for the next row
            });

            document.getElementById('custom_repeater_table').addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-row-button')) {
                    event.preventDefault();
                    var rowToRemove = event.target.closest('tr');
                    rowToRemove.parentNode.removeChild(rowToRemove);

                    // Decrement rowIndex
                    rowIndex--;

                    // If no rows left, add an empty row
                    if (tableBody.rows.length === 1) { // Adjust to 1 to account for the header row
                        addRowButton.click();
                    }
                }
            });
        });
    </script>
    <?php
}

// Save custom repeater data
function save_custom_repeater_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['custom_repeater_data'])) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $repeater_data = $_POST['custom_repeater_data'];

    // Ensure $repeater_data is properly formatted
    $cleaned_repeater_data = array();
    foreach ($repeater_data as $row) {
        // Check if all fields are set to avoid empty rows
        if (isset($row['field1']) || isset($row['field2']) || isset($row['field3'])) {
            $cleaned_repeater_data[] = array(
                'field1' => sanitize_text_field($row['field1']),
                'field2' => sanitize_text_field($row['field2']),
                'field3' => sanitize_text_field($row['field3'])
            );
        }
    }

    // If all rows are empty, save a single empty row
    if (empty($cleaned_repeater_data)) {
        $cleaned_repeater_data[] = array(
            'field1' => '',
            'field2' => '',
            'field3' => ''
        );
    }

    update_post_meta($post_id, 'custom_repeater_data', $cleaned_repeater_data);
}
add_action('save_post', 'save_custom_repeater_data');

?>
