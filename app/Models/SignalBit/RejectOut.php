<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class RejectOut extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_reject_out_detail';

    protected $fillable = [
        'id',
        'reject_out_id',
        'no_trans',
        'tujuan',
        'created_by',
        'created_by_username',
        'created_at',
        'updated_at',
    ];

    public static function lastId(): string
    {
        $prefix = "R".date("dmy");
        $max = DB::table("output_reject_out_detail")->selectRaw("MAX(CAST(SUBSTRING_INDEX(no_trans, '/', -1) AS UNSIGNED)) as max_id")->whereRaw("SUBSTRING_INDEX(no_trans, '/', 0) LIKE '%" . $prefix . "%'")->value('max_id');

        return $max ? $max : 0;
    }

    public function rejectInOut()
    {
        return $this->belongsTo(RejectInOut::class, 'reject_in_id', 'id');
    }
}
