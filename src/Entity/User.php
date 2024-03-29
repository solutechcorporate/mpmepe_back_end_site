<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\Delete\DeleteUserAction;
use App\Controller\MeAction;
use App\InterfacePersonnalise\UserOwnedInterface;
use App\Repository\UserRepository;
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
use App\Controller\AjouterUserAction;
use App\State\UserPasswordHasher;
use App\Utils\Traits\EntityTimestampTrait;
use App\Utils\Traits\UserAjoutModifTrait;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read:User','read:Entity']],
    denormalizationContext: ['groups' => ['write:User','write:Entity']],
    operations: [
        new Get(
            security: "is_granted('ROLE_ADMIN')"
        ),
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Post(
            controller: AjouterUserAction::class,
            write: false,
            validationContext: ['groups' => ['Default']],
            inputFormats: ['multipart' => ['multipart/form-data']],
        ),
//        new Put(
//            security: "is_granted('ROLE_ADMIN')"
//        ),
//        new Patch(
//            security: "is_granted('ROLE_ADMIN')"
//        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            controller: DeleteUserAction::class,
            write: false
        ),
        new GetCollection(
            name: 'profil',
            uriTemplate: '/profil',
            controller: MeAction::class,
            read: false,
            security: "is_granted('ROLE_ADMIN')",
            openapiContext: [
                'summary' => "Récupère les informations de l'utilisateur connecté",
            ]
        ),
    ]
)]
#[UniqueEntity('email')]
#[ApiFilter(DateFilter::class, properties: ['dateAjout', 'dateModif'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'email'])]
#[ApiFilter(SearchFilter::class, properties: ['deleted' => 'exact', 'userAjout' => 'exact', 'userModif' => 'exact'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, UserOwnedInterface, TwoFactorInterface
{
    use EntityTimestampTrait;
    use UserAjoutModifTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'read:User',
        'read:UserRole',
        'read:Historique',
        'read:Article',
        'read:ArticleGalerie',
        'read:ArticleTag',
        'read:CategorieDocument',
        'read:Copyright',
        'read:Demande',
        'read:Direction',
        'read:Dirigeant',
        'read:Document',
        'read:Galerie',
        'read:Header',
        'read:Menu',
        'read:Ministere',
        'read:Page',
        'read:Role',
        'read:SocialNetwork',
        'read:SousMenu',
        'read:Tag',
        'read:ValeurDemande',
    ])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups([
        'read:User',
        'write:User',
        'read:UserRole',
        'read:Historique',
        'read:Article',
        'read:ArticleGalerie',
        'read:ArticleTag',
        'read:CategorieDocument',
        'read:Copyright',
        'read:Demande',
        'read:Direction',
        'read:Dirigeant',
        'read:Document',
        'read:Galerie',
        'read:Header',
        'read:Menu',
        'read:Ministere',
        'read:Page',
        'read:Role',
        'read:SocialNetwork',
        'read:SousMenu',
        'read:Tag',
        'read:ValeurDemande',
    ])]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    #[Groups([
        'read:User',
        'read:UserRole',
        'read:Historique',
        'read:Article',
        'read:ArticleGalerie',
        'read:ArticleTag',
        'read:CategorieDocument',
        'read:Copyright',
        'read:Demande',
        'read:Direction',
        'read:Dirigeant',
        'read:Document',
        'read:Galerie',
        'read:Header',
        'read:Menu',
        'read:Ministere',
        'read:Page',
        'read:Role',
        'read:SocialNetwork',
        'read:SousMenu',
        'read:Tag',
        'read:ValeurDemande',
    ])]
    #[Assert\NotBlank]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank]
    #[Groups(['write:User'])]
    private ?string $plainPassword = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserRole::class)]
    private Collection $userRoles;

    #[ORM\Column(nullable: true)]
    private ?int $nbLiaison = null;

//    #[Groups([
//        'read:User',
//        'read:UserRole',
//    ])]
    public array $fichiers = [];

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Historique::class)]
    private Collection $historiques;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $userAjout = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $userModif = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $authCode = null;

    public function __construct()
    {
        $this->dateAjout = new \DateTimeImmutable();
        $this->dateModif = new \DateTime();
        $this->deleted = "0";
        $this->userRoles = new ArrayCollection();
        $this->historiques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = "null";
    }

    /**
     * @return Collection<int, UserRole>
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(UserRole $userRole): static
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->setUser($this);
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): static
    {
        if ($this->userRoles->removeElement($userRole)) {
            // set the owning side to null (unless already changed)
            if ($userRole->getUser() === $this) {
                $userRole->setUser(null);
            }
        }

        return $this;
    }

    public function getNbLiaison(): ?int
    {
        return $this->nbLiaison;
    }

    public function setNbLiaison(?int $nbLiaison): static
    {
        $this->nbLiaison = $nbLiaison;

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

    /**
     * @return Collection<int, Historique>
     */
    public function getHistoriques(): Collection
    {
        return $this->historiques;
    }

    public function addHistorique(Historique $historique): static
    {
        if (!$this->historiques->contains($historique)) {
            $this->historiques->add($historique);
            $historique->setUser($this);
        }

        return $this;
    }

    public function removeHistorique(Historique $historique): static
    {
        if ($this->historiques->removeElement($historique)) {
            // set the owning side to null (unless already changed)
            if ($historique->getUser() === $this) {
                $historique->setUser(null);
            }
        }

        return $this;
    }

    public function isEmailAuthEnabled(): bool
    {
        return true; // This can be a persisted field to switch email code authentication on/off
    }

    public function getEmailAuthRecipient(): string
    {
        return (string) $this->email;
    }

    public function getEmailAuthCode(): string
    {
        if (null === $this->authCode) {
            throw new \LogicException('The email authentication code was not set');
        }

        return $this->authCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->authCode = $authCode;
    }

}
