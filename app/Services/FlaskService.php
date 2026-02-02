<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class FlaskService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.flask.url', 'http://localhost:5000');
    }

    /**
     * Extract text from ID card using OCR
     */
    public function extractTextFromImage(UploadedFile $image): array
    {
        try {
            $response = Http::attach(
                'image',
                file_get_contents($image->getRealPath()),
                $image->getClientOriginalName()
            )->post("{$this->baseUrl}/ocr");

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => 'error',
                'message' => 'OCR service failed',
                'raw_text' => []
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'raw_text' => []
            ];
        }
    }

    /**
     * Compare selfie with master photo using face recognition
     */
    public function compareFaces(UploadedFile $selfie, string $masterPhotoPath): array
    {
        try {
            $response = Http::attach(
                'selfie',
                file_get_contents($selfie->getRealPath()),
                $selfie->getClientOriginalName()
            )->attach(
                    'master',
                    file_get_contents(storage_path('app/public/' . $masterPhotoPath)),
                    basename($masterPhotoPath)
                )->post("{$this->baseUrl}/compare");

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'match' => false,
                'reason' => 'Face recognition service failed',
                'score' => 0
            ];
        } catch (\Exception $e) {
            return [
                'match' => false,
                'reason' => $e->getMessage(),
                'score' => 0
            ];
        }
    }
}
