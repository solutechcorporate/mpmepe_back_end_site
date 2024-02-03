<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\AjouterMultimediaAction;
use App\Controller\Delete\DeleteMultimediaAction;
use App\InterfacePersonnalise\UserOwnedInterface;
use App\Repository\MultimediaRepository;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Utils\Traits\EntityTimestampTrait;
use App\Utils\Traits\UserAjoutModifTrait;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MultimediaRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read:Multimedia','read:Entity']],
    denormalizationContext: ['groups' => ['write:Multimedia','write:Entity']],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            controller: AjouterMultimediaAction::class,
            write: false,
            validationContext: ['groups' => ['Default']],
            inputFormats: ['multipart' => ['multipart/form-data']],
            security: "is_granted('ROLE_ADMIN')"
        ),
//        new Put(
//            security: "is_granted('ROLE_ADMIN')"
//        ),
//        new Patch(
//            security: "is_granted('ROLE_ADMIN')"
//        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            controller: DeleteMultimediaAction::class,
            write: false
        ),
    ]
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'titre'])]
#[ApiFilter(SearchFilter::class, properties: ['deleted' => 'exact', 'userAjout' => 'exact', 'userModif' => 'exact'])]
#[UniqueEntity(
    fields: 'titre'
)]
class Multimedia implements UserOwnedInterface
{
    use EntityTimestampTrait;
    use UserAjoutModifTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'read:Multimedia',
    ])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([
        'read:Multimedia',
        'write:Multimedia',
    ])]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Groups([
        'read:Multimedia',
    ])]
    private ?string $logo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([
        'read:Multimedia',
        'write:Multimedia',
    ])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups([
        'read:Multimedia',
    ])]
    private ?string $backgroundImage = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([
        'read:Multimedia',
        'write:Multimedia',
    ])]
    private ?string $lien = null;

    #[ORM\Column(length: 255)]
    #[Groups([
        'read:Multimedia',
        'write:Multimedia',
    ])]
    private ?string $typeLien = null;

    #[Groups([
        'read:Multimedia',
    ])]
    public array $fichiers = [];

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        'read:Multimedia',
    ])]
    private ?User $userAjout = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([
        'read:Multimedia',
    ])]
    private ?User $userModif = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBackgroundImage(): ?string
    {
        return $this->backgroundImage;
    }

    public function setBackgroundImage(string $backgroundImage): static
    {
        $this->backgroundImage = $backgroundImage;

        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(string $lien): static
    {
        $this->lien = $lien;

        return $this;
    }

    public function getTypeLien(): ?string
    {
        return $this->typeLien;
    }

    public function setTypeLien(string $typeLien): static
    {
        $this->typeLien = $typeLien;

        return $this;
    }

    public function getFichiers(): array
    {
        return $this->fichiers;
    }

    public function setFichiers(array $fichiers)
    {
        $this->fichiers = $fichiers;

        return $this;
    }

}
