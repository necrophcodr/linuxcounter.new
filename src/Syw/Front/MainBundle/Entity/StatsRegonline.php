<?php

namespace Syw\Front\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatsRegonline
 *
 * @ORM\Table(name="stats_regonline", indexes={@ORM\Index(name="stamp", columns={"stamp"})})
 * @ORM\Entity
 */
class StatsRegonline
{
    /**
     * @var integer
     *
     * @ORM\Column(name="stamp", type="integer", nullable=false)
     */
    private $stamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="num", type="integer", nullable=false)
     */
    private $num;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set stamp
     *
     * @param integer $stamp
     * @return StatsRegonline
     */
    public function setStamp($stamp)
    {
        $this->stamp = $stamp;

        return $this;
    }

    /**
     * Get stamp
     *
     * @return integer 
     */
    public function getStamp()
    {
        return $this->stamp;
    }

    /**
     * Set num
     *
     * @param integer $num
     * @return StatsRegonline
     */
    public function setNum($num)
    {
        $this->num = $num;

        return $this;
    }

    /**
     * Get num
     *
     * @return integer 
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
