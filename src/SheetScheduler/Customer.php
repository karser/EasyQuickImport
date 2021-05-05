<?php declare(strict_types=1);

namespace App\SheetScheduler;

class Customer
{
    /** @var string|null */
    private $customerFullName;

    /** @var string|null */
    private $firstName;

    /** @var string|null */
    private $lastName;

    /** @var string|null */
    private $companyName;

    /** @var string|null */
    private $terms;

    /** @var string|null */
    private $currency;

    /** @var string|null */
    private $addr1;

    /** @var string|null */
    private $addr2;

    /** @var string|null */
    private $city;

    /** @var string|null */
    private $state;

    /** @var string|null */
    private $postalcode;

    /** @var string|null */
    private $country;

    public function composeBillAddress(): array
    {
        $arr = [];
        $attn = $this->getCustomerNameWithAttention();
        if ('' !== $attn) {
            $arr[] = $attn;
        }
        $company = $this->companyName;
        if (null !== $company && '' !== trim($company)) {
            $arr[] = $company;
        }
        $arr[] = $this->addr1;
        $arr[] = $this->addr2;
        while (count($arr) < 5) { $arr[] = ''; }

        $arr[] = $this->city;
        $arr[] = $this->state;
        $arr[] = '';
        $arr[] = $this->postalcode;
        $arr[] = $this->country;

        return $arr;
    }

    private function getCustomerNameWithAttention(): string
    {
        $arr = array_filter([
            trim($this->firstName ?? ''),
            trim($this->lastName ?? ''),
        ]);
        if (count($arr) === 0) {
            return '';
        }
        return 'Attn: '.implode(' ', array_filter($arr));
    }

    /**
     * @return string|null
     */
    public function getCustomerFullName(): ?string
    {
        return $this->customerFullName;
    }

    /**
     * @param string|null $customerFullName
     */
    public function setCustomerFullName(?string $customerFullName): void
    {
        $this->customerFullName = $customerFullName;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     */
    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    /**
     * @param string|null $companyName
     */
    public function setCompanyName(?string $companyName): void
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string|null
     */
    public function getTerms(): ?string
    {
        return $this->terms;
    }

    /**
     * @param string|null $terms
     */
    public function setTerms(?string $terms): void
    {
        $this->terms = $terms;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string|null $currency
     */
    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string|null
     */
    public function getAddr1(): ?string
    {
        return $this->addr1;
    }

    /**
     * @param string|null $addr1
     */
    public function setAddr1(?string $addr1): void
    {
        $this->addr1 = $addr1;
    }

    /**
     * @return string|null
     */
    public function getAddr2(): ?string
    {
        return $this->addr2;
    }

    /**
     * @param string|null $addr2
     */
    public function setAddr2(?string $addr2): void
    {
        $this->addr2 = $addr2;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string|null
     */
    public function getPostalcode(): ?string
    {
        return $this->postalcode;
    }

    /**
     * @param string|null $postalcode
     */
    public function setPostalcode(?string $postalcode): void
    {
        $this->postalcode = $postalcode;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }
}
