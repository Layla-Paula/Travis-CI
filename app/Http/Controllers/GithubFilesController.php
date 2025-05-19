<?php

namespace SegWeb\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Chumper\Zipper\Facades\Zipper;
use SegWeb\File;
use SegWeb\FileResults;
use SegWeb\Http\Controllers\Tools;
use SegWeb\Http\Controllers\FileController;
use SegWeb\Http\Controllers\FileResultsController;
use SegWeb\Http\Controllers\TermController;
use Exception;

class GithubFilesController extends Controller
{
    private $github_files_ids = null;
    private const STORAGE_PATH = 'storage/app/';

    public function index()
    {
        return view('github');
    }

    public function downloadGithub(Request $request)
    {
        if (!Tools::contains('github', $request->github_link)) {
            return $this->respond($request, 'An invalid repository link has been submitted!', 'error');
        }

        $msg = ['text' => 'Repository has been successfully downloaded!', 'type' => 'success'];

        try {
            $user_id = Auth::check() ? Auth::id() : 0;

            $github_link = rtrim($request->github_link, '/');


            $branch = preg_replace('/[^a-zA-Z0-9_\-]/', '', $request->branch);

            $url = $github_link . '/archive/' . $branch . '.zip';
            $folder = 'github_uploads/';
            $now = date('ymdhis');
            $name = $folder . $now . '_' . basename($url);

            $put = Storage::put($name, file_get_contents($url));

            if (!$put) {
                return $this->respond($request, 'An error occurred during repository download', 'error');
            }

            $file_location = base_path(self::STORAGE_PATH . $folder . $now . '_' . $branch);

            Zipper::make(base_path(self::STORAGE_PATH . $name))->extractTo($file_location);
            unlink(base_path(self::STORAGE_PATH . $name));

            $file = new File();
            $file->user_id = $user_id;
            $file->file_path = $folder . $now . '_' . $branch;
            $project_name = explode('/', $github_link);
            $file->original_file_name = end($project_name);
            $file->type = 'Github Repository';
            $file->save();

            $this->analiseGithubFiles($file_location, $file->id);

            $file_contents = null;
            if (!empty($this->github_files_ids)) {
                $file_results_controller = new FileResultsController();
                foreach ($this->github_files_ids as $value) {
                    $file_contents[$value]['content'] = FileController::getFileContentArray($value);
                    $file_contents[$value]['results'] = $file_results_controller->getSingleByFileId($value);
                    $file_contents[$value]['file'] = FileController::getFileById($value);
                }
            }

            if ($request->path() === 'github') {
                return view('github', compact('file', 'file_contents', 'msg'));
            }

            return response()->json($this->getResultArray($file, $file_contents));

        } catch (Exception $e) {
            return $this->respond($request, 'An error occurred', 'error');
        }
    }

    private function respond(Request $request, string $text, string $type)
    {
        $msg = ['text' => $text, 'type' => $type];
        if ($request->path() === 'github') {
            return view('github', compact('msg'));
        }
        return response()->json(['error' => $text]);
    }

    public function analiseGithubFiles($dir, $repository_id)
    {
        $ffs = array_diff(scandir($dir), ['.', '..']);

        if (empty($ffs)) {
            return;
        }

        $term_controller = new TermController();
        $terms = $term_controller->getTerm();

        foreach ($ffs as $ff) {
            $full_file_path = $dir . '/' . $ff;
            $file_path = explode(self::STORAGE_PATH, $full_file_path)[1];

            if (is_dir($full_file_path)) {
                $this->analiseGithubFiles($full_file_path, $repository_id);
                continue;
            }

            $mime = mime_content_type($full_file_path);
            if (!in_array($mime, ['text/x-php', 'application/x-php'])) {
                continue;
            }

            $user_id = Auth::check() ? Auth::id() : 0;

            $file = new File();
            $file->user_id = $user_id;
            $file->file_path = $file_path;
            $file->original_file_name = $ff;
            $file->type = 'Github File';
            $file->repository_id = $repository_id;
            $file->save();

            $this->github_files_ids[] = $file->id;

            $fn = fopen($full_file_path, 'r');
            $line_number = 1;
            while (!feof($fn)) {
                $file_line = fgets($fn);
                foreach ($terms as $term) {
                    if (Tools::contains($term->term, $file_line)) {
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

    public function getResultArray($file, $file_contents)
    {
        $array = [];
        foreach ($file_contents as $value) {
            $file_results = $value['results'];
            $file_path = explode('/', explode($file->original_file_name, $value['file']->file_path)[1]);
            unset($file_path[0]);
            $file_path = $file->original_file_name . '/' . implode('/', $file_path);

            $entry = ['file' => $file_path];
            foreach ($file_results as $results) {
                $entry['problems'][] = [
                    'line' => $results->line_number,
                    'category' => $results->term_type,
                    'problem' => $results->term,
                ];
            }

            $array[] = $entry;
        }

        return $array;
    }
}
