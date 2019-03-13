<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Model extends CI_Model {

    public static $main_db;
    public static $minor_db;
    public function __construct()
    {
        parent::__construct();
        if(!self::$main_db){
            self::$main_db = $this->load->database('main',TRUE);
            self::$main_db->trans_strict(FALSE);
        }
        if(!self::$minor_db){
            self::$minor_db = $this->load->database('minor',TRUE);
            self::$minor_db->trans_strict(FALSE);
        }

    }
}