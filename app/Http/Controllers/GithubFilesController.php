<?php

namespace SegWeb\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
    private const GITHUB_FOLDER = 'github_uploads/';

    public function index()
    {
        return view('github');
    }

public function downloadGithub(Request $request)
{
    if (!Tools::contains('github.com', $request->github_link)) {
        return $this->respond($request, 'An invalid repository link has been submitted!', 'error');
    }

    try {
        $user_id = Auth::check() ? Auth::id() : 0;

        $github_link = rtrim($request->github_link, '/');
        $branch = preg_replace('/[^a-zA-Z0-9_\-]/', '', $request->branch);

        // Validação extra de URL
        if (!filter_var($github_link, FILTER_VALIDATE_URL)) {
            return $this->respond($request, 'Invalid GitHub URL format.', 'error');
        }

        $url = "{$github_link}/archive/{$branch}.zip";

        // Usa HTTP Client com timeout e validação
        $response = Http::timeout(10)->get($url);

        if (!$response->ok() || !$response->header('Content-Type') || !str_contains($response->header('Content-Type'), 'application/zip')) {
            return $this->respond($request, 'Failed to download valid zip file.', 'error');
        }

        $uniqueZipName = 'repo_' . Str::uuid() . '.zip';
        $name = self::GITHUB_FOLDER . $uniqueZipName;
        Storage::put($name, $response->body());

        $projectFolderName = 'project_' . Str::uuid();
        $file_location = base_path(self::STORAGE_PATH . self::GITHUB_FOLDER . $projectFolderName);

        Zipper::make(base_path(self::STORAGE_PATH . $name))->extractTo($file_location);
        unlink(base_path(self::STORAGE_PATH . $name));

        // Salva no banco
        $file = new File();
        $file->user_id = $user_id;
        $file->file_path = self::GITHUB_FOLDER . $projectFolderName;
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
            return view('github', compact('file', 'file_contents'));
        }

        return response()->json($this->getResultArray($file, $file_contents));

    } catch (\Exception $e) {
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
            $relative_path = explode(self::STORAGE_PATH, $full_file_path);
            $file_path = end($relative_path);

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
