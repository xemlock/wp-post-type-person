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
    $sortable = $multiple && !empty($field['sortable']);

    $name = $field['name'] . ($multiple ? '[]' : '');
    $value = array();

    if (isset($field['value'])) {
        $value = array_filter(array_map('intval', (array) $field['value']), 'intval');
    }

    /** @var WP_Post[] $people */
    if (count($value)) {
        $people = get_posts(array(
            'post_type'   => array('person'),
            'post_status' => 'publish',
            'nopaging'    => true,
            'post__in'    => $value,
        ));
    } else {
        $people = array();
    }
?>
<div class="person-select" id="<?php esc_attr_e($id) ?>">
    <div class="person-select__value-inputs">
        <?php foreach ($value as $val): ?>
        <input type="hidden" name="<?php esc_attr_e($name) ?>" value="<?php esc_attr_e($val) ?>" />
        <?php endforeach ?>
    </div>
    <div class="person-select__selected-items">
        <?php foreach ($people as $person): ?>
            <?php echo get_post_meta($person->ID, 'person__first_name', true) ?>
            <?php echo get_post_meta($person->ID, 'person__last_name', true) ?>
            <?php echo ____wp_get_post_thumbnail_url($person->ID) ?>
        <?php endforeach ?>
    </div>
    <div class="person-select__search">
        <input type="text" class="person-select__search-input" />
        <button class="person-select__search-button button button-large button-primary" type="button" disabled><?php esc_html_e('Add') ?></button>
    </div>
</div>
<script>
jQuery(function ($) {
    var closeTime;
    var selected = [];
    var field = $('.person-select#<?php esc_attr_e($id) ?>');
    var sortable = <?php echo json_encode($sortable) ?>;
    var multiple = <?php echo json_encode($multiple) ?>;

    var searchInput = field.find('.person-select__search-input');
    var searchButton = field.find('.person-select__search-button');

    if (sortable) {
        field.addClass('is-sortable');
        field.find('.person-select__selected-items').sortable({items: '.xxx'});
    }

    if (multiple) {
        field.addClass('is-multiple');
    }

    var autocomplete = searchInput.autocomplete({
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
        open: function (event, ui) {
            closeTime = null;
        },
        close: function (event, ui) {
            closeTime = event.timeStamp;
        },
        focus: function (event, ui) {
            event.preventDefault();
            searchInput.val(ui.item.full_name);
        },
        select: function(event, ui) {
            event.preventDefault();

            if (!multiple && selected.length) {
                return;
            }

            for (var i = 0; i < selected.length; ++i) {
                if (selected[i] === ui.item.ID) {
                    searchInput.val('');
                    searchButton.prop('disabled', true);
                    return;
                }
            }
            selected.push(ui.item.ID);

            if (!multiple) {
                field.find('.person-select__value-inputs').empty();
            }

            var valueInput = $('<input type="hidden" />').attr('name', '<?php echo esc_js($name) ?>').val(ui.item.ID);
            field.find('.person-select__value-inputs').append(valueInput);
            field.find('.person-select__selected-items').append(
                $('<div class="xxx" />').attr('data-id', ui.item.ID)
                    .append($('<img/>').attr('src', ui.item.thumbnail_url))
                    .append($('<div/>').text(ui.item.full_name))
                    .append($('<button type="button" />').text('DELETE').on('click', function (e) {
                        e.preventDefault();
                        selected.splice(selected.indexOf(ui.item.ID), 1);
                        valueInput.remove();
                        valueInput = null;
                        $(this).closest('.xxx').remove();
                    }))
            );
            searchInput.val('');
            searchButton.prop('disabled', true);

            if (!multiple) {
                field.find('person-select__search').hide();
            }
            return false;
        },
    }).autocomplete('instance');

    searchInput.attr('autocomplete', Math.random().toFixed(16));
    searchInput.on('keydown keyup keypress', function (e) {
        console.log(e.type, e.key, e.which);
        if (e.which === 13) {
            e.preventDefault();
            return false;
        }
    });

    searchInput.on('click', function (e) {

    });

    autocomplete._renderItem = function (ul, item) {
        return $('<li>')
            .append('<div>' + item.full_name + '<br><img src="' + (item.thumbnail_url || '') + '" alt="" /></div>')
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
