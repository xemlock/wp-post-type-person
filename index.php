<?php

/*
 * Plugin Name: WP Post Type: Person
 * Plugin URI:  https://github.com/xemlock/wp-post-type-person
 * Description: Person post type
 * Version:     0.1.0-dev
 * Author:      xemlock
 * Author URI:  https://github.com/xemlock
 */
$POST_TYPE = 'person';

require __DIR__ . '/admin_image_field.php';
require __DIR__ . '/person_select_field.php';

add_action('init', function () use ($POST_TYPE) {
    $TEXT_DOMAIN = __FILE__;

    $pt = array(
        'labels' => array(
            'name'          => __('People', $TEXT_DOMAIN),
            'singular_name' => __('Person', $TEXT_DOMAIN),
            'add_new'       => __('Add person', $TEXT_DOMAIN),
            'add_new_item'  => __('Add new person', $TEXT_DOMAIN),
            'edit_item'     => __('Edit person', $TEXT_DOMAIN),
        ),
        'description' => __('Persons', $TEXT_DOMAIN),
        'menu_icon' => 'dashicons-groups',
        'has_archive' => false,
        'supports' => array(
            // 'author',
            'editor',
            // 'title',
            // 'comments',
            // 'custom-fields',
            // 'trackbacks',
            // 'excerpt',
            // 'post-formats',
            // 'page-attributes',
            // 'thumbnail',
            'revisions',
        ),
        'hierarchical'         => false,
        'menu_position'        => 5,
        'show_in_admin_bar'    => true,
        'show_in_nav_menus'    => true,
        'show_ui'              => true,
        'public' => true,
        'rewrite' => false,
//        'rewrite' => array(
//            'slug' => 'people',
//        ),

        // 'capability_type' => $POST_TYPE,
        // 'map_meta_cap' => true, // use built-in map_meta_cap impl

//        'capabilities' => array(
//            'publish_posts'          => 'publish_' . $POST_TYPE . 's',
//            'edit_posts'             => 'edit_' . $POST_TYPE . 's',
//            'edit_others_posts'      => 'edit_others_' . $POST_TYPE . 's',
//            'edit_private_posts'     => 'edit_private_' . $POST_TYPE . 's',
//            'edit_published_posts'   => 'edit_published_' . $POST_TYPE . 's',
//            'delete_posts'           => 'delete_' . $POST_TYPE . 's',
//            'delete_others_posts'    => 'delete_others_' . $POST_TYPE . 's',
//            'delete_private_posts'   => 'delete_private_' . $POST_TYPE . 's',
//            'delete_published_posts' => 'delete_published_' . $POST_TYPE . 's',
//            'read_private_posts'     => 'read_private_' . $POST_TYPE . 's',
//            'edit_post'              => 'edit_' . $POST_TYPE,
//            'delete_post'            => 'delete_' . $POST_TYPE,
//            'read_post'              => 'read_' . $POST_TYPE,
//        ),
        'taxonomies' => array(
            $POST_TYPE . '_category',
            // 'question_tag',
        ),
    );
//    foreach (get_post_type_object($POST_TYPE)->cap as $cap) {
//        get_role('administrator')->add_cap($cap);
//    }

    $pt = apply_filters('register_post_type_' . $POST_TYPE . '_args', $pt);
    register_post_type($POST_TYPE, $pt);

    $rt = array(
        'public'            => true,
        'hierarchical'      => true, // categories are hierarchical
        'label'             => __('Categories'),
        'singular_label'    => __('Category'),
        'show_admin_column' => true,
        'rewrite'           => false,
//        'capabilities' => array(
//            'manage_terms' => 'manage_categories',
//            'edit_terms'   => 'manage_categories',
//            'delete_terms' => 'manage_categories',
//            'assign_terms' => 'edit_' . self::POST_TYPE . 's',
//        ),
    );

    $rt = apply_filters('register_taxonomy_' . $POST_TYPE . '_category_args', $rt);
    register_taxonomy($POST_TYPE . '_category', $POST_TYPE, $rt);
});



