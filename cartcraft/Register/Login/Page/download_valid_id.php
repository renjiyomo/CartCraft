<?php
require('fpdf/fpdf.php');
include 'cartcraft_db.php';

// Define file directories
define('FRONT_ID_DIR', 'image/');
define('BACK_ID_DIR', 'uploads/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artistId = $_POST['artist_id'] ?? null;

    if (!$artistId || !is_numeric($artistId)) {
        die("Invalid or missing Artist ID.");
    }

    $artistId = intval($artistId);

    // Fetch artist details
    $query = "SELECT names, front_valid_id, back_valid_id FROM artists WHERE artist_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $artistId);
    $stmt->execute();
    $result = $stmt->get_result();
    $artist = $result->fetch_assoc();

    if ($artist) {
        $artistName = $artist['names'];
        $frontImage = FRONT_ID_DIR . $artist['front_valid_id'];
        $backImage = BACK_ID_DIR . $artist['back_valid_id'];

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        $pdf->Cell(0, 10, "Artist: $artistName", 0, 1, 'C');
        $pdf->Ln(10);

        // Front ID
        if (file_exists($frontImage)) {
            $pdf->Cell(0, 10, "Front ID:", 0, 1);
            $pdf->Image($frontImage, 10, $pdf->GetY(), 90);
            $pdf->Ln(60);
        } else {
            $pdf->Cell(0, 10, "Front ID not available.", 0, 1);
        }

        // Back ID
        if (file_exists($backImage)) {
            $pdf->Cell(0, 10, "Back ID:", 0, 1);
            $pdf->Image($backImage, 10, $pdf->GetY(), 90);
        } else {
            $pdf->Cell(0, 10, "Back ID not available.", 0, 1);
        }

        // Output the PDF for download
        $fileName = "Valid_ID_$artistName.pdf";
        $pdf->Output("D", $fileName);
    } else {
        die("Artist not found.");
    }
} else {
    die("Invalid request method.");
}
?>
