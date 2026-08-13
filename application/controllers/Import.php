<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }

    public function images()
    {
        $imageFolder = FCPATH . "employee_images/";

        $imageMap = [

    1  => "Jigar_Shah.jpg",
    3  => "Priya_Sharma.jpg",
    4  => "Amit_Verma.jpg",
    6  => "Rohan_Mehta.jpg",
    7  => "Sneha_Joshi.jpg",
    8  => "Karan_Singh.jpg",
    9  => "Pooja_Shah.jpg",
    10 => "Vikas_Patel.jpg",
    11 => "Anjali_Desai.jpg",
    12 => "Nikhil_Sharma.jpg",
    13 => "Riya_Gupta.jpg",
    14 => "Arjun_Nair.jpg",
    15 => "Meera_Iyer.jpg",
    16 => "Sahil_Khan.jpg",
    17 => "Kavya_Rao.jpg",
    18 => "Deepak_Mishra.jpg",
    19 => "Isha_Malhotra.jpg",
    20 => "Yash_Kulkarni.jpg",
    25 => "Umang_Mehta.jpg",
    26 => "Dhananjay_Dube.jpg",
    27 => "Dhruv_Shetty.jpg",
    28 => "Sanket_Sawant.jpg"

];

        foreach($imageMap as $employeeId => $imageFile)
        {
            $imagePath = $imageFolder.$imageFile;

            if(file_exists($imagePath))
            {
                $imageData = file_get_contents($imagePath);

                $this->db->where('employee_id',$employeeId);

                $this->db->update(
                    'tbl_employee',
                    array(
                        'profile_image'=>$imageData
                    )
                );

                echo "Employee ".$employeeId." Imported<br>";
            }
            else
            {
                echo $imageFile." Not Found<br>";
            }
        }

        echo "<h2>Completed</h2>";
    }
}