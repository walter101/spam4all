<?php

namespace App\dto;

use Assert\Assertion;
use Assert\AssertionFailedException;

class PersonDto
{
    private int $id;
    private string $firstName;
    private string $lastName;
    private string $streetName;
    private string $streetNumber;
    private string $zipcode;

    /**
     * PersonDto constructor.
     * @param object $data
     */
    private function __construct(object $data)
    {
        $this->id = $data->id;
        $this->firstName = $data->firstName;
        $this->lastName = $data->lastName;
        $this->streetName = $data->streetName;
        $this->streetNumber = $data->streetNumber;
        $this->zipcode = $data->zipcode;
    }

    /**
     * @param object $data
     * @return PersonDto
     * @throws AssertionFailedException
     */
    public static function create(object $data)
    {
        self::validate($data);
        return new self($data);
    }

    /**
     * @param object $data
     * @throws AssertionFailedException
     */
    public static function validate(object $data)
    {
        Assertion::integer($data->id);
        Assertion::string($data->firstName);
        Assertion::string($data->lastName);
        Assertion::string($data->streetName);
        Assertion::string($data->streetNumber);
        Assertion::string($data->zipcode);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getStreetName(): string
    {
        return $this->streetName;
    }

    /**
     * @return string
     */
    public function getStreetNumber(): string
    {
        return $this->streetNumber;
    }

    /**
     * @return string
     */
    public function getZipcode(): string
    {
        return $this->zipcode;
    }
}
