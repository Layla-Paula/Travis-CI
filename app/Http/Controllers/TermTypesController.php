<?php

namespace SegWeb\Http\Controllers;

use SegWeb\TermTypes;
use Illuminate\Http\Request;

class TermTypesController extends Controller {
    public function index() {
        return view('term_types', ['term_types'=>$this->getAll(), 'term_type'=>NULL]);
    }

    public function store(Request $request) {
        if(empty($request->id)) {
            $term_type = new TermTypes();
            $msg['text'] = 'Term category successfully inserted!';
        } else {
            $term_type = TermTypes::find($request->id);
            $msg['text'] = 'Term category successfully updated!';
        }
        $term_type->term_type = $request->term_type;
        $term_type->color = $request->color;
        $term_type->save();
        $msg['type'] = 'success';
        $term_types = $this->getAll();
        return view('term_types', compact('msg', 'term_types'));
    }

    public function edit($id) {
        $term_type = TermTypes::findOrFail($id);
        $term_types = $this->getAll();
        return view('term_types', compact('term_types', 'term_type'));
    }

    public function getAll() {
        return TermTypes::all();
    }
}
