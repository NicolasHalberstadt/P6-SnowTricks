<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"trick"})
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"trick"})
     */
    private $name;
    
    /**
     * @ORM\Column(type="datetime")
     * @Groups({"trick"})
     */
    private $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity=Trick::class, inversedBy="images")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trick;
    
    /**
     * @ORM\Column(type="boolean")
     * @Groups({"trick"})
     */
    private $isMain = false;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        
        return $this;
    }
    
    public function getTrick(): ?Trick
    {
        return $this->trick;
    }
    
    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;
        
        return $this;
    }
    
    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }
    
    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;
        
        return $this;
    }
}
