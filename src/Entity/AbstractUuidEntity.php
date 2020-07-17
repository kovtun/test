<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class UuidEntityAbstract
 */
abstract class AbstractUuidEntity
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     *
     * @Serializer\Groups({"default"})
     */
    protected $id;

    /**
     * @todo: too general
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id->toString();
    }
}
