@extends('layout.app')

@section('content')

    <form action="{{route('files.store')}}" method="post" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-12 mb-3">
                <label>File</label>
                <input type="file" name="file" class="form-control">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </div>


    </form>


@endsection
