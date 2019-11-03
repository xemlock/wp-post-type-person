<?php

if (!defined('PERSON_SELECT_FIELD')): define('PERSON_SELECT_FIELD', __FILE__);

function ____wp_get_post_thumbnail_url($post_id, $size = 'thumbnail', $icon = false) {
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ($thumbnail_id) {
        $image = wp_get_attachment_image_src($thumbnail_id, $size, $icon);
        return isset($image[0]) ? $image[0] : false;
    }
    return false;
}

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_script('jquery-ui-sortable');
});

add_action('admin_head', function () {
?>
<!--<script>-->
<!--jQuery(function ($) {-->
<!---->
<!--    $('body').on('', '', function (e) {-->
<!--        $('.search-autocomplete').autoComplete({-->
<!--            minChars: 2,-->
<!--            source: function(term, suggest){-->
<!--                try { searchRequest.abort(); } catch(e){}-->
<!--                searchRequest = $.post(global.ajax, { search: term, action: 'search_site' }, function(res) {-->
<!--                    suggest(res.data);-->
<!--                });-->
<!--            }-->
<!--        });-->
<!--    });-->
<!--});-->
<!--</script>-->
<?php
});

/**
 * Options:
 * - id
 * - class
 * - value
 * - multiple
 * - sortable
 *
 * @param array $field
 * @return void
 */
function person_select_field(array $field)
{
    $id = isset($field['id']) ? $field['id'] : ('person-select-' . mt_rand(1E5, 1E6 - 1));

    $multiple = !empty($field['multiple']);
    $sortable = !empty($field['sortable']);

    $name = $multiple ? $field['name'] : ($field['name'] . '[]');
    $value = isset($field['value']) ? array_map('intval', (array) $field['value']) : array();

    // fetch people from value
    /** @var WP_Post[] $people */
    if (count($value)) {
        $people = get_posts(array(
            'post_type'      => array('person'),
            'post_status'    => 'publish',
            'nopaging'       => true,
            'post__in'       => $value,
            'posts_per_page' => 100,
        ));
    } else {
        $people = array();
    }
?>
<div>
    <div>
        <?php foreach ($people as $person): ?>
            <?php echo get_post_meta($person->ID, 'person__first_name', true) ?>
            <?php echo get_post_meta($person->ID, 'person__last_name', true) ?>
            <?php echo ____wp_get_post_thumbnail_url($person->ID) ?>
        <?php endforeach ?>
    </div>
    <div>
        <?php foreach ($value as $val): ?>
        <input type="hidden" name="<?php esc_attr_e($name) ?>" value="<?php esc_attr_e($val) ?>" />
        <?php endforeach ?>
    </div>
    <input type="text" id="<?php esc_attr_e($id) ?>" />
    <button class="button button-large button-primary" type="button" disabled><?php esc_html_e('Add') ?></button>
</div>
<script>
jQuery(function ($) {
    var autocomplete = $('#<?php esc_attr_e($id) ?>').autocomplete({
        source: function (request, response) {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php') ?>',
                dataType: 'json',
                data: {
                    action: 'person_search',
                    s: request.term
                },
                success: function (data) {
                    response(data);
                }
            });
        },
    }).autocomplete('instance');

    $('#<?php esc_attr_e($id) ?>').on('keydown keyup keypress', function (e) {
        console.log(e.type, e.key, e.which);
        if (e.which === 13) {
            e.preventDefault();
            return false;
        }
    });

    autocomplete._renderItem = function (ul, item) {
        return $('<li>')
            .append('<div>' + item.full_name + '<br><img src="' + item.thumbnail_url + '" /></div>')
            .appendTo(ul);
    };


});
</script>
<?php
}

add_action('wp_ajax_person_search', function () {
    $response = array();
    $s = isset($_GET['s']) ? trim($_GET['s']) : '';

    if (strlen($s)) {
        $posts = get_posts(array(
            'post_type'      => 'person',
            'post_status'    => 'publish',
            'nopaging'       => false,
            'posts_per_page' => 10,
            's'              => $s,
        ));

        foreach ($posts as $post) {
            $response[] = array(
                'ID'            => $post->ID,
                'full_name'     => implode(' ', array_filter(array(
                    get_post_meta($post->ID, 'person__first_name', true),
                    get_post_meta($post->ID, 'person__last_name', true),
                ), 'strlen')),
                'first_name'    => get_post_meta($post->ID, 'person__first_name', true),
                'last_name'     => get_post_meta($post->ID, 'person__last_name', true),
                'thumbnail_id'  => get_post_thumbnail_id($post->ID),
                'thumbnail_url' => ____wp_get_post_thumbnail_url($post->ID),
            );
        }
    }

    wp_send_json($response);
    exit;
});

endif;
