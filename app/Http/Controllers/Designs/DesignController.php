<?php

namespace App\Http\Controllers\Designs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Design;
use App\Http\Resources\DesignResource;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Contracts\IDesign;
use App\Repositories\Eloquent\Criteria\{LatestFirst, IsLive, ForUser};
use App\Repositories\Eloquent\Criteria\EagerLoad;
class DesignController extends Controller
{
    protected $designs;
    public function __construct(IDesign $designs){
      $this->designs=$designs;
    }
    public function index(){
      $designs=$this->designs->withCriteria([
        new LatestFirst(),
        new IsLive(),
        new ForUser(4),
        new EagerLoad(['user', 'comments'])
      ])->all();
      return DesignResource::collection($designs);
    }
    public function findDesign($id){
      $design=$this->designs->find($id);
      return new DesignResource($design);
    }
    public function update(Request $request, $id){
        $design=$this->designs->find($id);
       $this->authorize('update', $design);
    	$this->validate($request, [
             'title'=>['required', 'unique:designs,title,'. $id],
             'description'=>['required', 'string', 'min:20', 'max:140'],
             'tags'=>['required'],
             'team'=>['required_if:assign_to_team,true']
    	]);
    	$design=$this->designs->update($id, [
          'title'=>$request->title,
          'team'=>$request->team,
          'description'=>$request->description,
          'slug'=>Str::slug($request->title),
         // 'is_live'=>$request->is_live
          'is_live'=> ! $design->upload_successfull ? false : $request->is_live
    	]);
      $this->designs->applyTags($id, $request->tags);
    	return new DesignResource($design);
    }
    public function destroy($id){
      $design=$this->designs->find($id);
       $this->authorize('delete', $design);
       foreach(['thumbnail', 'large', 'original'] as $size){
        if(Storage::disk($design->disk)->exists("uploads/designs/{$size}/".$design->image)){
            Storage::disk($design->disk)->delete("uploads/designs/{$size}/".$design->image);
        }
       }
       $this->designs->delete($id);
       return response()->json(['message'=>'Record deleted'], 200);
    }
    public function like($id){
      $this->designs->like($id);
      return response()->json(['message'=>'Successful'], 200);
    }
    public function checkIfUserHasLiked($designId){
       $isLiked=$this->designs->isLikedByUser($designId);
       return response()->json(['liked' => $isLiked], 200);
    }
    public function search(Request $request){
      $design=$this->designs->search($request);
      return DesignResource::collection($design);
    }
    public function findBySlug($slug){
      $design=$this->designs->withCriteria([new isLive()])->findWhereFirst('slug', $slug);
      return new DesignResource($design);
    }
     public function getForTeam($teamId){
      $designs=$this->designs->withCriteria([new isLive()])->findWhere('team_id', $teamId);
      return DesignResource::collection($designs);
    }
    public function getForUser($userId){
      $designs=$this->designs->withCriteria([new isLive()])->findWhere('user_id', $userId);
      return DesignResource::collection($designs);
    }
    public function userOwnsDesign($id){
      $design=$this->designs->withCriteria(
           [new ForUser(auth()->id())])
           ->findWhereFirst('id', $id);
           return new DesignResource($design);
    }
}
