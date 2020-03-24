<?php
namespace xfudox\File\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use xfudox\File\Models\File;
use xfudox\File\Repositories\FileRepository;

class FileController extends Controller {

        public function index()
        {
            $files = File::all()->map(function(File $file){
                return [
                    'disk'      => $file->disk,
                    'fullname'  => $file->fullname,
                    'url'       => $file->url,
                ];
            });
            return view('file::index', ['files' => $files]);
        }

        public function upload(Request $request)
        {
            $uploaded_file = $request->file('file');

            app(FileRepository::class)->createFromUploadedFile($uploaded_file);

            return redirect(route('files.index'));
        }


    }
