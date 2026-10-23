<?php

namespace Tests\Unit;

use App\Services\Employability\CvTextExtractor;
use Dompdf\Dompdf;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CvTextExtractorTest extends TestCase
{
    public function test_it_extracts_text_from_a_valid_pdf(): void
    {
        $text = (new CvTextExtractor())->extract($this->pdfWithText('PHP Laravel desarrollo de software'));

        $this->assertStringContainsString('PHP Laravel desarrollo de software', $text);
    }

    public function test_it_rejects_a_pdf_without_extractable_text(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no contiene texto legible');

        (new CvTextExtractor())->extract($this->pdfWithText(''));
    }

    public function test_it_rejects_a_corrupt_pdf(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No se pudo leer el PDF');

        (new CvTextExtractor())->extract('%PDF-1.4 corrupto');
    }

    private function pdfWithText(string $text): string
    {
        $pdf = new Dompdf();
        $pdf->loadHtml('<html><body>'.htmlspecialchars($text, ENT_QUOTES, 'UTF-8').'</body></html>');
        $pdf->render();

        return $pdf->output();
    }
}
