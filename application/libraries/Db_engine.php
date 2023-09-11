<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Db_engine
{
  //put your code here

  protected $db;
  private $sql;
  private $bind_marker;

  public $created;

  public function __construct()
  {
    $ci = &get_instance();
    $this->db = $ci->db;
    $this->bind_marker = $this->db->bind_marker;

    $this->created = date("Y-m-d H:i:s");
  }

  public function generate_datatables_input($filters = array(), $column_search = array(), $column_order = array(), $order = array(), $pagination = false)
  {

    if (isset($filters["search"]["value"]) && !empty($filters["search"]["value"])) {
      // remove null for global search
      $tmp = $column_search;
      $column_search = array();
      foreach ($tmp as $i => $v) {
        if ($v == null || empty($v)) continue;
        $column_search[] = $v;
      }

      $i = 0;
      foreach ($column_search as $item) {
        if ($i == 0) {
          $this->db->group_start();
          $this->db->like($item, $filters["search"]["value"]);
        } else {
          $this->db->or_like($item, $filters["search"]["value"]);
        }

        if (count($column_search) - 1 == $i) {
          $this->db->group_end();
        }
        $i++;
      }
    }

    $i = 0;
    foreach ($column_search as $index => $item) {
      if (isset($filters['columns'][$index])) {
        if (!empty($filters["columns"][$index]['search']['value'])) {
          $value = $filters["columns"][$index]['search']['value'];
          $this->db->like($item, trim($value));
        }
      }

      $i++;
    }

    if (isset($filters["order"])) {
      if (isset($column_order[$filters["order"][0]["column"]])) {
        $this->db->order_by($column_order[$filters["order"][0]["column"]], $filters["order"][0]["dir"]);
      }
    } else if (isset($order)) {
      $this->db->order_by(key($order), $order[key($order)]);
    }

    if ($pagination) {
      $start  = isset($filters["start"]) ? (int) $filters["start"] : 0;
      $length = isset($filters["length"]) ? (int) $filters["length"] : -1;

      if ($length != -1) $this->db->limit($length, $start);
    }
  }

  public function table_get_rows_count($table_name = "")
  {
    if (empty($table_name)) return 0;

    $sql = "
        select count(1) as count from $table_name
        ";
    $res = $this->query($sql);
    $res = $res[0];
    return $res["count"];
  }

  final protected function query($sql, $bind = false, $return_object = true)
  {
    $this->sql = $sql;
    if (is_array($bind) and count($bind) > 0) {
      $bind = $this->process_bind($bind);
    }
    $query = $this->db->query($this->sql, $bind, $return_object);
    return $query->result_array();
  }

  private function process_bind($bind)
  {
    $bindOrder = null;
    $bindList = null;

    $pattern = "/[^']:[A-Za-z0-9_]+[^']/";
    $preg = preg_match_all($pattern, $this->sql, $matches, PREG_OFFSET_CAPTURE);
    if ($preg !== 0 and $preg !== false) {
      foreach ($matches[0] as $key => $val) {
        $bindOrder[$key] = trim($val[0]);
      }
      foreach ($bindOrder as $field) {
        $this->sql = str_replace($field, $this->bind_marker, $this->sql);
        $bindList[] = $bind[$field];
      }
    } else {
      $bindList = $bind;
    }

    return $bindList;
  }

  function get_app_setting_list()
  {
    $this->db->select("*");
    $this->db->from("app_settings");
    $res = $this->db->get()->result_array();

    if (count($res) == 0) return $res;
    return $res[0];
  }

  public function GetCallingMethodName()
  {
    $e = new Exception();
    $trace = $e->getTrace();
    //position 0 would be the line that called this function so we ignore it
    $last_call = $trace[1];
    printr($last_call);
  }

  public function uuid_v3($name, $namespace = null)
  {
    if (is_null($namespace))
      $namespace = $this->uuid_v4();

    if (empty($name))
      return FALSE;

    if (!$this->is_valid($namespace))
      return FALSE;

    // Get hexadecimal components of namespace
    $nhex = str_replace(array('-', '{', '}'), '', $namespace);

    // Binary Value
    $nstr = '';

    // Convert Namespace UUID to bits
    for ($i = 0; $i < strlen($nhex); $i += 2) {
      $nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
    }

    // Calculate hash value
    $hash = md5($nstr . $name);

    return sprintf(
      '%08s-%04s-%04x-%04x-%12s',

      // 32 bits for "time_low"
      substr($hash, 0, 8),

      // 16 bits for "time_mid"
      substr($hash, 8, 4),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 3
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

      // 48 bits for "node"
      substr($hash, 20, 12)
    );
  }

  public function uuid_v4($trim = false)
  {

    $format = ($trim == false) ? '%04x%04x-%04x-%04x-%04x-%04x%04x%04x' : '%04x%04x%04x%04x%04x%04x%04x%04x';

    return sprintf(
      $format,

      // 32 bits for "time_low"
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff)
    );
  }

  public function uuid_v5($name, $namespace = null)
  {
    if (is_null($namespace))
      $namespace = $this->uuid_v4();

    if (empty($name))
      return FALSE;

    if (!$this->is_valid($namespace))
      return FALSE;

    // Get hexadecimal components of namespace
    $nhex = str_replace(array('-', '{', '}'), '', $namespace);

    // Binary Value
    $nstr = '';

    // Convert Namespace UUID to bits
    for ($i = 0; $i < strlen($nhex); $i += 2) {
      $nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
    }

    // Calculate hash value
    $hash = sha1($nstr . $name);

    return sprintf(
      '%08s-%04s-%04x-%04x-%12s',

      // 32 bits for "time_low"
      substr($hash, 0, 8),

      // 16 bits for "time_mid"
      substr($hash, 8, 4),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 5
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

      // 48 bits for "node"
      substr($hash, 20, 12)
    );
  }

  public function is_valid($uuid)
  {
    return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
  }
}
