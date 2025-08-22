<?php



namespace App\Helpers;



class Auth
{
    // URL base do Moodle (ajuste conforme necessário)
    private static $moodleUrl = 'http://localhost/moodle';

    // Faz login via REST API do Moodle e retorna o token ou false
    public static function login($username, $password)
    {
        $loginUrl = self::$moodleUrl . '/login/token.php';
        $postData = [
            'username' => $username,
            'password' => $password,
            'service' => 'moodle_mobile_app' // ou o nome do serviço configurado
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $loginUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return false;
        }

        $data = json_decode($response, true);

        if (isset($data['token'])) {
            // Garante que a sessão está iniciada
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            // Salva o token temporariamente para buscar userInfo
            $_SESSION['moodle_token'] = $data['token'];
            $userInfo = self::userInfo();
            if (!empty($userInfo) && !empty($userInfo['userissiteadmin'])) {
                $_SESSION['moodle']['moodle_token'] = $data['token'];
                $_SESSION['moodle']['sitename'] = $userInfo['sitename'] ?? '';
                $_SESSION['moodle']['username'] = $userInfo['username'] ?? '';
                $_SESSION['moodle']['firstname'] = $userInfo['firstname'] ?? '';
                $_SESSION['moodle']['lastname'] = $userInfo['lastname'] ?? '';
                $_SESSION['moodle']['fullname'] = $userInfo['fullname'] ?? '';
                $_SESSION['moodle']['lang'] = $userInfo['lang'] ?? '';
                $_SESSION['moodle']['userid'] = $userInfo['userid'] ?? '';
                $_SESSION['moodle']['siteurl'] = $userInfo['siteurl'] ?? '';
                $_SESSION['moodle']['userpictureurl'] = $userInfo['userpictureurl'] ?? '';
                $_SESSION['moodle']['userissiteadmin'] = $userInfo['userissiteadmin'] ?? '';
                return $data['token'];
            } else {
                unset($_SESSION['moodle_token']);
                return false; // Usuário não é administrador ou userInfo inválido
            }
        }
        return false; // Retorna false se não conseguiu obter o token
    }

    // Verifica se o usuário está autenticado (token na sessão)
    public static function checkLogin()
    {
        return isset($_SESSION['moodle_token']) && !empty($_SESSION['moodle_token']);
    }

    // Remove o token da sessão (logout)
    public static function logout()
    {
        // Remove todas as variáveis de sessão
        $_SESSION = [];
        // Se desejar destruir o cookie de sessão também:
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        // Destroi a sessão
        session_destroy();
    }

    // Retorna informações do usuário autenticado usando o token
    public static function userInfo()
    {
        if (!self::checkLogin()) {
            return null;
        }
        $token = $_SESSION['moodle_token'];
        $url = self::$moodleUrl . '/webservice/rest/server.php'
            . '?wstoken=' . $token
            . '&wsfunction=core_webservice_get_site_info'
            . '&moodlewsrestformat=json';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return null;
        }

        $data = json_decode($response, true);
        if (isset($data['exception'])) {
            return null;
        }
        return $data;
    }

    // Retorna o nome completo do usuário autenticado
    public static function fullname()
    {
        $info = self::userInfo();
        return $info['fullname'] ?? '';
    }

    // Retorna a URL do perfil do usuário autenticado
    public static function profileUrl()
    {
        $info = self::userInfo();
        return isset($info['userid']) ? self::$moodleUrl . '/user/profile.php?id=' . $info['userid'] : '';
    }

    // Retorna a URL da foto do usuário autenticado
    public static function userPictureUrl()
    {
        $info = self::userInfo();
        return $info['userpictureurl'] ?? '';
    }

    // Exemplo de verificação de permissão (ajuste conforme necessário)
    public static function verificarPermissaoAdministrador()
    {
        $info = self::userInfo();
        // Exemplo: checa se o usuário tem algum campo customizado de admin
        // Ajuste conforme sua lógica de permissões
        if (empty($info) || (isset($info['roles']) && !in_array('admin', array_column($info['roles'], 'shortname')))) {
            throw new \Exception("Falha de permissão. Apenas administradores podem criar cursos.");
        }
    }
}
