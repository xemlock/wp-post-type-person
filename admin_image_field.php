<?php

if (!defined('ADMIN_IMAGE_FIELD')): define('ADMIN_IMAGE_FIELD', __FILE__);

add_action('admin_head', function () {
?>
<style>
[data-image-field] [data-action] {
    cursor: pointer;
}
[data-image-field] [data-action] .dashicons {
    position: relative;
    top: -.05em;
    margin-left: -.15em;
    vertical-align: middle;
}
[data-image-field] [data-action="change-image"],
[data-image-field] [data-action="clear-image"] {
    display: none;
}
[data-image-field] [data-action="clear-image"] {
    float: right;
}
[data-image-field] [data-action="set-image"] {
    display: inline-block;
    margin-top: 5px;
}
[data-image-field][data-has-image] [data-action="set-image"] {
    display: none;
}
[data-image-field][data-has-image] [data-action="change-image"],
[data-image-field][data-has-image] [data-action="clear-image"] {
    display: inline-block;
}

[data-image-field] img {
    display: none;
}
[data-image-field][data-has-image] img {
    display: block;
    width: 100%;
    margin: 10px 0;
}
[data-image-field] noscript {
    display: block;
    clear: both;
    margin-top: 15px;
    color: #a00;
}
</style>
<script>
jQuery(function ($) {
    var frame;

    $('body')
        .on('click', '[data-action="set-image"], [data-action="change-image"]', function (e) {
            e.preventDefault();

            var field = $(e.target).closest('[data-image-field]');
            if (!field.length) {
                return false;
            }

            if (frame) {
                frame.open();
                return;
            }

            // Sets up the media library frame
            frame = wp.media({
                title: <?php echo json_encode(__('Select image')) ?>,
                button: { text: <?php echo json_encode(__('Select image')) ?> },
                library: { type: 'image' },
                multiple: false
            });

            frame.on('open', function () {
                var selection = frame.state().get('selection');
                var attachment = wp.media.attachment(field.find('input').val());

                attachment.fetch();
                selection.add(attachment ? [attachment] : []);
            });

            // Runs when an image is selected.
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                field.find('input').val(attachment.id);
                field.attr('data-has-image', '');
                field.find('img').attr('src', attachment.url);
            });

            frame.open();
        })
        .on('click', '[data-action="clear-image"]', function (e) {
            e.preventDefault();

            var field = $(e.target).closest('[data-image-field]');
            if (!field.length) {
                return false;
            }

            field.find('input').val('');
            field.find('img').attr('src', '');
            field.removeAttr('data-has-image');
        });
});
</script>
<?php
});

/**
 * @param array $field
 * @return string
 */
function get_admin_image_field(array $field)
{
    $name = $field['name'];

    $id = isset($field['id']) ? $field['id'] : ('image-field-' . substr(sprintf('%.8f', mt_rand()), 2));
    $value = isset($field['value']) ? $field['value'] : '';

    $img = $value ? wp_get_attachment_image_src($value, 'full') : null;

    $html = '<div id="' . esc_attr($id) . '" data-image-field' . ($img ? ' data-has-image' : '') . '>';
    $html .= '<img src="' . ($img ? esc_attr($img[0]) : '') . '" alt="'. esc_html__('Image preview') .'" />';

    $html .= '<a href="#!" role="button" data-action="set-image">' . esc_html__('Select image') . '</a>';
    $html .= '<a href="#!" role="button" class="button button-primary button-large" data-action="change-image"><i class="dashicons dashicons-edit"></i> ' . esc_html__('Change') . '</a>';
    $html .= '<a href="#!" role="button" class="button button-large" data-action="clear-image"><i class="dashicons dashicons-no"></i> ' . esc_html__('Clear') . '</a>';

    $html .= '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" />';

    $html .= '<noscript>' . esc_html__('Image selection requires JavaScript to be enabled in the browser') . '</noscript>';
    $html .= '</div>';

    return $html;
}

/**
 * @param array $field
 * @return void
 */
function admin_image_field(array $field) {
    echo get_admin_image_field($field);
}

endif;
