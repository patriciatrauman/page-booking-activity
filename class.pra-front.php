<?php
class Pra_Front
{
  private static $prefixTable;

  public static function display_user_pages_front($args)
  {
    global $wp_roles, $wp, $wpdb;
    if (!is_user_logged_in()) {
      wp_redirect(get_home_url());
      exit;
    }

    extract(shortcode_atts(
      array(
        'id' => 1
      ),
      $args
    ));
    self::$prefixTable = $wpdb->prefix . PRA_PREFIX_PLUGIN . '_';
    self::load_resources();


    if (
      isset($_POST)
      && isset($_POST['eventId'])
      && isset($_POST['timeCal'])
      && isset($_POST['dateCal'])
    ) {
      $warning = self::add_date_to_calendar();
    }
    if (
      isset($_POST)
      && isset($_POST['idEvent'])
      && isset($_POST['booking'])
    ) {
      $warning = self::save_booking();
    }
    if (
      isset($_POST)
      && isset($_POST['eventId'])
      && isset($_POST['description'])
    ) {
      $warning = self::update_page_desc();
    }
    if (
      isset($_POST)
      && isset($_POST['action'])
      && $_POST['action'] == 'delete'
      && isset($_POST['eventId'])
      && isset($_POST['calId'])
    ) {
      $warning = self::delete_calendar_Id();
    }


    $roles  =   $wp_roles->roles;
    $params = array(
      'roles' => $roles
    );

    $event = self::get_activity_infos($id);
    $eventsToBook = self::get_activity_infos_items($id);
    $isAdminUser = self::is_admin($event->admins);
    $params['postId'] = get_the_ID();
    $params['event'] = $event;
    $params['rq'] = $wp->request;
    $params['calendar'] = $eventsToBook;
    $params['isUserAdmin'] = $isAdminUser;
    return Pra::view('front', $params);
  }
  public static function load_resources()
  {
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Material+Icons:ital,wght@0,300;0,400;0,700;1,400&family=Neuton:ital,wght@0,300;0,400;0,700;1,400&display=swap', [], null);

    wp_register_script(PRA_PREFIX_PLUGIN . 'Front.js', plugin_dir_url(__FILE__) . '_inc/' . PRA_PREFIX_PLUGIN . 'Front.js');
    wp_enqueue_script(PRA_PREFIX_PLUGIN . 'Front.js');
    wp_register_style(PRA_PREFIX_PLUGIN . '.css', plugin_dir_url(__FILE__) . '_inc/' . PRA_PREFIX_PLUGIN . '.css');
    wp_enqueue_style(PRA_PREFIX_PLUGIN . '.css');
  }

  public static  function is_admin($adminRoles)
  {
    $user = wp_get_current_user();
    $isAdmin = false;
    $caps = array_map('strtolower', array_keys($user->caps));
    $adminRoles = array_map('strtolower', $adminRoles);
    foreach ($caps as $userCap) {
      if (in_array($userCap, $adminRoles)) {
        $isAdmin = true;
        break;
      }
    }
    return $isAdmin;
  }

  public static function get_activity_infos_items($idEvent)
  {
    global $wpdb;
    $tables_cal =  self::$prefixTable . "calendar";
    $tables_booking =  self::$prefixTable . "booking";
    $user            = wp_get_current_user();
    $wpdb->query("SET lc_time_names = 'fr_FR';");
    $query = "SELECT cal.*,  DATE_FORMAT(cal.jour, '%W %d %M ') as jour,  cal.jour as jourTech  ";
    $query .= " , cal.heure as heureStart";
    $query .= " , cal.nbParticipant as nbPersonne";
    $query .= " , book.id as booked";
    $query .= " , nb as nbBooked";
    $query .= " , users";
    $query .= " FROM $tables_cal as cal";
    $query .= " LEFT JOIN $tables_booking as book on cal.id = book.eventCalendarId and book.user_login = '$user->ID'";
    $query .= " LEFT JOIN (select eventCalendarId, count(1) as nb, GROUP_CONCAT(user_login,',')  as users from $tables_booking group by eventCalendarId  )  AS bookingStatus";
    $query .= " ON cal.id = bookingStatus.eventCalendarId";
    $query .= " WHERE eventId = $idEvent";
    $query .= " and jour BETWEEN CURRENT_TIMESTAMP and CURRENT_TIMESTAMP + INTERVAL 1 MONTH ";
    $query .= " ORDER by jour asc ";
    $result = $wpdb->get_results($query);

    foreach ($result as $key => $val) {
      if (!is_null($val->users)) {
        $users = explode(',',  $val->users);
        $val->users = array();
        for ($i = 0; $i < count($users); $i++) {
          $user = get_user_by('ID', $users[$i]);
          if (isset($user->display_name)) {
            $val->users[] = $user->display_name;
          }
        }
      }
    }

    return $result;
  }

