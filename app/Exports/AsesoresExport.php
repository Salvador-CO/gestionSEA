<?php

namespace App\Exports;

use App\Models\Asesor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsesoresExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return Asesor::with(['cargo', 'centro'])->get()->map(function ($a) {
            return [
                'matricula' => $a->matricula,
                'nombre'    => $a->nombre,
                'apellidos' => $a->apellidos,
                'correo'    => $a->correo,
                'cargo'     => $a->cargo->nombre ?? 'Sin cargo',
                'centro'    => $a->centro->nombre ?? 'Sin centro',
            ];
        });
    }

    public function headings(): array
    {
        return ['Matrícula', 'Nombre', 'Apellidos', 'Correo', 'Cargo', 'Centro'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F8A7A']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 15, 'B' => 25, 'C' => 25, 'D' => 35, 'E' => 20, 'F' => 20];
    }
}
