<?php

require_once __DIR__ . '/fpdf_php8_polyfill.php';
require_once __DIR__ . '/../fpdf/fpdf.php';

function indoDate($iso)
{
    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $pecah = explode('-', date('Y-m-d', strtotime($iso)));

    return ltrim($pecah[2], '0') . ' ' .
           $bulan[(int)$pecah[1]] . ' ' .
           $pecah[0];
}

function buatPDFReservasi($data, $status)
{

    class SuratPDF extends \FPDF
    {

        public $logo = '';

        private $first = true;

        function Header()
        {

            if (!$this->first) {
                return;
            }

            if (is_file($this->logo)) {

                $this->Image(
                    $this->logo,
                    20,
                    12,
                    22
                );
            }

            $xText = 60;

            $this->SetFont('Arial', 'B', 14);

            $this->SetXY($xText, 15);

            $this->Cell(
                0,
                5,
                'PEMERINTAH KOTA TANGERANG',
                0,
                1,
                'C'
            );

            $this->SetX($xText);

            $this->SetFont('Arial', 'B', 16);

            $this->Cell(
                0,
                7,
                'DINAS KEBUDAYAAN DAN PARIWISATA',
                0,
                1,
                'C'
            );

            $this->SetFont('Arial', '', 10);

            $this->SetX($xText);

            $this->Cell(
                0,
                5,
                'Jl. Mayjen Sutoyo No.11, Kota Tangerang',
                0,
                1,
                'C'
            );

            $this->SetX($xText);

            $this->Cell(
                0,
                5,
                'disbudpar.tangerangkota.go.id',
                0,
                1,
                'C'
            );

            $this->SetLineWidth(1);

            $this->Line(
                15,
                $this->GetY() + 2,
                195,
                $this->GetY() + 2
            );

            $this->Ln(10);

            $this->first = false;
        }
    }

    $pdf = new SuratPDF('P', 'mm', 'A4');

    $pdf->SetMargins(20, 20, 20);

    $pdf->logo =
        __DIR__ . '/../assets/img/logotng.png';

    $pdf->AddPage();

    $pdf->SetTitle(
        'Surat Reservasi ' .
        $data['kode_booking']
    );

    $pdf->SetFont('Arial', '', 12);

    $y0 = $pdf->GetY();

    $pdf->Cell(
        0,
        6,
        'Perihal : Reservasi Tempat'
    );

    $pdf->SetXY(0, $y0);

    $pdf->Cell(
        0,
        6,
        'Tangerang, ' .
        indoDate($data['hari']),
        0,
        1,
        'R'
    );

    $pdf->Ln(5);

    $pdf->Cell(
        0,
        6,
        'Kepada Yth.',
        0,
        1
    );

    $pdf->MultiCell(
        0,
        6,
        "Dinas Kebudayaan dan Pariwisata Tangerang\ndi -\nTangerang",
        0
    );

    $pdf->Ln(5);

    $paragraf1 =
        "Kami berencana mengadakan kegiatan dan memerlukan tempat di lingkungan Pemerintah Kota Tangerang.";

    $pdf->MultiCell(
        0,
        7,
        $paragraf1,
        0,
        'J'
    );

    $pdf->Ln(5);

    $namaTempat =
        $data['nama_tempat'] ?? '-';

    $info = [

        'Tanggal' =>
            indoDate($data['hari']),

        'Tempat' =>
            $namaTempat,

        'Waktu' =>
            $data['jam_mulai'] .
            ' - ' .
            $data['jam_selesai'],

        'Keterangan' =>
            $data['keterangan']
    ];

    foreach ($info as $k => $v) {

        $pdf->Cell(30, 7, $k, 0, 0);

        $pdf->Cell(5, 7, ':', 0, 0);

        $pdf->MultiCell(0, 7, $v, 0);
    }

    $pdf->Ln(10);

    $pdf->Cell(
        0,
        6,
        'Yang Mengajukan',
        0,
        1,
        'R'
    );

    $pdf->Ln(20);

    $pdf->Cell(
        0,
        6,
        strtoupper($data['nama']),
        0,
        1,
        'R'
    );

    return $pdf->Output('S');
}
?>