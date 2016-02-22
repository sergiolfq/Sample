<?php/*
*@description user controller
*@author Sergio Fuenmayor
*@date Oct/05/2015
*/

class Usuarios extends CI_Controller {

  public function __construct(){
    parent::__construct();
    
    $this->load->model('usuarios_model');
    $this->load->model('bitacora'  );
    $this->load->model('Banner');
    $this->load->library('session');
    $this->load->helper('url');   
    $this->load->library('Ajax_pagination');
    $this->perPage = 10;
    
    // Inicializar data
    $this->data = array ();
    $this->add_data('function', '');  

     $this->es_admin = false;        
  }

    function add_data($key, $value, $merge = FALSE, $separator = '.') {
            $this->data [$key] = $value;
        }


  function init(){
        // echo $this->session->userdata['logged_in']['rol'];die;
        if(isset($this->session->userdata['logged_in']['rol']) && $this->session->userdata['logged_in']['rol'] == 'admin'){
             // echo "kndlknkn";
            $this->es_admin = true;
          
        }else{
            $this->es_admin = false;
        }
        
        $this->add_data('es_admin', $this->es_admin);
    }
  
		/* register method*/
        public function register()
        {

          $this->init();

            $this->buscar_banners_registro();

            // Para menu
            $this->add_data('function', 'usuariosa');

            $data['function'] = 'registraru';
            $this->load->helper('form');
            $this->load->library('form_validation');


            // $data['title'] = 'Registrar nuevo usuario';
            $this->add_data('title','Registrar nuevo usuario' );

            $this->form_validation->set_rules('username', 'Username', 'required|is_unique[usuario.username]');
            $this->form_validation->set_rules('email', 'Email', 'required|is_unique[usuario.email]');
            $this->form_validation->set_rules('password', 'Contraseña', 'required|max_length[15]|min_length[6]|alpha_numeric');
            $this->form_validation->set_rules('cpassword', 'Confirmar contraseña', 'required|matches[password]|max_length[15]|min_length[6]|alpha_numeric');            


            if ($this->form_validation->run() === FALSE)  //  si no  pasa la validacion se vuelve 
            {
               // $this->load->view('templates/header', $data);
                $this->load->view('usuarios/registro', $this->data);
                //$this->load->view('templates/footer');
            }
            else
            {
             try{
                 $codigo=sha1($this->input->post('email').$this->input->post('username').date('s'));
                 $this->usuarios_model->set_usuarios($codigo);              // se registra el usuario 
                
                 if($this->db->affected_rows() > 0)                  // si se guardo entonces envia correo con codigo de activacion
                 {
                    $enlace=base_url().'index.php/usuarios/activar/'.$codigo;
                    
                    $asunto="Activación de cuenta";                 
                    $cuerpo=" Gracias por registrarse, para activar su cuenta hacer click en el siguiente enlace $enlace";
                    mail($this->input->post('email'),$asunto,$cuerpo);
                    // $data['mensaje']=$cuerpo;
                    $this->add_data('mensaje','Revise su cuenta de correo para activar su cuenta');

                    $this->load->view('usuarios/mensaje',$this->data);
                    //$this->load->view('templates/header');
                    $this->load->view('usuarios/success',$this->data);
                    //$this->load->view('templates/footer');
                 }
                 else
                 {
                     // $data['mensaje']='Error al Registrar usuario';
                     $this->add_data('mensaje','Error al Registrar usuario');
                     //$this->load->view('templates/header');
                     $this->load->view('usuarios/error',$this->data);
                     //$this->load->view('templates/footer');
                 }
                 
                 }
                 catch (Exception $ex) {
                     // $data['mensaje']='Error al Registrar usuario, vuelva a intentarlo ';
                     $this->add_data('mensaje','Error al Registrar usuario, vuelva a intentarlo ');
                     
                     //$this->load->view('templates/header');
                     $this->load->view('usuarios/error',$this->data);
                     //$this->load->view('templates/footer');
                 }
               }
         }
        
        
        /*  this method will change the status of an user to active and allow him to log in   */
        public function activar($codigo){
           $this->init();

            try{
            $data['function']='';

            $this->add_data('function','');
            $this->usuarios_model->get_validarCod($codigo);
            
            if($this->db->affected_rows() > 0){   // si consiguió el codido y actualizo su estatus
                $data['mensaje']="Usuario activado ya puede iniciar sesion en nuestro sitio";
                $this->add_data('mensaje','Usuario activado ya puede iniciar sesion en nuestro sitio');
            }else 
            {
                $data['mensaje']=" El codigo de activación ya fue utilizado o es invalido";  
                 $this->add_data('mensaje','El codigo de activación ya fue utilizado o es invalido');
            }
            
            $this->load->view('usuarios/activar',$this->data);
            }
            catch(Exception $exp){
 
                $data['mensaje']=" Problema al validar el código de activación, intetelo de nuevo";  
               
                $this->load->view('usuarios/activar',$this->data);
                
                
            }
        }
        
        
        public function login(){            
            if(isset($this->session->userdata['logged_in']))
            {
                redirect(base_url().'index.php/');
            }

             $this->init();

            // Para el menu
            $this->add_data('function', 'login');

            $this->buscar_banners();
            
            $this->load->helper('form');
            $this->load->library('form_validation');

            $data['title'] = 'login';
            $data['mensaje']= '';

            $this->add_data('title', 'login');
            $this->add_data('mensaje', '');



            $this->form_validation->set_rules('username', 'Nombre de usuario', 'required');
            $this->form_validation->set_rules('password', 'Contrasena', 'required');
            $enviado=$this->input->post('submit');
            
            if(!empty($enviado))
            {
                // $data['username']=$this->input->post('username');
                // $data['password']=$this->input->post('password');

                $this->add_data('username', $this->input->post('username'));
                $this->add_data('password', $this->input->post('password'));
                
            }else {
                     // $data['username']='';
                     // $data['password']='';

                    $this->add_data('username', '');
                    $this->add_data('password', '');
            }
                        
            if ($this->form_validation->run() === FALSE)
            {
              $this->init();
                //$this->load->view('templates/header', $data);
                $this->load->view('usuarios/login',$this->data);
                //$this->load->view('templates/footer');
            }
            else
            {
                $usuario=$this->usuarios_model->get_usuario($this->input->post('username'),$this->input->post('password'));

                // print_r($usuario);die;
                
                if(!empty($usuario))
                {  
                  $session_data['username']=$usuario['username'];
                  $session_data['id']=$usuario['id'];
                  $session_data['email']=$usuario['email'];
                  $session_data['rol']=$usuario['rol'];
                  $this->session->set_userdata('logged_in',$session_data);
                  // $data['mensaje']='usuario logeado con id '.$this->session->userdata['logged_in'] ['id'];

                  $this->add_data('mensaje', ''.$this->session->userdata['logged_in'] ['id']);
                  
                  $bitacora['usuario_id']=$this->session->userdata['logged_in'] ['id'];
                  $bitacora['accion']="Inicio de session";
                  $bitacora['fecha_hora']= date("Y-m-d H:i:s");
                  $this->bitacora->agregar($bitacora);       
                  $this->init();           
                  
                  $this->load->view('usuarios/logeado',$this->data);
                }
                else 
                {
                $data['mensaje']=" El usuario y contrasena no coinciden";  
                 $this->add_data('mensaje', " El usuario y contrasena no coinciden");
                  
                  $this->init();
               // $this->load->view('templates/header', $data);
                $this->load->view('usuarios/login',$this->data);
                //$this->load->view('templates/footer');
                }
           }
            
           }
            
