<?php declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Import
{
    private ?QuickbooksCompany $company = null;

    private ?string $importType = null;

    private ?string $dateFormat = null;

    private ?UploadedFile $file = null;

    private ?array $fieldsMapping = null;

    public function getFieldsMapping(): array
    {
        return $this->fieldsMapping ?? [];
    }

    public function setFieldsMapping(?array $fieldsMapping): void
    {
        $this->fieldsMapping = $fieldsMapping;
    }

    public function getCompany(): ?QuickbooksCompany
    {
        return $this->company;
    }

    public function setCompany(?QuickbooksCompany $company): void
    {
        $this->company = $company;
    }

    public function getImportType(): ?string
    {
        return $this->importType;
    }

    public function setImportType(?string $importType): void
    {
        $this->importType = $importType;
    }

    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    public function setDateFormat(?string $dateFormat): void
    {
        $this->dateFormat = $dateFormat;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }
}
