#MY_Model
Codeigniter MY_Model to extend the CI_Model
* CRUD Function
* Validation
* Form Attributes
* Dropdown for <select>
* Soft delete
* Callbacks/Observers

###Help to improve this Code and contribute!

##Synopsis

    class Post_model extends MY_Model 
    { 
      protected $table_name = 'post';

      public function get($id=FALSE)
      {
        $this->db->select('author.name, post.text, post.heading');
        $this->db->join('author', 'author.id = post.author_id');
        ...
        return this->find();
      }
    }

    $this->load->model('Post_model', 'post');

get all posts

    $this->post->find();
	
get one post: ID = 1

    $this->post->find(1);
	
get more posts: IDs = 1,2,3

    $this->post->find(array(1,2,3));

##Installation/Usage
Download the folder and drag it into your application folder.

Extend your model classes from *MY_Model* and all the functionality will be baked in automatically.

##Callbacks
    
	protected $before_create = array('prep_data', 'add_author');
    ...
    protected function prep_data($data) {}
    protected function add_author($data) {}

protected $soft_delete = TRUE;

optional:    

    protected $soft_delete_key = 'deleted_status';

##Built-in Observers

change in MY_Model.php

    protected $created_at_key = 'created_at';
    
	protected $updated_at_key = 'updated_at';