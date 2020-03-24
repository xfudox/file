<form action="{{route('files.upload')}}" enctype="multipart/form-data" method="POST">
    <p>
        <label for="file">
            <input type="file" name="file" id="file">
        </label>
    </p>
    <button>Upload</button>
    {{ csrf_field() }}
</form>

<ul>
    @if( count($files)>=1)
        @foreach($files as $file)
            <li><a href="{{$file['url']}}" >{{$file['disk']}}::{{$file['fullname']}}</a></li>
        @endforeach
    @else
        <li>No file present.</li>
    @endif
</ul>