        // Logout , removiendo la informacion de session 
        public function logout(){   
            
        if(!isset($this->session->userdata['logged_in']))
        {   
         redirect(base_url());
        }    

         $this->init(); 

        // Para el menu
        $this->add_data('function', 'logout');
            
        $this->buscar_banners();
        $session_data = array(
        'username' => '',
            'id'=>'',
            'rol'=>''
        );
               
        $bitacora['usuario_id']=$this->session->userdata['logged_in'] ['id'];
        $bitacora['accion']="cerrar sesion";
        $bitacora['fecha_hora']= date("Y-m-d H:i:s");
        $this->bitacora->agregar($bitacora); 
        
        $this->session->unset_userdata('logged_in', $session_data);
        $data['title']="logout";
        $this->add_data('title', "logout");

        $this->init();
        //$this->load->view('templates/header', $data);
        $this->load->view('usuarios/logout', $this->data);
        //$this->load->view('templates/footer');
        }
  
       /* admin method*/
        public function administrar(){

           $this->init();
           $this->add_data('function', 'usuariosa');   
           if($this->es_admin){
              $page = $this->input->post('page');
            if(!$page){
                $offset = 0;
            }else{
                $offset = $page;
            }
            
            $busqueda='';            
            if($this->input->post('buscar_user'))
            {
                $busqueda=$this->input->post('buscar_user');               
            }   
             $session_data['cadena']=$busqueda;
             $this->session->set_userdata('busqueda_user',$session_data);
            
            $data["function"]="admin";
            $data["title"]="Administrar Usuarios";
            $usuarios= $this->usuarios_model->listar(array('limit'=>$this->perPage,'buscar_user'=>$busqueda));
            $totalRec=count($this->usuarios_model->listar(array('buscar_user'=>$busqueda)));
            $data["usuarios"]=$usuarios;

            //$this->add_data('function', 'admin');
            $this->add_data('title', 'Administrar Usuarios');
            $this->add_data('usuarios', $usuarios);
            
            $config['div']         = 'lista_admin'; //parent div tag id
            $config['base_url']    = base_url().'index.php/usuarios/ajaxPaginationAdministrar';
            $config['total_rows']  = $totalRec;
            $config['per_page']    = $this->perPage;
            $config['additional_param']  = 'serialize_form()';   
            $this->ajax_pagination->initialize($config);
            
            
         
            $this->load->view("usuarios/listar",$this->data);
            
          }else{
              redirect(base_url());
          }
            
            
        }
        
