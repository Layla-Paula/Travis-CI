<?php

namespace SegWeb\Http\Controllers;
use Auth;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SegWeb\File;
use SegWeb\FileResults;
use SegWeb\Http\Controllers\Tools;
use SegWeb\Http\Controllers\FileController;
use SegWeb\Http\Controllers\FileResultsController;

class GithubFilesController extends Controller {
    private $github_files_ids = NULL;

    public function index() {
        return view('github');
    }

    public function downloadGithub(Request $request) {
        if(Tools::contains("github", $request->github_link)) {
            $msg = ['text' => 'Repository has been successfully downloaded!', 'type' => 'success'];
            try {
                if(Auth::check()) {
                    $user = Auth::user();
                    $user_id = $user->id;
                } else {
                    $user_id = 0;
                }
                // Baixa o arquivo .zip do github
                $github_link = substr($request->github_link, -1) == '/' ? substr_replace($request->github_link ,"", -1)  : $request->github_link;
                
                $url = $github_link.'/archive/'.$request->branch.'.zip';
                $folder = 'github_uploads/';
                $now = date('ymdhis');
                $name = $folder.$now.'_'.substr($url, strrpos($url, '/') + 1);
                $put = Storage::put($name, file_get_contents($url));

                if($put) {
                    // Extrai e exclui o arquivo .zip do github
                    $file_location = base_path('storage/app/'.$folder.$now.'_'.$request->branch);
                    Zipper::make(base_path('storage/app/'.$name))->extractTo($file_location);
                    unlink(base_path('storage/app/'.$name));
                    
                    // Salva o registro do repositório do github
                    $file = new File();
                    $file->user_id = $user_id;
                    $file->file_path = $folder.$now.'_'.$request->branch;
                    $project_name = explode('/', $github_link);
                    $file->original_file_name = $project_name[sizeof($project_name) - 1];
                    $file->type = "Github Repository";
                    $file->save();

                    // Realiza a análise dos arquivos do repositório
                    $this->analiseGithubFiles($file_location, $file->id);
                    
                    // Busca o conteúdo dos arquivos para exibição
                    $file_results_controller = new FileResultsController();
                    $file_contents = NULL;
                    if(!empty($this->github_files_ids)) {
                        foreach($this->github_files_ids as $value) {
                            $file_contents[$value]['content'] = FileController::getFileContentArray($value);
                            $file_contents[$value]['results'] = $file_results_controller->getSingleByFileId($value);
                            $file_contents[$value]['file'] = FileController::getFileById($value);
                        }
                    }
                    
                    if($request->path() == "github") {
                        return view('github', compact(['file', 'file_contents', 'msg']));
                    } else {
                        header("Content-type:application/json");
                        echo json_encode($this->getResultArray($file, $file_contents));
                    }
                } else {
                    $msg['text'] = "An error occurred during repository download";
                    $msg['type'] = "error";
                    if($request->path() == "github") {
                        return view('github', compact(['msg']));
                    } else {
                        header("Content-type:application/json");
                        echo json_encode(['error' => $msg['text']]);
                    }
                }
            } catch (Exception $e) {
                $msg['text'] = "An error occurred";
                $msg['type'] = "error";
                if($request->path() == "github") {
                    return view('github', compact(['msg']));
                } else {
                    header("Content-type:application/json");
                    echo json_encode(['error' => $msg['text']]);
                }
            }
        } else {
            $msg['text'] = "An invalid repository link has been submitted!";
            $msg['type'] = "error";
            if($request->path() == "github") {
                return view('github', compact(['msg']));
            } else {
                header("Content-type:application/json");
                echo json_encode(['error' => $msg['text']]);
            }
        }
    }  

    public function analiseGithubFiles($dir, $repository_id) {
        $ffs = scandir($dir);
        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        if(!empty($ffs)) {
            $term = new TermController();
            $terms = $term->getTerm();
            foreach($ffs as $ff) {
                $full_file_path = $dir."/".$ff;
                $file_path = explode("storage/app/", $full_file_path)[1];
                if(is_dir($full_file_path)) {
                    $this->analiseGithubFiles($full_file_path, $repository_id);
                } else {
                    if(mime_content_type($full_file_path) == "text/x-php" || mime_content_type($full_file_path) == "application/x-php") {
                        if(Auth::check()) {
                            $user = Auth::user();
                            $user_id = $user->id;
                        } else {
                            $user_id = 0;
                        }
                        $file = new File();
                        $file->user_id = $user_id;
                        $file->file_path = $file_path;
                        $file->original_file_name = $ff;
                        $file->type = "Github File";
                        $file->repository_id = $repository_id;
                        $file->save();

                        $this->github_files_ids[] = $file->id;

                        $fn = fopen($full_file_path, 'r');
                        $line_number = 1;
                        while(!feof($fn)) {
                            $file_line = fgets($fn);
                            foreach($terms as $term) {
                                if(Tools::contains($term->term, $file_line)) {
                                    $file_results = new FileResults();
                                    $file_results->file_id = $file->id;
                                    $file_results->line_number = $line_number;
                                    $file_results->term_id = $term->id;
                                    $file_results->save();
                                }
                            }
                            $line_number++;
                        }
                        fclose($fn);
                    }
                }
            }
        }
    }

    public function getResultArray($file, $file_contents) {
        $array = [];
        foreach($file_contents as $value) {
            $file_results = $value['results'];
            $file_path = explode('/', explode($file->original_file_name, $value['file']->file_path)[1]);
            unset($file_path[0]);
            $file_path = $file->original_file_name.'/'.implode('/', $file_path);

            $array[] = ['file' => $file_path];

            foreach ($file_results as $results) {
                $array['problems'][] = [
                    'line' => $results->line_number,
                    'category' => $results->term_type,
                    'problem' => $results->term
                ];
            }
        }
        return $array;
    }
}
