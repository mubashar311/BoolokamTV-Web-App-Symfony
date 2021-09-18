<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Slide
 *
 * @ORM\Table(name="main_movie_slider_table")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MainSliderMovieRepository")
 */
class MainSliderMovie {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Poster")
     * @ORM\JoinColumn(name="poster_id", referencedColumnName="id", nullable=true)
     */
    private $poster;


    /**
     * @var int
     *
     * @Assert\Range(
     *      min = 1,
     *      max = 10000,
     * )
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get poster
     * @return
     */
    public function getPoster() {
        return $this->poster;
    }

    /**
     * Set poster
     * @return $this
     */
    public function setPoster($poster) {
        $this->poster = $poster;
        return $this;
    }

    /**
     * Get position
     * @return
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * Set position
     * @return $this
     */
    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }

}