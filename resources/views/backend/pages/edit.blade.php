@extends('layouts.admin')
@section('content')

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex">
            <h6 class="m-0 font-weight-bold text-primary">Edit page ({{ $page->title }})</h6>
            <div class="ml-auto">
                <a href="{{ route('admin.pages.index') }}" class="btn btn-primary">
                    <span class="icon text-white-50">
                        <i class="fa fa-home"></i>
                    </span>
                    <span class="text">Pages</span>
                </a>
            </div>
        </div>
        <div class="card-body">

            {!! Form::model($page, ['route' => ['admin.pages.update', $page->id], 'method' => 'patch', 'files' => true]) !!}
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        {!! Form::label('title', 'Title') !!}
                        {!! Form::text('title', old('title', $page->title), ['class' => 'form-control']) !!}
                        @error('title')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        {!! Form::label('description', 'Description') !!}
                        {!! Form::textarea('description', old('description', $page->description), ['class' => 'form-control summernote']) !!}
                        @error('description')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    {!! Form::label('category_id', 'category_id') !!}
                    {!! Form::select('category_id', ['' => '---'] + $categories->toArray(), old('category_id', $page->category_id), ['class' => 'form-control']) !!}
                    @error('category_id')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-6">
                    {!! Form::label('status', 'status') !!}
                    {!! Form::select('status', ['1' => 'Active', '0' => 'Inactive'], old('status', $page->status), ['class' => 'form-control']) !!}
                    @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="row pt-4">
                <div class="col-12">
                    {!! Form::label('Sliders', 'images') !!}
                    <br>
                    <div class="file-loading">
                        {!! Form::file('image', ['class' => 'custom-file']) !!}
                        <span class="form-text text-muted">Image width should be 800px x 500px</span>
                        @error('image')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <div class="carousel-inner">
                @if($page->image)
                    <img src="{{$page->image}}" style="width: 400px; height: 250px;"
                         alt=""> </a>
                @else
                    NO image
                @endif

                <div class="form-group pt-4">
                    {!! Form::submit('Update page', ['class' => 'btn btn-primary']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        @endsection
        @section('script')
            <script>
                $(function () {
                    $('.summernote').summernote({
                        tabSize: 2,
                        height: 200,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'underline', 'clear']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture', 'video']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ]
                    });


                    $('#image').fileinput({
                        theme: "fa",
                        maxFileCount: 5,
                        allowedFileTypes: ['image'],
                        showCancel: true,
                        showRemove: false,
                        showUpload: false,
                        overwriteInitial: false,
                    });
                });
            </script>
@endsection
