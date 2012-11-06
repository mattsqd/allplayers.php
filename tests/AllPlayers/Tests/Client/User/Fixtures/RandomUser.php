<?php
namespace AllPlayers\Tests\Client\User\Fixtures;

use AllPlayers\Objects\User;

use DateTime;

/**
 * A random AllPlayers user object for testing.
 */
class RandomUser extends User {
  const CHILD = TRUE;

  /**
   * RandomUser Constructor
   *
   * @param string $child
   *   (optional) RandomUser::child Create a random child or NULL.
   *
   * @param string $rand
   *   (optional) Random string, use this if you want to control how the rand
   *     is generated or regenerate the same.
   */
  public function __construct($child = NULL, $validate = TRUE, $rand = NULL) {
    $rand = (isset($rand) && !empty($rand)) ?$rand: rand();

    $this->email = self::getRandomEmail($rand);
    $this->password = '123testing';
    $this->firstname = "Robot";
    $this->lastname = "#$rand";
    $this->validate = $validate;

    // Kill TZ errors.
    date_default_timezone_set(@date_default_timezone_get());

    if ($child == self::CHILD) {
      // Random child birthday.
      $this->birthday = new DateTime('@' . rand(strtotime('11 years ago'), strtotime('4 years ago')));
    }
    else {
      // Random adult birthday.
      $this->birthday = new DateTime('@' . rand(strtotime('60 years ago'), strtotime('21 years ago')));
    }
    $this->birthday = $this->birthday->format('Y-m-d');
    $this->gender = (rand(0, 1)) ? 'm' : 'f';
  }

  /**
   * getRandomEmail
   *
   * @param mixed $rand
   *   (optional) The random element placed in the email
   *
   */
  static public function getRandomEmail($rand = NULL) {
    $rand = (isset($rand) && !empty($rand)) ?$rand: rand();

    return "number$rand@example.com";
  }
}
