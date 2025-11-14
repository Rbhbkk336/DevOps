<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ImageRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type:"integer")]
private ?int $id = null;

#[ORM\Column(type:"string", length:255)]
#[Assert\NotBlank]
private ?string $filename = null;

#[ORM\Column(type:"string", length:255, nullable:true)]
private ?string $originalFilename = null;

#[ORM\Column(type:"boolean")]
private bool $isPrivate = false;

#[ORM\Column(type:"text", nullable:true)]
private ?string $recognizedText = null;

#[ORM\Column(type:"json", nullable:true)]
private ?array $keywords = [];

#[ORM\ManyToOne(targetEntity: User::class, inversedBy: "images")]
#[ORM\JoinColumn(nullable: false)]
private ?User $user = null;

#[ORM\Column(type:"datetime")]
private \DateTimeInterface $createdAt;

public function __construct()
{
$this->createdAt = new \DateTimeImmutable();
}

public function getId(): ?int
{
return $this->id;
}

public function getFilename(): ?string
{
return $this->filename;
}

public function setFilename(string $filename): self
{
$this->filename = $filename;
return $this;
}

public function getOriginalFilename(): ?string
{
return $this->originalFilename;
}

public function setOriginalFilename(?string $originalFilename): self
{
$this->originalFilename = $originalFilename;
return $this;
}

public function getIsPrivate(): bool
{
return $this->isPrivate;
}

public function setIsPrivate(bool $isPrivate): self
{
$this->isPrivate = $isPrivate;
return $this;
}

public function getRecognizedText(): ?string
{
return $this->recognizedText;
}

public function setRecognizedText(?string $recognizedText): self
{
$this->recognizedText = $recognizedText;
return $this;
}

public function getKeywords(): ?array
{
return $this->keywords;
}

public function setKeywords(?array $keywords): self
{
$this->keywords = $keywords;
return $this;
}

public function getUser(): ?User
{
return $this->user;
}

public function setUser(?User $user): self
{
$this->user = $user;
return $this;
}

public function getCreatedAt(): \DateTimeInterface
{
return $this->createdAt;
}
}
