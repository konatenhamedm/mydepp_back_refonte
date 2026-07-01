<?php

namespace App\Entity;

use App\Repository\TypeAutreDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups as Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TypeAutreDocumentRepository::class)]
class TypeAutreDocument
{
    use TraitEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Group(["group1", "group_libelle"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Group(["group1", "group_libelle"])]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'typeAutreDocument', targetEntity: AutreDocumentProfessionnel::class)]
    private Collection $autreDocumentProfessionnels;

    public function __construct()
    {
        $this->autreDocumentProfessionnels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, AutreDocumentProfessionnel>
     */
    public function getAutreDocumentProfessionnels(): Collection
    {
        return $this->autreDocumentProfessionnels;
    }

    public function addAutreDocumentProfessionnel(AutreDocumentProfessionnel $autreDocumentProfessionnel): static
    {
        if (!$this->autreDocumentProfessionnels->contains($autreDocumentProfessionnel)) {
            $this->autreDocumentProfessionnels->add($autreDocumentProfessionnel);
            $autreDocumentProfessionnel->setTypeAutreDocument($this);
        }

        return $this;
    }

    public function removeAutreDocumentProfessionnel(AutreDocumentProfessionnel $autreDocumentProfessionnel): static
    {
        if ($this->autreDocumentProfessionnels->removeElement($autreDocumentProfessionnel)) {
            // set the owning side to null (unless already changed)
            if ($autreDocumentProfessionnel->getTypeAutreDocument() === $this) {
                $autreDocumentProfessionnel->setTypeAutreDocument(null);
            }
        }

        return $this;
    }
}
