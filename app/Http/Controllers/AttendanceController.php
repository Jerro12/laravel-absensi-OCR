<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Intern;
use App\Models\Presence;
use App\Services\FlaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected FlaskService $flaskService;

    public function __construct(FlaskService $flaskService)
    {
        $this->flaskService = $flaskService;
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:5120', // Single photo containing face + ID card
        ]);

        $photo = $request->file('photo');

        // 1. Extract name from ID card using OCR (from the same photo)
        $ocrResult = $this->flaskService->extractTextFromImage($photo);

        if ($ocrResult['status'] !== 'success' || empty($ocrResult['raw_text'])) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca ID Card. Pastikan ID card terlihat jelas di foto.'
            ], 400);
        }

        // 2. Find user by name from OCR result
        $detectedName = $this->extractNameFromOCR($ocrResult['raw_text']);
        $user = $this->findUserByName($detectedName);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Nama tidak ditemukan di database: ' . $detectedName,
                'ocr_result' => $ocrResult['raw_text']
            ], 404);
        }

        // 3. Compare face in photo with master photo
        $faceResult = $this->flaskService->compareFaces($photo, $user->foto_master);

        if (!$faceResult['match']) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah tidak cocok dengan data ' . $user->name . '! Score: ' . ($faceResult['score'] ?? 0),
                'detected_name' => $detectedName,
                'score' => $faceResult['score'] ?? 0
            ], 403);
        }

        // 4. Check if already checked in today
        $today = Carbon::today();
        $existingPresence = Presence::where('user_type', get_class($user))
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existingPresence && $existingPresence->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen masuk hari ini pada ' . $existingPresence->check_in
            ], 400);
        }

        // 5. Save photo
        $photoPath = $photo->store('presences', 'public');

        // 6. Create or update presence record
        $presence = Presence::updateOrCreate(
            [
                'user_type' => get_class($user),
                'user_id' => $user->id,
                'date' => $today,
            ],
            [
                'check_in' => Carbon::now()->format('H:i:s'),
                'image_capture' => $photoPath,
                'detected_name' => $detectedName,
                'face_score' => $faceResult['score'] ?? 0,
                'status' => 'valid',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil!',
            'data' => [
                'name' => $user->name,
                'division' => $user->division,
                'check_in' => $presence->check_in,
                'score' => $presence->face_score
            ]
        ]);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:5120',
        ]);

        $today = Carbon::today();
        $bestMatch = null;
        $bestScore = 0;

        // Try to match face with all active employees and interns
        foreach (Employee::where('status', true)->get() as $employee) {
            $result = $this->flaskService->compareFaces(
                $request->file('photo'),
                $employee->foto_master
            );

            if ($result['match'] && $result['score'] > $bestScore) {
                $bestScore = $result['score'];
                $bestMatch = $employee;
            }
        }

        foreach (Intern::where('status', true)->get() as $intern) {
            $result = $this->flaskService->compareFaces(
                $request->file('photo'),
                $intern->foto_master
            );

            if ($result['match'] && $result['score'] > $bestScore) {
                $bestScore = $result['score'];
                $bestMatch = $intern;
            }
        }

        if (!$bestMatch) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah tidak dikenali. Pastikan Anda sudah terdaftar.'
            ], 404);
        }

        // Find today's presence
        $presence = Presence::where('user_type', get_class($bestMatch))
            ->where('user_id', $bestMatch->id)
            ->whereDate('date', $today)
            ->first();

        if (!$presence) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum absen masuk hari ini.'
            ], 400);
        }

        if ($presence->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen pulang pada ' . $presence->check_out
            ], 400);
        }

        // Update check-out time
        $presence->update([
            'check_out' => Carbon::now()->format('H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil!',
            'data' => [
                'name' => $bestMatch->name,
                'division' => $bestMatch->division,
                'check_in' => $presence->check_in,
                'check_out' => $presence->check_out,
            ]
        ]);
    }

    /**
     * Extract name from OCR result (simple implementation)
     */
    protected function extractNameFromOCR(array $lines): string
    {
        // Ambil baris terpanjang sebagai nama (bisa disesuaikan)
        $longestLine = '';
        foreach ($lines as $line) {
            if (strlen($line) > strlen($longestLine)) {
                $longestLine = $line;
            }
        }
        return $longestLine;
    }

    /**
     * Find user (Employee or Intern) by name
     */
    protected function findUserByName(string $name)
    {
        // Try exact match first
        $employee = Employee::where('name', $name)->where('status', true)->first();
        if ($employee) {
            return $employee;
        }

        $intern = Intern::where('name', $name)->where('status', true)->first();
        if ($intern) {
            return $intern;
        }

        // Try partial match (case insensitive)
        $employee = Employee::where('name', 'LIKE', "%{$name}%")->where('status', true)->first();
        if ($employee) {
            return $employee;
        }

        $intern = Intern::where('name', 'LIKE', "%{$name}%")->where('status', true)->first();
        return $intern;
    }
}
