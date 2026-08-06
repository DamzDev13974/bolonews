<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Il faut remplir le titre')]
    #[Assert\Length(
        min: 10,
        max: 150,
        minMessage: 'Le titre doit avoir au minium {{ limit }}',
        maxMessage: 'Le titre doit avoir au maximum {{ limit }}',
    )]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Il faut remplir le chapeau')]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: 'Le chapeau doit avoir au minium {{ limit }}',
        maxMessage: 'Le chapeau doit avoir au maximum {{ limit }}',
    )]
    private ?string $chapeau = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Il faut remplir le contenu')]
    #[Assert\Length(
    min: 20,
    max: 1000,
    minMessage: "Le contenu doit contenir au moins {{ limit }} caractères.",
    maxMessage: "Le contenu ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    //Gérer dans le controller
    private ?string $photo = null;

    #[ORM\Column]
    //Gérer dans le controller
    private ?\DateTime $date_creation = null;

    #[ORM\Column]
    //Gérer dans le controller
    private ?\DateTime $date_modification = null;

    #[ORM\Column]
    //mise par défaut à false. Si non cochée sur le formulaire => 0,false, non publié sinon 1,true et publié
    private ?bool $publie = false;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $auteur = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(
        message : 'Il faut selectionner une categorie',
    )]
    private ?Categorie $categorie = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getChapeau(): ?string
    {
        return $this->chapeau;
    }

    public function setChapeau(string $chapeau): static
    {
        $this->chapeau = $chapeau;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateModification(): ?\DateTime
    {
        return $this->date_modification;
    }

    public function setDateModification(\DateTime $date_modification): static
    {
        $this->date_modification = $date_modification;

        return $this;
    }

    public function isPublie(): ?bool
    {
        return $this->publie;
    }

    public function setPublie(bool $publie): static
    {
        $this->publie = $publie;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }
}
