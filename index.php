<?php

/*
 * Plugin Name: WP People Manager
 * Plugin URI:  https://github.com/xemlock/wp-people-manager
 * Description:
 * Version:     0.1.0
 * Author:      xemlock
 * Author URI:  https://github.com/xemlock
 */
$POST_TYPE = 'person';

add_action('init', function () use ($POST_TYPE) {
    $TEXT_DOMAIN = __FILE__;

    register_post_type($POST_TYPE, $pt = array(
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
        'rewrite' => array(
            'slug' => 'people',
        ),

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
            // 'person_category',
            // 'question_tag',
        ),
    ));
//    foreach (get_post_type_object($POST_TYPE)->cap as $cap) {
//        get_role('administrator')->add_cap($cap);
//    }
});



$x = function (WP_Post $post) use ($POST_TYPE) {
    if ($post->post_type !== $POST_TYPE) {
        return;
    }

    $first_name = get_post_meta($post->ID,'person__first_name', true);
    $last_name = get_post_meta($post->ID,'person__last_name', true);
    $position = get_post_meta($post->ID,'person__position', true);
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

        .person__position {
            margin-bottom: 10px;
        }
        .person__position label {
            display: block;
        }
        .person__position input {
            box-sizing: border-box;
            margin: 0;
            padding: 10px 20px;
            display: block;
            width: 100%;
        }
    </style>
    <div class="person__name">
        <label for="person__first_name">Name:</label>
        <input type="text" name="person__first_name" placeholder="First name" id="person__first_name" value="<?php echo esc_attr($first_name) ?>" />
        <input type="text" name="person__last_name" placeholder="Last name" id="person__last_name" value="<?php echo esc_attr($last_name) ?>" />
    </div>
    <div class="person__position">
        <label for="person__position">Position:</label>
        <input type="text" name="person__position" placeholder="Position" id="person__position"  value="<?php echo esc_attr($position) ?>" />
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
        #preview-action {
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
    require __DIR__ . '/admin_image_field.php';

    add_meta_box('photo', __('Photo'), function (WP_Post $post) {
        admin_image_field(array(
            'name' => 'person__photo',
            // 'value' => get_post_meta($post->ID, 'person__photo', true),
            'value' => get_post_thumbnail_id($post->ID),
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
    if ($data['post_type'] !== $POST_TYPE || empty($post_data['post_ID'])) {
        return $data;
    }

    $post_id = $post_data['post_ID'];

    $first_name = trim($post_data['person__first_name']);
    $last_name = trim($post_data['person__last_name']);
    $position = trim($post_data['person__position']);

    update_post_meta($post_id, 'person__first_name', $first_name);
    update_post_meta($post_id, 'person__last_name', $last_name);
    update_post_meta($post_id, 'person__position', $position);

    $person_photo = (int) $post_data['person__photo'];
    // update_post_meta($post_id, 'person__photo', $person_photo);
    set_post_thumbnail($post_id, $person_photo);

    $person_contact_info = array();
    foreach (array('www', 'email', 'phone', 'address') as $type) {
        $value = isset($post_data['person__contact_info'][$type]) ? trim($post_data['person__contact_info'][$type]) : '';
        $person_contact_info[$type] = $value;
    }

    update_post_meta($post_id, 'person__contact_info', $person_contact_info);

    $data['post_title'] = join(' ', array_filter(compact('first_name', 'last_name'), 'strlen'));

    if (empty($data['post_title'])) {
        $data['post_title'] = '(unnamed)';
    }

    return $data;
} , '99', 2);



add_action("save_post_{$POST_TYPE}", function ($post_id, WP_Post $post) {
    if (empty($_POST['post_edit_form_submit'])) {
        return;
    }
}, 10, 3);