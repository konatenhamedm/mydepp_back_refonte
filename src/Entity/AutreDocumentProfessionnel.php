<?php

namespace App\Entity;

use App\Repository\AutreDocumentProfessionnelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;

#[ORM\Entity(repositoryClass: AutreDocumentProfessionnelRepository::class)]
class AutreDocumentProfessionnel
{
    use TraitEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1"])]
    private ?int $id = null;


    #[ORM\ManyToOne(cascade: ["persist"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true)]
    #[Group(["fichier", "group_pro"])]
    private ?Fichier $document = null;

    #[ORM\ManyToOne]
    #[Group(["group1"])]
    private ?Professionnel $professionnel = null;

    #[ORM\ManyToOne(inversedBy: 'autreDocumentProfessionnels')]
    #[Group(["group1"])]
    private ?TypeAutreDocument $typeAutreDocument = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Group(["group1"])]
    private ?string $etape = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocument(): ?Fichier
    {
        return $this->document;
    }

    public function setDocument(?Fichier $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getProfessionnel(): ?Professionnel
    {
        return $this->professionnel;
    }

    public function setProfessionnel(?Professionnel $professionnel): static
    {
        $this->professionnel = $professionnel;

        return $this;
    }

    public function getTypeAutreDocument(): ?TypeAutreDocument
    {
        return $this->typeAutreDocument;
    }

    public function setTypeAutreDocument(?TypeAutreDocument $typeAutreDocument): static
    {
        $this->typeAutreDocument = $typeAutreDocument;

        return $this;
    }

    public function getEtape(): ?string
    {
        return $this->etape;
    }

    public function setEtape(?string $etape): static
    {
        $this->etape = $etape;

        return $this;
    }
}
