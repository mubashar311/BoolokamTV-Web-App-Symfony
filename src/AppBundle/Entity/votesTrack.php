<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Version
 *
 * @ORM\Table(name="votes_track")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VotesTrackRepository")
 */
class votesTrack
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="ip",type="string", length=255 , nullable=true)
     */
    private $ip;

    /**
     * @var string
     * @ORM\Column(name="id_poster",type="integer" , nullable=true)
     */
    private $idposter;

    /**
     * @var \DateTime
     * @ORM\Column(name="datecreation",type="datetime" , nullable=true)
     */
    private $date;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getIdposter(): string
    {
        return $this->idposter;
    }

    /**
     * @param string $idposter
     */
    public function setIdposter(string $idposter)
    {
        $this->idposter = $idposter;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }


}
