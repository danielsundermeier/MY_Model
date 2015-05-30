<?php if(! defined('BASEPATH')) exit('No direct script access allowed');

  /**
   * MY_Model
   * 
   * @author Daniel Sundermeier
   */

  class MY_Model extends CI_Model
  {
    protected $table_name = '';
    protected $primary_key = 'id';
    protected $order_by = 'name DESC';

    protected $soft_delete = FALSE;
    protected $soft_delete_key = 'deleted';
    
    protected $validation_rules = array();
    protected $validation_error = array();
    protected $skip_validation = FALSE;

    protected $form = array();

    protected $data;
    
    /**
    * The various callbacks available to the model. Each are 
    * simple lists of method names (methods will be run on $this).
    */    
    protected $before_create = array();
    protected $after_create = array();
    protected $before_update = array();
    protected $after_update = array();
    protected $before_get = array();
    protected $after_get = array();
    protected $before_delete = array();
    protected $after_delete = array();
    protected $callback_parameters = array();

    /* --------------------------------------------------------------
     * GENERIC METHODS
     * -------------------------------------------------------------- */
    
    public function __construct()    
    {
      parent::__construct();
    }
    
    /* --------------------------------------------------------------
     * CRUD MERHODS
     * -------------------------------------------------------------- */    
    
    /**
    * checks if row exists and returns $return value
    *
    * @param string $key
    * @param mixed $val
    * @param string $return
    * @return mixed
    */
    public function exists($key, $val = FALSE, $return = 'id')
    {
      $this->db->select($return, FALSE);

      if($result = $this->get_by($key, $val, FALSE, TRUE))
      {
        return $result[$return];
      }
    
      return FALSE;    
    }
    
    public function find($ids = FALSE)
    {
      
    }
    
    public function find_by()
    {
      
    }
    
    public function get()
    {
      
    }
    
    public function insert()
    {
      
    }
    
    public function save()
    {
      
    }
    
    public function update()
    {
      
    }
    
    /* --------------------------------------------------------------
     * UTILITY METHODS
     * ------------------------------------------------------------ */
    
    public function dropdown()
    {
      
    }
    
    public function get_form($key)  
    {
      return $this->form[$key];
    } 
    
    public function get_validation_error()
    {
      return $this->validation_error;
    }
    
    /* --------------------------------------------------------------
     * VALIDATION METHODS
     * -------------------------------------------------------------- */
    
    /**
     * Skips validation before insert() or update()
     */    
    public function skip_validation()
    {
      $this->skip_validation = TRUE;
      
      return $this;
    }

    public function validate()
    {
      
    }
    
    /* --------------------------------------------------------------
     * INTERNAL METHODS
     * -------------------------------------------------------------- */
    
    protected function trigger($event, $data = FALSE, $last = TRUE)
    {
      if(isset($this->$event) && is_array($this->$event))
      {
        foreach($this->$event as $method)
        {
          if(strpos($method, '('))
          {
            preg_match('/([a-zA-Z0-9\_\-]+)(\(([a-zA-Z0-9\_\-\., ]+)\))?/', $method, $matches);
    
            $method = $matches[1];
            $this->callback_parameters = explode(',', $matches[3]);
          }
          $data = call_user_func_array(array($this, $method), array($data, $last));
        }
      }
    
      return $data;
    }
    
  }