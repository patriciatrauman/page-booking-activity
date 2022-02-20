<?php
class Pra_Admin
{
  private static $prefixTable;
  private static $initiated = false;
  public static function init()
  {
    global $wpdb;
    self::$prefixTable = $wpdb->prefix . PRA_PREFIX_PLUGIN . '_';
    if (!self::$initiated) {
      self::init_hooks();
    }
  }
  public static function init_hooks()
  {
    self::$initiated = true;

    // add_action('admin_init', array('Pra_Admin', 'admin_init'));
    add_action('admin_menu', array('Pra_Admin', 'admin_menu'), 5);
    add_action('admin_enqueue_scripts', array('Pra_Admin', 'load_resources'));
  }
  public static function admin_menu()
  {
    $hook = add_pages_page(
      __('PRA', PRA_PREFIX_PLUGIN), //page_title
      __('Booking Activity', PRA_PREFIX_PLUGIN), //menu_title
      'manage_options', //capability
      PRA_PREFIX_PLUGIN . '-key-config', //menu_slug
      array('Pra_Admin', 'display_page') //function ??
    );
  }

  public static function display_page()
  {
    global $wp_roles;
    if (!is_user_logged_in()) {
      wp_redirect(get_home_url());
      exit;
    }
    $roles  =   $wp_roles->roles;
    $params = array(
      'roles' => $roles
    );
    if (isset($_GET['action']) && $_GET['action'] === 'save-activity-key') {
      $datas = self::get_data_activity_from_post();
      if (
        isset($_POST['action'])
        && $_POST['action'] == 'delete'
        && isset($_POST['activityId'])
        && trim($_POST['activityId']) !== ""
      ) {
        self::delete_page($_POST['activityId']);
      } else  if (
        isset($datas['name']) && isset($datas['action'])
        && in_array($datas['action'], array('create', 'update'))
      ) {
        self::save_page($datas);
      } else {
        $params['errors'] = $datas;
        $params['activity'] = $_POST;
      }
    }

    $allActivities  = self::get_activities();
    $params['activities'] = $allActivities;
    Pra::view('config', $params);
  }

  public static function get_page_url($page = 'config')
  {
    if (!is_user_logged_in()) {
      wp_redirect(get_home_url());
      exit;
    }

    $args = array('page' => PRA_PREFIX_PLUGIN . '-key-config');

    if ($page == 'saveActivity') {
      $args = array('page' => PRA_PREFIX_PLUGIN . '-key-config', 'view' => 'config', 'action' => 'save-activity-key');
    }

    $args['post_type'] = 'page';
    $url = add_query_arg($args,  admin_url('edit.php'));
    return $url;
  }

  public static function get_activities()
  {
    global $wpdb;
    $tables_def =  self::$prefixTable . "page_definition";
    $query = "SELECT def.id, def.name,  def.admins, def.params, def.description";
    $query .= " FROM $tables_def as def";
    $result = $wpdb->get_results($query);
    foreach ($result as $res) {
      $res->params = maybe_unserialize($res->params);
      $res->admins = maybe_unserialize($res->admins);
    }
    return $result;
  }

  public static function get_data_activity_from_post()
  {
    $data = array();
    $errors = array();
    if (isset($_POST['activityId']) && !empty(trim($_POST['activityId']))) {
      $data['id'] = $_POST['activityId'];
    }
    if (isset($_POST['action']) && !empty(trim($_POST['action']))) {
      $data['action'] = $_POST['action'];
    }
    if (isset($_POST['activityName']) && !empty(trim($_POST['activityName']))) {
      $data['name'] = $_POST['activityName'];
    } else {
      $errors[] = 'Titre est un champs obligatoire';
    }
    if (isset($_POST['activityDesc']) && !empty(trim($_POST['activityDesc']))) {
      $data['description'] = $_POST['activityDesc'];
    } elseif (isset($_POST['activityDesc'])) {
      $data['description'] = '';
    }
    if (isset($_POST['activityAdmin']) && is_array($_POST['activityAdmin'])) {
      if (count($_POST['activityAdmin']) > 0) {
        $data['admins'] = $_POST['activityAdmin'];
      } else {
        $errors[] = "Au moins un role d'administration est nécessaire";
      }
    } else {
      $errors[] = "Au moins un role d'administration est nécessaire";
    }

    return (count($errors) != 0) ? $errors : $data;
  }

  public static function save_page($datas)
  {
    global $wpdb;
    if (!is_user_logged_in()) {
      wp_redirect(get_home_url());
      exit;
    }

    //1st save def
    $tables_def =  self::$prefixTable . "page_definition";
    if (isset($datas['admins'])) {
      $admins = maybe_serialize($datas['admins']);
    } else {
      $admins = '';
    }
    $data = array(
      'name' => $datas['name'],
      'description' => $datas['description'],
      'admins' => $admins
    );
    if (isset($datas['id'])) {
      $result = $wpdb->update($tables_def, $data, array('id' => $datas['id']));
    } else {
      $result = $wpdb->insert($tables_def, $data);
      $activityId = $wpdb->insert_id;
      //then create post
      if ($activityId !== false) {
        $user            = wp_get_current_user();
        $post_data = array(
          'post_content' => "[" . PRA_PREFIX_PLUGIN . "_page id='$activityId']",
          'post_title' => $datas['name'],
          'post_name' => PRA_PREFIX_PLUGIN . '_page',
          'post_parent' => 0,
          'post_type' => 'page',
          'post_author' => $user->ID,
          'post_category' => array(PRA_PREFIX_PLUGIN . '_page'),
          'post_status' => 'publish'
        );
        $post_data = wp_slash($post_data);

        $post_ID = wp_insert_post($post_data);
      }
    }
    return $result;
  }
  public static function delete_page($id)
  {
    global $wpdb;
    if (!is_user_logged_in()) {
      wp_redirect(get_home_url());
      exit;
    }
    error_log('ready to delete');

    $tables_def =  self::$prefixTable . "page_definition";
    $result = $wpdb->delete($tables_def, array('id' => $id));
    error_log(' deleted ...');
    $sql_post = "DELETE  FROM " . $wpdb->prefix . "posts where post_content = '[" . PRA_PREFIX_PLUGIN . "_page id=''" . $id . "'']' ";
    error_log($sql_post);
    $wpdb->query($sql_post);
    error_log($result);
    return $result;
  }

  public static function load_resources()
  {
    wp_register_style(PRA_PREFIX_PLUGIN . 'Admin.css', plugin_dir_url(__FILE__) . '_inc/' . PRA_PREFIX_PLUGIN . 'Admin.css');
    wp_enqueue_style(PRA_PREFIX_PLUGIN . 'Admin.css');

    wp_register_script(PRA_PREFIX_PLUGIN . 'Admin.js', plugin_dir_url(__FILE__) . '_inc/' . PRA_PREFIX_PLUGIN . 'Admin.js');
    wp_enqueue_script(PRA_PREFIX_PLUGIN . 'Admin.js');
  }
}
