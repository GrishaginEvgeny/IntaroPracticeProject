<?php

namespace App\Entity;

use App\Repository\ShopCartRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopCartRepository::class)
 */
class ShopCart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Offer::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $offer_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $count;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOfferId(): ?Offer
    {
        return $this->offer_id;
    }

    public function setOfferId(?Offer $offer_id): self
    {
        $this->offer_id = $offer_id;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

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
}
