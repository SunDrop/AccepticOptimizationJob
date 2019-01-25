<?php

namespace Acceptic\Publisher;

/**
 * Class Publisher
 * @package Publisher
 * Publisher stub
 */
class Publisher
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $email;

    /**
     * Stub(!)
     * @param $id
     * @return Publisher
     */
    public static function getById($id)
    {
        return new self();
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}