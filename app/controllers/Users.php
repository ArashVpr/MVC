<?php    

class Users extends AbstractController {
    private $userModel;
    private $commentModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->commentModel = $this->model('Comment');
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $errors = $this->validateInputs($_POST, 'register');
            // dd($errors);
            if(!empty($errors)) {
                $this->render('register', []);
                return;
            } else {

                $username = htmlspecialchars($_POST['username']);
                $email = htmlspecialchars($_POST['email']);
                $password = htmlspecialchars($_POST['password']);
                $pwdhash = password_hash($password, PASSWORD_DEFAULT);

                if ($this->userModel->register([
                            'nom'=> $username,
                            'email'=> $email,
                            'password'=> $pwdhash
                            ])) {
                                redirect('users/login');    
                            } else {
                                $errors;
                                $this->render('register', []);
                            }
                }
    } else {
        $this->render('register', []);
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'email' => htmlspecialchars(trim($_POST['email'])),
                'password' => trim($_POST['password']),
            ];

            $errors = $this->validateInputs($data, 'login');
            if(!empty($errors)) {
                
                $this->render('login', []);
                return;
            }
            else {
                $user = $this->userModel->authenticate($data['email'], $data['password']);
                // dd($user);
                if($user) {
                $this->startSession($user);
                redirect('posts/index');
                } else {
                    $errors;
                    $this->render('login', []);
                }
            }
        } else {
            $this->render('login', []);
        }
    }

    public function logout(){
        session_destroy();
        redirect('users/login');
    }

    // refac inputs======================================
    // with switch case
    public function validateInputs($data, $type) {
        $errors = [];
        switch ($type) {
            case 'register':
                if (empty(trim($data['username']))) {
                    $errors['flashName'] = 'Veuillez saisir un nom';
                }
                if (empty(trim($data['email']))) {
                    $errors['flashEmail'] = 'Veuillez saisir un email';
                } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['flashEmail'] = 'Veuillez saisir un email valide';
                } elseif ($this->userModel->findUserByEmail($data['email'])) {
                    $errors['flashEmail'] = 'Cet email est déjà utilisé';
                }
                if (empty(trim($data['password']))) {
                    $errors['flashPassword'] = 'Veuillez saisir un mot de passe';
                }
                if (empty(trim($data['confirm_password']))) {
                    $errors['flashConfirm'] = 'Veuillez confirmer votre mot de passe';
                }
                if (!empty($data['password']) && trim($data['password']) !== trim($data['confirm_password'])) {
                    $errors['flashConfirm2'] = 'Les mots de passe ne sont pas identiques';
                }
                break;
    
            case 'login':
                if (empty(trim($data['email']))) {
                    $errors['flashEmail'] = 'Veuillez saisir un email';
                } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['flashEmail'] = 'Veuillez saisir un email valide';
                } elseif (!$this->userModel->findUserByEmail($data['email'])) {
                    $errors['flashEmail'] = "Cet email n'existe pas";
                }
                if (empty(trim($data['password']))) {
                    $errors['flashPassword'] = 'Veuillez saisir un mot de passe';
                }
                elseif (!$this->userModel->authenticate($data['email'], $data['password'])) {
                    $errors['flashPassword'] = 'Mot de passe incorrect';
                }
                break;

            case 'comment':
                // add
                if (empty($data['comment'])) {
                    $errors['flashCommentFail'] =  'Veuillez entrer un commentaire';
                } else {
                    if ($this->commentModel->addComment($data)) {
                        $errors['flashComment'] =  'Commentaire envoyé avec succès';
                    } else {
                        $errors['flashComment'] =  "Erreur lors de l'ajout du commentaire";
                    }
                }
                // update
                if(empty($data['comment'])){
                    $errors['flashCommentFail'] =  "Le commentaire est vide";
                } else {
                    if($this->commentModel->updateComment($data)){
                        $errors['flashComment'] =  "Le commentaire a bien été ajouté";
                    } else {
                        $errors['flashCommentFail'] =  "Erreur lors de la modification";
                    }                
                }
                // delete
                // if($this->commentModel->deleteComment($idComment)){
                //     flash('flashComment','Le commentaire a bien été supprimé', 'alert alert-success');
                // } else {
                //     flash('flashCommentFail','Erreur lors de la suppression', 'alert alert-danger');
                // }
                break;
            }
            foreach ($errors as $name => $msg) {
                flash($name, $msg, 'alert alert-danger');
            }
        return $errors;
}
    // refac sessions
    public function startSession($user) { 
            $_SESSION['user_mail'] = $user -> email;
            $_SESSION['user_id'] = $user -> id;
            $_SESSION['username'] = $user -> nom;
        }
}
