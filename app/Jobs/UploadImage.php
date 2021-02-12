<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Design;
use Image;
use File;
use Illuminate\Support\Facades\Storage;

class UploadImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $design;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Design $design)
    {
        $this->design=$design;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $disk = $this->design->disk;
        $filename =  $this->design->image;
        $original_file = storage_path() . '/uploads/original/'. $filename;

        try {
            // create the Large and save to tmp disk
          //  Image::make($original_file)->resize(800, 600)
          //               ->save($large = storage_path('uploads/large/'. $filename));

            Image::make($original_file)->fit(800, 600, function($constraint){
                           $constraint->aspectRatio();
                        })->save($large = storage_path('uploads/large/'. $filename));

            // create the thumbnail image
            Image::make($original_file)
                        ->fit(250, 200, function($constraint){
                            $constraint->aspectRatio();
                        })
                     ->save($thumbnail = storage_path('uploads/thumbnail/'. $filename));

                // store image to permanent disk
                // Original image
               if (Storage::disk($disk)
                          ->put('uploads/designs/original/'.$filename, fopen($original_file, 'r+'))){
                                File::delete($original_file);
                    }

                // Large image
                if (Storage::disk($disk)
                          ->put('uploads/designs/large/'.$filename, fopen($large, 'r+'))){
                            File::delete($large);
                    }

                // thumbnail image
                if (Storage::disk($disk)
                         ->put('uploads/designs/thumbnail/'.$filename, fopen($thumbnail, 'r+'))){
                           File::delete($thumbnail);
                    }

                // Update the database record with success flag
                $this->design->update([
                    'upload_successfull' => true
                ]);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
