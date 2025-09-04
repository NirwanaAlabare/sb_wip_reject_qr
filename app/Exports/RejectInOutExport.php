<?php

namespace App\Exports;

use App\Models\SignalBit\Reject;
use App\Models\SignalBit\RejectIn;
use App\Models\SignalBit\RejectOut;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RejectInOutExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $dateFrom, $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function view(): View
    {
        $rejectInOutQuery = RejectIn::selectRaw("
                output_reject_in.created_at time_in,
                output_reject_in.updated_at time_out,
                master_plan.sewing_line sewing_line,
                output_reject_in.output_type,
                output_reject_in.kode_numbering,
                act_costing.kpno no_ws,
                act_costing.styleno style,
                so_det.color color,
                so_det.size size,
                output_defect_types.defect_type defect_type,
                output_defect_areas.defect_area defect_area,
                master_plan.gambar gambar,
                output_reject_in.reject_area_x reject_area_x,
                output_reject_in.reject_area_y reject_area_y,
                output_reject_in.status,
                output_reject_in.grade,
                GROUP_CONCAT(output_defect_types_reject.defect_type SEPARATOR ' , ') defect_types_check,
                GROUP_CONCAT(output_defect_areas_reject.defect_area SEPARATOR ' , ') defect_areas_check,
                GROUP_CONCAT(CONCAT_WS(' // ', output_defect_types_reject.defect_type, output_reject_in_detail.reject_area_x, output_reject_in_detail.reject_area_y) SEPARATOR ' | ') reject_area_position
            ")->
            // Reject
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in.reject_id")->
            // Reject Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in.reject_id")->
            // Reject Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in.reject_id")->
            // Reject Detail
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
            leftJoin("output_reject_in_detail", "output_reject_in_detail.reject_in_id", "=", "output_reject_in.id")->
            leftJoin("output_defect_types as output_defect_types_reject", "output_defect_types_reject.id", "=", "output_reject_in_detail.reject_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_reject", "output_defect_areas_reject.id", "=", "output_reject_in_detail.reject_area_id")->
            // Conditional
            whereBetween("output_reject_in.created_at", [$this->dateFrom." 00:00:00", $this->dateTo." 23:59:59"])->
            groupBy("output_reject_in.id")->
            get();

        return view('exports.reject-in-out', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'rejectInOut' => $rejectInOutQuery
        ]);
    }
}
