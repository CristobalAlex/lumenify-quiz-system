<?php
require_once __DIR__ . '/../../vendor/autoload.php';
class TextExtractor {
    
    public static function extract($filePath, $fileType) {
        if ($fileType === 'pdf') {
            return self::extractPdf($filePath);
        } elseif ($fileType === 'pptx') {
            return self::extractPptx($filePath);
        }
        return "";
    }

    private static function extractPdf($filePath) {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        } catch (Exception $e) {
            return "Error parsing PDF: " . $e->getMessage();
        }
    }

    private static function extractPptx($filePath) {
        // PPTX is actually a ZIP file containing XMLs
        $content = '';
        $zip = new ZipArchive;
        
        if ($zip->open($filePath) === true) {
            // Loop through slides (usually named slide1.xml, slide2.xml...)
            for ($i = 1; $i <= 50; $i++) { // Limit to 50 slides for performance
                $slideName = "ppt/slides/slide{$i}.xml";
                if ($zip->locateName($slideName) !== false) {
                    $xml = $zip->getFromName($slideName);
                    // Remove XML tags to get raw text
                    $slideText = strip_tags($xml); 
                    $content .= $slideText . " ";
                } else {
                    break; 
                }
            }
            $zip->close();
        }
        return $content;
    }
}
?>