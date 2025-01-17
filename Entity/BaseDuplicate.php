<?php
declare(strict_types=1);

namespace DMK\DuplicateCheckBundle\Entity;

use DMK\DuplicateCheckBundle\Model\DuplicateInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class BaseDuplicate implements DuplicateInterface
{
    /**
     * @var integer
     *
     * @ORM\Id @ORM\Column(type="integer", name="id", options={"unsigned":true}) @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="class")
     */
    protected $class;

    /**
     * @var float
     *
     * @ORM\Column(type="smallint", length=3, name="weight")
     */
    protected $weight;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", name="object_id")
     */
    protected $objectId;

    /**
     * @var object|null
     */
    protected $object;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * BaseDuplicate constructor.
     *
     * @param object $object
     * @param int $id
     * @param float $weight
     */
    public function __construct($object, int $id, float $weight = 0.5)
    {
        $this->weight = $weight * 100;
        $this->class = ClassUtils::getClass($object);
        $this->object = $object;
        $this->objectId = $id;
        $this->createdAt = new \DateTime();
    }

    /**
     * Returns the internal database id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight(): float
    {
        return $this->weight / 100;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject()
    {
        return $this->object;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function onLoad(LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        $this->object = $em->find($this->class, $this->id);
    }
}
