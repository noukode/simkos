<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('User_model', 'user');
        checker_tagihan();
    }

    function login()
    {
        // if (is_login()) return redirect('dasbor');
        $data['judul_halaman'] = 'Login';
        $data['pesan'] = $this->session->flashdata('pesan');

        if ($data['pesan'] == 'berhasil_logout' or $data['pesan'] == 'berhasil_ubah_pass') {
            $this->session->sess_destroy();
        } else if (is_login()) {
            redirect(base_url('dasbor'));
        }

        $this->load->view('_partials/v_head_form', $data);
        $this->load->view('v_login');
        $this->load->view('_partials/v_preloader');
        $this->load->view('_partials/v_js_form');
    }

    function verify()
    {
        if (is_login()) return redirect('dasbor');
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        $user = $this->user->cek_login($username, $password);
        if ($user->num_rows() > 0) {
            $user = $user->row();
            if ($user->is_active == 0) {
                $this->session->set_flashdata('pesan', 'not_activated');
                redirect(base_url('login'));
            }
            $data_session = array(
                'nama' => $user->nama,
                'username' => $user->username,
                'status' => 'login',
                'role_id' => $user->role_id
            );
            $this->session->set_userdata($data_session);
            $this->session->set_flashdata('pesan', 'toastr.success("Selamat datang, Anda masuk sebagai ' . $username . '")');
            if ($user->role_id == 2) {
                return redirect('tagihan-penghuni');
            }
            redirect(base_url('dasbor'));
        } else {
            $this->session->set_flashdata('pesan', 'gagal_login');
            redirect(base_url('login'));
        }
    }

    function register()
    {
        if (is_login()) return redirect('dasbor');
        $this->load->helper(array('form', 'url'));

        $this->load->library('form_validation');
        $this->form_validation->set_rules([
            [
                'field' => 'username',
                'label' => 'Username',
                'rules' => 'required|is_unique[users.username]|min_length[5]|max_length[20]',
                'errors' => [
                    'min_length' => '{field} minimum 5 characters and maximum 20 characters',
                    'max_length' => '{field} minimum 5 characters and maximum 20 characters'
                ]
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|min_length[8]|max_length[50]',
                'errors' => [
                    'min_length' => '{field} minimum 8 characters and maximum 50 characters',
                    'max_length' => '{field} minimum 8 characters and maximum 50 characters'
                ]
            ],
            [
                'field' => 'no_kamar',
                'label' => 'Kamar',
                'rules' => 'required|is_natural_no_zero|is_unique[penghuni.no_kamar]',
            ],
            [
                'field' => 'confirm_password',
                'label' => 'Confirm Password',
                'rules' => 'matches[password]'
            ],
            [
                'field' => 'name',
                'label' => 'Full Name',
                'rules' => 'required',
            ],
            [
                'field' => 'nik',
                'label' => 'NIK',
                'rules' => 'required|is_natural',
            ],
            [
                'field' => 'telepon',
                'label' => 'Nomor telepon',
                'rules' => 'required|is_natural',
            ],
            [
                'field' => 'alamat',
                'label' => 'Alamat',
                'rules' => 'required|min_length[5]',
                'errors' => [
                    'min_length' => '{field} minimum 5 characters',
                ]
            ],
        ]);
        if ($this->form_validation->run()) {
            $post = [
                'id' => null,
                'nama' => $this->input->post('name'),
                'username' => $this->input->post('username'),
                'password' => sha1($this->input->post('password')),
                'nik' => $this->input->post('nik'),
                'telepon' => '62' . $this->input->post('telepon'),
                'alamat' => $this->input->post('alamat'),
                'role_id' => 2,
                'is_active' => 0
            ];
            $insert = $this->user->insert($post);
            $userId = $this->db->insert_id();

            $this->load->model('Penghuni_model', 'penghuni');

            $this->penghuni->tambah_penghuni([
                'no_kamar' => $this->input->post('no_kamar'),
                'user_id' => $userId,
                'tgl_masuk' => date('Y-m-d', time())
            ]);

            if ($insert) {
                $this->session->set_flashdata('message', 'Registrasi Berhasil');
                $this->session->set_flashdata('type', 'success');
            } else {
                $this->session->set_flashdata('message', 'Registrasi Gagal');
                $this->session->set_flashdata('type', 'danger');
            }
            return redirect('login');
        }
        $data['judul_halaman'] = 'Register';
        $data['pesan'] = $this->session->flashdata('pesan');
        $this->load->model('Kamar_model', 'kamar');
        $data['kamar'] = $this->kamar->getKamar('p.no_kamar IS NULL')->result();

        $this->load->view('_partials/v_head_form', $data);
        $this->load->view('v_register');
        $this->load->view('_partials/v_preloader');
        $this->load->view('_partials/v_js_form');
    }

    function logout()
    {
        $this->session->set_flashdata('pesan', 'berhasil_logout');
        redirect(base_url('login'));
    }
}
