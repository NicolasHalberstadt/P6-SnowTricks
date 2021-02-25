<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message = "The email '{{ value }}' is not a valid email.")
     */
    private $email;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;
    
    private $plainPassword;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }
    
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        
        return $this;
    }
    
    public function getLastname(): ?string
    {
        return $this->lastname;
    }
    
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }
    
    /**
     * @param $plainPassword
     * @return string
     */
    public function setPlainPassword($plainPassword): string
    {
        $this->plainPassword = $plainPassword;
        
        return $this;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    public function setEmail(string $email): self
    {
        $this->email = $email;
        
        return $this;
    }
    
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }
    
    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;
        
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
    
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
    
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        
        return $this;
    }
    
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }
    
    public function getPassword(): ?string
    {
        return $this->password;
    }
    
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }
    
    public function getUsername(): string
    {
        return $this->getFirstname() . " " . $this->getLastname();
    }
    
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
    
    public function setPassword(string $password): self
    {
        $this->password = $password;
        
        return $this;
    }
    
    public function isVerified(): bool
    {
        return $this->isVerified;
    }
    
    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        
        return $this;
    }
}
