@extends('templates.template')
@section('content')
@php
    use SegWeb\Http\Controllers\Tools;
    if(isset($term_type)) {
        $term = $term_type->term_type;
        $color = $term_type->color;
        $id = $term_type->id;
    } else {
        $term = NULL;
        $color = NULL;
        $id = NULL;
    }
@endphp
<div class="row mt-2">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <form action="/term_types" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$id}}">
                    <div class="card">
                        <div class="card-header text-center">
                            <h3>Term Categories Management</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="term_type">Category:</label><input type="text" name="term_type" class="form-control" value="{{$term}}" required>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <label for="color">Color:</label><input type="text" name="color" class="form-control" value="{{$color}}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary pull-right">Save <i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Registered Term Categories</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table">
                                @if (!empty($term_types))
                                    <table id="table_term_types" class="table table-hover table-bordered addDataTable">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Color</th>
                                                <th>Submitted Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($term_types as $term_type)
                                            <tr class="clickable" onclick="document.location='/term_types/{{$term_type->id}}'">
                                                <td>{{$term_type->term_type}}</td>
                                                <td>{{$term_type->color}}</td>
                                                <td>{{Tools::db_to_date_time($term_type->created_at)}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    No categories found!
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@php
    unset($term);
    unset($color);
    unset($id);
@endphp
@endsection