$x = function (WP_Post $post) use ($POST_TYPE) {
    if ($post->post_type !== $POST_TYPE) {
        return;
    }

    $first_name = get_post_meta($post->ID,'person_first_name', true);
    $last_name = get_post_meta($post->ID,'person_last_name', true);
    $position = get_post_meta($post->ID,'person_position', true);
?>
    <style>
        /* hide 'See updated post' */
        body.post-type-person .notice.updated a {
            display: none;
        }


        .person__name {
            display: flex;
            flex-flow: row wrap;
            margin-bottom: 10px;
        }
        .person__name label {
            display: block;
            flex: 1 0 100%;
        }
        .person__name input {
            position: relative;
            flex: 0 0 50%;
            box-sizing: border-box;
            margin: 0;
            padding: 10px 20px;
        }
        .person__name input:focus {
            z-index: 1;
        }
        .person__name input + input {
            flex: 0 0 50%;
        }

        .person_position {
            margin-bottom: 10px;
        }
        .person_position label {
            display: block;
        }
        .person_position input {
            box-sizing: border-box;
            margin: 0;
            padding: 10px 20px;
            display: block;
            width: 100%;
        }
    </style>
    <div class="person__name">
        <label for="person_first_name">Name:</label>
        <input type="text" name="person_first_name" placeholder="First name" id="person_first_name" value="<?php echo esc_attr($first_name) ?>" />
        <input type="text" name="person_last_name" placeholder="Last name" id="person_last_name" value="<?php echo esc_attr($last_name) ?>" />
    </div>
    <div class="person_position">
        <label for="person_position">Position:</label>
        <input type="text" name="person_position" placeholder="Position" id="person_position"  value="<?php echo esc_attr($position) ?>" />
    </div>
    <input type="hidden" name="post_edit_form_submit" value="1" />
<?php
};

add_action('edit_form_after_title', $x);

$y = function (WP_Post $post) use ($POST_TYPE) {
    if ($post->post_type !== $POST_TYPE) {
        return;
    }

    $entries = [
        'www'     => __('Website'),
        'email'   => __('Email address'),
        'phone'   => __('Phone'),
        'address' => __('Address'),
    ];

    $values = get_post_meta($post->ID, 'person__contact_info', true);
?>
    <style>
        /* Hide 'Show preview' button */
        /* When editing post hide block with preview button */
        body.post-php #minor-publishing-actions {
            display: none;
        }
        /* When creating post hide button only, because there is a 'Save draft' button there */
        body.post-new-php #post-preview {
            display: none;
        }

        .person__contact {
            margin: 20px 0;
        }
        .person__contact ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .person__contact li {
            display: flex;
            flex-flow: row nowrap;
        }
        .person__contact li div:first-child {
            flex: 0 0 150px;
        }
        .person__contact li div + div {
            flex: 1 1 auto;
        }
        .person__contact li input {
            display: block;
            width: 100%;
            padding: 10px;
        }
    </style>
    <div class="postbox person__contact">
        <div class="hndle"><h3><?php echo __('Contact info') ?></h3></div>
        <div class="inside">
            <ul id="person_contact_entries">
            <?php foreach ($entries as $entry => $label): ?>
                <li>
                    <div><?php echo esc_html($label) ?></div>
                    <div><input type="text" name="person__contact_info[<?php echo $entry ?>]" value="<?php echo @$values[$entry] ?>" /></div>
                </li>
            <?php endforeach ?>
            </ul>
            <div>You can use markdown syntax for rich-text content, multiple values or custom link descriptions</div>
        </div>
    </div>
<?php
};
add_action('edit_form_after_editor', $y);

