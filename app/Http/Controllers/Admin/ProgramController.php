<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class ProgramController extends Controller
{
    public function __construct(){ $this->middleware('permission:view programs')->only('index'); $this->middleware('permission:create program')->only('store'); $this->middleware('permission:update program')->only('update'); $this->middleware('permission:delete program')->only('destroy'); }
    public function index(Request $request): View
    {
        $q=trim($request->string('q')->toString()); $status=trim($request->string('status')->toString());
        $programs=Program::query()->withCount('contributors')->withSum('contributions as total_received_sum','amount')->withSum('expenses as total_expenses_sum','amount')
            ->when($q!=='',fn($query)=>$query->where(fn($sub)=>$sub->where('name','like',"%{$q}%")->orWhere('location','like',"%{$q}%")))
            ->when($status!=='',fn($query)=>$query->where('status',$status))->latest()->paginate(20)->withQueryString();
        return view('admin.programs.index',compact('programs','status'));
    }
    public function store(Request $request): JsonResponse { Program::create($this->validated($request)); return response()->json(['message'=>'Program added successfully.'],201); }
    public function update(Request $request, Program $program): JsonResponse { $program->update($this->validated($request)); return response()->json(['message'=>'Program updated successfully.']); }
    public function destroy(Program $program): JsonResponse
    {
        if($program->contributions()->exists()||$program->expenses()->exists()){ $program->update(['status'=>'inactive']); return response()->json(['message'=>'Program has transactions, so it was marked inactive instead of deleted.']); }
        $program->delete(); return response()->json(['message'=>'Program deleted successfully.']);
    }
    private function validated(Request $request): array { return $request->validate(['name'=>['required','string','max:180'],'program_date'=>['nullable','date'],'location'=>['nullable','string','max:180'],'description'=>['nullable','string','max:4000'],'status'=>['required',Rule::in(['active','inactive'])],'notes'=>['nullable','string','max:3000']]); }
}
