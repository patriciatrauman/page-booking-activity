<?php
class Pra
{
  private static $prefixTable;
  private static $initiated = false;

  public static function init()
  {
    global $wpdb;
    self::$prefixTable = $wpdb->prefix . PRA_PREFIX_PLUGIN . '_';
    if (!self::$initiated) {
      self::$initiated = true;
    }
  }

  /**
   * Activate plugin
   *  creates tables and open admin link
   * @static
   */
  public static function plugin_activation()
  {
    if (!empty($_SERVER['SCRIPT_NAME']) && false !== strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/plugins.php')) {
      self::_create_tables_on_activation();
    }
  }

  public static function view($name, array $args = array())
  {
    $args = apply_filters(PRA_PREFIX_PLUGIN . '_view_arguments', $args, $name);

    foreach ($args as $key => $val) {
      $$key = $val;
    }

    $file = PRA_PLUGIN_DIR . 'views/' . $name . '.php';

    include($file);
  }

  /**
   * DROP all tables
   * @static
   */
  public static function plugin_deactivation()
  {
    global $wpdb;
    self::init();
    $tables_def =  self::$prefixTable . "page_definition";
    $tables_calendar =  self::$prefixTable . "calendar";
    $tables_booking =  self::$prefixTable . "booking";
    $sql_post = "DELETE  FROM " . $wpdb->prefix . "_posts where post_content like '[" . PRA_PREFIX_PLUGIN
      . "_page id=%' ";
    $sql_def = "DROP TABLE IF EXISTS $tables_def ";
    $sql_calendar = "DROP TABLE IF EXISTS $tables_calendar ";
    $sql_booking = "DROP TABLE IF EXISTS $tables_booking ";
    $wpdb->query($sql_post);
    $wpdb->query($sql_booking);
    $wpdb->query($sql_calendar);
    $wpdb->query($sql_def);
  }

  private static function _create_tables_on_activation()
  {
    global $wpdb;
    self::init();
    $tables_def =  self::$prefixTable . "page_definition";
    $tables_calendar =  self::$prefixTable . "calendar";
    $tables_booking =  self::$prefixTable . "booking";

    $sql_def = "CREATE TABLE $tables_def (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        description text NOT NULL,
        isActif BOOLEAN NOT NULL,
        params longtext NOT NULL,
        admins longtext NOT NULL,
        PRIMARY KEY  (id)
      )";

    $wpdb->query($sql_def);


    //Table de liens entre les calendrier et un event
    $sql_calendar = "CREATE TABLE $tables_calendar (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        eventId mediumint(9) NOT NULL,
        jour DATE NOT NULL,
        heure  VARCHAR(5) NOT NULL,
        nbParticipant  int NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (eventId)
        REFERENCES $tables_def(id)
        ON DELETE CASCADE
      ) ";
    $wpdb->query($sql_calendar);

    //Table de liens entre les calendrier et un event
    $sql_booking = "CREATE TABLE $tables_booking (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        eventCalendarId mediumint(9) NOT NULL,
        user_login varchar(60) NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (eventCalendarId)
        REFERENCES $tables_calendar(id)
        ON DELETE CASCADE
      );";
    $wpdb->query($sql_booking);
  }
}
