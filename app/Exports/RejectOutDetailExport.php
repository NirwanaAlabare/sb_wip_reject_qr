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

class RejectOutDetailExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $tanggal_awal, $tanggal_akhir, $tanggal, $no_transaksi, $tujuan, $kpno, $styleno, $color, $size;

    public function __construct($tanggal_awal, $tanggal_akhir, $tanggal, $no_transaksi, $tujuan, $kpno, $styleno, $color, $size)
    {
        $this->tanggal_awal = $tanggal_awal;
        $this->tanggal_akhir = $tanggal_akhir;
        $this->tanggal = $tanggal;
        $this->no_transaksi = $no_transaksi;
        $this->tujuan = $tujuan;
        $this->kpno = $kpno;
        $this->styleno = $styleno;
        $this->color = $color;
        $this->size = $size;
    }

    public function view(): View
    {
        $rangeFilter = " tanggal is not null ";
        if ($this->tanggal_awal) {
            $rangeFilter .= "and tanggal >= '".$this->tanggal_awal."'";
        }
        if ($this->tanggal_akhir) {
            $rangeFilter .= "and tanggal <= '".$this->tanggal_akhir."'";
        }

        $additionalFilter = " output_reject_out.id is not null ";
        if ($this->tanggal) {
            $additionalFilter .= " and output_reject_out.tanggal LIKE '%".$this->tanggal."%'";
        }
        if ($this->no_transaksi) {
            $additionalFilter .= " and output_reject_out.no_transaksi LIKE '%".$this->no_transaksi."%'";
        }
        if ($this->tujuan) {
            $additionalFilter .= " and output_reject_out.tujuan LIKE '%".$this->tujuan."%'";
        }
        if ($this->kpno) {
            $additionalFilter .= " and act_costing.kpno LIKE '%".$this->kpno."%'";
        }
        if ($this->styleno) {
            $additionalFilter .= " and act_costing.styleno LIKE '%".$this->styleno."%'";
        }
        if ($this->color) {
            $additionalFilter .= " and act_costing.color LIKE '%".$this->color."%'";
        }
        if ($this->size) {
            $additionalFilter .= " and act_costing.size LIKE '%".$this->size."%'";
        }

        $rejectOut = RejectOut::selectRaw("
                concat(output_reject_out.id, act_costing.id, so_det.color, so_det.size) group_key
            ")->
            leftJoin("output_reject_out_detail", "output_reject_out_detail.reject_out_id", "=", "output_reject_out.id")->
            leftJoin("output_reject_in", "output_reject_in.id", "=", "output_reject_out_detail.reject_in_id")->
            leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            where("output_reject_in.process", "sent")->
            whereRaw($rangeFilter)->
            whereRaw($additionalFilter)->
            whereRaw("output_reject_out_detail.updated_at > (NOW() - INTERVAL 6 MONTH)")->
            groupBy("output_reject_out.id", "act_costing.id", "so_det.color", "so_det.size")->
            pluck("group_key")->
            toArray();

        $rejectOutDetail = RejectOutDetail::selectRaw("
            output_reject_out.tanggal,
            output_reject_out.no_transaksi,
            output_reject_out.tujuan,
            output_reject_in.kode_numbering,
            act_costing.kpno,
            act_costing.styleno,
            so_det.color,
            so_det.size,
            output_reject_in.status,
            output_reject_in.grade,
            GROUP_CONCAT(output_defect_types.defect_type SEPARATOR ' , ') defect_types,
            GROUP_CONCAT(output_defect_areas.defect_area SEPARATOR ' , ') defect_areas
        ")->
        leftJoin("output_reject_out", "output_reject_out_detail.reject_out_id", "=", "output_reject_out.id")->
        leftJoin("output_reject_in", "output_reject_out_detail.reject_in_id", "=", "output_reject_in.id")->
        leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
        leftJoin("so", "so.id", "=", "so_det.id_so")->
        leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
        leftJoin("userpassword", "userpassword.line_id", "=", "output_reject_in.line_id")->
        leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
        leftJoin("output_reject_in_detail", "output_reject_in_detail.reject_in_id", "=", "output_reject_in.id")->
        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in_detail.reject_type_id")->
        leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in_detail.reject_area_id")->
        leftJoin("output_reject_in_detail_position", "output_reject_in_detail_position.reject_in_detail_id", "=", "output_reject_in_detail.id")->
        where("output_reject_in.process", "sent")->
        whereIn(DB::raw("concat(output_reject_out.id, act_costing.id, so_det.color, so_det.size)"), $rejectOut)->
        whereRaw("output_reject_out.updated_at > (NOW() - INTERVAL 6 MONTH)")->
        groupBy("output_reject_out_detail.id")->
        get();

        return view('exports.reject-out-detail', [
            'waktu' => Carbon::now()->format('d-m-Y H:i:s'),
            'tanggal_awal' => $this->tanggal_awal,
            'tanggal_akhir' => $this->tanggal_akhir,
            'tanggal' => $this->tanggal,
            'no_transaksi' => $this->no_transaksi,
            'tujuan' => $this->tujuan,
            'kpno' => $this->kpno,
            'styleno' => $this->styleno,
            'color' => $this->color,
            'size' => $this->size,
            'rejectOutDetail' => $rejectOutDetail
        ]);
    }
}