  public static function get_activity_infos($idEvent)
  {
    global $wpdb;
    $tables_def =  self::$prefixTable . "page_definition";
    $query = "SELECT def.id, def.name,  def.admins, def.params, def.description";
    $query .= " FROM $tables_def as def";
    $query .= " WHERE def.id = $idEvent";
    $result = $wpdb->get_results($query);
    foreach ($result as $res) {
      $res->admins = maybe_unserialize($res->admins);
      $res->params = maybe_unserialize($res->params);
    }
    return $result[0];
  }

  public static function update_page_desc()
  {
    global $wpdb;
    $tables_def =  self::$prefixTable . "page_definition";
    $desc = $_POST['description'];
    $eventId = $_POST['eventId'];
    $result = $wpdb->update($tables_def, array('description' => $desc), array('id' => $eventId));
    return $result;
  }

  public static function delete_calendar_Id()
  {
    global $wpdb;
    $calId = $_POST['calId'];
    $tables_booking = self::$prefixTable . "booking";
    $tables_cal = self::$prefixTable . "calendar";

    $delete_query = "DELETE $tables_booking FROM $tables_booking WHERE eventCalendarId = " . $calId;
    $result = $wpdb->query($delete_query);
    $delete_query = "DELETE $tables_cal FROM $tables_cal WHERE id = " . $calId;
    $result = $wpdb->query($delete_query);
    return $result;
  }

  public static function save_booking()
  {
    global $wpdb;
    $user = wp_get_current_user();

    $tables_booking = self::$prefixTable . "booking";
    $tables_cal = self::$prefixTable . "calendar";

    $delete_query = "DELETE $tables_booking
      FROM $tables_booking
      INNER JOIN $tables_cal ON $tables_booking.eventCalendarId = $tables_cal.id
      WHERE $tables_cal.eventId = " . $_POST['idEvent'] . "
      AND $tables_booking.user_login = " . $user->ID . "
      AND $tables_cal.jour BETWEEN CURRENT_TIMESTAMP and CURRENT_TIMESTAMP + INTERVAL 1 MONTH";
    $wpdb->query($delete_query);

    foreach ($_POST['booking'] as $key => $val) {
      $data = array(
        'eventCalendarId' => $key,
        'user_login' => $user->ID
      );
      $wpdb->insert($tables_booking, $data);
    }
  }
  public static function add_date_to_calendar()
  {
    global $wpdb;
    $postId = $_POST['postId'];
    $calId = $_POST['idCal'];
    $action = $_POST['action'];
    $eventId = $_POST['eventId'];
    $timeCal = $_POST['timeCal'];
    $dateCal = $_POST['dateCal'];
    $nbParticipant = $_POST['nbCal'];

    //1st save def
    $tables_cal = self::$prefixTable . "calendar";
    $data = array(
      'eventId' => $eventId,
      'heure' => $timeCal,
      'jour' => $dateCal,
      'nbParticipant' => $nbParticipant
    );
    if ($action == 'update') {
      $result = $wpdb->update($tables_cal, $data, array('id' => $calId));
    } else {
      $result = $wpdb->insert($tables_cal, $data);
    }

    return array('result' => $result);
  }
}
