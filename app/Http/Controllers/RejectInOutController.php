<?php

namespace App\Http\Controllers;

use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\RejectInOut;
use App\Exports\RejectInOutExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

class RejectInOutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index($id)
    // {
    //     $orderInfo = MasterPlan::selectRaw("
    //             master_plan.id as id,
    //             master_plan.tgl_plan as tgl_plan,
    //             REPLACE(master_plan.sewing_line, '_', ' ') as sewing_line,
    //             act_costing.kpno as ws_number,
    //             act_costing.styleno as style_name,
    //             mastersupplier.supplier as buyer_name,
    //             so_det.styleno_prod as reff_number,
    //             master_plan.color as color,
    //             so_det.size as size,
    //             so.qty as qty_order,
    //             CONCAT(masterproduct.product_group, ' - ', masterproduct.product_item) as product_type
    //         ")
    //         ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
    //         ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
    //         ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
    //         ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
    //         ->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')
    //         ->leftJoin('masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
    //         ->where('so_det.cancel', 'N')
    //         ->where('master_plan.cancel', 'N')
    //         ->where('master_plan.id', $id)
    //         ->first();

    //     $orderWsDetailsSql = MasterPlan::selectRaw("
    //             master_plan.id as id,
    //             master_plan.tgl_plan as tgl_plan,
    //             master_plan.color as color,
    //             mastersupplier.supplier as buyer_name,
    //             act_costing.styleno as style_name,
    //             mastersupplier.supplier as buyer_name
    //         ")
    //         ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
    //         ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
    //         ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
    //         ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
    //         ->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')
    //         ->leftJoin('masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
    //         ->where('so_det.cancel', 'N')
    //         ->where('master_plan.cancel', 'N');
    //         if (Auth::user()->Groupp != "ALLSEWING") {
    //             $orderWsDetailsSql->where('master_plan.sewing_line', Auth::user()->username);
    //         }
    //     $orderWsDetails = $orderWsDetailsSql->where('act_costing.kpno', $orderInfo->ws_number)
    //         ->where('master_plan.tgl_plan', $orderInfo->tgl_plan)
    //         ->groupBy(
    //             'master_plan.id',
    //             'master_plan.tgl_plan',
    //             'master_plan.color',
    //             'mastersupplier.supplier',
    //             'act_costing.styleno',
    //             'mastersupplier.supplier'
    //         )->get();

    //     return view('production-panel', ['orderInfo' => $orderInfo, 'orderWsDetails' => $orderWsDetails]);
    // }

    public function getMasterPlan(Request $request) {
        $additionalQuery = "";
        if ($request->date) {
            $additionalQuery .= " AND master_plan.tgl_plan = '".$request->date."' ";
        }
        if ($request->line) {
            $additionalQuery .= " AND master_plan.sewing_line = '".$request->line."' ";
        }

        $masterPlans = MasterPlan::selectRaw('
                master_plan.id,
                master_plan.tgl_plan as tanggal,
                master_plan.id_ws as id_ws,
                act_costing.kpno as no_ws,
                act_costing.styleno as style,
                master_plan.color as color
            ')->
            leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')->
            whereRaw('
                master_plan.cancel != "Y"
                '.$additionalQuery.'
            ')->
            get();

        return $masterPlans;
    }

    public function getSize(Request $request) {
        if ($request->master_plan) {
            $sizes = MasterPlan::selectRaw("
                so_det.id,
                so_det.color,
                so_det.size
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("so", "so.id_cost", "=", "act_costing.id")->
            leftJoin("so_det", "so_det.id_so", "=", "so.id")->
            whereRaw("master_plan.id = '".$request->master_plan."'")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("so_det.color", "so_det.size")->
            orderBy("so_det.id")->
            get();

            return $sizes;
        }

        return null;
    }

    public function getDefectType(Request $request) {
        $additionalQuery = "";
        if ($request->date) {
            $additionalQuery .= " AND master_plan.tgl_plan = '".$request->date."' ";
        }
        if ($request->line) {
            $additionalQuery .= " AND master_plan.sewing_line = '".$request->line."' ";
        }
        if ($request->master_plan) {
            $additionalQuery .= " AND master_plan.id = '".$request->master_plan."' ";
        }
        if ($request->size) {
            $additionalQuery .= " AND rejects.so_det_id = '".$request->size."' ";
        }
        if ($request->defect_area) {
            $additionalQuery .= " AND rejects.defect_area_id = '".$request->defect_area."' ";
        }

        $rejects = Reject::selectRaw("
                output_rejects.reject_type_id as id,
                output_defect_types.defect_type,
                COUNT(output_rejects.id) defect_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
            whereRaw("
                output_defect_types.allocation = '".Auth::user()->Groupp."'
                ".$additionalQuery."
            ")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("output_rejects.reject_type_id")->
            orderBy("output_defect_types.defect_type")->
            get();

        return $rejects;
    }

    public function getDefectArea(Request $request) {
        $additionalQuery = "";
        if ($request->date) {
            $additionalQuery .= " AND master_plan.tgl_plan = '".$request->date."' ";
        }
        if ($request->line) {
            $additionalQuery .= " AND master_plan.sewing_line = '".$request->line."' ";
        }
        if ($request->master_plan) {
            $additionalQuery .= " AND master_plan.id = '".$request->master_plan."' ";
        }
        if ($request->size) {
            $additionalQuery .= " AND output_rejects.so_det_id = '".$request->size."' ";
        }
        if ($request->defect_type) {
            $additionalQuery .= " AND output_rejects.reject_type_id = '".$request->defect_type."' ";
        }

        $rejects = Reject::selectRaw("
                output_rejects.reject_area_id as id,
                output_defect_areas.defect_area,
                COUNT(output_rejects.id) defect_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
            whereRaw("
                output_defect_types.allocation = '".Auth::user()->Groupp."'
                ".$additionalQuery."
            ")->
            whereRaw("so_det.color = master_plan.color")->
            groupBy("output_rejects.reject_area_id")->
            orderBy("output_defect_areas.defect_area")->
            get();

        return $rejects;
    }

    public function getRejectInOutDaily(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

        $rejectInOutDaily = RejectInOut::selectRaw("
                DATE(output_reject_in_out.created_at) tanggal,
                SUM(CASE WHEN (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_rejects.id END) END) IS NOT NULL THEN 1 ELSE 0 END) total_in,
                SUM(CASE WHEN (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_rejects.id END) END) IS NOT NULL AND output_reject_in_out.status = 'defect' THEN 1 ELSE 0 END) total_process,
                SUM(CASE WHEN (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_rejects.id END) END) IS NOT NULL AND output_reject_in_out.status = 'reworked' THEN 1 ELSE 0 END) total_out
            ")->
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in_out.reject_id")->
            where("output_reject_in_out.type", strtolower(Auth::user()->Groupp))->
            whereBetween("output_reject_in_out.created_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
            groupByRaw("DATE(output_reject_in_out.created_at)")->
            get();

        return DataTables::of($rejectInOutDaily)->toJson();
    }

    public function getRejectInOutDetail(Request $request) {
        $rejectInOutQuery = RejectInOut::selectRaw("
                output_reject_in_out.created_at time_in,
                output_reject_in_out.reworked_at time_out,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) sewing_line,
                output_reject_in_out.output_type,
                output_reject_in_out.kode_numbering,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) no_ws,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) style,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN so_det_packing.color ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN so_det_finish.color ELSE so_det.color END) END) color,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) size,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) defect_type,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_defect_areas_packing.defect_area ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_defect_areas_finish.defect_area ELSE output_defect_areas.defect_area END) END) defect_area,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.gambar ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.gambar ELSE master_plan.gambar END) END) gambar,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.reject_area_x ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.defect_area_x ELSE output_rejects.reject_area_x END) END) reject_area_x,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.reject_area_y ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.defect_area_y ELSE output_rejects.reject_area_y END) END) reject_area_y,
                output_reject_in_out.status
            ")->
            // Defect
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
            // Defect Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_defect_types as output_defect_types_packing", "output_defect_types_packing.id", "=", "output_rejects_packing.reject_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_packing", "output_defect_areas_packing.id", "=", "output_rejects_packing.reject_area_id")->
            leftJoin("so_det as so_det_packing", "so_det_packing.id", "=", "output_rejects_packing.so_det_id")->
            leftJoin("so as so_packing", "so_packing.id", "=", "so_det_packing.id_so")->
            leftJoin("act_costing as act_costing_packing", "act_costing_packing.id", "=", "so_packing.id_cost")->
            leftJoin("master_plan as master_plan_packing", "master_plan_packing.id", "=", "output_rejects_packing.master_plan_id")->
            // Defect Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_defect_types as output_defect_types_finish", "output_defect_types_finish.id", "=", "output_check_finishing.defect_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_finish", "output_defect_areas_finish.id", "=", "output_check_finishing.defect_area_id")->
            leftJoin("so_det as so_det_finish", "so_det_finish.id", "=", "output_check_finishing.so_det_id")->
            leftJoin("so as so_finish", "so_finish.id", "=", "so_det_finish.id_so")->
            leftJoin("act_costing as act_costing_finish", "act_costing_finish.id", "=", "so_finish.id_cost")->
            leftJoin("master_plan as master_plan_finish", "master_plan_finish.id", "=", "output_check_finishing.master_plan_id")->
            // Conditional
            where("output_reject_in_out.type", strtolower(Auth::user()->Groupp))->
            whereBetween("output_reject_in_out.created_at", [$request->tanggal." 00:00:00", $request->tanggal." 23:59:59"])->
            whereRaw("
                (
                    output_reject_in_out.id IS NOT NULL AND
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qc' THEN output_rejects.id ELSE null END) END) END) IS NOT NULL
                    ".($request->line ? "AND (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE (CASE WHEN output_reject_in_out.output_type = 'qc' THEN master_plan.sewing_line ELSE null END) END) END) LIKE '%".$request->line."%'" : "")."
                    ".($request->departemen && $request->departemen != "all" ? "AND output_reject_in_out.output_type = '".$request->departemen."'" : "")."
                )
            ")->
            groupBy("output_reject_in_out.id")->
            get();

            return DataTables::of($rejectInOutQuery)->toJson();
    }

    public function getRejectInOutDetailTotal(Request $request) {
        $rejectInOutQuery = RejectInOut::selectRaw("
                output_reject_in_out.created_at time_in,
                output_reject_in_out.reworked_at time_out,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) sewing_line,
                output_reject_in_out.output_type,
                output_reject_in_out.kode_numbering,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) no_ws,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) style,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN so_det_packing.color ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN so_det_finish.color ELSE so_det.color END) END) color,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) size,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) defect_type,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_defect_areas_packing.defect_area ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_defect_areas_finish.defect_area ELSE output_defect_areas.defect_area END) END) defect_area,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.gambar ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.gambar ELSE master_plan.gambar END) END) gambar,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.reject_area_x ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.defect_area_x ELSE output_rejects.reject_area_x END) END) reject_area_x,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.reject_area_y ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.defect_area_y ELSE output_rejects.reject_area_y END) END) reject_area_y,
                output_reject_in_out.status
            ")->
            // Defect
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
            // Defect Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_defect_types as output_defect_types_packing", "output_defect_types_packing.id", "=", "output_rejects_packing.reject_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_packing", "output_defect_areas_packing.id", "=", "output_rejects_packing.reject_area_id")->
            leftJoin("so_det as so_det_packing", "so_det_packing.id", "=", "output_rejects_packing.so_det_id")->
            leftJoin("so as so_packing", "so_packing.id", "=", "so_det_packing.id_so")->
            leftJoin("act_costing as act_costing_packing", "act_costing_packing.id", "=", "so_packing.id_cost")->
            leftJoin("master_plan as master_plan_packing", "master_plan_packing.id", "=", "output_rejects_packing.master_plan_id")->
            // Defect Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_defect_types as output_defect_types_finish", "output_defect_types_finish.id", "=", "output_check_finishing.defect_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_finish", "output_defect_areas_finish.id", "=", "output_check_finishing.defect_area_id")->
            leftJoin("so_det as so_det_finish", "so_det_finish.id", "=", "output_check_finishing.so_det_id")->
            leftJoin("so as so_finish", "so_finish.id", "=", "so_det_finish.id_so")->
            leftJoin("act_costing as act_costing_finish", "act_costing_finish.id", "=", "so_finish.id_cost")->
            leftJoin("master_plan as master_plan_finish", "master_plan_finish.id", "=", "output_check_finishing.master_plan_id")->
            // Conditional
            where("output_reject_in_out.type", strtolower(Auth::user()->Groupp))->
            whereBetween("output_reject_in_out.created_at", [$request->tanggal." 00:00:00", $request->tanggal." 23:59:59"])->
            whereRaw("
                (
                    output_reject_in_out.id IS NOT NULL AND
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qc' THEN output_rejects.id ELSE null END) END) END) IS NOT NULL
                    ".($request->line ? "AND (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE (CASE WHEN output_reject_in_out.output_type = 'qc' THEN master_plan.sewing_line ELSE null END) END) END) LIKE '%".$request->line."%'" : "")."
                    ".($request->departemen && $request->departemen != "all" ? "AND output_reject_in_out.output_type = '".$request->departemen."'" : "")."
                )
            ")->
            groupBy("output_reject_in_out.id")->
            get();

        return array("defectIn" => $rejectInOutQuery->count(), "defectProcess" => $rejectInOutQuery->where("status", "reject")->count(), "defectOut" => $rejectInOutQuery->where("status", "reworked")->count());
    }

    public function exportRejectInOut(Request $request) {
        return Excel::download(new RejectInOutExport($request->dateFrom, $request->dateTo), 'Report Reject In Out.xlsx');
    }
}
