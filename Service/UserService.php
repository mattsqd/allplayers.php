<?php
namespace AllPlayers\Service;

use AllPlayers\Objects\User;

/**
 * Interact with AllPlayers via Client using User objects.
 *
 * @todo - Add _RUD methods.
 */
class UserService extends ClientService
{
    public function login(User $user)
    {
        $this->client->userLogin($user->email, $user->password);
    }

    /**
     * Create a user represented by User.
     *
     * @param User $user
     *   The user to create, with no UUID provided.
     * @return User
     *   The created user with UUID populated.
     */
    public function create(User $user)
    {
        $birthdate_formatted = $user->birthdate->format('Y-m-d') . 'T00:00:00';
        $gender_mf = $user->gender == 'Male' ? 'M' : 'F';
        $response = $this->client->userCreateUser(
            $user->first_name,
            $user->last_name,
            $user->email,
            $gender_mf,
            $birthdate_formatted,
            $user->password);
        $user->uuid = $response->uuid;
        return $user;
    }

    /**
     * Get a user by known properties.
     *
     * @param User $user
     *   An User object partially filled out. Supports the following
     *   filled in properties: email, uuid
     *
     * @return mixed
     *   Returns an array of stdClass objects with information about matching
     *   users.
     */
    public function get(User $user)
    {
        $options = array();
        if (!empty($user->uuid)) {
            return array($this->client->userGetUser($user->uuid));
        }
        if (!empty($user->email)) {
            $options['email'] = $user->email;
        }
        $response = $this->client->usersIndex($options);
        return $response;
    }
}
