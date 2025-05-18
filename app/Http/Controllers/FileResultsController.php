<?php

namespace SegWeb\Http\Controllers;

use SegWeb\File;
use Illuminate\Support\Facades\DB;

class FileResultsController extends Controller {
    public function getAllByFileId($file_id) {
        $file = File::find($file_id);
        $return_collection = [];
        if($file->type == 'File') {
            $return_collection[] = DB::table('file_results')
                    ->leftJoin('terms', 'file_results.term_id', '=', 'terms.id')
                    ->leftJoin('term_types', 'terms.term_type_id', '=', 'term_types.id')
                    ->where('file_results.file_id', $file_id)
                    ->orderByRaw('line_number ASC')
                    ->get(['line_number', 'term', 'term_type', 'color', 'file_id']);
        } else {
            $fileController = new FileController();
            $github_files = $fileController->getGithubFiles($file_id);
            
            if(!empty($github_files)) {
                foreach($github_files as $github_file) {
                    $return_collection[] = DB::table('file_results')
                    ->leftJoin('terms', 'file_results.term_id', '=', 'terms.id')
                    ->leftJoin('term_types', 'terms.term_type_id', '=', 'term_types.id')
                    ->where('file_results.file_id', $github_file->id)
                    ->orderByRaw('line_number ASC')
                    ->get(['line_number', 'term', 'term_type', 'color', 'file_id']);
                }
            }
        }
        return $return_collection;
    }

    public function getSingleByFileId($file_id) {
        return DB::table('file_results')
                ->leftJoin('terms', 'file_results.term_id', '=', 'terms.id')
                ->leftJoin('term_types', 'terms.term_type_id', '=', 'term_types.id')
                ->where('file_results.file_id', $file_id)
                ->orderByRaw('line_number ASC')
                ->get(['line_number', 'term', 'term_type', 'color', 'file_id']);
    }

    public function showFileResults($file_id) {
        $file = File::find($file_id);
        $file_results_controller = new FileResultsController();
        $fileController = new FileController();
        $files_ids = [];
        if($file->type == 'File') {
            $file_contents[$file->id]['content'] = FileController::getFileContentArray($file->id);
            $file_contents[$file->id]['results'] = $file_results_controller->getSingleByFileId($file->id);
            $file_contents[$file->id]['file'] = FileController::getFileById($file->id);
        } else {
            $github_files = $fileController->getGithubFiles($file_id);
            if(!empty($github_files)) {
                foreach($github_files as $github_file) {
                    $file_contents[$github_file->id]['content'] = FileController::getFileContentArray($github_file->id);
                    $file_contents[$github_file->id]['results'] = $file_results_controller->getSingleByFileId($github_file->id);
                    $file_contents[$github_file->id]['file'] = FileController::getFileById($github_file->id);
                }
            }
        }
        return view('file_results', compact(['file', 'file_contents']));
    }
}

