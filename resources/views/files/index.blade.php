@extends('layout.app')

@section('content')

    <div class="mt-5">
        <div class="row ">
            <div class="col-10">
                <p>List of files uploded to telegram </p>
                <small>Click Upload to upload new file</small>
            </div>
            <div class="col-2">
                <a href="{{route('files.create')}}" class="btn btn-primary">Upload</a>
            </div>
        </div>


        <table class="table">
            <thead>
            <tr>
                <th>Thumb</th>
                <th>name</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>


            @foreach($files as $file)
                <tr>
                    <td><img src="data:image/jpg;base64,{{$file->thumb}}" alt="{{$file->file_name}}" width="50" height="50" class="rounded img-fluid">
                    </td>
                    <td><p>{{$file->file_name}}</p></td>
                    <td><a href="{{route('files.download',$file->file_id)}}" type="button" class="btn btn-primary">Download</a>
                    <form action="{{route('files.destroy',$file->file_id)}}" method="post">
                        @csrf
                        @method('delete')
                        <button  type="submit" class="btn btn-danger">Delete</button>
                    </form>
                    </td>
                </tr>


            @endforeach
            </tbody>
        </table>

    </div>



@endsection
