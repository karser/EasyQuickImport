<?php declare(strict_types=1);

namespace App\SheetScheduler;

use App\Exception\RuntimeException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class FileDecoder
{
    private $decoder;

    public function __construct(DecoderInterface $decoder)
    {
        $this->decoder = $decoder;
    }

    public function decodeFile(string $path, ?string $mimeType): array
    {
        $mimeType = $mimeType ?? 'text/plain';
        return $mimeType === 'text/plain' ? $this->decodeAsCsv($path) : $this->decodeAsExcel($path);
    }

    private function decodeAsCsv(string $path): array
    {
        $contents = file_get_contents($path);
        if (false === $contents) {
            throw new RuntimeException('Unable to read file: ' . $path);
        }
        $contents = trim($contents);
        return $this->decoder->decode($contents, 'csv', ['as_collection' => true]);
    }

    private function decodeAsExcel(string $path): array
    {
        try {
            /** @var BaseReader $reader */
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $worksheet = $spreadsheet->getActiveSheet();
        } catch (\Throwable $e) {
            throw new RuntimeException('Unable to read file: ' . $path, $e->getCode(), $e);
        }
        $rows = $worksheet->toArray();
        if (count($rows) <= 1) {
            return [];
        }
        $results = [];
        $header = array_shift($rows);
        foreach ($rows as $row) {
            $results[] = array_combine($header, $row);
        }
        return $results;
    }
}
