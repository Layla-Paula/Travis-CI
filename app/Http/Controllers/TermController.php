<?php

namespace SegWeb\Http\Controllers;

use Illuminate\Http\Request;
use SegWeb\Terms;
use SegWeb\TermTypes;
use Illuminate\Support\Facades\DB;

class TermController extends Controller {
    
    public function index() {
        return view('terms', ['terms'=>$this->getAllJoinTermTypes(), 'term'=>NULL, 'term_types'=>TermTypes::all()]);
    }

    public function store(Request $request) {
        if(empty($request->id)) {
            $term = new Terms();
            $msg['text'] = 'Term successfully inserted!';
        } else {
            $term = Terms::find($request->id);
            $msg['text'] = 'Term successfully updated!';
        }
        $term->term = $request->term;
        $term->term_type_id = $request->term_type;
        $term->save();
        $msg['type'] = 'success';
        $term_types = TermTypes::all();
        $terms = $this->getAllJoinTermTypes();
        return view('terms', compact('msg', 'term', 'terms', 'term_types'));
    }

    public function edit($id) {
        $term = Terms::findOrFail($id);
        $terms = $this->getAllJoinTermTypes();
        $term_types = TermTypes::all();
        return view('terms', compact('terms', 'term', 'term_types'));
    }

    public function getTerm($id=NULL) {
        return empty($id) ? Terms::get() : Terms::where('id', $id)->get();
    }

    public function getAll() {
        return Terms::all();
    }

    public function getAllJoinTermTypes() {
        return DB::table('terms')
                ->leftJoin('term_types', 'terms.term_type_id', '=', 'term_types.id')
                ->get(['terms.id', 'terms.term', 'term_types.term_type', 'terms.created_at']);
    }
}
