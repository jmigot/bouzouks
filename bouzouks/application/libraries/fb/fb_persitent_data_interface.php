<?php
use Facebook\PersistentData\PersistentDataInterface;

class CIFacebookPersistentDataHandler implements PersistentDataInterface
{
  // Prefix to use for session variables.
  private $sessionPrefix = 'FBRLH_';
  
  // The main CodeIgniter instance
  private $CI;

  public function __construct()
  {
    $this->CI =& get_instance();
    // If you haven't already...
    //$this->CI->load->library('session');
  }
  
  /**
   * Retrieves a value from a session.
   */
  public function get($key)
  {
    return $this->CI->session->userdata($this->sessionPrefix.$key);
  }

  /**
   * Stores a value into a session.
   */
  public function set($key, $value)
  {
    $this->CI->session->set_userdata($this->sessionPrefix.$key, $value);
  }
}