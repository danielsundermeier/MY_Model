<?php if(! defined('BASEPATH')) exit('No direct script access allowed');

  /**
   * MY_Model
   * 
   * @author Daniel Sundermeier
   * 
   * Originally developed for www.serienguide.tv
   */

  class MY_Model extends CI_Model
  {
    /**
     * DB table name
     * @var string
     */
    protected $table_name = '';
    /**
     * Table primary field
     * @var string
     */
    protected $primary_key = 'id';
    /**
     * Value field for dropdown
     * @var string
     */
    protected $value_key = 'name';
    /**
     * Created at field for timestamps
     * @var string
     */
    protected $created_at_key = 'erstellt';
    /**
     * Updated at field for timestamp
     * @var string
     */
    protected $updated_at_key = 'bearbeitet';
    /**
     * Default order_by
     * @var string
     */
    protected $order_by = '';
    /**
     * Filter for $this->primary_key
     * @var string
     */
    protected $primary_filter = 'intval';
    /**
     * Soft delete enabled?
     * @var boolean
     */
    protected $soft_delete = FALSE;
    /**
     * Soft delete key
     * @var string
     */
    protected $soft_delete_key = 'deleted';
    /**
     * Validation rules for formvalidation library
     * @var array
     */
    protected $validation_rules = array();
    /**
     * Form validation error
     * @var array
     */
    protected $validation_error = array();
    /**
     * Skip validation?
     * @var boolean
     */
    protected $skip_validation = FALSE;
    /**
     * Form attribute array for form helper
     * @var array
     */
    protected $form = array();
    /**
     * Form data for repopulation
     * @var array
     */
    protected $form_data = array();
    /**
     * Insert, Update data 
     * @var array
     */
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
     * CRUD METHODS
     * -------------------------------------------------------------- */    

    /**
     * Deletes rows by IDs
     * 
     * @param array|int $ids
     * @return void
     */
    public function delete($ids)
    {
      $filter = $this->primary_filter;
      $ids = ! is_array($ids) ? array($ids) : $ids;
       
      foreach($ids as $id)
      {
        $id = $filter($id);
        if($id)
        {
          if($this->soft_delete)
          {
            $this->skip_validation()->update(array($this->soft_delete_key, TRUE), $id);
          }
          else
          {
            $this->db->where($this->primary_key, $id)->limit(1)->delete($this->table_name);
          }          
        }
      }
      
      return TRUE;
    }
    
    public function delete_by($where)
    {
      $ids = array();
      foreach($where as $w)
      {
        $ids[] = $this->exists($w, $this->primary_key);
      }
      
      $this->delete($ids);
    }
    
    /**
     * Gets one, some or all results
     * 
     * @example find(); => all results
     * @example find(1); => one result
     * @example find(array(1, 2)); => many results
     *
     * @param mixed array|string $ids
     * @return array
     */
    public function find($ids = FALSE)
    {      
      //Einzelner Eintrag? TRUE => kein Array; nicht FALSE
      $single = $ids == FALSE || is_array($ids) ? FALSE : TRUE;
      
      // Soft delete
      if($this->soft_delete)
      {
        $this->db->where($this->soft_delete_key, FALSE);
      }    
      
      // IDs
      if($ids !== FALSE)
      {
        // $ids in array
        is_array($ids) || $ids = array($ids);
        
        // Sanitize ids
        $filter = $this->primary_filter;
        $ids = array_map($filter, $ids);
        
        // set where
        $this->db->where_in($this->table_name.'.'.$this->primary_key, $ids);
      }

      // ORDER BY to defaul, if no other order_by is present
      count($this->db->ar_orderby) || $this->db->order_by($this->order_by);
      
      // FROM to default, if no other from is present
      count($this->db->ar_from) || $this->db->from($this->table_name);
      
      // Return $ergebnisse
      $single == FALSE || $this->db->limit(1);
      $method = $single ? 'row_array' : 'result_array';
      
      return $this->db->get()->$method();      
    }
    
    /**
     * Gets result by $where
     * 
     * @param mixed array|string $where
     * @param boolean $or_where
     * @param boolean $single
     * @return array
     */
    public function find_by($where, $or_where = FALSE, $single = FALSE)
    {
      if($this->soft_delete)
      {
        $this->db->where($this->soft_delete_key, FALSE);
      }
      
      $where = array_map('htmlentities', $where);
      $where_method = $or_where == TRUE ? 'or_where' : 'where';
      $this->db->$where_method($where);
     
      // ORDER BY to defaul, if no other order_by is present
      count($this->db->ar_orderby) || $this->db->order_by($this->order_by);
      
      // Return results
      $single == FALSE || $this->db->limit(1);
      $method = $single ? 'row_array' : 'result_array';
      
      return $this->db->get($this->table_name)->$method();
    }
    
    /**
     * Inserts row
     * 
     * @param array $data
     * @return mixed int|boolean
     */
    public function insert($data)
    {      
      $data = $this->trigger('before_create', $data);
     
      if(! is_array($data)) { return $data; }
      
      if(! empty($data))
      {
        if($this->validate($data) === FALSE)
        {
          return FALSE;
        }
        
        // Timstamp
        $this->db->set($this->created_at_key, date('Y-m-d H:i:s'));
        // This is an insert
        $this->db->set($data)->insert($this->table_name);
      }
      $this->data['id'] = $this->db->insert_id();
      
      $data = $this->trigger('after_create', $data);
      
      // Return the ID
      return $this->data['id'];
    }
    
    /**
     * Updates or inserts row
     * 
     * @param array $data
     * @param int $id
     * @return mixed int|boolean
     */
    public function save($data, $id = FALSE)
    {
      if($id)
      {
        $return = $this->update($data, $id);
      }
      else
      {
        $return = $this->insert($data);
      }
      
      return $return;
    }
    
    /**
     * Updates row
     * 
     * @param array $data
     * @param int $id
     * @return mixed int|boolean
     */
    public function update($data, $id)
    {
      // This is an update
      $data = $this->trigger('before_update', $data);
      
      $filter = $this->primary_filter;
      // Timestamp
      $this->db->set($this->updated_at_key, date('Y-m-d H:i:s'));
      // Update
      $this->db->set($data)->where($this->primary_key, $filter($id))->update($this->table_name);
      
      $this->data['id'] = $id;
      
      $data = $this->trigger('after_update', $data);
      
      // Return the ID
      return $id == FALSE ? $this->data['id'] : $id;
    }
    
    /* --------------------------------------------------------------
     * UTILITY METHODS
     * ------------------------------------------------------------ */
    
    /**
     * Gets a key value pair for dropdowns
     *
     * @param string $key
     * @param string $value
     * @param array $ids
     * @return array('key' => 'value')
     */
    public function dropdown($key = FALSE, $value = FALSE, $ids = FALSE)
    {
      if($key === FALSE)
      {
        $key = $this->primary_key;
      }
      if($value === FALSE)
      {
        $value = $this->value_key;
      }
      
      $this->db->select($key.', '.$value);
      
      if($ids)
      {
        $this->db->where_in($key, $ids);
      }
      
      $result = $this->find();
      
      // Turn results into key=>value pair array.
      $data = array();
      if(count($result) > 0)
      {      
        if($ids != FALSE && ! is_array($ids))
        {
          $result = array(
              $result
          );
        }
      
        foreach($result as $row)
        {
          $data[$row[$key]] = $row[$value];
        }
      }
      
      return $data;
    }
    
    /**
     * checks if row exists and returns $return value
     *
     * @param mixed $where
     * @param string $return default: 'id'
     * @return mixed
     */
    public function exists($where, $return = 'id')
    {
      $this->db->select($return, FALSE);
    
      if($result = $this->find_by($where, FALSE, TRUE))
      {
        return $result[$return];
      }
    
      return FALSE;
    }
    
    /**
     * Get Data from table
     */
    public function get()
    {
      
    }
    
    /**
     * Gets the form attributes for a key
     * 
     * @param string $key
     * @return array
     */
    public function get_form($key)  
    {
      return $this->form[$key];
    } 
    
    /**
     * Returns the data send via form
     * 
     * @return array
     */
    public function get_form_data()
    {
      return $this->form_data;
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

    public function validate($data)
    {
      if($this->skip_validation)    
      {
        return $data;
      }
      
      $this->form_data = $data;
      
      if(is_array($this->validate))    
      {
        $this->load->library('form_validation');

        unset($_POST);
      
        $rules = array();           
        // Set variables and rules
        foreach($data as $key => $val)     
        {
          $rules[] = $this->validate[$key];
          $_POST[$key] = $val;
        }
      
        $this->form_validation->set_rules($rules);
      
        if($this->form_validation->run() === TRUE)
        {
          return $data;
        }
        else
        {
          $this->session->set_userdata('validation_error', $this->form_validation->get_error_array());
     
          return FALSE;
        }
      }

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