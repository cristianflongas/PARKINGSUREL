<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;

class PlacaOCRService
{
    /**
     * Extraer texto de placa desde imagen
     * 
     * @param string $imagePath Ruta de la imagen (puede ser base64 o archivo)
     * @return string|null Texto de la placa detectado o null si falla
     */
    public function extraerPlacaDesdeImagen($imagePath)
    {
        try {
            // Si es base64, convertir a archivo temporal
            if (preg_match('/^data:image\/(\w+);base64,/', $imagePath, $type)) {
                $imagePath = $this->convertirBase64AImagen($imagePath);
            }

            // Configurar Tesseract para reconocimiento de placas
            $ocr = new TesseractOCR($imagePath);
            
            // Configuración optimizada para placas vehiculares
            $ocr->lang('spa')  // Español para placas latinoamericanas
                ->psm(7)       // Page Segmentation Mode 7: Treat image as single text line
                ->oem(3)       // OCR Engine Mode 3: Default, based on what is available
                ->config('tessedit_char_whitelist', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-') // Solo caracteres válidos de placas
                ->dpi(300);    // Alta resolución para mejor precisión

            $texto = $ocr->run();

            // Limpiar y formatear el texto de la placa
            return $this->formatearPlaca($texto);
            
        } catch (\Exception $e) {
            \Log::error('Error en OCR: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Convertir imagen base64 a archivo temporal
     */
    private function convertirBase64AImagen($base64String)
    {
        $base64String = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);
        $base64String = str_replace(' ', '+', $base64String);
        $imageData = base64_decode($base64String);
        
        $tempPath = tempnam(sys_get_temp_dir(), 'placa_');
        file_put_contents($tempPath, $imageData);
        
        return $tempPath;
    }

    /**
     * Formatear texto extraído a formato de placa estándar
     */
    private function formatearPlaca($texto)
    {
        // Eliminar espacios y caracteres especiales
        $texto = preg_replace('/[^A-Z0-9-]/', '', strtoupper(trim($texto)));
        
        // Validar formato de placa (ej: ABC-123, ABC-12-345, etc.)
        if (strlen($texto) >= 6 && strlen($texto) <= 8) {
            return $texto;
        }
        
        return null;
    }

    /**
     * Verificar si Tesseract está instalado
     */
    public function verificarTesseract()
    {
        try {
            $ocr = new TesseractOCR();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