        public function ajaxPaginationAdministrar(){
        
        $page = $this->input->post('page');
        if(!$page){
            $offset = 0;
        }else{
            $offset = $page;
        }
        
        
        $busqueda=$this->session->userdata['busqueda_user']['cadena'];

        //total rows count
        $totalRec = count($this->usuarios_model->listar(array('buscar_user'=>$busqueda)));
        
        //pagination configuration
        //   $config['first_link']  = 'First';
        $config['div']         = 'lista_admin'; //parent div tag id
        $config['base_url']    = base_url().'index.php/usuarios/ajaxPaginationAdministrar';
        $config['total_rows']  = $totalRec;
        $config['per_page']    = $this->perPage;
        
        $this->ajax_pagination->initialize($config);
        
        //get the posts data y agreegar la busqueda aqui tambieén y listo 
        $usuarios = $this->usuarios_model->listar(array('start'=>$offset,'limit'=>$this->perPage,'buscar_user'=>$busqueda));

        $data['usuarios']=$usuarios;     
              
        $this->load->view('usuarios/ajaxPaginationAdministrar', $data, false);
        
        
        }
        
        public function edit($id){

           $this->init();

          $this->add_data('function', 'usuariosa');
           $tiene_permiso = false;

           if($this->es_admin || ($id == $this->session->userdata['logged_in']['id'])){
              $tiene_permiso = true;
           }


           if($tiene_permiso){

            // agregar restincion de acceso 
            $usuario=$this->usuarios_model->get($id);
            if(count($usuario)>1){
                $data['id']=$usuario['id'];
                $data['username']=$usuario['username'];
                $data['email']=$usuario['email'];
                $data['estatus']=$usuario['estatus'];
                $data['function']="Editar usuario";

                $this->add_data('id',$usuario['id'] );
                $this->add_data('username',$usuario['username'] );
                $this->add_data('email',$usuario['email'] );
                $this->add_data('estatus',$usuario['estatus'] );
               // $this->add_data('function','Editar usuario' );
                $this->load->view('usuarios/edit',$this->data);
            }else{
                $data['mensaje']="usuario no encontrado";
                $this->add_data('mensaje','usuario no encontrado' );
                $this->load->view('usuarios/mensaje', $this->data);
   
            }
          }else{
              redirect(base_url());
          }
            
            
            
        }
        
        public function update(){

           $this->init();

           if($this->es_admin){
           $usuario['id']=$this->input->post('id');
            $usuario['username']=$this->input->post('username');
            $usuario['email']=$this->input->post('email');
            $usuario['estatus']=$this->input->post('estatus');
            $this->usuarios_model->update($usuario);
            $this->administrar();
  
          }else{
              redirect(base_url());
          } 
        }
        /* this method is aim to reasign user's password */
        public function reasignar(){

           //$this->init();

      //    if($this->es_admin){
            $correo=$this->input->post("correo");
            
            // acomodar mayusculas en el sistema que el sistema no las diferencie
            $usuario=$this->usuarios_model->get_correo($correo);
            $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);

            
            if($usuario){
                
            $this->usuarios_model->reasignar($correo,sha1($password));
            $mensaje=" Para ingresar a nuestro sistema debera usar los siguientes datos"
                    . " Usuario :".$usuario['username']. " Contraseña : ".$password;
            
            mail($correo," Reasignacion de Contraseña",$mensaje); 
            
            }
            $mostrar=" Si el correo proporcionado es correcto deberá recibir un mensaje con los datos de ingreso al sistema.";
            $this->load->view("usuarios/mensaje",array('mensaje'=> $mostrar, 'function'=>'reasignar','es_admin'=>false));
           
          /*}else{
              redirect(base_url());
          }*/
            
            
        }
        
        
        /* assign password */
        public function asignar_contrasena(){   
            
            // validar que este en su session y que sea de su usuario
              if(!isset($this->session->userdata['logged_in']))
            {
              redirect(base_url().'index.php/usuarios/login');
            }
            
             $this->init();
            
            if($this->input->post('contrasena')){
            
             $data['id']=$this->session->userdata['logged_in']['id'];
             $data['password']=sha1($this->input->post('contrasena'));
            $this->db->usuarios_model->update($data);
             redirect(base_url().'index.php/usuarios/administrar');
            }else {
           
            $this->load->view('usuarios/contrasena'); }
            
        }

 
        
            
} 
