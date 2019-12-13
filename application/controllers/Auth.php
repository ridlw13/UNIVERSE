<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->library('form_validation');
    }

    public function index(){
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('password','Password', 'trim|required');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'WPU Login Page';
            $this->load->view('template/auth_header', $data);
            $this->load->view('auth/login');
            $this->load->view('template/auth_footer');
        }else{
            $this->_login();
        }

    }

    private function _login(){
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();

        if($user){
            if ($user['is_active'] == 1) {
                if (password_verify($password, $user['password'])) {
                    $data =[
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    if ($user['role_id'] == 1) {
                        redirect('admin');
                    }else{
                        redirect('user');
                    }
                }else{
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Wrong Password!</div>');
                redirect('auth');
                }
            }else{
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">This email has not been activated</div>');
                redirect('auth');
            }
        }else{
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email is not registered!</div>');
            redirect('auth');
        }
    }

    public function registration(){

        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
            'is_unique' => "This email has already registered!"
        ]);

        $this->form_validation->set_rules('password','Password', 'required|trim|min_length[3]|matches[password2]',[
            'matches' => 'Password dont match!',
            'min_length'=>'Password to short!'
        ]);

        $this->form_validation->set_rules('password2' , 'Password2' , 'required|trim|matches[password]');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'WPU User Registration';
            $this->load->view('template/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('template/auth_footer');
        }else{
            $data = [
                'name' => htmlspecialchars($this->input->post('name',true)),
                'email' => htmlspecialchars($this->input->post('email',true)),
                'image' => 'default.png',
                'password'=>password_hash(htmlspecialchars($this->input->post('password')),PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 1,
                'date_created'=>time(),
            ];
            $this->db->insert('user',$data);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Congratulation! your account has been created. Please Login</div>');
            redirect('auth');

    }
} 
}