add_action( 'add_meta_boxes', function () use ($POST_TYPE) {
    add_meta_box('photo', __('Photo'), function (WP_Post $post) {
        admin_image_field(array(
            'name' => 'person__photo',
            // 'value' => get_post_meta($post->ID, 'person__photo', true),
            'value' => get_post_thumbnail_id($post->ID),
        ));
    }, $POST_TYPE, 'side', 'default', 2);

    add_meta_box('lecture_speaker', __('Lecture speaker'), function (WP_Post $post) {
        person_select_field(array(
            'name'     => 'lecture_speaker',
            'multiple' => true,
            'sortable' => true,
            'value'    => get_post_meta($post->ID, 'lecture_speaker', true),
        ));
    }, $POST_TYPE, 'side', 'default', 2);

    $teams = get_posts(array(
        'numberposts'      => 0,
        'category'         => 0,
        'orderby'          => 'post_title',
        'order'            => 'ASC',
        'include'          => array(),
        'exclude'          => array(),
        'post_type'        => 'team',
        'suppress_filters' => true,
    ));

    if (count($teams)) {
        add_meta_box('team', __('Teams'),
            function () use ($POST_TYPE, $teams) {
                print_r($teams);
                ?>
                <?php
            },
            $POST_TYPE, 'side', 'default', null
        );
    }


});

add_filter('wp_insert_post_data', function (array $data, array $post_data) use ($POST_TYPE) {
    if (empty($data['post_type']) || empty($post_data['post_ID']) || $data['post_type'] !== $POST_TYPE) {
        return $data;
    }

    $post_id = $post_data['post_ID'];

    $first_name = trim($post_data['person_first_name']);
    $last_name = trim($post_data['person_last_name']);
    $position = trim($post_data['person_position']);

    update_post_meta($post_id, 'person_first_name', $first_name);
    update_post_meta($post_id, 'person_last_name', $last_name);
    update_post_meta($post_id, 'person_position', $position);

    $person_photo = (int) $post_data['person__photo'];
    // update_post_meta($post_id, 'person__photo', $person_photo);
    set_post_thumbnail($post_id, $person_photo);

    update_post_meta($post_id, 'person_www', trim($post_data['person__contact_info']['www']));
    update_post_meta($post_id, 'person_email', trim($post_data['person__contact_info']['email']));
    update_post_meta($post_id, 'person_phone', trim($post_data['person__contact_info']['phone']));
    update_post_meta($post_id, 'person_address', trim($post_data['person__contact_info']['address']));

    $person_contact_info = array();
    foreach (array('www', 'email', 'phone', 'address') as $type) {
        $value = isset($post_data['person__contact_info'][$type]) ? trim($post_data['person__contact_info'][$type]) : '';
        $person_contact_info[$type] = $value;
    }

    update_post_meta($post_id, 'person__contact_info', $person_contact_info);

    $title = join(' ', array_filter(compact('first_name', 'last_name'), 'strlen'));

    if (strlen($title)) {
        $data['post_title'] = $title;
    }

    if (empty($data['post_title'])) {
        $data['post_title'] = '(unnamed)';
    }

    $data['post_name'] = sanitize_title($data['post_title']);

    return $data;
} , 99, 2);

add_action("save_post_{$POST_TYPE}", function ($post_id, WP_Post $post) {
    if (empty($_POST['post_edit_form_submit'])) {
        return;
    }
}, 10, 2);

add_filter('admin_head', function () {
?>
<style>
.post-type-person .wp-list-table .column-person_thumbnail {
    width: 60px;
}
</style>
<?php
});

add_filter('manage_person_posts_columns', function (array $columns) {
    return array_slice($columns, 0, 1)
        + array('person_thumbnail' => __('Photo'))
        + array_slice($columns, 1);
}, 10, 1);

add_action('manage_person_posts_custom_column', function ($column, $post_id) {
    if ($column === 'person_thumbnail') {
        if (function_exists('get_the_post_thumbnail_url')) { // since 4.4.0
            $post_thumbnail = get_the_post_thumbnail_url((int) $post_id, 'post-thumbnail');
        } else {
            $img = wp_get_attachment_image_src((int) $post_id, 'post-thumbnail');
            $post_thumbnail = $img ? $img[0] : false;
        }

        if ($post_thumbnail) {
            echo '<img src="' . $post_thumbnail . '" style="width:48px;height:48px;" />';
        }
    }
}, 100, 2);
