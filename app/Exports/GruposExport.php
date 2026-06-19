<?php

namespace App\Exports;

use App\Models\Grupo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GruposExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return Grupo::with(['centro', 'asignatura', 'asesor'])->get()->map(function ($g) {
            return [
                'codigo_moodle' => $g->codigo_moodle,
                'centro'        => $g->centro->nombre ?? 'Sin centro',
                'asignatura'    => $g->asignatura->nombre ?? 'Sin asignatura',
                'clave_asig'    => $g->asignatura->clave ?? '',
                'semestre'      => $g->asignatura->semestre ?? '',
                'asesor'        => $g->asesor
                                    ? mb_strtoupper($g->asesor->nombre . ' ' . $g->asesor->apellidos, 'UTF-8')
                                    : 'Sin asesor',
                'correo_asesor' => $g->asesor->correo ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return ['Código Moodle', 'Centro', 'Asignatura', 'Clave', 'Semestre', 'Asesor Asignado', 'Correo Asesor'];
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
        return ['A' => 20, 'B' => 18, 'C' => 30, 'D' => 10, 'E' => 10, 'F' => 35, 'G' => 35];
    }
}
