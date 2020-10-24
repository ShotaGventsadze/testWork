<?php
    session_start();

    class Customer
    {
        public $conn;
        public $db_hostname;
        public $db_username;
        public $db_password;
        public $db_dbname;
        public $no_img; // თუ ფოტოს ატვირთვის გარეშე დარეგისტრირდა ვანიჭებ default img
        public $upload_path; //  ატვირთული ფაილების საქაღალდე

        public $pars;


        public function __construct ()
        {
            $this->no_img = "noimg.png";
            $this->upload_path = $_SERVER['DOCUMENT_ROOT'] . '/credo/uploads/';

            $this->conn = new mysqli(
                $this->db_hostname = "",
                $this->db_username = "",
                $this->db_password = "",
                $this->db_dbname = "test_credo"
            );
            if ($this->conn->connect_error) {
                die("კავშირის პრობლემა ბაზასთან: " . $this->conn->connect_error);
            }

        }


        /**
         * შესაბამისი მეთოდის გამოძახება კლიენტის მოთხოვნის საფუძველზე
         *
         * @param null $method
         * @param null $parameters
         */
        public function post ($method = null, $parameters = null)
        {
            $this->pars = $parameters;
            echo $this->$method();
        }

        /**
         * ავტორიზაცია
         *
         * @param user * required
         * @param password * required
         *
         * @return false|string
         */

        public function Login ()
        {


            if (!isset($this->pars['user'], $this->pars['password'])) {
                return json_encode(array('status' => 'fail', 'data' => $this->pars, 'comment' => 'შეუვსებელი პარამეტრი/_ები'));
            }
            $this->pars['password'] = md5($this->pars['password']);


            $sql = "SELECT ID,USERNAME,FULLNAME,PHOTO FROM credo_users WHERE USERNAME=? AND PWD=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $this->pars['user'], $this->pars['password']);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $this->conn->close();

            if (count($data) !== 1) {
                return json_encode(
                    array(
                        'status' => 'fail',
                        'data' => $data,
                        'comment' => 'მსგავსი მომხმარბელი ვერ მოიძებნა'
                    )
                );
            }

            if ($data[0]['PHOTO'] === "") {
                $data[0]['PHOTO'] = $this->no_img;
            }

            $_SESSION['USER_DATA'] = [
                "user_id" => $data[0]['ID'],
                "user" => $data[0]['USERNAME'],
                "fullname" => $data[0]['FULLNAME'],
                "img" => $data[0]['PHOTO'],
            ];

            return json_encode(
                array(
                    "status" => "success",
                    'data' => "",
                    'comment' => 'წარმატებული ავტორიზაცია'
                )
            );

        }

        /**
         * მომხმარებლის რეგისტრაცია
         *
         * @param user * required
         * @param password * required
         * @param img optional
         * @param fullname  optional
         *
         * @return false|string
         */

        public function Register ()
        {
            if (empty($this->pars['user']) || empty($this->pars['password'])) {
                return json_encode(array('status' => 'fail', 'data' => $this->pars, 'comment' => 'შეუვსებელი პარამეტრი/_ები'));
            }

            $sql = "SELECT USERNAME FROM credo_users WHERE USERNAME=?";
            $stmt = $this->conn->prepare($sql);

            $stmt->bind_param("s", $this->pars['user']);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);

            if (count($data) > 0) {
                return json_encode(
                    array(
                        "status" => "fail",
                        'data' => "",
                        'comment' => 'მსგავსი მომხმარებელი უკვე რეგისტრირებულია'
                    )
                );
            }

            $this->pars['password'] = md5($this->pars['password']);
            $ip = $this->get_client_ip();
            $image = mt_rand() . 'png';
            if (empty($this->pars['img'])) {
                $this->pars['img'] = "noimg.png";
            }

            try {
                // Start transaction
                $this->conn->begin_transaction();
                $sql = "INSERT INTO credo_users (USERNAME,PWD,PHOTO,FULLNAME,REGISTRED_IP) VALUES (?,?,?,?,?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssss", $this->pars['user'], $this->pars['password'], $image, $this->pars['fullname'], $ip);
                $stmt->execute();
                $last_id = $stmt->insert_id;
                // Commit changes
                $this->conn->commit();
                return json_encode(
                    array(
                        "status" => "success",
                        'data' => $last_id,
                        'comment' => 'რეგისტრაცია დასრულდა წარამატებით'
                    )
                );

            } catch (\Throwable $e) {
                $this->conn->rollback();
                return json_encode(
                    array(
                        "status" => "fail",
                        'data' => "",
                        'comment' => 'ტექნიკური შეფერხება ცადეთ რეგისტრაციის გავლა თავიდან'
                    )
                );
            }

        }

        /**
         * პროფილის რედაქტირება
         * @return bool|string
         */
        public function UpdateProfile ()
        {
            if (!isset($_SESSION['USER_DATA'])) {
                return json_encode(array('status' => 'fail', 'data' => "UserSessionExpired", 'comment' => ''));
            }
            if (empty($this->pars['fullname']) && empty($this->pars['img'])) {
                return json_encode(array('status' => 'fail', 'data' => [], 'comment' => 'შეუვსებელი პარამეტრი/_ები'));
            }

            $user_id = $_SESSION['USER_DATA']['user_id'];

            $image = $this->pars['img'];
            if (!empty($image)) {
                $image = $this->upload_image($this->pars['img']);
                if (!empty($image)) {
                    $sql = "SELECT PHOTO FROM credo_users WHERE ID=?";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $data = $result->fetch_all(MYSQLI_ASSOC);
                    $oldImage = $this->upload_path . $data[0]['PHOTO'];
                    unlink($oldImage);
                    $_SESSION['USER_DATA']['img'] = $image;
                }
            }

            try {
                // Start transaction
                $this->conn->begin_transaction();
                $sql = "UPDATE credo_users SET PHOTO=?, FULLNAME=? WHERE  id=?";

                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssi", $image, $this->pars['fullname'], $user_id);
                $stmt->execute();
                $_SESSION['USER_DATA']['fullname'] = $this->pars['fullname'];
                // Commit changes
                $this->conn->commit();
                $this->conn->close();
                return json_encode(
                    array(
                        "status" => "success",
                        'data' => $image,
                        'comment' => 'თქვენი ინფორმაცია რედაქტირებულია'
                    )
                );
                return true;

            } catch (\Throwable $e) {
                $this->conn->rollback();
                return json_encode(
                    array(
                        "status" => "fail",
                        'data' => "",
                        'comment' => 'ტექნიკური შეფერხება ცადეთ თავიდან'
                    )
                );
            }


        }


        // კლასის დამხარე მეთოდები

        /**
         * მომხმარებლის IP მისამართის გაგება
         * @return mixed
         */
        public function get_client_ip ()
        {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            return $ip;
        }

        /**
         * სურათის ატვირთვა ფაილ-სერვერზე
         *
         * @param $data
         *
         * @return string
         */
        public function upload_image ($data)
        {
            // file upload ->start
            $base64string = $data;
            $file_parts = explode(";base64,", $base64string);
            $base64data = base64_decode($file_parts[1], true);
            $file_path = $this->upload_path;
//            return 7777;

            $fileName = time();
            $file = $file_path . $fileName . '.png';
            file_put_contents($file, $base64data); // storing data
            // file upload ->end
            return $fileName . '.png';
        }

        /**
         * არა საჭირო სურათის წაშლა სერვერიდან
         *
         * @param $data
         *
         * @return bool
         */


    }

?>