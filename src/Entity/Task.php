<?php

namespace App\Entity;

use App\Entity\Constants\TaskStatuses;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Requestum\ApiBundle\Rest\Metadata\Reference;


/**
 * User
 *
 * @ORM\Table(name="task")
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 *
 */
class Task extends AbstractUuidEntity
{
    use TimestampableEntity;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Groups({"default"})
     * @Assert\Choice(callback={"App\Entity\Constants\TaskStatuses", "getStatuses"})
     */
    private $status = TaskStatuses::STATUS_CREATED;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Groups({"default"})
     * @Assert\Length(min=3,minMessage = "Your description must be at least {{ limit }} characters long",)
     */
    private $description = '';

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Task
     */
    public function setStatus(string $status): Task
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Task
     */
    public function setDescription(string $description): Task
    {
        $this->description = $description;

        return $this;
    }
}
