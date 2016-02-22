<?php/*
*@description Model class for users 
*@author Sergio fuenmayor
*@date Oct/05/2015
*/
Class Usuarios_model extends CI_Model 
{
    
    public function __construct() {
        $this->load->database();
    }
    
    /*Creates a new user*/
    public function create_usuarios($codigo)
    {
        $this->load->helper('url');


        $data = array(
            'username' => $this->input->post('username'),
            'password' => sha1($this->input->post('password')),
            'email' => $this->input->post('email'),
            'codigo'=> $codigo,
            'estatus'=>0,
            'rol' => 'regular'

        );

        return $this->db->insert('usuario', $data);
    }
    /* checks if validation code exists in database*/
    public function get_validarCod($codigo)
    {
        $data['estatus']=1;        
        $this->db->where('codigo',$codigo);
        $this->db->where('estatus',0);
        
        return $this->db->update('usuario', $data);
    }
    
    /* update user information*/
    public function update($data){
        /*$data['id']=$this->input->post("id");
        $data['username']=$this->input->post("username");
        $data['email']=$this->input->post("email");
        $data['estatus']=$this->input->post("estatus");*/
        $this->db->where('id',$data['id']);
        return $this->db->update('usuario',$data);
    }
    
    
    
    /* check if user, password and status are correct to login*/ 
    public function get_usuario($username,$password)
    {
        $query = $this->db->get_where('usuario', array('username' => $username,'password'=>  sha1($password),'estatus'=>1));
        return $query->row_array();
    }
    
       public function get($id)
    {
        $query = $this->db->get_where('usuario',array('id'=>$id));
        return $query->row_array();
    }
    
           public function get_correo($correo)
    {
        $query = $this->db->get_where('usuario',array('email'=>$correo));
        return $query->row_array();
    }
    
	/* Provide a list of users */
    public function listar($params=array()){
        
        $this->db->select('usuario.*');
        $this->db->from('usuario');
        
        if(array_key_exists("buscar_user",$params)){
         $cadena=$params['buscar_user'];
         $this->db->like('UPPER(username)',  strtoupper($cadena));
         $this->db->or_like('UPPER(email)',  strtoupper($cadena));
       
        }
        
        
        if(array_key_exists("start",$params) && array_key_exists("limit",$params)){
            $this->db->limit($params['limit'],$params['start']);
        }elseif(!array_key_exists("start",$params) && array_key_exists("limit",$params)){
            $this->db->limit($params['limit']);
        }     
        
        $query=$this->db->get();
        return $query->result_array();
    }
   /* Method to reassign a new password */
    public function reasignar($correo,$contrasena){    
        
    $this->db->where('UPPER(email)',  strtoupper($correo));
    return $this->db->update('usuario',array('password'=>$contrasena));
        
    }

	/* search method */
    public function buscar(){
        $this->db->select('usuario.*');
        $this->db->from('usuario');

        $query=$this->db->get();
        return $query->result_array();
    }

    
}