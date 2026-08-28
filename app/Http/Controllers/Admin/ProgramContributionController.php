<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramContribution;
use App\Models\ProgramContributor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class ProgramContributionController extends Controller
{
    public function __construct(){ $this->middleware('permission:view contributions')->only('index'); $this->middleware('permission:create contribution')->only('store'); $this->middleware('permission:update contribution')->only('update'); $this->middleware('permission:delete contribution')->only('destroy'); }
    public function index(Request $request): View
    {
        $programId=$request->integer('program_id'); $q=trim($request->string('q')->toString()); $from=$request->string('date_from')->toString(); $to=$request->string('date_to')->toString();
        $contributions=ProgramContribution::with(['program','contributor'])
            ->when($programId,fn($query)=>$query->where('program_id',$programId))
            ->when($q!=='',fn($query)=>$query->whereHas('contributor',fn($sub)=>$sub->where('name','like',"%{$q}%")->orWhere('father_name','like',"%{$q}%")->orWhere('from_location','like',"%{$q}%")))
            ->when($from,fn($q2)=>$q2->whereDate('contribution_date','>=',$from))->when($to,fn($q2)=>$q2->whereDate('contribution_date','<=',$to))
            ->orderByDesc('contribution_date')->orderByDesc('id')->paginate(20)->withQueryString();
        $programs=Program::latest()->get();
        $total=(float)ProgramContribution::query()->when($programId,fn($q2)=>$q2->where('program_id',$programId))->when($from,fn($q2)=>$q2->whereDate('contribution_date','>=',$from))->when($to,fn($q2)=>$q2->whereDate('contribution_date','<=',$to))->sum('amount');
        return view('admin.program-contributions.index',compact('contributions','programs','programId','total'));
    }
    public function store(Request $request): JsonResponse
    {
        $data=$this->validated($request);
        DB::transaction(function() use($data): void { $contributor=$this->contributor($data); ProgramContribution::create(['program_id'=>$data['program_id'],'contributor_id'=>$contributor->id,'amount'=>$data['amount'],'contribution_date'=>$data['contribution_date'],'details'=>$data['details']??null,'reference'=>$data['reference']??null,'notes'=>$data['notes']??null,'created_by'=>auth()->id()]); });
        return response()->json(['message'=>'Income added successfully.'],201);
    }
    public function update(Request $request, ProgramContribution $contribution): JsonResponse
    {
        $data=$this->validated($request);
        DB::transaction(function() use($data,$contribution): void { $contributor=$this->contributor($data); $contribution->update(['program_id'=>$data['program_id'],'contributor_id'=>$contributor->id,'amount'=>$data['amount'],'contribution_date'=>$data['contribution_date'],'details'=>$data['details']??null,'reference'=>$data['reference']??null,'notes'=>$data['notes']??null]); });
        return response()->json(['message'=>'Income updated successfully.']);
    }
    public function destroy(ProgramContribution $contribution): JsonResponse { $contribution->delete(); return response()->json(['message'=>'Income deleted successfully.']); }
    private function validated(Request $request): array { return $request->validate(['program_id'=>['required','exists:programs,id'],'name'=>['required','string','max:150'],'father_name'=>['nullable','string','max:150'],'from_location'=>['nullable','string','max:180'],'amount'=>['required','numeric','gt:0'],'contribution_date'=>['required','date'],'details'=>['nullable','string','max:3000'],'reference'=>['nullable','string','max:120'],'notes'=>['nullable','string','max:3000']]); }
    private function contributor(array $data): ProgramContributor
    {
        return ProgramContributor::firstOrCreate(['program_id'=>$data['program_id'],'name'=>$data['name'],'father_name'=>$data['father_name']??null,'from_location'=>$data['from_location']??null]);
    }
}
