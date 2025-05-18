<?php

namespace SegWeb\Http\Controllers;


use Illuminate\Http\Request;
use SegWeb\File;
use SegWeb\Http\Controllers\Tools;
use SegWeb\Http\Controllers\TermController;
use SegWeb\Http\Controllers\FileResultsController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Auth;
use SegWeb\FileResults;

class FileController extends Controller {
    public function index() {
        return view('index', ['file_content' => null, 'originalname' => null]);
    }

    public function getJsonTerms() {
        $json_file = Storage::disk('local')->get('terms/terms.json');
        return json_decode($json_file, true);
    }

    public function submitFile(Request $request) {
        $msg = [
            'text' => 'Successfully Submitted!',
            'type' => 'success'
        ];
        if($request->file('file')->getClientMimeType() == 'application/x-php') {
            if(Auth::check()) {
                $user = Auth::user();
                $user_id = $user->id;
            } else {
                $user_id = 0;
            }
            
            $file = new File();
            $file->user_id = $user_id;
            $file->file_path = $request->file('file')->store('uploads', 'local');
            $file->original_file_name = $request->file('file')->getClientOriginalName();
            $file->type = "File";
            $file->save();
            
            $file_content = $this->analiseFile($file->id);
            $file_results_controller = new FileResultsController();
            return view('index', [
                'file' => $file, 
                'file_results' => $file_results_controller->getSingleByFileId($file->id), 
                'file_content' => $file_content, 
                'msg' => $msg
            ]);
        } else {
            $msg['text'] = "File format not allowed! Please, submit a PHP file.";
            $msg['type'] = "error";
            return view('index', ['msg' => $msg]);
        }
    }

    public static function getFileById($id) {
        return DB::table('files')->find($id);
    }

    public function analiseFile($id_file) {
        try {
            $file = $this->getFileById($id_file);
            $term = new TermController();
            $terms = $term->getTerm();

            $file_location = Storage::disk('local')->getDriver()->getAdapter()->applyPathPrefix($file->file_path);
            $fn = fopen("$file_location","r");
            $line_number = 1;
            $file_content = NULL;
            while(!feof($fn)) {
                $file_line = fgets($fn);

                foreach($terms as $term) {
                    if(Tools::contains($term->term, $file_line)) {
                        $file_results = new FileResults();
                        $file_results->file_id = $id_file;
                        $file_results->line_number = $line_number;
                        $file_results->term_id = $term->id;
                        $file_results->save();
                    }
                }
                $file_content[$line_number] = $file_line;
                $line_number++;
            }
            fclose($fn);
            return $file_content;
        } catch (Illuminate\Contracts\Filesystem\FileNotFoundException $exception) {
            return "File Not Found!";
        }
    }

    public function indexYourFiles() {
        $files = $this->getAllByUserId();
        return view('your_files', compact('files'));
    }

    public function getAllByUserId() {
        $user = Auth::user();
        return File::where('user_id', $user->id)->where('type', '<>', 'Github File')->orderByRaw('id DESC')->get();
    }

    public static function getFileContentArray($id_file) {
        $file = DB::table('files')->find($id_file);
        $full_file_path = base_path('storage/app/'.$file->file_path);
        if(file_exists($full_file_path)) {
            $fn = fopen($full_file_path, 'r');
            $line_number = 1;
            $file_content = NULL;
            while(!feof($fn)) {
                $file_line = fgets($fn);
                $file_content[$line_number] = $file_line;
                $line_number++;
            }
            fclose($fn);
            return $file_content;
        } else {
            return NULL;
        }
    }

    public function getGithubFiles($file_id) {
        return DB::table('files')->where('repository_id', $file_id)->get();
    }
}