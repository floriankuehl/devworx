<?php

namespace Devworx\Models;

/**
 * User Model for table user
 */

class User extends AbstractModel
{
  /**
   * login of the User
   * 
   * @var string $login
   */
  protected $login = '';

  /**
   * name of the User
   * 
   * @var string $name
   */
  protected $name = '';

  /**
   * salutation of the User
   * 
   * @var string $salutation
   */
  protected $salutation = '';

  /**
   * firstName of the User
   * 
   * @var string $firstName
   */
  protected $firstName = '';

  /**
   * lastName of the User
   * 
   * @var string $lastName
   */
  protected $lastName = '';

  /**
   * address of the User
   * 
   * @var string $address
   */
  protected $address = '';

  /**
   * address2 of the User
   * 
   * @var string $address2
   */
  protected $address2 = '';

  /**
   * zip of the User
   * 
   * @var string $zip
   */
  protected $zip = '';

  /**
   * city of the User
   * 
   * @var string $city
   */
  protected $city = '';

  /**
   * country of the User
   * 
   * @var string $country
   */
  protected $country = '';

  /**
   * email of the User
   * 
   * @var string $email
   */
  protected $email = '';

  /**
   * tel of the User
   * 
   * @var string $tel
   */
  protected $tel = '';

  /**
   * lastLogin of the User
   * 
   * @var \DateTime $lastLogin
   */
  protected $lastLogin = null;


  /**
   * Gets the Users login
   * 
   * @return string
   */
  public function getLogin(): string {
    return $this->login;
  }

  /**
   * Sets the Users login
   * 
   * @param string $value
   * @return void
   */
  public function setLogin(string $value): void {
    $this->login = $value;
  }

  /**
   * Gets the Users name
   * 
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Sets the Users name
   * 
   * @param string $value
   * @return void
   */
  public function setName(string $value): void {
    $this->name = $value;
  }

  /**
   * Gets the Users salutation
   * 
   * @return string
   */
  public function getSalutation(): string {
    return $this->salutation;
  }

  /**
   * Sets the Users salutation
   * 
   * @param string $value
   * @return void
   */
  public function setSalutation(string $value): void {
    $this->salutation = $value;
  }

  /**
   * Gets the Users firstName
   * 
   * @return string
   */
  public function getFirstName(): string {
    return $this->firstName;
  }

  /**
   * Sets the Users firstName
   * 
   * @param string $value
   * @return void
   */
  public function setFirstName(string $value): void {
    $this->firstName = $value;
  }

  /**
   * Gets the Users lastName
   * 
   * @return string
   */
  public function getLastName(): string {
    return $this->lastName;
  }

  /**
   * Sets the Users lastName
   * 
   * @param string $value
   * @return void
   */
  public function setLastName(string $value): void {
    $this->lastName = $value;
  }

  /**
   * Gets the Users address
   * 
   * @return string
   */
  public function getAddress(): string {
    return $this->address;
  }

  /**
   * Sets the Users address
   * 
   * @param string $value
   * @return void
   */
  public function setAddress(string $value): void {
    $this->address = $value;
  }

  /**
   * Gets the Users address2
   * 
   * @return string
   */
  public function getAddress2(): string {
    return $this->address2;
  }

  /**
   * Sets the Users address2
   * 
   * @param string $value
   * @return void
   */
  public function setAddress2(string $value): void {
    $this->address2 = $value;
  }

  /**
   * Gets the Users zip
   * 
   * @return string
   */
  public function getZip(): string {
    return $this->zip;
  }

  /**
   * Sets the Users zip
   * 
   * @param string $value
   * @return void
   */
  public function setZip(string $value): void {
    $this->zip = $value;
  }

  /**
   * Gets the Users city
   * 
   * @return string
   */
  public function getCity(): string {
    return $this->city;
  }

  /**
   * Sets the Users city
   * 
   * @param string $value
   * @return void
   */
  public function setCity(string $value): void {
    $this->city = $value;
  }

  /**
   * Gets the Users country
   * 
   * @return string
   */
  public function getCountry(): string {
    return $this->country;
  }

  /**
   * Sets the Users country
   * 
   * @param string $value
   * @return void
   */
  public function setCountry(string $value): void {
    $this->country = $value;
  }

  /**
   * Gets the Users email
   * 
   * @return string
   */
  public function getEmail(): string {
    return $this->email;
  }

  /**
   * Sets the Users email
   * 
   * @param string $value
   * @return void
   */
  public function setEmail(string $value): void {
    $this->email = $value;
  }

  /**
   * Gets the Users tel
   * 
   * @return string
   */
  public function getTel(): string {
    return $this->tel;
  }

  /**
   * Sets the Users tel
   * 
   * @param string $value
   * @return void
   */
  public function setTel(string $value): void {
    $this->tel = $value;
  }

  /**
   * Gets the Users lastLogin
   * 
   * @return \DateTime
   */
  public function getLastLogin(): ?\DateTime {
    return $this->lastLogin;
  }

  /**
   * Sets the Users lastLogin
   * 
   * @param string $value
   * @return void
   */
  public function setLastLogin(?string $value): void {
    $this->lastLogin = new \DateTime($value);
  }

}

?>