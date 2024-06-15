<?php

namespace App\Classes\Maatwebsite;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\ChunkReader;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithFormatData;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Exceptions\SheetNotFoundException;
use Maatwebsite\Excel\Factories\ReaderFactory;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;
use Maatwebsite\Excel\Reader;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Throwable;

class CustomReader extends Reader
{
    protected function getSheet($import, $sheetImport, $index)
    {
        try {
            return CustomSheet::make($this->spreadsheet, $index);
        } catch (SheetNotFoundException $e) {
            if ($import instanceof SkipsUnknownSheets) {
                $import->onUnknownSheet($index);

                return null;
            }

            if ($sheetImport instanceof SkipsUnknownSheets) {
                $sheetImport->onUnknownSheet($index);

                return null;
            }

            throw $e;
        }
    }

    public function read($import, $filePath, string $readerType = null, string $disk = null)
    {
        $this->reader = $this->getReader($import, $filePath, $readerType, $disk);

        if ($import instanceof WithChunkReading) {
            return app(ChunkReader::class)->read($import, $this, $this->currentFile);
        }

        try {
            $this->loadSpreadsheet($import);

            ($this->transaction)(function () use ($import) {
                $sheetsToDisconnect = [];

                foreach ($this->sheetImports as $index => $sheetImport) {
                    if ($sheet = $this->getSheet($import, $sheetImport, $index)) {
                        $sheet->setStartRow($import->startRow);
                        $sheet->import($sheetImport, $sheet->getStartRow($sheetImport));

                        // when using WithCalculatedFormulas we need to keep the sheet until all sheets are imported
                        if (!($sheetImport instanceof HasReferencesToOtherSheets)) {
                            $sheet->disconnect();
                        } else {
                            $sheetsToDisconnect[] = $sheet;
                        }
                    }
                }

                foreach ($sheetsToDisconnect as $sheet) {
                    $sheet->disconnect();
                }
            });

            $this->afterImport($import);
        } catch (Throwable $e) {
            $this->raise(new ImportFailed($e));
            $this->garbageCollect();
            throw $e;
        }

        return $this;
    }

    protected function getReader($import, $filePath, string $readerType = null, string $disk = null): IReader
    {
        $shouldQueue = $import instanceof ShouldQueue;
        if ($shouldQueue && !$import instanceof WithChunkReading) {
            throw new \InvalidArgumentException('ShouldQueue is only supported in combination with WithChunkReading.');
        }

        if ($import instanceof WithEvents) {
            $this->registerListeners($import->registerEvents());
        }

        if ($import instanceof WithCustomValueBinder) {
            Cell::setValueBinder($import);
        }

        $fileExtension     = pathinfo($filePath, PATHINFO_EXTENSION);
        $temporaryFile     = $shouldQueue ? $this->temporaryFileFactory->make($fileExtension) : $this->temporaryFileFactory->makeLocal(null, $fileExtension);
        $this->currentFile = $temporaryFile->copyFrom(
            $filePath,
            $disk
        );

        return ReaderFactory::make(
            $import,
            $this->currentFile,
            $readerType
        );
    }

    /**
     * Garbage collect.
     */
    protected function garbageCollect()
    {
        $this->clearListeners();
        $this->setDefaultValueBinder();

        // Force garbage collecting
        unset($this->sheetImports, $this->spreadsheet);

        $this->currentFile->delete();
    }

    public function toArray($import, $filePath, string $readerType = null, string $disk = null): array
    {
        $this->reader = $this->getReader($import, $filePath, $readerType, $disk);

        $this->loadSpreadsheet($import);

        $sheets             = [];
        $sheetsToDisconnect = [];
        foreach ($this->sheetImports as $index => $sheetImport) {
            $calculatesFormulas = $sheetImport instanceof WithCalculatedFormulas;
            $formatData         = $sheetImport instanceof WithFormatData;
            if ($sheet = $this->getSheet($import, $sheetImport, $index)) {
                $sheets[$index] = $sheet->toArray($sheetImport, HeadingRowExtractor::determineStartRow($sheetImport), null, $calculatesFormulas, $formatData);

                // when using WithCalculatedFormulas we need to keep the sheet until all sheets are imported
                if (!($sheetImport instanceof HasReferencesToOtherSheets)) {
                    $sheet->disconnect();
                } else {
                    $sheetsToDisconnect[] = $sheet;
                }
            }
        }

        foreach ($sheetsToDisconnect as $sheet) {
            $sheet->disconnect();
        }

        $this->afterImport($import);

        return $sheets;
    }
}

