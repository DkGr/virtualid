<?php

require_once __DIR__ . '/../lib/openfire.php';
require_once __DIR__ . '/../class/User.php';
require_once __DIR__ . '/../class/PrivacyController.php';

/**
 * Description of UserController
 *
 * @author padman
 */
class UserController {
    
    public function authorize()
    {
        if(isset($_SESSION['user']))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Gets the user by id or current user
     *
     * @url GET /users/current
     * @url GET /users/$id
     */
    public function getUser($id = null)
    {
        if ($id != null) {
            $user = PrivacyController::pleaseShowMeUserInformation($_SESSION['user']['_id'], $id);
        } else {
            $_SESSION['user'] = PrivacyController::pleaseShowMeUserInformation($_SESSION['user']['_id'], $_SESSION['user']['_id']);
            $user = $_SESSION['user'];
        }
        return $user;
    }
    
    /**
     * Gets the user posts by id or current user
     *
     * @url GET /users/$id/posts
     * @url GET /users/current/posts
     */
    public function getUserPosts($id = null)
    {
        if ($id != "current") {
            $posts = PrivacyController::pleaseShowMeUserPosts((string)$_SESSION['user']['_id'], $id);
        } else {
            $posts = PrivacyController::pleaseShowMeUserPosts((string)$_SESSION['user']['_id'], (string)$_SESSION['user']['_id']);
        }
        return $posts;
    }

    /**
     * Saves a user to the database
     *
     * @noAuth
     * @url POST /users
     */
    public function saveUser($data)
    {
        $obj = new User();
        $user = $obj->saveNew($data);
        if($user)
        {
            return $user; // returning the newly created user object
        }
        else{
            return array('error' => "Nom d'utilisateur indisponible");
        }
    }
    
    /**
     * Check and return friend status between authenticated user and another user
     *
     * @url GET /friends/status/$id
     */
    public function isMyFriend($id)
    {
        $obj = new User();
        $obj->setId((string)$_SESSION['user']['_id']);
        $obj2 = new User();
        $obj2->setId($id);
        $friendStatus = 'none';
        if(!$obj->IsMyFriend($id)){
            if($obj->IsAskedFriend($id)){
                $friendStatus = 'asked';
            }
            elseif ($obj2->IsAskedFriend((string)$_SESSION['user']['_id'])) {
                $friendStatus = 'acceptation';
            }
        } else {
            $friendStatus = 'friend';
        }
        return array('friendStatus' => $friendStatus);
    }
    
    /**
     * Update friend status between two users
     *
     * @url POST /friends/add
     */
    public function addFriend($data)
    {
        $user = new User();
        $user->setId((string)$_SESSION['user']['_id']);
        $user->AddFriend($data->{'userid'});
        $friendAdded = new User();
        $friendAdded->setId($data->{'userid'});
        $OpenfireAPI = new Gidkom\OpenFireRestApi\OpenFireRestApi;

        // Set the required config parameters
        $OpenfireAPI->secret = "m8D6vTN7L0QVwUq4";
        $OpenfireAPI->host = "octeau.fr";
        $OpenfireAPI->port = "9091";  // default 9090

        // Optional parameters (showing default values)
        $OpenfireAPI->useSSL = true;
        $OpenfireAPI->plugin = "/plugins/restapi/v1";  // plugin

        SetFriends($OpenfireAPI, $user->getUsername(), $friendAdded->getUsername());
    }
}