<?php

namespace App\Exports;

use App\Models\SignalBit\Reject;
use App\Models\SignalBit\RejectIn;
use App\Models\SignalBit\RejectOut;
use App\Models\SignalBit\RejectOutDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;
use DB;

class RejectWipExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $kode_numbering, $waktu, $department, $line, $ws, $style, $size, $quality_check, $grade, $defect_type_check, $defect_area_check;

    public function __construct($kode_numbering, $waktu, $department, $line, $ws, $style, $size, $quality_check, $grade, $defect_type_check, $defect_area_check)
    {
        $this->kode_numbering = $kode_numbering;
        $this->waktu = $waktu;
        $this->department = $department;
        $this->line = $line;
        $this->ws = $ws;
        $this->style = $style;
        $this->size = $size;
        $this->quality_check = $quality_check;
        $this->grade = $grade;
        $this->defect_type_check = $defect_type_check;
        $this->defect_area_check = $defect_area_check;
    }

    public function view(): View
    {
        $additionalFilter = " output_reject_in.id is not null ";
        if ($this->kode_numbering) {
            $additionalFilter .= " and COALESCE(output_reject_in.kode_numbering, output_reject_in.id) LIKE '%".$this->kode_numbering."%'";
        }
        if ($this->waktu) {
            $additionalFilter .= " and output_reject_in.updated_at LIKE '%".$this->waktu."%'";
        }
        if ($this->department) {
            $additionalFilter .= " and output_reject_in.output_type LIKE '%".$this->department."%'";
        }
        if ($this->line) {
            $additionalFilter .= " and userpassword.username LIKE '%".$this->line."%'";
        }
        if ($this->ws) {
            $additionalFilter .= " and act_costing.kpno LIKE '%".$this->ws."%'";
        }
        if ($this->style) {
            $additionalFilter .= " and act_ocsting.styleno LIKE '%".$this->style."%'";
        }
        if ($this->size) {
            $additionalFilter .= " and so_det.size LIKE '%".$this->size."%'";
        }
        if ($this->quality_check) {
            $additionalFilter .= " and (CASE WHEN output_reject_in.status = 'reworked' THEN 'GOOD' ELSE 'REJECT' END) LIKE '%".$this->quality_check."%'";
        }
        if ($this->grade) {
            $additionalFilter .= " and output_reject_in.grade LIKE '%".$this->grade."%'";
        }
        if ($this->defect_type_check) {
            $additionalFilter .= " and output_defect_types.defect_type LIKE '%".$this->defect_type_check."%'";
        }
        if ($this->defect_area_check) {
            $additionalFilter .= " and output_defect_areas.defect_area LIKE '%".$this->defect_area_check."%'";
        }

        $rejectOut = RejectIn::selectRaw("
                output_reject_in.id,
                output_reject_in.kode_numbering,
                output_reject_in.updated_at,
                output_reject_in.output_type,
                userpassword.username,
                act_costing.id as act_costing_id,
                act_costing.kpno,
                act_costing.styleno,
                so_det.color,
                so_det.size,
                output_reject_in.status,
                output_reject_in.grade,
                GROUP_CONCAT(output_defect_types.defect_type SEPARATOR ' , ') defect_types,
                GROUP_CONCAT(output_defect_areas.defect_area SEPARATOR ' , ') defect_areas,
                GROUP_CONCAT(CONCAT_WS(' // ', output_defect_types.defect_type, output_reject_in_detail.reject_area_x, output_reject_in_detail.reject_area_y) SEPARATOR ' | ') reject_area_position,
                master_plan.gambar,
                CONCAT(act_costing.id, so_det.color, so_det.size, output_reject_in.grade) grouping
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("userpassword", "userpassword.line_id", "=", "output_reject_in.line_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
            leftJoin("output_reject_in_detail", "output_reject_in_detail.reject_in_id", "=", "output_reject_in.id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in_detail.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in_detail.reject_area_id")->
            leftJoin("output_reject_in_detail_position", "output_reject_in_detail_position.reject_in_detail_id", "=", "output_reject_in_detail.id")->
            where("output_reject_in.process", "wip")->
            whereRaw($additionalFilter)->
            whereRaw("output_reject_in.updated_at > (NOW() - INTERVAL 6 MONTH)")->
            groupBy("output_reject_in.id")->
            get();

        return view('exports.reject-out-wip', [
            'waktu_export' => Carbon::now()->format('d-m-Y H:i:s'),
            'kode_numbering' => $this->kode_numbering,
            'waktu' => $this->waktu,
            'department' => $this->department,
            'line' => $this->line,
            'ws' => $this->ws,
            'style' => $this->style,
            'size' => $this->size,
            'quality_check' => $this->quality_check,
            'grade' => $this->grade,
            'defect_type_check' => $this->defect_type_check,
            'defect_area_check' => $this->defect_area_check,
            'rejectOut' => $rejectOut
        ]);
    }
}
