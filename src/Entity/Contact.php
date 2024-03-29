<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\AjouterContactAction;
use App\Controller\Delete\DeleteContactAction;
use App\Repository\ContactRepository;
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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read:Contact','read:Entity']],
    denormalizationContext: ['groups' => ['write:Contact','write:Entity']],
    operations: [
        new Get(
            security: "is_granted('ROLE_ADMIN')"
        ),
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Post(
            controller: AjouterContactAction::class,
            write: false,
            validationContext: ['groups' => ['Default']],
            inputFormats: ['multipart' => ['multipart/form-data']],
//            security: "is_granted('ROLE_ADMIN')"
        ),
//        new Put(
//            security: "is_granted('ROLE_ADMIN')"
//        ),
//        new Patch(
//            security: "is_granted('ROLE_ADMIN')"
//        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            controller: DeleteContactAction::class,
            write: false
        )
    ]
)]
#[ApiFilter(OrderFilter::class, properties: ['nomPrenom'])]
#[ApiFilter(SearchFilter::class, properties: ['deleted' => 'exact'])]
class Contact
{
    use EntityTimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'read:Contact',
        'read:ContactValeurDemande'
    ])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([
        'read:Contact',
        'write:Contact',
        'read:ContactValeurDemande'
    ])]
    private ?string $nomPrenom = null;

    #[ORM\Column(length: 255)]
    #[Groups([
        'read:Contact',
        'write:Contact',
        'read:ContactValeurDemande'
    ])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups([
        'read:Contact',
        'write:Contact',
        'read:ContactValeurDemande'
    ])]
    private ?string $phone = null;

    #[ORM\Column]
    #[Groups([
        'read:Contact',
        'write:Contact',
        'read:ContactValeurDemande'
    ])]
    private ?string $objet = null;

    #[ORM\Column]
    #[Groups([
        'read:Contact',
        'write:Contact',
        'read:ContactValeurDemande'
    ])]
    private ?string $message = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbLiaison = null;

    #[ORM\OneToMany(mappedBy: 'contact', targetEntity: ContactValeurDemande::class)]
    private Collection $contactValeurDemandes;

    public function __construct()
    {
        $this->contactValeurDemandes = new ArrayCollection();
        $this->dateAjout = new \DateTimeImmutable();
        $this->dateModif = new \DateTime();
        $this->deleted = "0";
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

    public function getNomPrenom(): ?string
    {
        return $this->nomPrenom;
    }

    public function setNomPrenom(string $nomPrenom): static
    {
        $this->nomPrenom = $nomPrenom;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): static
    {
        $this->objet = $objet;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

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

    /**
     * @return Collection<int, ContactValeurDemande>
     */
    public function getContactValeurDemandes(): Collection
    {
        return $this->contactValeurDemandes;
    }

    public function addContactValeurDemande(ContactValeurDemande $contactValeurDemande): static
    {
        if (!$this->contactValeurDemandes->contains($contactValeurDemande)) {
            $this->contactValeurDemandes->add($contactValeurDemande);
            $contactValeurDemande->setContact($this);
        }

        return $this;
    }

    public function removeContactValeurDemande(ContactValeurDemande $contactValeurDemande): static
    {
        if ($this->contactValeurDemandes->removeElement($contactValeurDemande)) {
            // set the owning side to null (unless already changed)
            if ($contactValeurDemande->getContact() === $this) {
                $contactValeurDemande->setContact(null);
            }
        }

        return $this;
    }

}
