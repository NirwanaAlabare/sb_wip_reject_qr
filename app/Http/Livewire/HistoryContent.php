<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\RejectInOut;

class HistoryContent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $dateFrom;
    public $dateTo;
    public $rejectInOutSearch;

    public function mount()
    {
        $this->dateFrom = $this->dateFrom ? $this->dateFrom : date('Y-m-d');
        $this->dateTo = $this->dateTo ? $this->dateTo : date('Y-m-d');
    }

    public function updatingRejectInOutSearch()
    {
        $this->resetPage("rejectInOutPage");
    }

    public function render()
    {
        $masterPlan = session()->get('orderInfo');
        $this->masterPlan = $masterPlan ? $masterPlan->id : null;

        $rejectInOutQuery = RejectInOut::selectRaw("
                COALESCE(output_reject_in_out.reworked_at, output_reject_in_out.updated_at) time,
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_rejects.reject_type_id,
                output_defect_types.defect_type,
                output_rejects.so_det_id,
                so_det.size,
                COUNT(output_reject_in_out.id) qty,
                output_reject_in_out.status
            ")->
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
            where("output_reject_in_out.type", Auth::user()->Groupp);
            if ($this->rejectInOutSearch) {
                $rejectInOutQuery->whereRaw("(
                    COALESCE(output_reject_in_out.reworked_at, output_reject_in_out.updated_at) LIKE '%".$this->rejectInOutSearch."%' OR
                    master_plan.tgl_plan LIKE '%".$this->rejectInOutSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->rejectInOutSearch."%' OR
                    act_costing.kpno LIKE '%".$this->rejectInOutSearch."%' OR
                    act_costing.styleno LIKE '%".$this->rejectInOutSearch."%' OR
                    master_plan.color LIKE '%".$this->rejectInOutSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->rejectInOutSearch."%' OR
                    output_reject_in_out.status LIKE '%".$this->rejectInOutSearch."%' OR
                    so_det.size LIKE '%".$this->rejectInOutSearch."%'
                )");
            }
            if ($this->dateFrom) {
                $rejectInOutQuery->whereRaw("DATE(COALESCE(output_reject_in_out.reworked_at, output_reject_in_out.updated_at)) >= '".$this->dateFrom."'");
            }
            if ($this->dateTo) {
                $rejectInOutQuery->whereRaw("DATE(COALESCE(output_reject_in_out.reworked_at, output_reject_in_out.updated_at)) <= '".$this->dateTo."'");
            }
            $latestRejectInOut = $rejectInOutQuery->
                groupByRaw("
                    master_plan.sewing_line,
                    master_plan.id,
                    output_defect_types.id,
                    output_rejects.so_det_id,
                    COALESCE(output_reject_in_out.reworked_at, output_reject_in_out.updated_at)
                ")->
                orderBy("output_reject_in_out.updated_at", "desc")->
                orderBy("output_reject_in_out.reworked_at", "desc")->
                paginate(10, ['*'], 'lastRejectInOut');

        return view('livewire.history-content', [
            'latestRejectInOut' => $latestRejectInOut,
        ]);
    }
}
