<?php

namespace App\Controllers;

use App\Models\AccessoriesModel;
use App\Libraries\Auth as LibrariesAuth;



class MstAccessories extends BaseController
{
    function __construct()
    {
        date_default_timezone_set("Asia/Jakarta");
        $this->auth = new LibrariesAuth;
        $this->auth->routeAccess();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        $this->url = base_url("mstaccessories");
    }

    public function index()
    {
        $data["title"] = 'Accessories';
        $data["folder"] = ['Master', 'Accessories'];
        $data["content"] = "content/mstaccessories";
        $data["info"] = $this->session->getFlashdata('info'); //$data;
        return view('index', $data);
    }

    public function ajax()
    {
        $model = new AccessoriesModel();
        $list = $model->get_datatables();
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $rows) {
            $button = '
            <div class="btn-group">
            <a class="btn btn-danger btn-xs" onClick="return confirm(' . "'hapus data ini?'" . ');" href="' . $this->url . '/delete/' . trim(base64_encode($rows->id), '=') . '' . '" title="View"><i class="fa fa-trash"></i></a>
                <a  class="btn btn-primary btn-xs" href="' . $this->url . '/edit/' . trim(base64_encode($rows->id), '=') . '' . '"><i class="fa fa-edit"></i></a>
            </div>
            ';
            $no++;
            $row   = array();
            $row[] = $no;
            $row[] = $rows->accessoriesid;
            $row[] = $rows->satuan;
            $row[] = $rows->qty;
            $row[] = $rows->description;
            $row[] = $button;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $model->count_all(),
            "recordsFiltered" => $model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function create()
    {
        helper(['form', 'url']);
        // lakukan validasi
        $rules = [
            'accessoriesid' => 'required',
            'satuan' => 'required',
            'qty' => 'required',
            'description' => 'required',
        ];

        $isDataValid = false;
        if (isset($_POST["cancel"])) {
            $this->session->setFlashdata('info', "input batal");
            return redirect()->to("mstaccessories");
        }

        if (isset($_POST["submit"])) {
            $isDataValid = $this->validate($rules);
        }

        if ($isDataValid) {
            // $photo = $this->request->getFile('photo');
            // $newName = '';
            // if ($photo->getName() != '') {
            //     $newName = $photo->getRandomName();
            //     $photo->move('uploads/bidan/', $newName);
            // }

            $data = [
                "accessoriesid" => $this->request->getPost('accessoriesid'),
                "satuan" => $this->request->getPost('satuan'),
                "qty" => $this->request->getPost('qty'),
                "description" => $this->request->getPost('description'),
            ];

            $mst = $this->db->table("mstaccessories");
            $mst->insert($data);
            $this->session->setFlashdata('info', "data berhasil disimpan");
            return redirect()->to("mstaccessories");
        } else {
            // $this->session->setFlashdata('info', "data gagal disimpan");
            // return redirect()->to("mstcolor");
        }


        // tampilkan form create
        $data["title"] = 'Tambah Accessories';
        $data["folder"] = ['Master', 'Accessories', 'Create'];
        $data["content"] = "content/mstaccessories_create";
        // $data["validation"] = $validation;
        $data["validation"] = $this->validator;


        return view('index', $data);
    }

    public function edit()
    {
        if ($this->request->uri->getSegment(4) === FALSE) {
            return redirect();
        } else {
            $id = base64_decode($this->request->uri->getSegment(3));

            // tampilkan form create
            $mst = $this->db->table("mstaccessories");
            $mst->select("*");
            $mst->where("id = '$id'");
            $accessories = $mst->get()->getRow();
            if ($accessories) {
                $data["title"] = 'Edit Accessories';
                $data["folder"] = ['Master', 'Accessories', "Edit"];
                $data["content"] = "content/mstaccessories_edit";
                $data["accessories"] = $accessories;
                $data["validation"] = $this->validator;
                $data["id"] = $id;

                return view('index', $data);
            }
            $this->session->setFlashdata('info', "data not found");
            return redirect()->to("mstaccessories");
        }
    }

    public function save_edit()
    {
        helper(['form', 'url']);

        // lakukan validasi
        $rules = [
            'satuan' => 'required',
            'qty' => 'required',
            'description' => 'required',
        ];


        $isDataValid = false;

        if (isset($_POST["cancel"])) {
            $this->session->setFlashdata('info', "edit batal");
            return redirect()->to("mstaccessories");
        }

        if (isset($_POST["submit"])) {
            $isDataValid = $this->validate($rules);
        }
        $id =  $this->request->getPost('fid');
        if ($isDataValid) {
            // $photo = $this->request->getFile('photo');
            // $photoname = $this->request->getPost('oldPhotoName');
            // if ($photo->getName() != '') {
            //     $path_to_file = '../uploads/bidan/' . $photoname;
            //     if (file_exists($path_to_file)) {
            //         unlink($path_to_file);
            //     }
            //     $photoname = $photo->getRandomName();
            //     $photo->move('uploads/bidan/', $photoname);
            // }
            $data = [
                "satuan" => $this->request->getPost('satuan'),
                "qty" => $this->request->getPost('qty'),
                "description" => $this->request->getPost('description'),
            ];

            $mst = $this->db->table("mstaccessories");
            $mst->where('id', $id);
            $mst->update($data);
            $this->session->setFlashdata('info', "data berhasil disimpan");
            return redirect()->to("mstaccessories");
        } else {

            $mst = $this->db->table("mstaccessories");
            $mst->select("*");
            $mst->where("id = '$id'");
            $accessories = $mst->get()->getRow();
            if ($accessories) {
                $data["title"] = 'Edit accessories';
                $data["folder"] = ['Master', 'Accessories', "Edit"];
                $data["content"] = "content/mstaccessories_edit";
                $data["accessories"] = $accessories;
                $data["validation"] = $this->validator;
                $data["id"] = $id;

                return view('index', $data);
            }
        }

        $this->session->setFlashdata('info', "data not found");
        return redirect()->to("mstaccessories");
    }


    public function delete()
    {
        if ($this->request->uri->getSegment(4) === FALSE) {
            return redirect();
        } else {
            $id = base64_decode($this->request->uri->getSegment(3));
            $data = [
                "is_deleted" => 1,
                "deleted_on" => date("Y-m-d h:i:s")
            ];
            $mst = $this->db->table("mstaccessories");
            $mst->where('id', $id);
            $mst->update($data);
            $this->session->setFlashdata('info', "data berhasil dihapus");
            return redirect()->to($this->url);
        }
    }
}
