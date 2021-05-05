<?php declare(strict_types=1);

namespace App\SheetScheduler;

class Vendor
{
    /** @var string|null */
    private $vendorFullname;

    /** @var string|null */
    private $vendorCompanyName;

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

    /** @var string|null */
    private $vendorType;

    /** @var string|null */
    private $terms;

    /** @var string|null */
    private $currency;

    /**
     * @return string|null
     */
    public function getVendorFullname(): ?string
    {
        return $this->vendorFullname;
    }

    /**
     * @param string|null $vendorFullname
     */
    public function setVendorFullname(?string $vendorFullname): void
    {
        $this->vendorFullname = $vendorFullname;
    }

    /**
     * @return string|null
     */
    public function getVendorCompanyName(): ?string
    {
        return $this->vendorCompanyName;
    }

    /**
     * @param string|null $vendorCompanyName
     */
    public function setVendorCompanyName(?string $vendorCompanyName): void
    {
        $this->vendorCompanyName = $vendorCompanyName;
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
    public function getVendorType(): ?string
    {
        return $this->vendorType;
    }

    /**
     * @param string|null $vendorType
     */
    public function setVendorType(?string $vendorType): void
    {
        $this->vendorType = $vendorType;
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
